<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WarehousesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pos_warehouses')->insert([
            [
                'id' => (string) Str::uuid(),
                'code' => 'WH001',
                'name' => 'Main Warehouse',
                'address' => '123 Main St, Nairobi',
                'map' => null,
                'phone' => '0123456789',
                'email' => 'mainwarehouse@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table('pos_warehouses')->insert([
            [
                'id' => (string) Str::uuid(),
                'code' => 'WH002',
                'name' => 'Secondary Warehouse',
                'address' => '456 Secondary St, Nairobi',
                'map' => null,
                'phone' => '0987654321',
                'email' => 'secondarywarehouse@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'code' => 'WH003',
                'name' => 'Tertiary Warehouse',
                'address' => '789 Tertiary St, Nairobi',
                'map' => null,
                'phone' => '1231231234',
                'email' => 'tertiarywarehouse@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
