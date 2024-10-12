<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {
        $firstGroup = DB::table('pos_groups')->orderBy('created_at', 'asc')->first();

        DB::table('pos_users')->insert([
            'id' => (string) Str::uuid(),
            'first_name' => 'Michael',
            'last_name' => 'Njoroge',
            'email' => 'mikethecoder12@gmail.com',
            'password' => Hash::make('admin123#'), 
            'username' => 'mike',
            'group_id' => $firstGroup->id,
            'ip_address' => '127.0.0.1', 
            'active' => 1,
            'remember_code' => null,
            'last_login' => now(),
            'avatar' => null,
            'phone' => null,
            'gender' => null,
            'last_ip_address' => null,
            'activation_code' => null,
            'forgotten_password_code' => null,
            'forgotten_password_time' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
