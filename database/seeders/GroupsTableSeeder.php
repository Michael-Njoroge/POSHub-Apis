<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pos_groups')->insert([
            [
                'id' => (string) Str::uuid(),
                'name' => 'Owner',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        DB::table('pos_groups')->insert([
            [
                'id' => (string) Str::uuid(),
                'name' => 'Admin',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Cashier',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
