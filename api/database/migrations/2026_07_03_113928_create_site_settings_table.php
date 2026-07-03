<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });

        // Popular dados iniciais
        DB::table('site_settings')->insert([
            [
                'key' => 'hero_subtitle',
                'value' => 'nova coleção'
            ],
            [
                'key' => 'hero_title',
                'value' => "CASIO MTP-1274 \n DARK EDITION \n COLEÇÃO PREMIUM"
            ],
            [
                'key' => 'hero_description',
                'value' => "Na RELOGIOS.inc selecionamos apenas o que resiste ao tempo.\nO Casio MTP-1274 combina aço inoxidável, precisão e um\ndesign atemporal que se adapta a qualquer ocasião."
            ],
            [
                'key' => 'hero_image',
                'value' => '/products/keyboards/relogio2.png'
            ],
            [
                'key' => 'hero_link',
                'value' => '/homens'
            ],
            [
                'key' => 'hero_button_text',
                'value' => 'ver relógio'
            ],
            [
                'key' => 'grid_hero_title',
                'value' => "Casio \nMTP-1274"
            ],
            [
                'key' => 'grid_hero_description',
                'value' => 'Precisão japonesa num design atemporal. Aço inoxidável, mostrador elegante e resistência para o dia-a-dia.'
            ],
            [
                'key' => 'grid_hero_image',
                'value' => '/products/categories/relogiocontainer1.png'
            ],
            [
                'key' => 'grid_hero_link',
                'value' => '/homens'
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
