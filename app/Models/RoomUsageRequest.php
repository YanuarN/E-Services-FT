<?php

namespace App\Models;

use App\Models\Concerns\HasPublicToken;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomUsageRequest extends Model
{
    use HasPublicToken;

    protected $fillable = [
        'student_name',
        'nim',
        'study_program',
        'phone_number',
        'unit',
        'activity_name',
        'start_at',
        'end_at',
        'room_id',
        'room_name',
        'number_of_participants',
        'status',
        'document',
        'public_token',
        'letter_number',
        'letter_date',
        'pdf_path',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'number_of_participants' => 'integer',
        'letter_date' => 'date',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(RoomUsageRequestSlot::class);
    }

    public function getResolvedRoomNameAttribute(): string
    {
        if ($this->relationLoaded('slots') && $this->slots->isNotEmpty()) {
            return $this->slots
                ->map(fn (RoomUsageRequestSlot $slot): string => $slot->room_name_snapshot ?: (string) ($slot->room?->name ?? 'Ruang'))
                ->filter()
                ->unique()
                ->values()
                ->join(', ');
        }

        if (filled($this->room_name)) {
            return (string) $this->room_name;
        }

        if ($this->relationLoaded('room') && $this->room) {
            return (string) $this->room->name;
        }

        return (string) $this->room()->value('name');
    }

    public function getSlotSummaryAttribute(): string
    {
        if (! $this->relationLoaded('slots')) {
            $this->loadMissing('slots.room');
        }

        $hasMultipleBookingDates = $this->slots
            ->pluck('booking_date')
            ->filter()
            ->map(fn (CarbonInterface|string $date): string => $this->formatDateValue($date, 'Y-m-d'))
            ->unique()
            ->count() > 1;

        return $this->slots
            ->sortBy('start_at')
            ->map(function (RoomUsageRequestSlot $slot) use ($hasMultipleBookingDates): string {
                $roomName = trim((string) ($slot->room_name_snapshot ?: ($slot->room?->name ?? 'Ruang')));
                $dateLabel = $this->formatDateValue($slot->booking_date, 'd M Y');
                $timeLabel = sprintf(
                    '%s-%s',
                    $this->formatTime($slot->start_at),
                    $this->formatTime($slot->end_at),
                );

                if ($hasMultipleBookingDates && $dateLabel !== '-') {
                    return "{$dateLabel} - {$roomName} ({$timeLabel})";
                }

                return "{$roomName} ({$timeLabel})";
            })
            ->values()
            ->join(', ');
    }

    private function formatTime(CarbonInterface|string|null $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('H:i');
        }

        return '-';
    }

    private function formatDateValue(CarbonInterface|string|null $value, string $format): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format($format);
        }

        return filled($value) ? Carbon::parse($value)->format($format) : '-';
    }
}
