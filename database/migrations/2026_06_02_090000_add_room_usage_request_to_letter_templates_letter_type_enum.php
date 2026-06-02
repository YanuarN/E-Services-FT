<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE `letter_templates`
            MODIFY COLUMN `letter_type` ENUM(
                'exam_permission',
                'internship',
                'internship_recommendation',
                'letter_of_assignment',
                'letter_of_assignment_individual',
                'passport_application',
                'research_data_request',
                'research_permission',
                'room_usage_request',
                'scholarships_statement',
                'testing_permission_request'
            ) NOT NULL
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE `letter_templates`
            MODIFY COLUMN `letter_type` ENUM(
                'exam_permission',
                'internship',
                'internship_recommendation',
                'letter_of_assignment',
                'letter_of_assignment_individual',
                'passport_application',
                'research_data_request',
                'research_permission',
                'scholarships_statement',
                'testing_permission_request'
            ) NOT NULL
        SQL);
    }
};
