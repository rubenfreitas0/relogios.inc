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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('order_number', 30)->unique();

            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('pending')->index();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->index();

            $table->string('shipping_firstname', 100);
            $table->string('shipping_lastname', 100);
            $table->string('shipping_phone', 20)->nullable();
            $table->string('shipping_address_line1', 255);
            $table->string('shipping_address_line2', 255)->nullable();
            $table->string('shipping_city', 100);
            $table->string('shipping_postal_code', 20);
            $table->string('shipping_country', 2)->default('PT');

            $table->string('nif', 20)->nullable();

            $table->decimal('subtotal', 10, 2)->unsigned();
            $table->decimal('shipping_cost', 10, 2)->unsigned()->default(0);
            $table->decimal('total', 10, 2)->unsigned();

            $table->string('tracking_number', 255)->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
