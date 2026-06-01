<?php

namespace App\Services;

use App\Models\AdminWhatsappContact;
use App\Models\LetterTemplate;
use App\Models\RoomUsageRequest;
use App\Services\Letters\DocumentVerificationService;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

class WhatsAppNotificationService
{
    /**
     * Normalize a phone number to international format (62xxx).
     */
    public static function normalizePhoneNumber(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (blank($digits)) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        return $digits;
    }

    /**
     * Get the WhatsApp/phone number from a record, handling different column names.
     */
    public static function getPhoneFromRecord(Model $record): ?string
    {
        return self::firstFilledAttributeValue($record, [
            'phone_number',
            'phone',
            'student_phone',
            'whatsapp_number',
            'whatsapp',
            'nomor_telepon',
            'no_hp',
        ]) ?? self::firstFilledNestedArrayValue($record, [
            'student_list',
            'group_member',
        ], [
            'nomor_telepon',
            'phone_number',
            'phone',
            'whatsapp',
            'no_hp',
        ]);
    }

    /**
     * Get the student name from a record.
     */
    public static function getStudentName(Model $record): string
    {
        return self::firstFilledAttributeValue($record, [
            'student_name',
            'name',
            'nama_mahasiswa',
        ]) ?? self::firstFilledNestedArrayValue($record, [
            'student_list',
            'group_member',
        ], [
            'nama',
            'student_name',
            'name',
            'nama_mahasiswa',
        ]) ?? 'Mahasiswa';
    }

    /**
     * Resolve the letter type name based on the registered workflow or model class.
     */
    public static function getLetterTypeName(Model $record): string
    {
        try {
            $definition = app(DocumentVerificationService::class)->definitionForLetter($record);

            return LetterTemplate::LETTER_TYPES[$definition['letter_type']]
                ?? Str::headline(str_replace('_', ' ', $definition['letter_type']));
        } catch (Throwable) {
            return Str::headline(class_basename($record));
        }
    }

    /**
     * Build the message body for a submission notification to admin.
     */
    public static function buildSubmissionMessage(Model $record): string
    {
        if ($record instanceof RoomUsageRequest) {
            return self::buildRoomSubmissionMessage($record);
        }

        $studentName = self::getStudentName($record);
        $nim = self::getStudentNim($record);
        $letterType = self::getLetterTypeName($record);
        $submittedDate = self::formatRecordDate(now());

        return implode("\n", [
            "Konfirmasi Pengajuan Surat {$letterType}",
            '',
            'Yth. Admin Fakultas Teknik,',
            'Saya telah mengisi formulir pengajuan surat pada sistem website. Berikut adalah detail data saya:',
            "Nama: {$studentName}",
            "NIM: {$nim}",
            "Jenis Surat: {$letterType}",
            "Tanggal Pengajuan: {$submittedDate}",
            '',
            'Mohon kesediaannya untuk memeriksa dan memproses ajuan tersebut. Terima kasih.',
        ]);
    }

    /**
     * Build the message body for an APPROVED notification.
     */
    public static function buildApproveMessage(Model $record, ?string $documentUrl = null): string
    {
        $studentName = self::getStudentName($record);
        $letterType = self::getLetterTypeName($record);
        $destinationUrl = self::resolveDestinationUrl($documentUrl);

        return implode("\n", [
            "Notifikasi Sistem Informasi Surat - {$letterType}",
            "Halo, {$studentName}. Pengajuan surat Anda untuk:",
            "Jenis Surat: {$letterType}",
            'Status: DISETUJUI / SELESAI',
            "Anda dapat melihat dan mengunduh surat tersebut secara mandiri melalui tautan resmi berikut: 🔗 {$destinationUrl}",
            '(Surat ini dilengkapi dengan QR Code sebagai alat validasi keabsahan dokumen).',
            'Terima kasih.',
        ]);
    }

    /**
     * Build the message body for a REJECTED notification.
     */
    public static function buildRejectMessage(Model $record, string $rejectionReason = ''): string
    {
        $studentName = self::getStudentName($record);
        $letterType = self::getLetterTypeName($record);
        $destinationUrl = self::resolveResubmissionUrl($record);
        $reasonText = filled($rejectionReason) ? $rejectionReason : '-';

        return implode("\n", [
            "Pemberitahuan Status Ajuan Surat {$letterType}",
            "Halo, {$studentName}. Mohon maaf, pengajuan {$letterType} Anda saat ini DITOLAK.",
            "Catatan Admin: {$reasonText}",
            'Silakan mengajukan ulang melalui tautan berikut:',
            "🔗 {$destinationUrl}",
            'Terima kasih.',
        ]);
    }

    /**
     * Build the WhatsApp URL for a submission notification to admin.
     */
    public static function buildSubmissionUrl(Model $record): ?string
    {
        return self::buildWhatsAppUrl(
            self::getAdminPhoneNumber(),
            self::buildSubmissionMessage($record),
        );
    }

    /**
     * Build the WhatsApp URL for an APPROVED notification.
     */
    public static function buildApproveUrl(Model $record, ?string $documentUrl = null): ?string
    {
        return self::buildWhatsAppUrl(
            self::getPhoneFromRecord($record),
            self::buildApproveMessage($record, $documentUrl),
        );
    }

    /**
     * Build the WhatsApp URL for a REJECTED notification.
     */
    public static function buildRejectUrl(Model $record, string $rejectionReason = ''): ?string
    {
        return self::buildWhatsAppUrl(
            self::getPhoneFromRecord($record),
            self::buildRejectMessage($record, $rejectionReason),
        );
    }

