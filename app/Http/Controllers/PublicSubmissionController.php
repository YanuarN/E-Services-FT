<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomUsageRequest;
use App\Models\RoomUsageRequestSlot;
use App\Services\Letters\DocumentVerificationService;
use App\Services\WhatsAppNotificationService;
use App\Support\PublicServiceCatalog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PublicSubmissionController extends Controller
{
    public function storeLetter(Request $request, string $letterType): RedirectResponse
    {
        $service = PublicServiceCatalog::find($letterType);
        abort_if(! $service, 404);

        $validated = $request->validate($this->letterRules($letterType));
        $definition = app(DocumentVerificationService::class)->definitions()[$letterType] ?? null;
        abort_if(! $definition, 404);

        $modelClass = $definition['model'];
        $record = $modelClass::query()->create($this->buildLetterPayload($letterType, $validated));

        return to_route('form', ['letterType' => $letterType])
            ->with('success', 'Pengajuan surat berhasil dikirim. Seluruh proses dan hasil surat akan diinformasikan melalui WhatsApp.')
            ->with('whatsappUrl', WhatsAppNotificationService::buildSubmissionUrl($record));
    }

    public function storeRoomBooking(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:255'],
            'study_program' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:255'],
            'activity_name' => ['required', 'string', 'max:500'],
            'number_of_participants' => ['required', 'integer', 'min:1'],
            'selected_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_slots' => ['required', 'array', 'min:1'],
            'booking_slots.*.room_id' => ['required', 'integer', Rule::exists('rooms', 'id')],
            'booking_slots.*.start_time' => ['required', 'date_format:H:i'],
            'booking_slots.*.end_time' => ['required', 'date_format:H:i'],
            'document' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        $bookingDate = Carbon::parse($validated['selected_date'], config('app.timezone'))->toDateString();
        $roomMap = Room::query()
            ->whereIn('id', collect($validated['booking_slots'])->pluck('room_id')->unique()->values())
            ->pluck('name', 'id');
        $slots = $this->normalizeBookingSlots($validated['booking_slots'], $bookingDate, $roomMap->all());
        $conflicts = $this->findConflictedSlots($slots, $bookingDate);

        if ($conflicts->isNotEmpty()) {
            $conflictSummary = $conflicts
                ->map(fn (array $slot): string => sprintf(
                    '%s (%s-%s)',
                    $slot['room_name'],
                    $slot['start_time'],
                    $slot['end_time'],
                ))
                ->unique()
                ->values()
                ->join(', ');

            return back()
                ->withErrors([
                    'booking_slots' => "Jadwal bentrok dengan booking aktif: {$conflictSummary}.",
                ])
                ->withInput()
                ->with('roomBookingConflicts', $conflicts->values()->all());
        }

        $documentPath = Storage::disk('local')->putFile('room-usage-requests', $request->file('document'));

        $record = DB::transaction(function () use ($validated, $slots, $documentPath): RoomUsageRequest {
            $firstSlot = $slots->first();
            $startAt = $slots->min('start_at');
            $endAt = $slots->max('end_at');
            $roomNames = $slots->pluck('room_name')->filter()->unique()->values()->join(', ');

            $requestRecord = RoomUsageRequest::query()->create([
                'student_name' => $validated['student_name'],
                'nim' => $validated['nim'],
                'study_program' => $validated['study_program'],
                'phone_number' => $validated['phone_number'],
                'unit' => $validated['unit'],
                'activity_name' => $validated['activity_name'],
                'start_at' => $startAt,
                'end_at' => $endAt,
                'room_id' => $firstSlot['room_id'],
                'room_name' => $roomNames,
                'number_of_participants' => $validated['number_of_participants'],
                'status' => 'PENDING',
                'document' => $documentPath,
            ]);

            $requestRecord->slots()->createMany(
                $slots->map(fn (array $slot): array => [
                    'room_id' => $slot['room_id'],
                    'room_name_snapshot' => $slot['room_name'],
                    'booking_date' => $slot['booking_date'],
                    'start_at' => $slot['start_at'],
                    'end_at' => $slot['end_at'],
                ])->values()->all()
            );

            return $requestRecord->loadMissing('slots.room');
        });

        return to_route('booking')
            ->with('success', 'Pengajuan booking ruangan berhasil dikirim. Informasi lanjutan akan dikirim melalui WhatsApp.')
            ->with('whatsappUrl', WhatsAppNotificationService::buildSubmissionUrl($record));
    }

    public function roomBookingsByDate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'selected_date' => ['required', 'date'],
        ]);

        $bookingDate = Carbon::parse($validated['selected_date'], config('app.timezone'))->toDateString();

        $bookings = RoomUsageRequestSlot::query()
            ->with([
                'room:id,name',
                'roomUsageRequest:id,student_name,activity_name,unit,status',
            ])
            ->where('booking_date', $bookingDate)
            ->whereHas('roomUsageRequest', function ($query): void {
                $query->whereIn('status', ['PENDING', 'APPROVED']);
            })
            ->orderBy('start_at')
            ->get()
            ->map(function (RoomUsageRequestSlot $slot): array {
                $requestRecord = $slot->roomUsageRequest;
                $roomName = $slot->room_name_snapshot ?: (string) ($slot->room?->name ?? 'Ruang');

                return [
                    'id' => $slot->id,
                    'requestId' => $requestRecord?->id,
                    'roomId' => $slot->room_id,
                    'roomName' => $roomName,
                    'studentName' => $requestRecord?->student_name ?? '-',
                    'activityName' => $requestRecord?->activity_name ?? '-',
                    'unit' => $requestRecord?->unit ?? '',
                    'start' => $slot->start_at?->toIso8601String(),
                    'end' => $slot->end_at?->toIso8601String(),
                    'status' => (string) ($requestRecord?->status ?? 'PENDING'),
                ];
            })
            ->values();

        return response()->json([
            'data' => $bookings,
        ]);
    }

    /**
     * @param  array<int, array{room_id:int, start_time:string, end_time:string}>  $rawSlots
     * @param  array<int, string>  $roomMap
     * @return Collection<int, array{room_id:int, room_name:string, booking_date:string, start_at:Carbon, end_at:Carbon}>
     *
     * @throws ValidationException
     */
    private function normalizeBookingSlots(array $rawSlots, string $bookingDate, array $roomMap): Collection
    {
        $timezone = config('app.timezone');
        $slots = collect();
        $duplicateKeys = [];

        foreach ($rawSlots as $index => $slot) {
            $startAt = Carbon::createFromFormat('Y-m-d H:i', "{$bookingDate} {$slot['start_time']}", $timezone);
            $endAt = Carbon::createFromFormat('Y-m-d H:i', "{$bookingDate} {$slot['end_time']}", $timezone);

            if ($endAt->lessThanOrEqualTo($startAt)) {
                throw ValidationException::withMessages([
                    "booking_slots.{$index}.end_time" => 'Jam selesai harus lebih besar dari jam mulai.',
                ]);
            }

            $duplicateKey = implode('|', [
                $slot['room_id'],
                $startAt->format('Y-m-d H:i:s'),
                $endAt->format('Y-m-d H:i:s'),
            ]);

            if (isset($duplicateKeys[$duplicateKey])) {
                throw ValidationException::withMessages([
                    "booking_slots.{$index}.room_id" => 'Slot ruangan duplikat tidak diperbolehkan.',
                ]);
            }

            $duplicateKeys[$duplicateKey] = true;

            $slots->push([
                'room_id' => (int) $slot['room_id'],
                'room_name' => (string) ($roomMap[(int) $slot['room_id']] ?? 'Ruang'),
                'booking_date' => $bookingDate,
                'start_at' => $startAt,
                'end_at' => $endAt,
            ]);
        }

        return $slots;
    }

    /**
     * @param  Collection<int, array{room_id:int, room_name:string, booking_date:string, start_at:Carbon, end_at:Carbon}>  $slots
     * @return Collection<int, array{room_id:int, room_name:string, start_time:string, end_time:string}>
     */
    private function findConflictedSlots(Collection $slots, string $bookingDate): Collection
    {
        if ($slots->isEmpty()) {
            return collect();
        }

        $conflicts = RoomUsageRequestSlot::query()
            ->join(
                'room_usage_requests',
                'room_usage_requests.id',
                '=',
                'room_usage_request_slots.room_usage_request_id',
            )
            ->whereIn('room_usage_requests.status', ['PENDING', 'APPROVED'])
            ->where('room_usage_request_slots.booking_date', $bookingDate)
            ->where(function ($query) use ($slots): void {
                foreach ($slots as $slot) {
                    $query->orWhere(function ($innerQuery) use ($slot): void {
                        $innerQuery
                            ->where('room_usage_request_slots.room_id', $slot['room_id'])
                            ->where('room_usage_request_slots.start_at', '<', $slot['end_at'])
                            ->where('room_usage_request_slots.end_at', '>', $slot['start_at']);
                    });
                }
            })
            ->get([
                'room_usage_request_slots.room_id',
                'room_usage_request_slots.room_name_snapshot',
                'room_usage_request_slots.start_at',
                'room_usage_request_slots.end_at',
            ]);

        return $conflicts
            ->map(fn ($conflict): array => [
                'room_id' => (int) $conflict->room_id,
                'room_name' => (string) ($conflict->room_name_snapshot ?? 'Ruang'),
                'start_time' => Carbon::parse($conflict->start_at, config('app.timezone'))->format('H:i'),
                'end_time' => Carbon::parse($conflict->end_at, config('app.timezone'))->format('H:i'),
            ])
            ->values();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function letterRules(string $letterType): array
    {
        $common = [
            'student_name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:255'],
            'study_program' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:255'],
        ];

        return match ($letterType) {
            'exam_permission' => [
                'student_name' => ['required', 'string', 'max:255'],
                'nim' => ['required', 'string', 'max:255'],
                'company_name' => ['required', 'string', 'max:255'],
                'company_address' => ['required', 'string'],
                'group_member' => ['nullable', 'string'],
                'exam' => ['required', 'string', 'max:255'],
                'semester' => ['required', 'string', 'max:255'],
                'exam_date' => ['required', 'date'],
            ],
            'internship' => [
                ...$common,
                'company_name' => ['required', 'string', 'max:255'],
                'company_address' => ['required', 'string'],
                'group_member' => ['nullable', 'string'],
            ],
            'internship_recommendation' => [
                ...$common,
                'semester' => ['required', 'string', 'max:255'],
                'ipk' => ['required', 'numeric', 'min:0', 'max:4'],
                'program_name' => ['required', 'string', 'max:255'],
            ],
            'letter_of_assignment' => [
                'activity_date' => ['required', 'string', 'max:255'],
                'activity_time' => ['required', 'string', 'max:255'],
                'activity' => ['required', 'string'],
                'assigment' => ['required', 'string'],
                'place' => ['required', 'string', 'max:255'],
                'student_list' => ['nullable', 'string'],
            ],
            'letter_of_assignment_individual' => [
                'student_name' => ['required', 'string', 'max:255'],
                'nim' => ['required', 'string', 'max:255'],
                'phone_number' => ['required', 'string', 'max:255'],
                'departement' => ['required', 'string', 'max:255'],
                'faculty' => ['required', 'string', 'max:255'],
                'assignment' => ['required', 'string'],
                'place' => ['required', 'string', 'max:255'],
                'date' => ['required', 'date'],
            ],
            'passport_application' => [
                ...$common,
                'event_name' => ['required', 'string', 'max:255'],
            ],
            'research_data_request' => [
                ...$common,
                'company_name' => ['required', 'string', 'max:255'],
                'company_address' => ['required', 'string'],
                'group_member' => ['nullable', 'string'],
            ],
            'research_permission', 'testing_permission_request' => [
                ...$common,
                'company_name' => ['required', 'string', 'max:255'],
                'company_address' => ['required', 'string'],
                'group_member' => ['nullable', 'string'],
            ],
            'scholarships_statement' => [
                ...$common,
                'scolarship_name' => ['required', 'string', 'max:255'],
                'scolarship_provider' => ['required', 'string', 'max:255'],
            ],
            default => abort(404),
        };
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildLetterPayload(string $letterType, array $validated): array
    {
        return match ($letterType) {
            'exam_permission' => [
                'status' => 'SUBMITTED',
                'name' => $validated['student_name'],
                'nim' => $validated['nim'],
                'company_name' => $validated['company_name'],
                'company_address' => $validated['company_address'],
                'group_member' => $this->memberLinesToArray((string) ($validated['group_member'] ?? '')),
                'exam' => $validated['exam'],
                'semester' => $validated['semester'],
                'date' => $validated['exam_date'],
            ],
            'internship' => [
                'status' => 'SUBMITTED',
                'student_name' => $validated['student_name'],
                'nim' => $validated['nim'],
                'study_program' => $validated['study_program'],
                'phone_number' => $validated['phone_number'],
                'company_name' => $validated['company_name'],
                'company_address' => $validated['company_address'],
                'group_member' => $this->memberLinesToArray((string) ($validated['group_member'] ?? '')),
            ],
            'internship_recommendation' => [
                'status' => 'SUBMITTED',
                'student_name' => $validated['student_name'],
                'nim' => $validated['nim'],
                'study_program' => $validated['study_program'],
                'semester' => $validated['semester'],
                'ipk' => $validated['ipk'],
                'program_name' => $validated['program_name'],
                'phone_number' => $validated['phone_number'],
            ],
            'letter_of_assignment' => [
                'status' => 'SUBMITTED',
                'date' => $validated['activity_date'],
                'time' => $validated['activity_time'],
                'activity' => $validated['activity'],
                'assigment' => $validated['assigment'],
                'place' => $validated['place'],
                'student_list' => $this->memberLinesToArray((string) ($validated['student_list'] ?? '')),
            ],
            'letter_of_assignment_individual' => [
                'status' => 'SUBMITTED',
                'name' => $validated['student_name'],
                'nim' => $validated['nim'],
                'phone_number' => $validated['phone_number'],
                'departement' => $validated['departement'],
                'faculty' => $validated['faculty'],
                'assignment' => $validated['assignment'],
                'place' => $validated['place'],
                'date' => $validated['date'],
            ],
            'passport_application' => [
                'status' => 'SUBMITTED',
                'student_name' => $validated['student_name'],
                'study_program' => $validated['study_program'],
                'nim' => $validated['nim'],
                'phone_number' => $validated['phone_number'],
                'event_name' => $validated['event_name'],
            ],
            'research_data_request' => [
                'status' => 'SUBMITTED',
                'student_name' => $validated['student_name'],
                'nim' => $validated['nim'],
                'study_program' => $validated['study_program'],
                'phone_number' => $validated['phone_number'],
                'company_name' => $validated['company_name'],
                'company_address' => $validated['company_address'],
                'group_member' => $this->memberLinesToArray((string) ($validated['group_member'] ?? '')),
            ],
            'research_permission', 'testing_permission_request' => [
                'status' => 'SUBMITTED',
                'student_name' => $validated['student_name'],
                'nim' => $validated['nim'],
                'study_program' => $validated['study_program'],
                'phone_number' => $validated['phone_number'],
                'company_name' => $validated['company_name'],
                'company_address' => $validated['company_address'],
                'group_member' => $this->memberLinesToArray((string) ($validated['group_member'] ?? '')),
            ],
            'scholarships_statement' => [
                'status' => 'SUBMITTED',
                'student_name' => $validated['student_name'],
                'study_program' => $validated['study_program'],
                'nim' => $validated['nim'],
                'scolarship_name' => $validated['scolarship_name'],
                'scolarship_provider' => $validated['scolarship_provider'],
                'phone_number' => $validated['phone_number'],
            ],
            default => abort(404),
        };
    }

    /**
     * @return array<int, string>
     */
    private function linesToArray(string $value): array
    {
        return Collection::make(preg_split('/\r\n|\n|\r/', $value))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{nama: string, nim: string, program_studi: string, nomor_telepon: string}>
     */
    private function memberLinesToArray(string $value): array
    {
        return Collection::make($this->linesToArray($value))
            ->map(function (string $line): array {
                [$name, $nim, $studyProgram, $phoneNumber] = array_pad(
                    array_map('trim', explode('-', $line, 4)),
                    4,
                    '',
                );

                return [
                    'nama' => $name,
                    'nim' => $nim,
                    'program_studi' => $studyProgram,
                    'nomor_telepon' => $phoneNumber,
                ];
            })
            ->filter(fn (array $member): bool => filled($member['nama']) || filled($member['nim']))
            ->values()
            ->all();
    }
}
