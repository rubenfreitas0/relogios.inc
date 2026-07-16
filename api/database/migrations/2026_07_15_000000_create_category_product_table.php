<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Passa a relação produto–categoria de 1:N (products.category_id) para
     * N:N através da tabela pivot category_product. Um produto pode assim
     * pertencer a várias categorias (ex: um Tipo + um Mecanismo).
     */
    public function up(): void
    {
        // 1. Tabela pivot
        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'category_id']);
        });

        // 2. Copiar as associações existentes para o pivot
        if (Schema::hasColumn('products', 'category_id')) {
            DB::statement('
                INSERT INTO category_product (product_id, category_id, created_at, updated_at)
                SELECT id, category_id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                FROM products
                WHERE category_id IS NOT NULL
            ');
        }

        // 3. A função de descontos em lote deixa de depender de products.category_id
        if (DB::getDriverName() !== 'sqlite') {
            DB::unprepared("
                CREATE OR REPLACE FUNCTION fn_apply_batch_discount(p_category_id BIGINT, p_discount_percentage DECIMAL)
                RETURNS INT AS $$
                DECLARE
                    v_updated_rows INT;
                BEGIN
                    UPDATE products
                    SET discount_price = ROUND(price * (1.0 - (p_discount_percentage / 100.0)), 2)
                    WHERE is_active = true
                      AND deleted_at IS NULL
                      AND id IN (
                          SELECT product_id FROM category_product WHERE category_id = p_category_id
                      );

                    GET DIAGNOSTICS v_updated_rows = ROW_COUNT;
                    RETURN v_updated_rows;
                END;
                $$ LANGUAGE plpgsql;
            ");
        }

        // 4. Remover a coluna category_id dos produtos
        if (Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('category_id');
            });
        }
    }

    public function down(): void
    {
        // Repor a coluna category_id
        if (!Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            });

            // Repor a partir do pivot (primeira categoria de cada produto)
            DB::statement('
                UPDATE products
                SET category_id = (
                    SELECT category_id FROM category_product
                    WHERE category_product.product_id = products.id
                    ORDER BY category_product.category_id
                    LIMIT 1
                )
            ');
        }

        // Repor a função original baseada em category_id
        if (DB::getDriverName() !== 'sqlite') {
            DB::unprepared("
                CREATE OR REPLACE FUNCTION fn_apply_batch_discount(p_category_id BIGINT, p_discount_percentage DECIMAL)
                RETURNS INT AS $$
                DECLARE
                    v_updated_rows INT;
                BEGIN
                    UPDATE products
                    SET discount_price = ROUND(price * (1.0 - (p_discount_percentage / 100.0)), 2)
                    WHERE category_id = p_category_id AND is_active = true AND deleted_at IS NULL;

                    GET DIAGNOSTICS v_updated_rows = ROW_COUNT;
                    RETURN v_updated_rows;
                END;
                $$ LANGUAGE plpgsql;
            ");
        }

        Schema::dropIfExists('category_product');
    }
};
