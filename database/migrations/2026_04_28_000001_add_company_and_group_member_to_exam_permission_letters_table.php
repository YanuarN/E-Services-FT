<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_permission_letters', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('nim');
            $table->text('company_address')->nullable()->after('company_name');
            $table->json('group_member')->nullable()->after('company_address');
        });
    }

    public function down(): void
    {
        Schema::table('exam_permission_letters', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'company_address',
                'group_member',
            ]);
        });
    }
};
