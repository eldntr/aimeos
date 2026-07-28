<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Aimeos\MShop;
use Illuminate\Support\Facades\Log;
use Aimeos\MShop\Order\Item\Base;
use App\Models\SellerWithdrawal;
use App\Models\User;

/**
 * Class WalletController
 *
 * Handles wallet controller operations for the application.
 */
class WalletController extends Controller
{
    use HasSellerContext;

    /**
     * Get commission rate.
     */
    private function getCommissionRate(): float
    {
        return (float) \App\Models\SystemSetting::getVal('platform_commission', 5.0);
    }

    /**
     * Get seller share.
     */
    private function getSellerShare(float $grossAmount): float
    {
        return $grossAmount - (($grossAmount * $this->getCommissionRate()) / 100);
    }

    /**
     * Get completed seller order rows.
     */
    private function getCompletedSellerOrderRows(string $siteid)
    {
        return \DB::table('mshop_order')
            ->join('mshop_order_product', 'mshop_order.id', '=', 'mshop_order_product.parentid')
            ->join('mshop_product', 'mshop_order_product.prodid', '=', 'mshop_product.id')
            ->where('mshop_product.siteid', $siteid)
            ->where('mshop_order.statusdelivery', Base::STAT_DELIVERED)
            ->whereNotIn('mshop_order.statuspayment', [Base::PAY_REFUSED, Base::PAY_REFUND, Base::PAY_CANCELED])
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('mshop_review')
                    ->whereColumn('mshop_review.refid', 'mshop_order.id')
                    ->where('mshop_review.domain', 'order')
                    ->where('mshop_review.status', '>', 0);
            })
            ->groupBy('mshop_order.id', 'mshop_order.ctime', 'mshop_order.statuspayment')
            ->selectRaw('mshop_order.id, mshop_order.ctime, mshop_order.statuspayment, SUM(mshop_order_product.price * mshop_order_product.quantity) as seller_product_total')
            ->get();
    }

    /**
     * Get completed seller revenue.
     */
    private function getCompletedSellerRevenue(string $siteid): float
    {
        return $this->getCompletedSellerOrderRows($siteid)
            ->sum(function ($order) {
                $netGross = (float) $order->seller_product_total * (1 - ($this->getAdminRefundPercent((string) $order->id) / 100));
                return $this->getSellerShare($netGross);
            });
    }

    /**
     * Get admin refund percent.
     */
    private function getAdminRefundPercent(string $orderId): float
    {
        $responses = \DB::table('mshop_review')
            ->where('domain', 'order')
            ->where('refid', $orderId)
            ->where('status', 0)
            ->pluck('response');

        foreach ($responses as $response) {
            if (preg_match('/\[ADMIN RESOLUTION:\s*PARTIAL_REFUND\s+(\d+)%/i', (string) $response, $matches)) {
                return min(99, max(1, (float) $matches[1]));
            }
        }

        return 0.0;
    }

    /**
     * Calculate wallet balance for the seller.
     */
    public function getWallet(Request $request)
    {
        try {
            $context = $this->getSellerContext();
            $user = auth()->user();
            
            MShop::cache(false); 
            MShop::cache(true);
            
            $totalRevenue = $this->getCompletedSellerRevenue($user->siteid);
            
            // Calculate withdrawn and pending
            $withdrawals = SellerWithdrawal::where('siteid', $user->siteid)->get();
            
            $totalWithdrawn = $withdrawals->where('status', 'approved')->sum('amount');
            $pendingWithdrawal = $withdrawals->where('status', 'pending')->sum('amount');
            
            // Calculate available balance
            $availableBalance = (float) $totalRevenue - (float) $totalWithdrawn - (float) $pendingWithdrawal;
            
            return response()->json([
                'message' => 'Informasi wallet berhasil diambil.',
                'data' => [
                    'total_revenue' => (float) $totalRevenue,
                    'total_withdrawn' => (float) $totalWithdrawn,
                    'pending_withdrawal' => (float) $pendingWithdrawal,
                    'available_balance' => max(0, $availableBalance),
                    'currency' => 'IDR' // Assume IDR for simplicity or get from first order
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('SellerWallet getWallet error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil informasi wallet.'], 500);
        }
    }

    /**
     * Request a withdrawal
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        try {
            $user = auth()->user();
            $context = $this->getSellerContext();
            
            MShop::cache(false); 
            MShop::cache(true);
            
            $totalRevenue = $this->getCompletedSellerRevenue($user->siteid);
            
            // Calculate withdrawn and pending
            $withdrawals = SellerWithdrawal::where('siteid', $user->siteid)->get();
            $totalWithdrawn = $withdrawals->where('status', 'approved')->sum('amount');
            $pendingWithdrawal = $withdrawals->where('status', 'pending')->sum('amount');
            
            $availableBalance = (float) $totalRevenue - (float) $totalWithdrawn - (float) $pendingWithdrawal;
            $withdrawAmount = (float) $request->amount;

            if ($withdrawAmount > $availableBalance) {
                return response()->json(['message' => 'Saldo tidak mencukupi untuk melakukan penarikan.'], 400);
            }

            // We use the bank details from the user's profile
            $bankDetail = $user->bankDetail;
            if (!$bankDetail || !$bankDetail->bank_account_number || !$bankDetail->bank_name) {
                return response()->json(['message' => 'Informasi rekening bank belum lengkap. Harap lengkapi profil toko.'], 400);
            }

            $withdrawal = SellerWithdrawal::create([
                'siteid' => $user->siteid,
                'amount' => $withdrawAmount,
                'status' => 'pending',
                'bank_name' => $bankDetail->bank_name,
                'bank_account_number' => $bankDetail->bank_account_number,
                'bank_account_name' => $bankDetail->bank_account_name,
            ]);

            return response()->json([
                'message' => 'Permintaan penarikan dana berhasil diajukan.',
                'data' => $withdrawal
            ], 201);

        } catch (\Exception $e) {
            Log::error('SellerWallet withdraw error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengajukan penarikan dana.'], 500);
        }
    }

    /**
     * Get wallet ledger logs (Buku Kas & Riwayat Mutasi)
     */
    public function getLedger(Request $request)
    {
        try {
            $user = auth()->user();
            
            // 1. Fetch credits: completed orders containing this seller's products
            $orders = $this->getCompletedSellerOrderRows($user->siteid);
            
            // 2. Fetch debits: withdrawals
            $withdrawals = \DB::table('seller_withdrawals')
                ->where('siteid', $user->siteid)
                ->select('id', 'amount', 'created_at', 'status', 'bank_name', 'bank_account_number')
                ->get();
                
            $mutations = [];
            
            $globalCommissionRate = $this->getCommissionRate();
            
            // Map orders to credit mutations
            foreach ($orders as $order) {
                $refundPercent = $this->getAdminRefundPercent((string) $order->id);
                $netGross = $order->seller_product_total * (1 - ($refundPercent / 100));
                $platformFee = ($netGross * $globalCommissionRate) / 100;
                $sellerShare = $netGross - $platformFee;
                
                $mutations[] = [
                    'id' => 'TX-ORD-' . $order->id,
                    'reference' => 'Pesanan #' . $order->id,
                    'type' => 'credit',
                    'amount' => (float) $sellerShare,
                    'timestamp' => strtotime($order->ctime),
                    'date_string' => $order->ctime,
                    'description' => 'Pembayaran pesanan dari pembeli (Dipotong komisi platform ' . $globalCommissionRate . '%' . ($refundPercent > 0 ? ', refund pembeli ' . $refundPercent . '%' : '') . ')',
                    'status' => 'Saldo Cair'
                ];
            }
            
            // Map withdrawals to debit mutations
            foreach ($withdrawals as $w) {
                $statusText = $w->status === 'approved' ? 'Berhasil' : 'Menunggu Persetujuan';
                $mutations[] = [
                    'id' => 'TX-WD-' . $w->id,
                    'reference' => 'Tarik Dana #' . $w->id,
                    'type' => 'debit',
                    'amount' => (float) $w->amount,
                    'timestamp' => strtotime($w->created_at),
                    'date_string' => $w->created_at,
                    'description' => 'Penarikan dana ke Rekening ' . $w->bank_name . ' (' . $w->bank_account_number . ')',
                    'status' => $statusText
                ];
            }
            
            // Sort chronologically (oldest first) to compute running balance
            usort($mutations, function($a, $b) {
                return $a['timestamp'] <=> $b['timestamp'];
            });
            
            $runningBalance = 0.0;
            foreach ($mutations as &$m) {
                if ($m['type'] === 'credit') {
                    $runningBalance += $m['amount'];
                } else {
                    $runningBalance -= $m['amount'];
                }
                $m['balance_after'] = $runningBalance;
            }
            
            // Sort back chronologically descending (newest first)
            usort($mutations, function($a, $b) {
                return $b['timestamp'] <=> $a['timestamp'];
            });
            
            return response()->json([
                'message' => 'Buku Kas & Riwayat Mutasi berhasil diambil.',
                'data' => $mutations
            ]);
            
        } catch (\Exception $e) {
            Log::error('SellerWallet getLedger error: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil riwayat mutasi.'], 500);
        }
    }
}
