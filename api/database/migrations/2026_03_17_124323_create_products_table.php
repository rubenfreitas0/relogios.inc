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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            $table->enum('gender', ['masculino', 'feminino', 'unisexo'])->default('unisexo');
            $table->string('name', 255);
            $table->string('slug', 255)->unique();

            $table->string('short_description', 255);
            $table->text('description');

            $table->decimal('price', 10, 2)->unsigned();
            $table->integer('stock')->unsigned()->default(0);
            
            $table->boolean('is_active')->default(true)->index();;
            $table->boolean('is_featured')->default(false)->index();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
