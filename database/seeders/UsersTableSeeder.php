<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {
        $faker = Faker::create();
        $groupIds = DB::table('pos_groups')->pluck('id')->toArray();

        DB::table('pos_users')->insert([
            'id' => (string) Str::uuid(),
            'first_name' => 'Michael',
            'last_name' => 'Njoroge',
            'email' => 'mikethecoder12@gmail.com',
            'password' => Hash::make('admin123#'), 
            'username' => 'mike',
            'group_id' => DB::table('pos_groups')->where('name', 'Owner')->value('id'),
            'ip_address' => '127.0.0.1', 
            'active' => 1,
            'remember_code' => null,
            'last_login' => now(),
            'avatar' => null,
            'phone' => '+254716002152',
            'gender' => null,
            'last_ip_address' => null,
            'activation_code' => null,
            'forgotten_password_code' => null,
            'forgotten_password_time' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        for ($i = 1; $i <= 20; $i++) {
            DB::table('pos_users')->insert([
                'id' => (string) Str::uuid(),
                'first_name' => $faker->firstName, 
                'last_name' => $faker->lastName, 
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password@123'),
                'username' => $faker->unique()->userName,
                'group_id' => $groupIds[array_rand($groupIds)], 
                'ip_address' => $faker->ipv4, 
                'active' => rand(0, 1), 
                'remember_code' => null,
                'last_login' => now(),
                'avatar' => null,
                'phone' => $faker->e164PhoneNumber,
                'gender' => array_rand(['male', 'female', 'other']), 
                'last_ip_address' => $faker->ipv4,
                'activation_code' => null,
                'forgotten_password_code' => null,
                'forgotten_password_time' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
