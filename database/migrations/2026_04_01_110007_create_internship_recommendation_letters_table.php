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
        Schema::create('internship_recommendation_letters', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->string('nim');
            $table->string('study_program');
            $table->string('semester');
            $table->decimal('ipk', 3, 2);
            $table->string('program_name');
            $table->string('phone_number');
            $table->enum('status', ['SUBMITTED', 'APPROVE', 'REJECT'])->default('SUBMITTED');
            $table->date('letter_date')->nullable();
            $table->string('letter_number')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('public_token')->nullable()->unique();
            $table->timestamps();

            $table->index('public_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_recommendation_letters');
    }
};
