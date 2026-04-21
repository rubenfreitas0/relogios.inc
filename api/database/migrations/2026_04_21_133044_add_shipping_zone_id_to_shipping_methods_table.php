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
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->foreignId('shipping_zone_id')->nullable()->constrained('shipping_zones')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->dropForeign(['shipping_zone_id']);
            $table->dropColumn('shipping_zone_id');
        });
    }
};
