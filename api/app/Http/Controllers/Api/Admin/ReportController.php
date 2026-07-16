<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    /**
     * Relatório de vendas: agregação anual e mensal das encomendas pagas.
     * GET /api/admin/reports
     */
    public function index(): JsonResponse
    {
        $orders = Order::where('payment_status', PaymentStatus::PAID)
            ->get(['total', 'created_at']);

        $monthNames = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
        ];

        // Agregação anual e mensal
        $byYear = [];
        $byMonth = [];

        foreach ($orders as $order) {
            $date = $order->created_at;
            $year = (int) $date->format('Y');
            $month = (int) $date->format('n');
            $total = (float) $order->total;

            $byYear[$year] ??= ['revenue' => 0.0, 'orders' => 0];
            $byYear[$year]['revenue'] += $total;
            $byYear[$year]['orders']++;

            $key = $year . '-' . $month;
            $byMonth[$key] ??= ['year' => $year, 'month' => $month, 'revenue' => 0.0, 'orders' => 0];
            $byMonth[$key]['revenue'] += $total;
            $byMonth[$key]['orders']++;
        }

        ksort($byYear);

        // Série anual (com crescimento face ao ano anterior)
        $yearly = [];
        $prevRevenue = null;
        foreach ($byYear as $year => $data) {
            $revenue = round($data['revenue'], 2);
            $count = $data['orders'];
            $growth = 0.0;
            if ($prevRevenue !== null && $prevRevenue > 0) {
                $growth = round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1);
            }
            $yearly[] = [
                'year'           => $year,
                'revenue'        => $revenue,
                'orders'         => $count,
                'growth'         => $growth,
                'average_ticket' => $count > 0 ? round($revenue / $count, 2) : 0,
            ];
            $prevRevenue = $revenue;
        }

        // Série mensal (ordenada por ano/mês)
        $monthly = [];
        foreach ($byMonth as $data) {
            $revenue = round($data['revenue'], 2);
            $count = $data['orders'];
            $monthly[] = [
                'year'           => $data['year'],
                'month'          => $data['month'],
                'month_name'     => $monthNames[$data['month']],
                'label'          => $monthNames[$data['month']] . ' ' . $data['year'],
                'revenue'        => $revenue,
                'orders'         => $count,
                'average_ticket' => $count > 0 ? round($revenue / $count, 2) : 0,
            ];
        }
        usort($monthly, fn($a, $b) => [$a['year'], $a['month']] <=> [$b['year'], $b['month']]);

        // Resumo global
        $totalRevenue = round(array_sum(array_column($yearly, 'revenue')), 2);
        $totalOrders  = array_sum(array_column($yearly, 'orders'));
        $growthValues = array_map(fn($y) => $y['growth'], array_slice($yearly, 1));
        $avgGrowth = count($growthValues) > 0
            ? round(array_sum($growthValues) / count($growthValues), 1)
            : 0;

        return response()->json([
            'yearly'  => $yearly,
            'monthly' => $monthly,
            'summary' => [
                'total_revenue_since_start' => $totalRevenue,
                'total_orders_since_start'  => $totalOrders,
                'average_annual_growth'     => $avgGrowth,
            ],
        ]);
    }
}
