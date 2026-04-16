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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_method_id')
                ->nullable()
                ->after('shipping_country')
                ->constrained('shipping_methods')
                ->nullOnDelete();

            $table->decimal('weight', 8, 3)->nullable()->after('shipping_method_id');
            $table->string('shipping_carrier', 50)->nullable()->after('weight');
            $table->string('estimated_days', 30)->nullable()->after('shipping_carrier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_method_id']);
            $table->dropColumn([
                'shipping_method_id',
                'weight',
                'shipping_carrier',
                'estimated_days',
            ]);
        });
    }
};
