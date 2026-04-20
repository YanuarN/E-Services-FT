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

        $response = $this->post(route('booking.store'), [
            'student_name' => 'Budi Santoso',
            'nim' => '221000001',
            'study_program' => 'Teknik Informatika',
            'phone_number' => '081234567890',
            'unit' => 'BEM FT',
            'activity_name' => 'Rapat koordinasi',
            'number_of_participants' => 25,
            'selected_date' => '2026-04-25',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'document' => UploadedFile::fake()->create('surat.pdf', 200, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('room_id');
        $this->assertDatabaseCount('room_usage_requests', 0);
    }

    public function test_public_booking_rejects_overlapping_schedule_for_same_room(): void
    {
        Storage::fake('local');

        $room = Room::query()->create([
            'name' => 'H.4.1',
            'capacity' => 60,
        ]);

        RoomUsageRequest::query()->create([
            'student_name' => 'Siti Rahma',
            'nim' => '221000002',
            'study_program' => 'Teknik Sipil',
            'phone_number' => '081200000001',
            'unit' => 'HMTS',
            'activity_name' => 'Seminar internal',
            'start_at' => Carbon::parse('2026-04-25 09:00:00'),
            'end_at' => Carbon::parse('2026-04-25 11:00:00'),
            'room_id' => $room->id,
            'room_name' => $room->name,
            'number_of_participants' => 30,
            'status' => 'APPROVED',
            'document' => 'room-usage-requests/existing.pdf',
        ]);

        $response = $this->from(route('booking'))->post(route('booking.store'), [
            'student_name' => 'Budi Santoso',
            'nim' => '221000001',
            'study_program' => 'Teknik Informatika',
            'phone_number' => '081234567890',
            'unit' => 'BEM FT',
            'activity_name' => 'Rapat koordinasi',
            'room_id' => $room->id,
            'number_of_participants' => 25,
            'selected_date' => '2026-04-25',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'document' => UploadedFile::fake()->create('surat.pdf', 200, 'application/pdf'),
        ]);

        $response->assertRedirect(route('booking'));
        $response->assertSessionHasErrors('start_time');
        $this->assertDatabaseCount('room_usage_requests', 1);
    }

    public function test_room_booking_lookup_returns_only_active_bookings_for_selected_room(): void
    {
        Carbon::setTestNow('2026-04-20 08:00:00');

        $room = Room::query()->create([
            'name' => 'H.4.1',
            'capacity' => 60,
        ]);

        $otherRoom = Room::query()->create([
            'name' => 'H.4.5',
            'capacity' => 60,
        ]);

        $matchingBooking = RoomUsageRequest::query()->create([
            'student_name' => 'Nadia Putri',
            'nim' => '221000003',
            'study_program' => 'Teknik Kimia',
            'phone_number' => '081200000003',
            'unit' => 'HMJ',
            'activity_name' => 'Diskusi organisasi',
            'start_at' => Carbon::parse('2026-04-21 13:00:00'),
            'end_at' => Carbon::parse('2026-04-21 15:00:00'),
            'room_id' => $room->id,
            'room_name' => $room->name,
            'number_of_participants' => 20,
            'status' => 'PENDING',
            'document' => 'room-usage-requests/matching.pdf',
        ]);

        RoomUsageRequest::query()->create([
            'student_name' => 'Ditolak',
            'nim' => '221000004',
            'study_program' => 'Teknik Lingkungan',
            'phone_number' => '081200000004',
            'unit' => 'UKM',
            'activity_name' => 'Kegiatan ditolak',
            'start_at' => Carbon::parse('2026-04-21 09:00:00'),
            'end_at' => Carbon::parse('2026-04-21 11:00:00'),
            'room_id' => $room->id,
            'room_name' => $room->name,
            'number_of_participants' => 15,
            'status' => 'REJECTED',
            'document' => 'room-usage-requests/rejected.pdf',
        ]);

        RoomUsageRequest::query()->create([
            'student_name' => 'Ruangan lain',
            'nim' => '221000005',
            'study_program' => 'Teknik Elektro',
            'phone_number' => '081200000005',
            'unit' => 'BEM',
            'activity_name' => 'Kegiatan lain',
            'start_at' => Carbon::parse('2026-04-21 09:00:00'),
            'end_at' => Carbon::parse('2026-04-21 10:00:00'),
            'room_id' => $otherRoom->id,
            'room_name' => $otherRoom->name,
            'number_of_participants' => 10,
            'status' => 'APPROVED',
            'document' => 'room-usage-requests/other-room.pdf',
        ]);

        RoomUsageRequest::query()->create([
            'student_name' => 'Booking lama',
            'nim' => '221000006',
            'study_program' => 'Teknik Mesin',
            'phone_number' => '081200000006',
            'unit' => 'Panitia',
            'activity_name' => 'Acara selesai',
            'start_at' => Carbon::parse('2026-04-18 09:00:00'),
            'end_at' => Carbon::parse('2026-04-18 12:00:00'),
            'room_id' => $room->id,
            'room_name' => $room->name,
            'number_of_participants' => 40,
            'status' => 'APPROVED',
            'document' => 'room-usage-requests/past.pdf',
        ]);

        $response = $this->getJson(route('booking.rooms.bookings', $room));

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingBooking->id)
            ->assertJsonPath('data.0.roomId', $room->id)
            ->assertJsonPath('data.0.roomName', $room->name);

        Carbon::setTestNow();
    }
}
