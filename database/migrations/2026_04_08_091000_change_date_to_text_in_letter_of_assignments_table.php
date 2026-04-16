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
        if (! Schema::hasTable('letter_of_assignments') || ! Schema::hasColumn('letter_of_assignments', 'date')) {
            return;
        }

        Schema::table('letter_of_assignments', function (Blueprint $table) {
            $table->text('date')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('letter_of_assignments') || ! Schema::hasColumn('letter_of_assignments', 'date')) {
            return;
        }

        Schema::table('letter_of_assignments', function (Blueprint $table) {
            $table->date('date')->change();
        });
    }
};

