<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Aimeos\MShop;
use Illuminate\Support\Facades\Log;
use Aimeos\MShop\Order\Item\Base;
use Carbon\Carbon;

class ReportController extends Controller
{
    use HasSellerContext;

    /**
     * Get basic sales reports
     */
    public function getSales(Request $request)
    {
        try {
            $context = $this->getSellerContext();
            
            MShop::cache(false); 
            MShop::cache(true);
            
            $user = auth()->user();
            
            // Get all orders that are paid
            $query = \DB::table('mshop_order')
                ->where('siteid', $user->siteid)
                ->where('statuspayment', '>=', Base::PAY_AUTHORIZED);
            
            // Allow optional filtering by date
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            
            if ($startDate && $endDate) {
                $query->whereBetween('ctime', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }
            
            $orders = $query->get(['price', 'ctime']);
            
            $totalRevenue = 0;
            $totalOrders = count($orders);
            $dailySales = [];
            
            foreach ($orders as $order) {
                $revenue = (float) $order->price;
                $totalRevenue += $revenue;
                
                // Group by date
                $date = substr($order->ctime, 0, 10); // YYYY-MM-DD
                
                if (!isset($dailySales[$date])) {
                    $dailySales[$date] = [
                        'date' => $date,
                        'revenue' => 0,
                        'orders_count' => 0
                    ];
                }
                
                $dailySales[$date]['revenue'] += $revenue;
                $dailySales[$date]['orders_count'] += 1;
            }
            
            // Sort by date desc
            krsort($dailySales);
            
            return response()->json([
                'message' => 'Laporan penjualan berhasil diambil.',
                'data' => [
                    'summary' => [
                        'total_revenue' => $totalRevenue,
                        'total_orders' => $totalOrders,
                    ],
                    'daily_sales' => array_values($dailySales)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('SellerReport getSales error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil laporan penjualan.'], 500);
        }
    }
}
