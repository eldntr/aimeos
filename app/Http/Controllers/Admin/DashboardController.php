<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Aimeos\MShop\Order\Item\Base;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        // Total Users
        $totalUsers = User::where('superuser', 0)->count();

        // Total Merchants (approved sellers)
        $totalMerchants = User::where('seller_status', 'approved')->count();

        // Transaction stats (last 30 days default)
        $days = (int) $request->query('days', 30);
        $startDate = now()->subDays($days)->startOfDay()->format('Y-m-d H:i:s');

        // Total Revenue and Orders count from all paid orders
        $query = DB::table('mshop_order')
            ->where('statuspayment', '>=', Base::PAY_AUTHORIZED)
            ->where('ctime', '>=', $startDate);

        $orders = $query->get(['price', 'ctime']);
        
        $totalRevenue = 0;
        $totalTransactions = count($orders);
        $dailyTransactions = [];

        foreach ($orders as $order) {
            $revenue = (float) $order->price;
            $totalRevenue += $revenue;
            
            $date = substr($order->ctime, 0, 10);
            
            if (!isset($dailyTransactions[$date])) {
                $dailyTransactions[$date] = [
                    'date' => $date,
                    'revenue' => 0,
                    'transactions_count' => 0
                ];
            }
            
            $dailyTransactions[$date]['revenue'] += $revenue;
            $dailyTransactions[$date]['transactions_count'] += 1;
        }

        // Fill missing days with 0
        $graph = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $graph[] = $dailyTransactions[$date] ?? [
                'date' => $date,
                'revenue' => 0,
                'transactions_count' => 0
            ];
        }

        return response()->json([
            'message' => 'Statistik dashboard berhasil diambil.',
            'data' => [
                'total_users' => $totalUsers,
                'total_merchants' => $totalMerchants,
                'total_transactions' => $totalTransactions,
                'total_revenue' => (float) $totalRevenue,
                'graph' => $graph
            ]
        ]);
    }
}
