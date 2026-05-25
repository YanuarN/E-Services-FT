<?php

namespace App\Filament\Resources\RoomUsageRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoomUsageRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pemohon')
                    ->schema([
                        TextEntry::make('student_name')
                            ->label('Nama Mahasiswa'),
                        TextEntry::make('nim')
                            ->label('NIM'),
                        TextEntry::make('study_program')
                            ->label('Program Studi'),
                        TextEntry::make('phone_number')
                            ->label('Nomor Telepon'),
                        TextEntry::make('unit')
                            ->label('Unit/Organisasi'),
                    ])
                    ->columns(2),

                Section::make('Informasi Peminjaman')
                    ->schema([
                        TextEntry::make('activity_name')
                            ->label('Nama Kegiatan'),
                        TextEntry::make('resolved_room_name')
                            ->label('Daftar Ruangan')
                            ->placeholder('-'),
                        TextEntry::make('slot_summary')
                            ->label('Detail Slot')
                            ->placeholder('-'),
                        TextEntry::make('number_of_participants')
                            ->label('Jumlah Peserta'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'APPROVE', 'APPROVED' => 'success',
                                'REJECT', 'REJECTED' => 'danger',
                                default => 'warning',
                            }),
                        TextEntry::make('letter_number')
                            ->label('Nomor Surat')
                            ->placeholder('-'),
                        TextEntry::make('letter_date')
                            ->label('Tanggal Surat')
                            ->date('d M Y')
                            ->placeholder('-'),
                        TextEntry::make('pdf_path')
                            ->label('File PDF')
                            ->placeholder('-'),
                        TextEntry::make('public_token')
                            ->label('Token Verifikasi')
                            ->placeholder('-'),
                        TextEntry::make('document')
                            ->label('Dokumen')
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Jadwal')
                    ->schema([
                        TextEntry::make('start_at')
                            ->label('Mulai')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('end_at')
                            ->label('Selesai')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(2),

                Section::make('Metadata')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
