<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * OBSOLETA — substituída pelo campo `group` na tabela categories
 * e pelo campo `color` na tabela products. Este ficheiro pode ser
 * apagado; o down() limpa a tabela caso tenha sido criada.
 */
return new class extends Migration
{
    public function up(): void
    {
        // no-op
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_options');
    }
};
