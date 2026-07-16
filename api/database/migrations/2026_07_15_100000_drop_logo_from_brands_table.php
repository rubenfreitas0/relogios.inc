<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove a coluna logo das marcas — a funcionalidade de logótipo foi retirada.
     */
    public function up(): void
    {
        if (Schema::hasColumn('brands', 'logo')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->dropColumn('logo');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('brands', 'logo')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->string('logo')->nullable();
            });
        }
    }
};
