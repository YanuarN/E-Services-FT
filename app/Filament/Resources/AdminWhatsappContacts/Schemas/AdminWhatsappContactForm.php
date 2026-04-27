<?php

namespace App\Filament\Resources\AdminWhatsappContacts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdminWhatsappContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kontak')
                    ->description('Hanya nomor WhatsApp admin yang dapat diubah dari panel.')
                    ->schema([
                        TextInput::make('whatsapp_number')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->maxLength(30)
                            ->placeholder('Contoh: 081234567890 atau +6281234567890')
                            ->helperText('Simpan satu nomor utama admin yang akan dipakai sistem.')
                            ->required(),
                    ]),
            ]);
    }
}
