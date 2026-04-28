<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('research_data_request_letters', function (Blueprint $table) {
            $table->json('group_member')->nullable()->after('company_address');
        });
    }

    public function down(): void
    {
        Schema::table('research_data_request_letters', function (Blueprint $table) {
            $table->dropColumn('group_member');
        });
    }
};
