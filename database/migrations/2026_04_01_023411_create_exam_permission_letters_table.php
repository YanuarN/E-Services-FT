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
        Schema::create('exam_permission_letters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nim');
            $table->string('exam');
            $table->string('semester');
            $table->date('date');
            $table->enum('status', ['SUBMITTED', 'APPROVE', 'REJECT'])->default('SUBMITTED');
            $table->string('letter_number')->nullable();
            $table->date('letter_date')->nullable();
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
        Schema::dropIfExists('exam_permission_letters');
    }
};
