<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                    // ex: "CTT Normal"
            $table->string('carrier', 50);                  // ex: "CTT", "DPD", "DHL"
            $table->decimal('price', 10, 2);                // preço do envio
            $table->decimal('min_weight', 8, 3)->default(0); // peso mínimo em kg
            $table->decimal('max_weight', 8, 3);            // peso máximo em kg
            $table->string('estimated_days', 30);           // ex: "3-5 dias úteis"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
