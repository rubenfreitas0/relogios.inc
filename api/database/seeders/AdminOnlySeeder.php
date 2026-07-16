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
        // Categorias (tipo/mecanismo) — fixas, editáveis no admin, usadas nos filtros
        $this->call(CategoriesSeeder::class);

        // Zonas e métodos de envio (Europa)
        $this->call(ShippingZoneSeeder::class);
        $this->call(ShippingMethodSeeder::class);

        User::firstOrCreate(
            ['email' => 'admin@relogios.inc'],
            [
                'firstname'         => 'Admin',
                'lastname'          => 'RELOGIOS',
                'email_verified_at' => now(),
                'password'          => bcrypt('password'),
                'role'              => 'admin',
                'is_active'         => true,
            ]
        );
    }
}
