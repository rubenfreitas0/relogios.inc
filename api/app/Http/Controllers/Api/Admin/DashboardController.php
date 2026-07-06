<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Estatísticas gerais para o dashboard do backoffice.
     */
    public function stats(): JsonResponse
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // — Produtos
        $productStats = Product::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_active = true THEN 1 ELSE 0 END) as active')
            ->selectRaw('SUM(CASE WHEN is_active = true AND stock = 0 THEN 1 ELSE 0 END) as out_of_stock')
            ->selectRaw('SUM(CASE WHEN is_active = true AND stock > 0 AND stock <= 5 THEN 1 ELSE 0 END) as low_stock')
            ->first();

        // — Encomendas
        $orderStats = Order::query()
            ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as this_month', [$startOfMonth])
            ->selectRaw('SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN 1 ELSE 0 END) as last_month', [$startOfLastMonth, $endOfLastMonth])
            ->selectRaw('SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today', [$now->toDateString()])
            ->first();

        $pendingOrdersCount = Order::where('payment_status', PaymentStatus::PAID)
            ->whereIn('status', [OrderStatus::PENDING, OrderStatus::PROCESSING, OrderStatus::SHIPPED])
            ->count();

        $ordersByStatus = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // — Revenue
        $twelveMonthsAgo = $now->copy()->subMonths(11)->startOfMonth();

        $driver = DB::connection()->getDriverName();
        $monthExpression = match ($driver) {
            'mysql' => "DATE_FORMAT(created_at, '%Y-%m')",
            'pgsql' => "to_char(created_at, 'YYYY-MM')",
            default => "strftime('%Y-%m', created_at)", // sqlite & others
        };

        $revenueRaw = Order::query()
            ->selectRaw("$monthExpression as month")
            ->selectRaw('SUM(CASE WHEN payment_status = ? THEN total ELSE 0 END) as revenue', ['paid'])
            ->selectRaw('COUNT(*) as orders')
            ->where('created_at', '>=', $twelveMonthsAgo)
            ->groupByRaw($monthExpression)
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $revenueByMonth = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $key = $monthStart->format('Y-m');
            $row = $revenueRaw->get($key);

            $revenueByMonth[] = [
                'month' => $key,
                'label' => $monthStart->translatedFormat('M Y'),
                'total' => $row ? (float) $row->revenue : 0.0,
                'orders' => $row ? (int) $row->orders : 0,
            ];
        }

        $thisMonthKey     = $now->format('Y-m');
        $lastMonthKey     = $now->copy()->subMonth()->format('Y-m');
        $revenueThisMonth = $revenueRaw->has($thisMonthKey) ? (float) $revenueRaw->get($thisMonthKey)->revenue : 0.0;
        $revenueLastMonth = $revenueRaw->has($lastMonthKey) ? (float) $revenueRaw->get($lastMonthKey)->revenue : 0.0;

        // — Clientes —
        $totalCustomers = User::where('role', '!=', 'admin')->count();

        // — Últimas encomendas —
        $latestOrders = Order::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn(Order $order) => [
                'order_number'   => $order->order_number,
                'customer_name'  => $order->shipping_firstname . ' ' . $order->shipping_lastname,
                'customer_email' => $order->user?->email,
                'status'         => [
                    'value' => $order->status->value,
                    'label' => $order->status->label(),
                ],
                'payment_status' => [
                    'value' => $order->payment_status->value,
                    'label' => $order->payment_status->label(),
                ],
                'total'      => (float) $order->total,
                'created_at' => $order->created_at->toIso8601String(),
            ]);

        return response()->json([
            'products' => [
                'total'        => (int) $productStats->total,
                'active'       => (int) $productStats->active,
                'out_of_stock' => (int) $productStats->out_of_stock,
                'low_stock'    => (int) $productStats->low_stock,
            ],
            'orders' => [
                'today'         => (int) $orderStats->today,
                'this_month'    => (int) $orderStats->this_month,
                'last_month'    => (int) $orderStats->last_month,
                'pending_count' => $pendingOrdersCount,
                'by_status'     => $ordersByStatus,
            ],
            'revenue' => [
                'this_month' => $revenueThisMonth,
                'last_month' => $revenueLastMonth,
                'by_month'   => $revenueByMonth,
            ],
            'customers' => [
                'total' => $totalCustomers,
            ],
            'latest_orders' => $latestOrders,
        ]);
    }
}
