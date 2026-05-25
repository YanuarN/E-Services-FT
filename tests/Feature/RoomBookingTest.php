<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomUsageRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoomBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_booking_requires_registered_room_selection(): void
    {
        Storage::fake('local');
        Carbon::setTestNow('2026-04-20 08:00:00');

        $response = $this->post(route('booking.store'), [
            'student_name' => 'Budi Santoso',
            'nim' => '221000001',
            'study_program' => 'Teknik Informatika',
            'phone_number' => '081234567890',
            'unit' => 'BEM FT',
            'activity_name' => 'Rapat koordinasi',
            'number_of_participants' => 25,
            'selected_date' => '2026-04-25',
            'booking_slots' => [
                [
                    'start_time' => '09:00',
                    'end_time' => '11:00',
                ],
            ],
            'document' => UploadedFile::fake()->create('surat.pdf', 200, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('booking_slots.0.room_id');
        $this->assertDatabaseCount('room_usage_requests', 0);
        $this->assertDatabaseCount('room_usage_request_slots', 0);

        Carbon::setTestNow();
    }

    public function test_public_booking_rejects_entire_submission_when_any_slot_conflicts(): void
    {
        Storage::fake('local');
        Carbon::setTestNow('2026-04-20 08:00:00');

        $roomA = Room::query()->create([
            'name' => 'H.4.1',
            'capacity' => 60,
        ]);

        $roomB = Room::query()->create([
            'name' => 'H.4.2',
            'capacity' => 40,
        ]);

        $existingRequest = RoomUsageRequest::query()->create([
            'student_name' => 'Siti Rahma',
            'nim' => '221000002',
            'study_program' => 'Teknik Sipil',
            'phone_number' => '081200000001',
            'unit' => 'HMTS',
            'activity_name' => 'Seminar internal',
            'start_at' => Carbon::parse('2026-04-25 09:00:00'),
            'end_at' => Carbon::parse('2026-04-25 11:00:00'),
            'room_id' => $roomA->id,
            'room_name' => $roomA->name,
            'number_of_participants' => 30,
            'status' => 'APPROVED',
            'document' => 'room-usage-requests/existing.pdf',
        ]);

        $existingRequest->slots()->create([
            'room_id' => $roomA->id,
            'room_name_snapshot' => $roomA->name,
            'booking_date' => '2026-04-25',
            'start_at' => Carbon::parse('2026-04-25 09:00:00'),
            'end_at' => Carbon::parse('2026-04-25 11:00:00'),
        ]);

        $response = $this->from(route('booking'))->post(route('booking.store'), [
            'student_name' => 'Budi Santoso',
            'nim' => '221000001',
            'study_program' => 'Teknik Informatika',
            'phone_number' => '081234567890',
            'unit' => 'BEM FT',
            'activity_name' => 'Rapat koordinasi',
            'number_of_participants' => 25,
            'selected_date' => '2026-04-25',
            'booking_slots' => [
                [
                    'room_id' => $roomA->id,
                    'start_time' => '10:00',
                    'end_time' => '12:00',
                ],
                [
                    'room_id' => $roomB->id,
                    'start_time' => '13:00',
                    'end_time' => '14:00',
                ],
            ],
            'document' => UploadedFile::fake()->create('surat.pdf', 200, 'application/pdf'),
        ]);

        $response->assertRedirect(route('booking'));
        $response->assertSessionHasErrors('booking_slots');
        $this->assertDatabaseCount('room_usage_requests', 1);
        $this->assertDatabaseCount('room_usage_request_slots', 1);

        Carbon::setTestNow();
    }

    public function test_public_booking_stores_multiple_room_slots_in_single_request(): void
    {
        Storage::fake('local');
        Carbon::setTestNow('2026-04-20 08:00:00');

        $roomA = Room::query()->create([
            'name' => 'H.4.1',
            'capacity' => 60,
        ]);

        $roomB = Room::query()->create([
            'name' => 'H.4.2',
            'capacity' => 40,
        ]);

        $response = $this->post(route('booking.store'), [
            'student_name' => 'Nadia Putri',
            'nim' => '221000003',
            'study_program' => 'Teknik Kimia',
            'phone_number' => '081200000003',
            'unit' => 'HMJ',
            'activity_name' => 'Diskusi organisasi',
            'number_of_participants' => 20,
            'selected_date' => '2026-04-25',
            'booking_slots' => [
                [
                    'room_id' => $roomA->id,
                    'start_time' => '08:00',
                    'end_time' => '09:30',
                ],
                [
                    'room_id' => $roomB->id,
                    'start_time' => '10:00',
                    'end_time' => '12:00',
                ],
            ],
            'document' => UploadedFile::fake()->create('surat.pdf', 200, 'application/pdf'),
        ]);

        $response->assertRedirect(route('booking'));
        $this->assertDatabaseCount('room_usage_requests', 1);
        $this->assertDatabaseCount('room_usage_request_slots', 2);

        $requestRecord = RoomUsageRequest::query()->firstOrFail();
        $this->assertSame('PENDING', $requestRecord->status);
        $this->assertSame('2026-04-25 08:00:00', $requestRecord->start_at?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-04-25 12:00:00', $requestRecord->end_at?->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    public function test_public_booking_rejects_duplicate_slots_in_same_submission(): void
    {
        Storage::fake('local');
        Carbon::setTestNow('2026-04-20 08:00:00');

        $room = Room::query()->create([
            'name' => 'H.4.1',
            'capacity' => 60,
        ]);

        $response = $this->from(route('booking'))->post(route('booking.store'), [
            'student_name' => 'Budi Santoso',
            'nim' => '221000001',
            'study_program' => 'Teknik Informatika',
            'phone_number' => '081234567890',
            'unit' => 'BEM FT',
            'activity_name' => 'Rapat koordinasi',
            'number_of_participants' => 25,
            'selected_date' => '2026-04-25',
            'booking_slots' => [
                [
                    'room_id' => $room->id,
                    'start_time' => '10:00',
                    'end_time' => '12:00',
                ],
                [
                    'room_id' => $room->id,
                    'start_time' => '10:00',
                    'end_time' => '12:00',
                ],
            ],
            'document' => UploadedFile::fake()->create('surat.pdf', 200, 'application/pdf'),
        ]);

        $response->assertRedirect(route('booking'));
        $response->assertSessionHasErrors('booking_slots.1.room_id');
        $this->assertDatabaseCount('room_usage_requests', 0);
        $this->assertDatabaseCount('room_usage_request_slots', 0);

        Carbon::setTestNow();
    }

    public function test_room_booking_lookup_returns_only_active_bookings_for_selected_day(): void
    {
        Carbon::setTestNow('2026-04-20 08:00:00');

        $roomA = Room::query()->create([
            'name' => 'H.4.1',
            'capacity' => 60,
        ]);

        $roomB = Room::query()->create([
            'name' => 'H.4.2',
            'capacity' => 60,
        ]);

        $pendingRequest = RoomUsageRequest::query()->create([
            'student_name' => 'Nadia Putri',
            'nim' => '221000003',
            'study_program' => 'Teknik Kimia',
            'phone_number' => '081200000003',
            'unit' => 'HMJ',
            'activity_name' => 'Diskusi organisasi',
            'start_at' => Carbon::parse('2026-04-25 13:00:00'),
            'end_at' => Carbon::parse('2026-04-25 15:00:00'),
            'room_id' => $roomA->id,
            'room_name' => $roomA->name,
            'number_of_participants' => 20,
            'status' => 'PENDING',
            'document' => 'room-usage-requests/matching.pdf',
        ]);

        $pendingRequest->slots()->create([
            'room_id' => $roomA->id,
            'room_name_snapshot' => $roomA->name,
            'booking_date' => '2026-04-25',
            'start_at' => Carbon::parse('2026-04-25 13:00:00'),
            'end_at' => Carbon::parse('2026-04-25 15:00:00'),
        ]);

        $approvedRequest = RoomUsageRequest::query()->create([
            'student_name' => 'Ruangan lain',
            'nim' => '221000005',
            'study_program' => 'Teknik Elektro',
            'phone_number' => '081200000005',
            'unit' => 'BEM',
            'activity_name' => 'Kegiatan lain',
            'start_at' => Carbon::parse('2026-04-25 09:00:00'),
            'end_at' => Carbon::parse('2026-04-25 10:00:00'),
            'room_id' => $roomB->id,
            'room_name' => $roomB->name,
            'number_of_participants' => 10,
            'status' => 'APPROVED',
            'document' => 'room-usage-requests/other-room.pdf',
        ]);

        $approvedRequest->slots()->create([
            'room_id' => $roomB->id,
            'room_name_snapshot' => $roomB->name,
            'booking_date' => '2026-04-25',
            'start_at' => Carbon::parse('2026-04-25 09:00:00'),
            'end_at' => Carbon::parse('2026-04-25 10:00:00'),
        ]);

        $rejectedRequest = RoomUsageRequest::query()->create([
            'student_name' => 'Ditolak',
            'nim' => '221000004',
            'study_program' => 'Teknik Lingkungan',
            'phone_number' => '081200000004',
            'unit' => 'UKM',
            'activity_name' => 'Kegiatan ditolak',
            'start_at' => Carbon::parse('2026-04-25 09:00:00'),
            'end_at' => Carbon::parse('2026-04-25 11:00:00'),
            'room_id' => $roomA->id,
            'room_name' => $roomA->name,
            'number_of_participants' => 15,
            'status' => 'REJECTED',
            'document' => 'room-usage-requests/rejected.pdf',
        ]);

        $rejectedRequest->slots()->create([
            'room_id' => $roomA->id,
            'room_name_snapshot' => $roomA->name,
            'booking_date' => '2026-04-25',
            'start_at' => Carbon::parse('2026-04-25 09:00:00'),
            'end_at' => Carbon::parse('2026-04-25 11:00:00'),
        ]);

        $otherDateRequest = RoomUsageRequest::query()->create([
            'student_name' => 'Hari lain',
            'nim' => '221000006',
            'study_program' => 'Teknik Mesin',
            'phone_number' => '081200000006',
            'unit' => 'Panitia',
            'activity_name' => 'Acara selesai',
            'start_at' => Carbon::parse('2026-04-26 09:00:00'),
            'end_at' => Carbon::parse('2026-04-26 12:00:00'),
            'room_id' => $roomA->id,
            'room_name' => $roomA->name,
            'number_of_participants' => 40,
            'status' => 'APPROVED',
            'document' => 'room-usage-requests/past.pdf',
        ]);

        $otherDateRequest->slots()->create([
            'room_id' => $roomA->id,
            'room_name_snapshot' => $roomA->name,
            'booking_date' => '2026-04-26',
            'start_at' => Carbon::parse('2026-04-26 09:00:00'),
            'end_at' => Carbon::parse('2026-04-26 12:00:00'),
        ]);

        $response = $this->getJson(route('booking.bookings.by-date', [
            'selected_date' => '2026-04-25',
        ]));

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertSame(
            ['H.4.1', 'H.4.2'],
            collect($response->json('data'))->pluck('roomName')->sort()->values()->all(),
        );

        Carbon::setTestNow();
    }
}
