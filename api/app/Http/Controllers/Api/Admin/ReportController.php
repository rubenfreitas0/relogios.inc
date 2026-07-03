<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Obter dados de relatórios estatísticos desde 2013.
     */
    public function index(Request $request): JsonResponse
    {
        $startYear = 2013;
        $currentYear = (int) date('Y');
        
        // Dados base para 2013
        $baseRevenue = 45000.00;
        $baseOrders = 250;
        
        $yearlyData = [];
        $lastRevenue = $baseRevenue;
        $lastOrders = $baseOrders;
        
        // Semente de aleatoriedade fixa para consistência
        srand(42);

        for ($year = $startYear; $year <= $currentYear; $year++) {
            if ($year === $startYear) {
                $revenue = $baseRevenue;
                $orders = $baseOrders;
                $growth = 0.0;
            } else {
                // Crescimento de ~15% com uma pequena variação de +/- 3%
                $growthFactor = 0.15 + (rand(-30, 30) / 1000.0);
                $revenue = $lastRevenue * (1 + $growthFactor);
                $orders = (int) round($lastOrders * (1 + $growthFactor));
                $growth = $growthFactor * 100;
            }
            
            $yearlyData[$year] = [
                'year' => $year,
                'revenue' => round($revenue, 2),
                'orders' => $orders,
                'growth' => round($growth, 1),
                'average_ticket' => round($revenue / $orders, 2)
            ];
            
            $lastRevenue = $revenue;
            $lastOrders = $orders;
        }

        // Dados mensais para os últimos 3 anos (detalhamento)
        $monthlyData = [];
        $months = [
            1 => ['name' => 'Janeiro', 'factor' => 0.75],
            2 => ['name' => 'Fevereiro', 'factor' => 0.80],
            3 => ['name' => 'Março', 'factor' => 0.95],
            4 => ['name' => 'Abril', 'factor' => 0.90],
            5 => ['name' => 'Maio', 'factor' => 1.05],
            6 => ['name' => 'Junho', 'factor' => 1.00],
            7 => ['name' => 'Julho', 'factor' => 0.85],
            8 => ['name' => 'Agosto', 'factor' => 0.70],
            9 => ['name' => 'Setembro', 'factor' => 0.95],
            10 => ['name' => 'Outubro', 'factor' => 1.05],
            11 => ['name' => 'Novembro', 'factor' => 1.45], // Black Friday
            12 => ['name' => 'Dezembro', 'factor' => 1.60], // Natal
        ];

        // Gerar histórico mensal dos últimos 3 anos
        for ($year = $currentYear - 2; $year <= $currentYear; $year++) {
            $yearTotalRevenue = $yearlyData[$year]['revenue'];
            $yearTotalOrders = $yearlyData[$year]['orders'];
            
            // Distribuir com base no fator de sazonalidade
            $totalFactors = array_sum(array_column($months, 'factor'));
            
            foreach ($months as $mNum => $mInfo) {
                // Se for o ano atual, parar no mês corrente
                if ($year === $currentYear && $mNum > (int) date('n')) {
                    break;
                }
                
                $monthShare = $mInfo['factor'] / $totalFactors;
                // Pequena variação mensal aleatória (+/- 5%)
                $variance = 1.0 + (rand(-50, 50) / 1000.0);
                
                $mRevenue = $yearTotalRevenue * $monthShare * $variance;
                $mOrders = (int) round($yearTotalOrders * $monthShare * $variance);
                
                $monthlyData[] = [
                    'year' => $year,
                    'month' => $mNum,
                    'month_name' => $mInfo['name'],
                    'label' => "{$mInfo['name']} {$year}",
                    'revenue' => round($mRevenue, 2),
                    'orders' => $mOrders,
                    'average_ticket' => $mOrders > 0 ? round($mRevenue / $mOrders, 2) : 0.0
                ];
            }
        }

        return response()->json([
            'yearly' => array_values($yearlyData),
            'monthly' => $monthlyData,
            'summary' => [
                'total_revenue_since_start' => round(array_sum(array_column($yearlyData, 'revenue')), 2),
                'total_orders_since_start' => array_sum(array_column($yearlyData, 'orders')),
                'average_annual_growth' => round(array_sum(array_column($yearlyData, 'growth')) / (count($yearlyData) - 1), 1)
            ]
        ]);
    }
}
