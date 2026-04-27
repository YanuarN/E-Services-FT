<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomUsageRequest;
use App\Services\Letters\DocumentVerificationService;
use App\Services\WhatsAppNotificationService;
use App\Support\PublicServiceCatalog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
            'room_id' => ['required', 'integer', Rule::exists('rooms', 'id')],
            'number_of_participants' => ['required', 'integer', 'min:1'],
            'selected_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'document' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        $startAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            "{$validated['selected_date']} {$validated['start_time']}",
            config('app.timezone')
        );
        $endAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            "{$validated['selected_date']} {$validated['end_time']}",
            config('app.timezone')
        );

        $room = Room::query()->findOrFail($validated['room_id']);

        $hasConflict = RoomUsageRequest::query()
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->where('room_id', $room->id)
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->exists();

        if ($hasConflict) {
            return back()
                ->withErrors([
                    'start_time' => 'Jam yang dipilih bentrok dengan jadwal booking lain. Silakan pilih jam lain.',
                ])
                ->withInput();
        }

        $documentPath = Storage::disk('local')->putFile('room-usage-requests', $request->file('document'));

        $record = RoomUsageRequest::query()->create([
            'student_name' => $validated['student_name'],
            'nim' => $validated['nim'],
            'study_program' => $validated['study_program'],
            'phone_number' => $validated['phone_number'],
            'unit' => $validated['unit'],
            'activity_name' => $validated['activity_name'],
            'start_at' => $startAt,
            'end_at' => $endAt,
            'room_id' => $room->id,
            'room_name' => $room->name,
            'number_of_participants' => $validated['number_of_participants'],
            'status' => 'PENDING',
            'document' => $documentPath,
        ]);

        return to_route('booking')
            ->with('success', 'Pengajuan booking ruangan berhasil dikirim. Informasi lanjutan akan dikirim melalui WhatsApp.')
            ->with('whatsappUrl', WhatsAppNotificationService::buildSubmissionUrl($record));
    }

    public function roomBookings(Room $room): JsonResponse
    {
        $bookings = RoomUsageRequest::query()
            ->where('room_id', $room->id)
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->where('end_at', '>=', Carbon::today(config('app.timezone'))->startOfDay())
            ->orderBy('start_at')
            ->get([
                'id',
                'room_id',
                'room_name',
                'student_name',
                'activity_name',
                'unit',
                'start_at',
                'end_at',
                'status',
            ])
            ->map(fn (RoomUsageRequest $booking) => [
                'id' => $booking->id,
                'roomId' => $booking->room_id,
                'roomName' => $booking->resolved_room_name,
                'studentName' => $booking->student_name,
                'activityName' => $booking->activity_name,
                'unit' => $booking->unit,
                'start' => $booking->start_at?->toIso8601String(),
                'end' => $booking->end_at?->toIso8601String(),
                'status' => $booking->status,
            ])
            ->values();

        return response()->json([
            'data' => $bookings,
        ]);
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
                'exam' => ['required', 'string', 'max:255'],
                'semester' => ['required', 'string', 'max:255'],
                'exam_date' => ['required', 'date'],
            ],
            'internship' => [
                ...$common,
                'company_name' => ['required', 'string', 'max:255'],
                'company_address' => ['required', 'string'],
                'group_member' => ['required', 'string'],
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
                'place' => ['required', 'string', 'max:255'],
                'student_list' => ['required', 'string'],
            ],
            'letter_of_assignment_individual' => [
                'student_name' => ['required', 'string', 'max:255'],
                'nim' => ['required', 'string', 'max:255'],
                'departement' => ['required', 'string', 'max:255'],
                'faculty' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string'],
                'assignment' => ['required', 'string'],
                'place' => ['required', 'string', 'max:255'],
                'date' => ['required', 'date'],
            ],
            'passport_application' => [
                ...$common,
                'event_name' => ['required', 'string', 'max:255'],
            ],
            'research_data_request', 'research_permission', 'testing_permission_request' => [
                ...$common,
                'company_name' => ['required', 'string', 'max:255'],
                'company_address' => ['required', 'string'],
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
                'group_member' => $this->linesToArray($validated['group_member']),
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
                'place' => $validated['place'],
                'student_list' => $this->linesToArray($validated['student_list']),
            ],
            'letter_of_assignment_individual' => [
                'status' => 'SUBMITTED',
                'name' => $validated['student_name'],
                'nim' => $validated['nim'],
                'departement' => $validated['departement'],
                'faculty' => $validated['faculty'],
                'address' => $validated['address'],
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
            'research_data_request', 'research_permission', 'testing_permission_request' => [
                'status' => 'SUBMITTED',
                'student_name' => $validated['student_name'],
                'nim' => $validated['nim'],
                'study_program' => $validated['study_program'],
                'phone_number' => $validated['phone_number'],
                'company_name' => $validated['company_name'],
                'company_address' => $validated['company_address'],
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
}
