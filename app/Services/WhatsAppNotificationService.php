<?php

namespace App\Services;

use App\Models\LetterTemplate;
use App\Services\Letters\DocumentVerificationService;
use Illuminate\Database\Eloquent\Model;
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
     * Build the message body for an APPROVED notification.
     */
    public static function buildApproveMessage(Model $record, ?string $documentUrl = null): string
    {
        $studentName = self::getStudentName($record);
        $letterType = self::getLetterTypeName($record);
        $destinationUrl = self::resolveDestinationUrl($documentUrl);
        $letterNumber = (string) ($record->getAttribute('letter_number') ?? '');

        $message = [
            "Notifikasi E-Service Fakultas Teknik - {$letterType}",
            '',
            "Halo, *{$studentName}*.",
            "Pengajuan surat Anda untuk *{$letterType}* telah *DISETUJUI / SELESAI*.",
        ];

        if ($letterNumber !== '') {
            $message[] = "Nomor surat: *{$letterNumber}*";
        }

        $message = [
            ...$message,
            '',
            'Anda dapat melihat atau mengunduh dokumen melalui tautan berikut:',
            "🔗 {$destinationUrl}",
            '',
            'Dokumen ini dilengkapi QR Code untuk verifikasi keabsahan surat.',
            '',
            'Terima kasih.',
        ];

        return implode("\n", $message);
    }

    /**
     * Build the message body for a REJECTED notification.
     */
    public static function buildRejectMessage(Model $record, string $rejectionReason = ''): string
    {
        $studentName = self::getStudentName($record);
        $letterType = self::getLetterTypeName($record);
        $destinationUrl = self::resolveDestinationUrl();
        $reasonText = filled($rejectionReason) ? $rejectionReason : '-';

        return implode("\n", [
            "Pemberitahuan Status Ajuan - {$letterType}",
            '',
            "Halo, *{$studentName}*.",
            "Mohon maaf, pengajuan *{$letterType}* Anda saat ini *DITOLAK*.",
            '',
            "*Catatan Admin:* {$reasonText}",
            '',
            'Silakan perbaiki data Anda dan lakukan pengajuan ulang melalui tautan berikut:',
            "🔗 {$destinationUrl}",
            '',
            'Terima kasih.',
        ]);
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
}
