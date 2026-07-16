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
        // Apenas a conta de administrador (sem utilizadores de teste).
        User::firstOrCreate(
            ['email' => 'admin@relogios.inc'],
            [
                'firstname' => 'Admin',
                'lastname'  => 'RELOGIOS',
                'email_verified_at' => now(),
                'password'  => bcrypt('password'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );
    }
}
