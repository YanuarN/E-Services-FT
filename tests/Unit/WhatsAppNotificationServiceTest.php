<?php

namespace Tests\Unit;

use App\Models\AdminWhatsappContact;
use App\Models\ExamPermissionLetter;
use App\Models\ResearchPermissionLetter;
use App\Models\RoomUsageRequest;
use App\Models\RoomUsageRequestSlot;
use App\Services\WhatsAppNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

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
        $this->assertStringContainsString('Notifikasi Sistem Informasi Surat - Surat Izin Survey Untuk Penelitian', $decodedUrl);
        $this->assertStringContainsString('Halo, Siti Rahma.', $decodedUrl);
        $this->assertStringContainsString('Status: DISETUJUI / SELESAI', $decodedUrl);
        $this->assertStringContainsString('https://e-service-ft.test/verification/research_permission/VERIFYTOKEN12345', $decodedUrl);
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
        $this->assertStringContainsString('Pemberitahuan Status Ajuan Surat Formulir Peminjaman Ruangan', $decodedUrl);
        $this->assertStringContainsString('Lampiran belum lengkap', $decodedUrl);
        $this->assertStringContainsString(route('booking'), $decodedUrl);
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

    public function test_it_builds_submission_url_for_letter_request_to_admin(): void
    {
        AdminWhatsappContact::query()->create([
            'whatsapp_number' => '081298765432',
        ]);

        $record = new ResearchPermissionLetter([
            'student_name' => 'Siti Rahma',
            'nim' => '221234567',
            'phone_number' => '081234567890',
        ]);

        $url = WhatsAppNotificationService::buildSubmissionUrl($record);
        $decodedUrl = urldecode((string) $url);

        $this->assertNotNull($url);
        $this->assertStringContainsString('https://wa.me/6281298765432?text=', $url);
        $this->assertStringContainsString('Konfirmasi Pengajuan Surat Surat Izin Survey Untuk Penelitian', $decodedUrl);
        $this->assertStringContainsString('Nama: Siti Rahma', $decodedUrl);
        $this->assertStringContainsString('NIM: 221234567', $decodedUrl);
    }

    public function test_it_builds_submission_url_for_room_booking_to_admin(): void
    {
        AdminWhatsappContact::query()->create([
            'whatsapp_number' => '+628111222333',
        ]);

        $record = new RoomUsageRequest([
            'student_name' => 'Budi Santoso',
            'nim' => '22001122',
            'room_name' => 'Lab Komputer 1',
            'activity_name' => 'Rapat Panitia',
            'start_at' => '2026-04-30 09:00:00',
        ]);
        $record->setRelation('slots', collect([
            new RoomUsageRequestSlot([
                'room_name_snapshot' => 'Lab Komputer 1',
                'booking_date' => '2026-04-30',
                'start_at' => '2026-04-30 09:00:00',
                'end_at' => '2026-04-30 11:00:00',
            ]),
            new RoomUsageRequestSlot([
                'room_name_snapshot' => 'Lab Komputer 2',
                'booking_date' => '2026-05-02',
                'start_at' => '2026-05-02 13:00:00',
                'end_at' => '2026-05-02 15:00:00',
            ]),
        ]));

        $url = WhatsAppNotificationService::buildSubmissionUrl($record);
        $decodedUrl = urldecode((string) $url);

        $this->assertNotNull($url);
        $this->assertStringContainsString('https://wa.me/628111222333?text=', $url);
        $this->assertStringContainsString('Konfirmasi Pengajuan Peminjaman Ruang Lab Komputer 1', $decodedUrl);
        $this->assertStringContainsString('Ruang: Lab Komputer 1', $decodedUrl);
        $this->assertStringContainsString('Detail Ruang/Jam: Lab Komputer 1 (09:00-11:00), Lab Komputer 2 (13:00-15:00)', $decodedUrl);
        $this->assertStringContainsString('Agenda: Rapat Panitia', $decodedUrl);
        $this->assertStringContainsString('Tanggal Peminjaman: 30 April 2026 - 02 Mei 2026', $decodedUrl);
    }
}
