<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reset para testes manuais — SEM apagar toda a base de dados.
 *
 * Corre com:  php artisan db:seed --class=ManualTestResetSeeder
 *
 * O que faz:
 *  - Apaga todas as encomendas (order_items, payments, orders) e carrinhos.
 *  - Deixa apenas o utilizador admin@relogios.inc.
 *  - Garante marcas e categorias (tipo/mecanismo) semeadas.
 *  - Reconstrói as zonas/métodos de envio (Continental, Ilhas, Europa).
 */
class ManualTestResetSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::transaction(function () {
            // 1. Encomendas e carrinhos
            DB::table('order_items')->delete();
            DB::table('payments')->delete();
            DB::table('orders')->delete();
            DB::table('cart_items')->delete();

            // 2. Deixar apenas o admin
            $adminEmail = 'admin@relogios.inc';
            $otherUserIds = User::where('email', '!=', $adminEmail)->pluck('id');

            if ($otherUserIds->isNotEmpty()) {
                DB::table('addresses')->whereIn('user_id', $otherUserIds)->delete();
                DB::table('personal_access_tokens')
                    ->where('tokenable_type', User::class)
                    ->whereIn('tokenable_id', $otherUserIds)
                    ->delete();

                if (Schema::hasTable('tickets')) {
                    DB::table('tickets')->whereIn('user_id', $otherUserIds)->delete();
                }

                User::whereIn('id', $otherUserIds)->delete();
            }

            // 3. Reconstruir envios (Continental, Ilhas, Europa)
            DB::table('shipping_methods')->delete();
            DB::table('shipping_zone_countries')->delete();
            DB::table('shipping_zones')->delete();
        });

        Schema::enableForeignKeyConstraints();

        // 4. Garantir o admin, marcas, categorias e envios via seeders
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

        $this->call([
            BrandsSeeder::class,
            CategoriesSeeder::class,
            ShippingZoneSeeder::class,
            ShippingMethodSeeder::class,
        ]);
    }
}
