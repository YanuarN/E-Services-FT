<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminWhatsappContactSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admin_whatsapp_contacts')->updateOrInsert(
            ['id' => 1],
            [
                'whatsapp_number' => '081234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
