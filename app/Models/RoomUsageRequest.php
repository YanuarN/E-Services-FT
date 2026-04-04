<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomUsageRequest extends Model
{
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
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'number_of_participants' => 'integer',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
