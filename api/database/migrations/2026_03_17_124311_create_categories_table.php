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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();

            // Grupo do filtro nas páginas de categoria:
            // 'tipo' (clássico, mergulho...), 'mecanismo' (analógico, digital...)
            // null = categoria de sistema, não aparece nos filtros
            $table->enum('group', ['tipo', 'mecanismo'])->nullable()->index();

            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
