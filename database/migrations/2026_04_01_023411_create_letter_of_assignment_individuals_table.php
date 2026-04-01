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
        Schema::create('letter_of_assignment_individuals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nim');
            $table->string('number');
            $table->string('departement');
            $table->string('faculty');
            $table->text('address');
            $table->text('assignment');
            $table->string('place');
            $table->date('date');
            $table->enum('status', ['SUBMITTED', 'APPROVE', 'REJECT'])->default('SUBMITTED');
            $table->string('letter_number');
            $table->date('letter_date');
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
        Schema::dropIfExists('letter_of_assignment_individuals');
    }
};
