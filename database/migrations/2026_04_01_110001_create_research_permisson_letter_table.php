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
        Schema::create('research_permisson_letter', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->string('nim');
            $table->string('study_program');
            $table->string('phone_number');
            $table->string('company_name');
            $table->text('company_address');
            $table->enum('status', ['SUBMITTED', 'APPROVE', 'REJECT'])->default('SUBMITTED');
            $table->string('public_token')->nullable()->unique();
            $table->string('letter_number')->nullable();
            $table->date('letter_date')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index('public_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research_permisson_letter');
    }
};
