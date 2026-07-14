<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
 
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
 
        // 1. fn_calculate_order_totals
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_calculate_order_totals(p_order_id BIGINT)
            RETURNS VOID AS $$
            DECLARE
                v_subtotal DECIMAL(10, 2);
                v_shipping_cost DECIMAL(10, 2);
                v_total DECIMAL(10, 2);
            BEGIN
                -- Calculate subtotal from order_items
                SELECT COALESCE(SUM(item_total), 0) INTO v_subtotal
                FROM order_items
                WHERE order_id = p_order_id;

                -- Get shipping cost from order
                SELECT COALESCE(shipping_cost, 0) INTO v_shipping_cost
                FROM orders
                WHERE id = p_order_id;

                -- Calculate total
                v_total := v_subtotal + v_shipping_cost;

                -- Update order table
                UPDATE orders
                SET
                    subtotal = v_subtotal,
                    total = v_total
                WHERE id = p_order_id;
            END;
            $$ LANGUAGE plpgsql;
        ");
 
        // 2. fn_apply_batch_discount
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
 
        // 3. Trigger Function fn_log_price_history
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_log_price_history()
            RETURNS TRIGGER AS $$
            BEGIN
                IF (OLD.price IS DISTINCT FROM NEW.price) OR (OLD.discount_price IS DISTINCT FROM NEW.discount_price) THEN
                    INSERT INTO product_price_logs (
                        product_id,
                        old_price,
                        new_price,
                        old_discount_price,
                        new_discount_price,
                        created_at
                    ) VALUES (
                        NEW.id,
                        OLD.price,
                        NEW.price,
                        OLD.discount_price,
                        NEW.discount_price,
                        NOW()
                    );
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");
 
        // 4. Create Trigger on products table
        DB::unprepared("
            CREATE TRIGGER tg_log_price_history
            AFTER UPDATE ON products
            FOR EACH ROW
            EXECUTE FUNCTION fn_log_price_history();
        ");
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
 
        DB::unprepared("DROP TRIGGER IF EXISTS tg_log_price_history ON products");
        DB::unprepared("DROP FUNCTION IF EXISTS fn_log_price_history()");
        DB::unprepared("DROP FUNCTION IF EXISTS fn_apply_batch_discount(BIGINT, DECIMAL)");
        DB::unprepared("DROP FUNCTION IF EXISTS fn_calculate_order_totals(BIGINT)");
    }
};
