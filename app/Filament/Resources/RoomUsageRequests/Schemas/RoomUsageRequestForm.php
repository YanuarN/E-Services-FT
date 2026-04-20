<?php

namespace App\Filament\Resources\RoomUsageRequests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoomUsageRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pemohon')
                    ->schema([
                        TextInput::make('student_name')
                            ->label('Nama Mahasiswa')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nim')
                            ->label('NIM')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('study_program')
                            ->label('Program Studi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone_number')
                            ->label('Nomor Telepon')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('unit')
                            ->label('Unit/Organisasi')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Informasi Peminjaman')
                    ->schema([
                        Textarea::make('activity_name')
                            ->label('Nama Kegiatan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        DateTimePicker::make('start_at')
                            ->label('Mulai')
                            ->required()
                            ->seconds(false),
                        DateTimePicker::make('end_at')
                            ->label('Selesai')
                            ->required()
                            ->seconds(false),
                        Select::make('room_id')
                            ->label('Ruang')
                            ->relationship('room', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Tambahkan data ruang lebih dulu melalui menu Ruangan bila belum tersedia.'),
                        TextInput::make('number_of_participants')
                            ->label('Jumlah Peserta')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'PENDING' => 'PENDING',
                                'APPROVED' => 'APPROVED',
                                'REJECTED' => 'REJECTED',
                            ])
                            ->required()
                            ->default('PENDING'),
                    ])
                    ->columns(2),

                Section::make('Dokumen')
                    ->schema([
                        FileUpload::make('document')
                            ->label('Dokumen Pendukung')
                            ->directory('room-usage-requests')
                            ->maxSize(10240)
                            ->required(),
                    ]),
            ]);
    }
}
