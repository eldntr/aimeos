<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Aimeos\MShop;
use Illuminate\Support\Facades\Log;
use Aimeos\MShop\Order\Item\Base;
use App\Models\SellerWithdrawal;
use App\Models\User;

class WalletController extends Controller
{
    protected function getNumericSiteId($siteid)
    {
        $parts = array_filter(explode('.', trim($siteid, '.')));
        return end($parts);
    }

    protected function getSellerContext()
    {
        $context = app('aimeos.context')->get(false);
        $user = auth()->user();
        if ($user && $user->siteid) {
            $siteManager = \Aimeos\MShop::create($context, 'locale/site');
            $numericId = $this->getNumericSiteId($user->siteid);
            $siteItem = $siteManager->get($numericId);
            $context->setLocale(app('aimeos.locale')->get($context, $siteItem->getCode()));
        }
        return $context;
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
            
            // Calculate total revenue from Aimeos orders
            $totalRevenue = \DB::table('mshop_order')
                ->where('siteid', $user->siteid)
                ->where('statuspayment', '>=', Base::PAY_AUTHORIZED)
                ->sum('price');
            
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
            
            // Calculate total revenue from Aimeos orders
            $totalRevenue = \DB::table('mshop_order')
                ->where('siteid', $user->siteid)
                ->where('statuspayment', '>=', Base::PAY_AUTHORIZED)
                ->sum('price');
            
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
}
