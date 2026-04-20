<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_usage_requests', function (Blueprint $table): void {
            $table->string('public_token')->nullable()->unique()->after('document');
            $table->string('letter_number')->nullable()->after('public_token');
            $table->date('letter_date')->nullable()->after('letter_number');
            $table->string('pdf_path')->nullable()->after('letter_date');

            $table->index('public_token');
        });
    }

    public function down(): void
    {
        Schema::table('room_usage_requests', function (Blueprint $table): void {
            $table->dropIndex(['public_token']);
            $table->dropUnique(['public_token']);
            $table->dropColumn([
                'public_token',
                'letter_number',
                'letter_date',
                'pdf_path',
            ]);
        });
    }
};
