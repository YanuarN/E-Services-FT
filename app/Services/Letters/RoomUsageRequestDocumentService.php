<?php

namespace App\Services\Letters;

use App\Models\RoomUsageRequest;
use Illuminate\Database\Eloquent\Model;

class RoomUsageRequestDocumentService extends UniversalLetterService
{
    protected function letterType(): string
    {
        return 'room_usage_request';
    }

    protected function modelClass(): string
    {
        return RoomUsageRequest::class;
    }

    protected function buildTemplatePayload(Model $letter): array
    {
        /** @var RoomUsageRequest $letter */
        $letter->loadMissing('slots.room');
        $roomName = $this->resolveRoomName($letter);
        $slotSummary = $this->buildSlotSummary($letter);
        $usageTime = $this->buildUsageTime($letter);

        return array_merge(
            $this->baseLetterPayload($letter),
            $this->studentIdentityPayload(
                $letter->student_name,
                $letter->nim,
                $letter->study_program,
                $letter->phone_number,
            ),
            [
                'tanggal_penggunaan' => $this->buildBookingDateSummary($letter),
                'tanggal_peminjaman' => $this->formatDate($letter->created_at),
                'tanggal_pengajuan' => $this->formatDate($letter->created_at),
                'tanggal_permohonan' => $this->formatDate($letter->created_at),
                'waktu_mulai' => $this->formatTime($letter->start_at),
                'waktu_selesai' => $this->formatTime($letter->end_at),
                'waktu_penggunaan' => $usageTime,
                'waktu' => $usageTime,
                'pukul' => $usageTime,
                'tempat_ruang' => $roomName,
                'ruang' => $roomName,
                'tempat' => $roomName,
                'daftar_ruangan_jam' => $slotSummary,
                'detail_ruangan' => $slotSummary,
                'nama' => $letter->student_name,
                'nama_peminjam' => $letter->student_name,
                'no_telp' => $letter->phone_number,
                'unit' => $letter->unit,
                'kegiatan' => $letter->activity_name,
                'nama_kegiatan' => $letter->activity_name,
                'jumlah_peserta' => $letter->number_of_participants,
            ],
        );
    }

    protected function buildFilenameParts(Model $letter): array
    {
        /** @var RoomUsageRequest $letter */
        return ['formulir-peminjaman-ruangan', $letter->student_name, $letter->nim];
    }

    protected function verificationFields(Model $letter): array
    {
        /** @var RoomUsageRequest $letter */
        return [
            $this->makeVerificationField('Nama Mahasiswa', $letter->student_name),
            $this->makeVerificationField('NIM', $letter->nim),
            $this->makeVerificationField('Program Studi', $letter->study_program),
            $this->makeVerificationField('Nomor Telepon', $letter->phone_number),
            $this->makeVerificationField('Unit/Organisasi', $letter->unit),
            $this->makeVerificationField('Kegiatan', $letter->activity_name),
            $this->makeVerificationField('Tanggal Penggunaan', $this->buildBookingDateSummary($letter)),
            $this->makeVerificationField('Waktu Penggunaan', $this->buildUsageTime($letter)),
            $this->makeVerificationField('Tempat/Ruang', $this->resolveRoomName($letter)),
            $this->makeVerificationField('Detail Ruangan/Jam', $this->buildSlotSummary($letter)),
            $this->makeVerificationField('Jumlah Peserta', $letter->number_of_participants),
        ];
    }

    private function buildUsageTime(RoomUsageRequest $letter): string
    {
        return trim(implode('-', array_filter([
            $this->formatTime($letter->start_at),
            $this->formatTime($letter->end_at),
        ])));
    }

    private function resolveRoomName(RoomUsageRequest $letter): string
    {
        return $letter->resolved_room_name;
    }

    private function buildSlotSummary(RoomUsageRequest $letter): string
    {
        $letter->loadMissing('slots.room');

        if ($letter->slots->isEmpty()) {
            return $letter->resolved_room_name;
        }

        $hasMultipleBookingDates = $letter->slots
            ->pluck('booking_date')
            ->filter()
            ->map(fn ($date): string => $this->formatDate($date))
            ->unique()
            ->count() > 1;

        return $letter->slots
            ->sortBy('start_at')
            ->map(function ($slot) use ($hasMultipleBookingDates): string {
                $roomName = trim((string) ($slot->room_name_snapshot ?: ($slot->room?->name ?? 'Ruang')));
                $start = $this->formatTime($slot->start_at);
                $end = $this->formatTime($slot->end_at);
                $dateLabel = $this->formatDate($slot->booking_date);

                if ($hasMultipleBookingDates && $dateLabel !== '') {
                    return "{$dateLabel} - {$roomName} ({$start}-{$end})";
                }

                return "{$roomName} ({$start}-{$end})";
            })
            ->values()
            ->join(', ');
    }

    private function buildBookingDateSummary(RoomUsageRequest $letter): string
    {
        $letter->loadMissing('slots.room');

        if ($letter->slots->isEmpty()) {
            return $this->formatDate($letter->start_at);
        }

        return $letter->slots
            ->pluck('booking_date')
            ->filter()
            ->sort()
            ->map(fn ($date): string => $this->formatDate($date))
            ->unique()
            ->values()
            ->join(', ');
    }
}
