<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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

        // — Produtos —
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $outOfStockProducts = Product::where('stock', 0)->where('is_active', true)->count();
        $lowStockProducts = Product::where('stock', '>', 0)
            ->where('stock', '<=', 5)
            ->where('is_active', true)
            ->count();

        // — Encomendas —
        $ordersThisMonth = Order::where('created_at', '>=', $startOfMonth)->count();
        $ordersLastMonth = Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $ordersToday = Order::whereDate('created_at', $now->toDateString())->count();

        $ordersByStatus = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // — Revenue —
        $revenueThisMonth = (float) Order::where('created_at', '>=', $startOfMonth)
            ->where('payment_status', 'paid')
            ->sum('total');

        $revenueLastMonth = (float) Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->where('payment_status', 'paid')
            ->sum('total');

        // — Revenue últimos 6 meses (para gráfico) —
        $revenueByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $now->copy()->subMonths($i)->endOfMonth();

            $revenueByMonth[] = [
                'month' => $monthStart->format('Y-m'),
                'label' => $monthStart->translatedFormat('M Y'),
                'total' => (float) Order::whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where('payment_status', 'paid')
                    ->sum('total'),
                'orders' => Order::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
            ];
        }

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
                'total'        => $totalProducts,
                'active'       => $activeProducts,
                'out_of_stock' => $outOfStockProducts,
                'low_stock'    => $lowStockProducts,
            ],
            'orders' => [
                'today'      => $ordersToday,
                'this_month' => $ordersThisMonth,
                'last_month' => $ordersLastMonth,
                'by_status'  => $ordersByStatus,
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
