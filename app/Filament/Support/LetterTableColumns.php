<?php

namespace App\Filament\Support;

use App\Models\RoomUsageRequest;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

class LetterTableColumns
{
    public static function evidence(): TextColumn
    {
        return TextColumn::make('submission_evidence')
            ->label('Bukti')
            ->state(fn (Model $record): string => static::hasEvidence($record) ? 'Lihat Bukti' : '-')
            ->badge()
            ->color(fn (string $state): string => $state === 'Lihat Bukti' ? 'success' : 'gray')
            ->url(function (Model $record): ?string {
                if (! static::hasEvidence($record) || ! $record instanceof RoomUsageRequest) {
                    return null;
                }

                return route('admin.room-usage-requests.evidence.download', ['record' => $record]);
            }, shouldOpenInNewTab: true);
    }

    private static function hasEvidence(Model $record): bool
    {
        return filled((string) $record->getAttribute('document'));
    }
}
