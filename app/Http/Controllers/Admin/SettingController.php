<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;

class SettingController extends Controller
{
    /**
     * Get all active system configurations
     */
    public function index()
    {
        return response()->json([
            'message' => 'Pengaturan sistem berhasil diambil.',
            'data' => [
                'max_product_images' => (int) SystemSetting::getVal('max_product_images', 6),
                'max_product_videos' => (int) SystemSetting::getVal('max_product_videos', 2),
                'max_cart_items' => (int) SystemSetting::getVal('max_cart_items', 20),
                'platform_commission' => (float) SystemSetting::getVal('platform_commission', 5.0),
                'page_about' => SystemSetting::getVal('page_about', 'Reborns bergerak untuk memudahkan gaya hidup berkelanjutan melalui marketplace prelove yang aman, terpercaya, dan penuh pilihan. Misi kami adalah membantu pengguna menemukan produk berkualitas sambil mendukung ekonomi sirkular.'),
                'page_how_it_works' => SystemSetting::getVal('page_how_it_works', 'Di Reborns, kamu bisa mencari, membeli, dan menjual barang preloved dengan mudah. Kami menyediakan fitur pencarian, kategori, dan checkout yang nyaman, serta dukungan keamanan untuk transaksi yang aman.'),
                'page_career' => SystemSetting::getVal('page_career', 'Sedang mencari peluang baru? Reborns sedang berkembang dan kami membuka kesempatan bagi talenta yang ingin berkontribusi pada ekonomi berkelanjutan.'),
                'page_help_center' => SystemSetting::getVal('page_help_center', 'Butuh bantuan? Temukan jawaban atas pertanyaan umum seputar pembelian, penjualan, pengiriman, dan kebijakan kami di sini.'),
                'page_security' => SystemSetting::getVal('page_security', 'Keamanan transaksi adalah prioritas kami. Kami menggunakan standar keamanan terbaik untuk melindungi data pengguna dan memastikan proses belanja tetap aman.'),
                'page_terms' => SystemSetting::getVal('page_terms', 'Pelajari syarat dan ketentuan penggunaan layanan Reborns agar transaksi kamu berjalan lancar dan sesuai kebijakan kami.')
            ]
        ]);
    }

    /**
     * Store/update system configurations
     */
    public function store(Request $request)
    {
        $request->validate([
            'max_product_images' => 'required|integer|min:1|max:50',
            'max_product_videos' => 'required|integer|min:0|max:10',
            'max_cart_items' => 'required|integer|min:1|max:100',
            'platform_commission' => 'required|numeric|min:0|max:100',
            'page_about' => 'nullable|string',
            'page_how_it_works' => 'nullable|string',
            'page_career' => 'nullable|string',
            'page_help_center' => 'nullable|string',
            'page_security' => 'nullable|string',
            'page_terms' => 'nullable|string'
        ]);

        SystemSetting::setVal('max_product_images', $request->max_product_images);
        SystemSetting::setVal('max_product_videos', $request->max_product_videos);
        SystemSetting::setVal('max_cart_items', $request->max_cart_items);
        SystemSetting::setVal('platform_commission', $request->platform_commission);
        
        SystemSetting::setVal('page_about', $request->page_about ?? '');
        SystemSetting::setVal('page_how_it_works', $request->page_how_it_works ?? '');
        SystemSetting::setVal('page_career', $request->page_career ?? '');
        SystemSetting::setVal('page_help_center', $request->page_help_center ?? '');
        SystemSetting::setVal('page_security', $request->page_security ?? '');
        SystemSetting::setVal('page_terms', $request->page_terms ?? '');

        return response()->json([
            'message' => 'Pengaturan sistem berhasil diperbarui.',
            'data' => [
                'max_product_images' => (int) SystemSetting::getVal('max_product_images'),
                'max_product_videos' => (int) SystemSetting::getVal('max_product_videos'),
                'max_cart_items' => (int) SystemSetting::getVal('max_cart_items'),
                'platform_commission' => (float) SystemSetting::getVal('platform_commission'),
                'page_about' => SystemSetting::getVal('page_about'),
                'page_how_it_works' => SystemSetting::getVal('page_how_it_works'),
                'page_career' => SystemSetting::getVal('page_career'),
                'page_help_center' => SystemSetting::getVal('page_help_center'),
                'page_security' => SystemSetting::getVal('page_security'),
                'page_terms' => SystemSetting::getVal('page_terms')
            ]
        ]);
    }
}
