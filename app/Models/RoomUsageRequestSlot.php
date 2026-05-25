<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomUsageRequestSlot extends Model
{
    protected $fillable = [
        'room_usage_request_id',
        'room_id',
        'room_name_snapshot',
        'booking_date',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function roomUsageRequest(): BelongsTo
    {
        return $this->belongsTo(RoomUsageRequest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
