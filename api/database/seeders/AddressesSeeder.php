<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Address;

use App\Models\User;

class AddressesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'customer')->get();

        if ($users->isEmpty()) {
            Address::factory()->count(3)->create();
            return;
        }

        // Give some customer users a default address, and optionally a second one
        foreach ($users->take(5) as $user) {
            Address::factory()->create([
                'user_id' => $user->id,
                'is_default' => true,
            ]);

            if (rand(0, 1)) {
                Address::factory()->create([
                    'user_id' => $user->id,
                    'is_default' => false,
                ]);
            }
        }
    }
}
