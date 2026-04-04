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
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->enum('letter_type', [
                'exam_permission',
                'internship',
                'internship_recommendation',
                'letter_of_assignment',
                'letter_of_assignment_individual',
                'passport_application',
                'research_data_request',
                'research_permission',
                'scholarships_statement',
                'testing_permission_request',
            ])->unique();
            $table->string('document_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letter_templates');
    }
};
