<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('room_usage_requests', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->string('nim');
            $table->string('study_program');
            $table->string('phone_number');
            $table->string('unit');
            $table->string('activity_name');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->cascadeOnUpdate()->nullOnDelete();
            $table->string('room_name')->nullable();
            $table->integer('number_of_participants');
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->string('document');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_usage_requests');
    }
};
