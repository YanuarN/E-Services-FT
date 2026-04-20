<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_usage_requests', function (Blueprint $table): void {
            $table->index(
                ['room_id', 'status', 'start_at', 'end_at'],
                'room_usage_requests_room_status_time_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('room_usage_requests', function (Blueprint $table): void {
            $table->dropIndex('room_usage_requests_room_status_time_index');
        });
    }
};