    private static function buildWhatsAppUrl(?string $phone, string $message): ?string
    {
        $normalizedPhone = self::normalizePhoneNumber($phone);

        if (! $normalizedPhone) {
            return null;
        }

        $baseUrl = rtrim((string) config('services.whatsapp.base_url', 'https://wa.me'), '/');

        return "{$baseUrl}/{$normalizedPhone}?text=".urlencode($message);
    }

    private static function resolveDestinationUrl(?string $documentUrl = null): string
    {
        if (filled($documentUrl)) {
            return $documentUrl;
        }

        return (string) config('services.whatsapp.app_url', config('app.url'));
    }

    private static function resolveResubmissionUrl(Model $record): string
    {
        try {
            $definition = app(DocumentVerificationService::class)->definitionForLetter($record);

            if ($record instanceof RoomUsageRequest) {
                return route('booking');
            }

            return route('form', ['letterType' => $definition['letter_type']]);
        } catch (Throwable) {
            return self::resolveDestinationUrl();
        }
    }

    private static function getAdminPhoneNumber(): ?string
    {
        $phone = AdminWhatsappContact::query()
            ->whereNotNull('whatsapp_number')
            ->where('whatsapp_number', '!=', '')
            ->orderBy('id')
            ->value('whatsapp_number');

        if (! is_string($phone)) {
            return null;
        }

        return trim($phone);
    }

    private static function getStudentNim(Model $record): string
    {
        return self::firstFilledAttributeValue($record, [
            'nim',
            'student_nim',
            'mahasiswa_nim',
        ]) ?? self::firstFilledNestedArrayValue($record, [
            'student_list',
            'group_member',
        ], [
            'nim',
            'student_nim',
            'mahasiswa_nim',
        ]) ?? '-';
    }

    private static function buildRoomSubmissionMessage(RoomUsageRequest $record): string
    {
        $studentName = self::getStudentName($record);
        $nim = self::getStudentNim($record);
        $record->loadMissing('slots.room');
        $roomName = trim((string) ($record->resolved_room_name ?: 'Ruang'));
        $submittedDate = self::formatRecordDate(now());
        $bookingDate = self::buildRoomBookingDateLabel($record);
        $agenda = trim((string) ($record->activity_name ?? '-'));
        $slotDetails = self::buildRoomSlotLines($record);

        return implode("\n", [
            "Konfirmasi Pengajuan Peminjaman Ruang {$roomName}",
            '',
            'Yth. Admin Fakultas Teknik,',
            'Saya telah mengisi formulir pengajuan pinjam ruang pada sistem website. Berikut adalah detail data saya:',
            "Nama: {$studentName}",
            "NIM: {$nim}",
            "Ruang: {$roomName}",
            "Detail Ruang/Jam: {$slotDetails}",
            "Tanggal Pengajuan: {$submittedDate}",
            "Tanggal Peminjaman: {$bookingDate}",
            "Agenda: {$agenda}",
            '',
            'Mohon kesediaannya untuk memeriksa dan memproses ajuan tersebut. Terima kasih.',
        ]);
    }

    private static function buildRoomSlotLines(RoomUsageRequest $record): string
    {
        if ($record->slots->isEmpty()) {
            return '-';
        }

        return $record->slots
            ->sortBy('start_at')
            ->map(function ($slot): string {
                $roomName = trim((string) ($slot->room_name_snapshot ?: ($slot->room?->name ?? 'Ruang')));
                $start = $slot->start_at ? $slot->start_at->format('H:i') : '-';
                $end = $slot->end_at ? $slot->end_at->format('H:i') : '-';

                return "{$roomName} ({$start}-{$end})";
            })
            ->values()
            ->unique()
            ->join(', ');
    }

    private static function buildRoomBookingDateLabel(RoomUsageRequest $record): string
    {
        if ($record->slots->isEmpty()) {
            return self::formatRecordDate($record->start_at);
        }

        $bookingDates = $record->slots
            ->pluck('booking_date')
            ->filter()
            ->sort()
            ->map(fn ($date): string => self::formatRecordDate($date))
            ->unique()
            ->values();

        if ($bookingDates->isEmpty()) {
            return self::formatRecordDate($record->start_at);
        }

        if ($bookingDates->count() === 1) {
            return (string) $bookingDates->first();
        }

        return sprintf(
            '%s - %s',
            $bookingDates->first(),
            $bookingDates->last(),
        );
    }

    private static function formatRecordDate(null|string|CarbonInterface $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->locale('id')->translatedFormat('d F Y');
        }

        if (blank($value)) {
            return '-';
        }

        try {
            return Carbon::parse($value)->locale('id')->translatedFormat('d F Y');
        } catch (Throwable) {
            return (string) $value;
        }
    }

    private static function firstFilledAttributeValue(Model $record, array $attributes): ?string
    {
        foreach ($attributes as $attribute) {
            $value = $record->getAttribute($attribute);

            if (filled($value)) {
                return trim((string) $value);
            }
        }

        return null;
    }

    private static function firstFilledNestedArrayValue(Model $record, array $attributes, array $keys): ?string
    {
        foreach ($attributes as $attribute) {
            $items = $record->getAttribute($attribute);

            if (! is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                foreach ($keys as $key) {
                    $value = $item[$key] ?? null;

                    if (filled($value)) {
                        return trim((string) $value);
                    }
                }
            }
        }

        return null;
    }
}
