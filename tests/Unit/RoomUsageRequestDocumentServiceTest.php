<?php

namespace Tests\Unit;

use App\Models\Room;
use App\Models\RoomUsageRequest;
use App\Models\RoomUsageRequestSlot;
use App\Services\Letters\RoomUsageRequestDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomUsageRequestDocumentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_usage_request_slot_summary_includes_dates_for_multi_day_bookings(): void
    {
        $record = new RoomUsageRequest([
            'room_name' => 'Lab Komputer 1, Lab Komputer 2',
        ]);

        $record->setRelation('slots', collect([
            new RoomUsageRequestSlot([
                'booking_date' => '2026-04-30',
                'room_name_snapshot' => 'Lab Komputer 1',
                'start_at' => '2026-04-30 09:00:00',
                'end_at' => '2026-04-30 11:00:00',
            ]),
            new RoomUsageRequestSlot([
                'booking_date' => '2026-05-02',
                'room_name_snapshot' => 'Lab Komputer 2',
                'start_at' => '2026-05-02 13:00:00',
                'end_at' => '2026-05-02 15:00:00',
            ]),
        ]));

        $this->assertSame(
            '30 Apr 2026 - Lab Komputer 1 (09:00-11:00), 02 May 2026 - Lab Komputer 2 (13:00-15:00)',
            $record->slot_summary,
        );
    }

    public function test_room_usage_request_document_payload_builds_date_range_for_multi_day_bookings(): void
    {
        $roomA = Room::query()->create([
            'name' => 'Lab Komputer 1',
            'capacity' => 30,
        ]);

        $roomB = Room::query()->create([
            'name' => 'Lab Komputer 2',
            'capacity' => 30,
        ]);

        $record = RoomUsageRequest::query()->create([
            'student_name' => 'Budi Santoso',
            'nim' => '22001122',
            'study_program' => 'Teknik Informatika',
            'phone_number' => '081300000000',
            'unit' => 'BEM FT',
            'activity_name' => 'Rapat Panitia',
            'start_at' => '2026-04-30 09:00:00',
            'end_at' => '2026-05-02 15:00:00',
            'room_id' => null,
            'room_name' => 'Lab Komputer 1, Lab Komputer 2',
            'number_of_participants' => 20,
            'status' => 'PENDING',
            'document' => 'room-usage-requests/surat.pdf',
        ]);

        $record->slots()->createMany([
            [
                'room_id' => $roomA->id,
                'room_name_snapshot' => 'Lab Komputer 1',
                'booking_date' => '2026-04-30',
                'start_at' => '2026-04-30 09:00:00',
                'end_at' => '2026-04-30 11:00:00',
            ],
            [
                'room_id' => $roomB->id,
                'room_name_snapshot' => 'Lab Komputer 2',
                'booking_date' => '2026-05-02',
                'start_at' => '2026-05-02 13:00:00',
                'end_at' => '2026-05-02 15:00:00',
            ],
        ]);

        $service = new class extends RoomUsageRequestDocumentService
        {
            public function exposeBuildTemplatePayload(RoomUsageRequest $letter): array
            {
                return $this->buildTemplatePayload($letter);
            }
        };

        $payload = $service->exposeBuildTemplatePayload($record->load('slots.room'));

        $this->assertSame('30 April 2026 - 02 Mei 2026', $payload['tanggal_penggunaan']);
        $this->assertSame(
            '30 April 2026 - Lab Komputer 1 (09:00-11:00), 02 Mei 2026 - Lab Komputer 2 (13:00-15:00)',
            $payload['detail_ruangan'],
        );
        $this->assertSame('09:00-11:00, 13:00-15:00', $payload['waktu_penggunaan']);
    }

    public function test_room_usage_request_document_payload_keeps_single_date_for_non_recurring_booking(): void
    {
        $room = Room::query()->create([
            'name' => 'H46',
            'capacity' => 50,
        ]);

        $record = RoomUsageRequest::query()->create([
            'student_name' => 'Tegar Restu Indrawan',
            'nim' => 'D200240111',
            'study_program' => 'Teknik Mesin',
            'phone_number' => '081645439373',
            'unit' => 'KMTM',
            'activity_name' => 'Rapat pembagian LKTIN',
            'start_at' => '2026-03-13 15:00:00',
            'end_at' => '2026-03-13 23:00:00',
            'room_id' => $room->id,
            'room_name' => 'H46',
            'number_of_participants' => 50,
            'status' => 'PENDING',
            'document' => 'room-usage-requests/bukti.pdf',
        ]);

        $record->slots()->create([
            'room_id' => $room->id,
            'room_name_snapshot' => 'H46',
            'booking_date' => '2026-03-13',
            'start_at' => '2026-03-13 15:00:00',
            'end_at' => '2026-03-13 23:00:00',
        ]);

        $service = new class extends RoomUsageRequestDocumentService
        {
            public function exposeBuildTemplatePayload(RoomUsageRequest $letter): array
            {
                return $this->buildTemplatePayload($letter);
            }
        };

        $payload = $service->exposeBuildTemplatePayload($record->load('slots.room'));

        $this->assertSame('13 Maret 2026', $payload['tanggal_penggunaan']);
        $this->assertSame('15:00-23:00', $payload['waktu_penggunaan']);
    }
}
