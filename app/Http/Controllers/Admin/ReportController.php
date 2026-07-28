<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Aimeos\MShop\Order\Item\Base;

/**
 * Class ReportController
 *
 * Handles report controller operations for the application.
 */
class ReportController extends Controller
{
    /**
     * Export.
     */
    public function export(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = DB::table('mshop_order')
            ->select('id', 'siteid', 'price', 'statuspayment', 'ctime', 'currencyid', 'customerid')
            ->where('statuspayment', '>=', Base::PAY_AUTHORIZED)
            ->orderBy('ctime', 'desc');

        if ($startDate && $endDate) {
            $query->whereBetween('ctime', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $orders = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan_transaksi.csv"',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID Transaksi', 
                'Site ID', 
                'Customer ID', 
                'Total (Harga)', 
                'Mata Uang', 
                'Status Pembayaran', 
                'Waktu Transaksi'
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->siteid,
                    $order->customerid,
                    $order->price,
                    $order->currencyid,
                    $order->statuspayment,
                    $order->ctime
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
