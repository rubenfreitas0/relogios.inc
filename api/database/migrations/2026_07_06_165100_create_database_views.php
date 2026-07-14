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
 
        // 1. vw_revenue_by_region
        DB::statement("
            CREATE OR REPLACE VIEW vw_revenue_by_region AS
            SELECT 
                o.shipping_country AS country,
                sz.name AS shipping_zone_name,
                COUNT(o.id) AS total_orders,
                COALESCE(SUM(o.total), 0) AS total_revenue
            FROM orders o
            LEFT JOIN shipping_methods sm ON o.shipping_method_id = sm.id
            LEFT JOIN shipping_zones sz ON sm.shipping_zone_id = sz.id
            WHERE o.payment_status = 'paid'
            GROUP BY o.shipping_country, sz.name
        ");
 
        // 2. vw_product_stock_status
        DB::statement("
            CREATE OR REPLACE VIEW vw_product_stock_status AS
            SELECT 
                p.id AS product_id,
                p.name AS product_name,
                p.stock AS current_stock,
                p.price AS regular_price,
                p.discount_price AS discount_price,
                COALESCE(p.discount_price, p.price) AS active_price,
                CASE 
                    WHEN p.stock = 0 THEN 'OUT_OF_STOCK'
                    WHEN p.stock <= 5 THEN 'LOW_STOCK'
                    ELSE 'IN_STOCK'
                END AS stock_status,
                p.is_active AS is_active
            FROM products p
        ");
 
        // 3. vw_customer_lifetime_value
        DB::statement("
            CREATE OR REPLACE VIEW vw_customer_lifetime_value AS
            SELECT 
                u.id AS user_id,
                (u.firstname || ' ' || u.lastname) AS customer_name,
                u.email AS customer_email,
                COUNT(o.id) AS total_orders,
                COALESCE(SUM(o.total), 0) AS total_spent,
                CASE 
                    WHEN COUNT(o.id) > 0 THEN COALESCE(SUM(o.total), 0) / COUNT(o.id)
                    ELSE 0
                END AS average_order_value,
                u.created_at AS registered_at
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id AND o.payment_status = 'paid'
            GROUP BY u.id, u.firstname, u.lastname, u.email, u.created_at
        ");
 
        // 4. vw_monthly_revenue_report
        DB::statement("
            CREATE OR REPLACE VIEW vw_monthly_revenue_report AS
            SELECT 
                EXTRACT(YEAR FROM o.created_at)::INTEGER AS year,
                EXTRACT(MONTH FROM o.created_at)::INTEGER AS month,
                COUNT(o.id) AS total_orders,
                COALESCE(SUM(o.subtotal), 0) AS total_subtotal,
                COALESCE(SUM(o.shipping_cost), 0) AS total_shipping,
                COALESCE(SUM(o.total), 0) AS total_revenue
            FROM orders o
            WHERE o.payment_status = 'paid'
            GROUP BY EXTRACT(YEAR FROM o.created_at), EXTRACT(MONTH FROM o.created_at)
            ORDER BY year DESC, month DESC
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
 
        DB::statement("DROP VIEW IF EXISTS vw_monthly_revenue_report");
        DB::statement("DROP VIEW IF EXISTS vw_customer_lifetime_value");
        DB::statement("DROP VIEW IF EXISTS vw_product_stock_status");
        DB::statement("DROP VIEW IF EXISTS vw_revenue_by_region");
    }
};
