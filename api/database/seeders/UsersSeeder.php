<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;


class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@relogios.inc'],
            [
                'firstname' => 'Admin',
                'lastname'  => 'RELOGIOS',
                'email_verified_at' => now(),
                'password'  => bcrypt('password'),
                'phone'     => '912345678',
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        User::factory()->count(5)->create();

        // User::factory()->count(10)->create();
    }
}
