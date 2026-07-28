<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Aimeos\MShop;
use Illuminate\Support\Facades\Log;
use Aimeos\MShop\Order\Item\Base;
use Carbon\Carbon;

/**
 * Class ReportController
 *
 * Handles report controller operations for the application.
 */
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
            
            $commissionRate = (float) \App\Models\SystemSetting::getVal('platform_commission', 5.0);
            $totalRevenue = 0;
            $grossRevenue = 0;
            $platformCommissionFee = 0;
            $totalOrders = count($orders);
            $dailySales = [];
            
            foreach ($orders as $order) {
                $gross = (float) $order->price;
                $commission = ($gross * $commissionRate) / 100;
                $revenue = $gross - $commission;
                $grossRevenue += $gross;
                $platformCommissionFee += $commission;
                $totalRevenue += $revenue;
                
                // Group by date
                $date = substr($order->ctime, 0, 10); // YYYY-MM-DD
                
                if (!isset($dailySales[$date])) {
                    $dailySales[$date] = [
                        'date' => $date,
                        'revenue' => 0,
                        'gross_revenue' => 0,
                        'platform_commission_fee' => 0,
                        'orders_count' => 0
                    ];
                }
                
                $dailySales[$date]['revenue'] += $revenue;
                $dailySales[$date]['gross_revenue'] += $gross;
                $dailySales[$date]['platform_commission_fee'] += $commission;
                $dailySales[$date]['orders_count'] += 1;
            }
            
            // Sort by date desc
            krsort($dailySales);
            
            return response()->json([
                'message' => 'Laporan penjualan berhasil diambil.',
                'data' => [
                    'summary' => [
                        'total_revenue' => $totalRevenue,
                        'gross_revenue' => $grossRevenue,
                        'platform_commission_fee' => $platformCommissionFee,
                        'commission_rate' => $commissionRate,
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

    /**
     * Export all merchant sales into a beautifully structured CSV file
     */
    public function export(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || !$user->siteid) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $orders = \DB::table('mshop_order')
                ->where('siteid', $user->siteid)
                ->orderBy('ctime', 'desc')
                ->get(['id', 'price', 'statuspayment', 'statusdelivery', 'ctime']);

            $filename = 'laporan_penjualan_merchant_' . Carbon::now()->format('Ymd_His') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $commissionRate = (float) \App\Models\SystemSetting::getVal('platform_commission', 5.0);

            $callback = function() use ($orders, $commissionRate) {
                $file = fopen('php://output', 'w');
                // CSV headers
                fputcsv($file, [
                    'ID Pesanan', 
                    'Tanggal Transaksi', 
                    'Total Pembayaran (Gross)', 
                    'Komisi Platform (' . $commissionRate . '%)', 
                    'Pendapatan Bersih Penjual', 
                    'Status Pembayaran', 
                    'Status Pengiriman'
                ]);

                foreach ($orders as $order) {
                    $gross = (float) $order->price;
                    $commission = ($gross * $commissionRate) / 100;
                    $net = $gross - $commission;

                    fputcsv($file, [
                        $order->id,
                        $order->ctime,
                        $gross,
                        $commission,
                        $net,
                        $this->getPaymentStatusText($order->statuspayment),
                        $this->getDeliveryStatusText($order->statusdelivery)
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('MerchantReport export error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal mengekspor laporan.']);
        }
    }

    /**
     * Get payment status text.
     */
    private function getPaymentStatusText($code)
    {
        switch ($code) {
            case -1: return 'Dibatalkan';
            case 0: return 'Belum Bayar';
            case 1: return 'Menunggu Pembayaran';
            case 2: return 'Pembayaran Escrow';
            case 3: return 'Pembayaran Berhasil';
            case 4: return 'Refunded';
            default: return 'Pending';
        }
    }

    /**
     * Get delivery status text.
     */
    private function getDeliveryStatusText($code)
    {
        switch ($code) {
            case -1: return 'Gagal Kirim';
            case 0: return 'Belum Diproses';
            case 1: return 'Sedang Dipacking';
            case 2: return 'Dalam Pengiriman';
            case 3: return 'Telah Sampai';
            case 4: return 'Selesai';
            default: return 'Pending';
        }
    }
}
