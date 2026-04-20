<?php

namespace App\Models;

use App\Models\Concerns\HasPublicToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function getResolvedRoomNameAttribute(): string
    {
        if ($this->relationLoaded('room') && $this->room) {
            return (string) $this->room->name;
        }

        if (filled($this->room_name)) {
            return (string) $this->room_name;
        }

        return (string) $this->room()->value('name');
    }
}
