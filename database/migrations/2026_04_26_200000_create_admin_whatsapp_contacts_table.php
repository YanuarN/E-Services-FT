<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_whatsapp_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('whatsapp_number', 30)->nullable();
            $table->timestamps();
        });

        DB::table('admin_whatsapp_contacts')->insert([
            'id' => 1,
            'whatsapp_number' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_whatsapp_contacts');
    }
};
