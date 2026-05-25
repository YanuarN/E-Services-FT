<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_usage_request_slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_usage_request_id')
                ->constrained('room_usage_requests')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('room_id')
                ->nullable()
                ->constrained('rooms')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('room_name_snapshot')->nullable();
            $table->date('booking_date');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->timestamps();

            $table->index(
                ['room_id', 'booking_date', 'start_at', 'end_at'],
                'room_usage_request_slots_room_date_time_index',
            );
            $table->index(
                ['booking_date', 'start_at'],
                'room_usage_request_slots_date_time_index',
            );
            $table->unique(
                ['room_usage_request_id', 'room_id', 'start_at', 'end_at'],
                'room_usage_request_slots_request_room_time_unique',
            );
        });

        Schema::table('room_usage_requests', function (Blueprint $table): void {
            $table->index('status', 'room_usage_requests_status_index');
        });

        DB::table('room_usage_requests')
            ->select([
                'id',
                'room_id',
                'room_name',
                'start_at',
                'end_at',
                'created_at',
                'updated_at',
            ])
            ->orderBy('id')
            ->chunkById(200, function ($requests): void {
                $rows = [];

                foreach ($requests as $request) {
                    if (blank($request->start_at) || blank($request->end_at)) {
                        continue;
                    }

                    $startAt = (string) $request->start_at;
                    $bookingDate = substr($startAt, 0, 10);
                    $now = now();

                    $rows[] = [
                        'room_usage_request_id' => $request->id,
                        'room_id' => $request->room_id,
                        'room_name_snapshot' => $request->room_name,
                        'booking_date' => $bookingDate,
                        'start_at' => $request->start_at,
                        'end_at' => $request->end_at,
                        'created_at' => $request->created_at ?: $now,
                        'updated_at' => $request->updated_at ?: $now,
                    ];
                }

                if (! empty($rows)) {
                    DB::table('room_usage_request_slots')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::table('room_usage_requests', function (Blueprint $table): void {
            $table->dropIndex('room_usage_requests_status_index');
        });

        Schema::dropIfExists('room_usage_request_slots');
    }
};
