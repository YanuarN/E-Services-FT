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
        Schema::create('internship_letters', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->string('nim');
            $table->string('study_program');
            $table->string('phone_number');
            $table->string('company_name');
            $table->text('company_address');
            $table->json('group_member');
            $table->enum('status', ['SUBMITTED', 'APPROVE', 'REJECT'])->default('SUBMITTED');
            $table->date('letter_date');
            $table->string('letter_number');
            $table->string('pdf_path');
            $table->string('public_token')->unique();
            $table->timestamps();

            $table->index('public_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_letters');
    }
};
