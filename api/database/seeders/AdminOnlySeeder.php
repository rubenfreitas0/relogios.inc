<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

/**
 * Seeder mínimo: apenas a conta de administrador.
 * Usar com: php artisan migrate:fresh --seeder=AdminOnlySeeder
 */
class AdminOnlySeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@relogios.inc'],
            [
                'firstname'         => 'Admin',
                'lastname'          => 'RELOGIOS',
                'email_verified_at' => now(),
                'password'          => bcrypt('password'),
                'phone'             => '912345678',
                'role'              => 'admin',
                'is_active'         => true,
            ]
        );
    }
}
