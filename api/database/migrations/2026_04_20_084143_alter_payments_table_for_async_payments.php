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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_id', 255)->nullable()->change();
            $table->timestamp('paid_at')->nullable()->change();
            $table->json('payment_data')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_data');
            // Nota: SQLite nem sempre suporta revert de change() bem como o driver antigo do MySQL, 
            // mas conceptualmente o down de allow NULL seria:
            $table->string('transaction_id', 255)->nullable(false)->change();
            $table->timestamp('paid_at')->nullable(false)->change();
        });
    }
};
