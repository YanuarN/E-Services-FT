<?php

namespace Tests\Unit;

use App\Models\ExamPermissionLetter;
use App\Models\ResearchPermissionLetter;
use App\Models\RoomUsageRequest;
use App\Services\WhatsAppNotificationService;
use Tests\TestCase;

class WhatsAppNotificationServiceTest extends TestCase
{
    public function test_it_normalizes_indonesian_phone_numbers(): void
    {
        $this->assertSame('628123456789', WhatsAppNotificationService::normalizePhoneNumber('0812-3456-789'));
        $this->assertSame('628123456789', WhatsAppNotificationService::normalizePhoneNumber('+62 812-3456-789'));
        $this->assertNull(WhatsAppNotificationService::normalizePhoneNumber(''));
    }

    public function test_it_builds_approve_url_for_registered_letter_model(): void
    {
        $record = new ResearchPermissionLetter([
            'student_name' => 'Siti Rahma',
            'phone_number' => '081234567890',
            'letter_number' => 'FT/045/IV/2026',
        ]);

        $url = WhatsAppNotificationService::buildApproveUrl(
            $record,
            'https://e-service-ft.test/verification/research_permission/VERIFYTOKEN12345',
        );
        $decodedUrl = urldecode((string) $url);

        $this->assertNotNull($url);
        $this->assertStringContainsString('https://wa.me/6281234567890?text=', $url);
        $this->assertStringContainsString('Siti Rahma', $decodedUrl);
        $this->assertStringContainsString('Surat Izin Survey Untuk Penelitian', $decodedUrl);
        $this->assertStringContainsString('FT/045/IV/2026', $decodedUrl);
    }

    public function test_it_builds_reject_url_for_registered_room_usage_request(): void
    {
        $record = new RoomUsageRequest([
            'student_name' => 'Budi Santoso',
            'phone_number' => '081300000000',
        ]);

        $url = WhatsAppNotificationService::buildRejectUrl($record, 'Lampiran belum lengkap');
        $decodedUrl = urldecode((string) $url);

        $this->assertNotNull($url);
        $this->assertStringContainsString('https://wa.me/6281300000000?text=', $url);
        $this->assertStringContainsString('Formulir Peminjaman Ruangan', $decodedUrl);
        $this->assertStringContainsString('Lampiran belum lengkap', $decodedUrl);
    }

    public function test_it_returns_null_when_record_has_no_phone_number(): void
    {
        $record = new ExamPermissionLetter([
            'name' => 'Nadia Putri',
            'nim' => '221223344',
        ]);

        $this->assertNull(WhatsAppNotificationService::buildApproveUrl($record));
        $this->assertNull(WhatsAppNotificationService::buildRejectUrl($record));
    }
}
