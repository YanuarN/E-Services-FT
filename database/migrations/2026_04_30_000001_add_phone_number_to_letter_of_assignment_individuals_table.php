<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_of_assignment_individuals', function (Blueprint $table): void {
            $table->string('phone_number')->nullable()->after('nim');
        });
    }

    public function down(): void
    {
        Schema::table('letter_of_assignment_individuals', function (Blueprint $table): void {
            $table->dropColumn('phone_number');
        });
    }
};
