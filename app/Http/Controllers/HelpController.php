<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserReport;
use Illuminate\Support\Facades\Auth;

/**
 * Class HelpController
 *
 * Handles help controller operations for the application.
 */
class HelpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $faqs = [
            'umum' => [
                [
                    'q' => 'Apa itu Reborns?',
                    'a' => 'Reborns adalah platform e-commerce preloved terpercaya untuk jual-beli barang bekas layak pakai berkualitas, aman, dan mendukung gaya hidup ramah lingkungan.'
                ],
                [
                    'q' => 'Apakah transaksi di Reborns terjamin aman?',
                    'a' => 'Sangat aman. Reborns menggunakan sistem saldo rekening bersama, verifikasi toko penjual (KYC), serta sistem penanganan sengketa (dispute) untuk menjamin hak pembeli dan penjual.'
                ]
            ],
            'pembelian' => [
                [
                    'q' => 'Bagaimana cara berbelanja produk di Reborns?',
                    'a' => 'Cukup cari produk yang Anda inginkan, klik tombol "Tambah ke Keranjang", masuk ke halaman keranjang untuk checkout, lalu selesaikan pembayaran menggunakan metode transfer bank.'
                ],
                [
                    'q' => 'Mengapa saya tidak bisa melakukan Checkout?',
                    'a' => 'Pastikan Anda telah mengisi alamat pengiriman secara lengkap di menu "Pengaturan Akun > Alamat" terlebih dahulu sebelum melakukan proses checkout.'
                ]
            ],
            'penjualan' => [
                [
                    'q' => 'Bagaimana cara membuka toko dan berjualan?',
                    'a' => 'Masuk ke akun Anda, klik menu "Toko Saya" di sidebar navigasi untuk mendaftar sebagai Merchant. Isi profil toko, unggah data KTP untuk verifikasi, serta daftarkan rekening bank tujuan pencairan dana.'
                ],
                [
                    'q' => 'Bagaimana cara mengedit produk dan stok barang?',
                    'a' => 'Masuk ke dashboard merchant, buka halaman "Produk Saya", pilih edit pada produk terkait. Anda dapat mengubah detail deskripsi, stok barang, harga, serta menambah/menghapus foto di Galeri Foto Produk.'
                ]
            ],
            'kebijakan' => [
                [
                    'q' => 'Bagaimana jika barang yang diterima tidak sesuai deskripsi?',
                    'a' => 'Anda dapat mengajukan sengketa (Dispute) pesanan dari detail riwayat transaksi Anda. Saldo pembelian akan ditahan oleh sistem kami sampai sengketa diselesaikan secara adil antara pembeli dan penjual.'
                ],
                [
                    'q' => 'Berapa lama batas waktu penjual mengirimkan barang?',
                    'a' => 'Penjual memiliki waktu maksimal 2x24 jam untuk mengirimkan paket dan memperbarui resi pengiriman setelah pesanan lunas.'
                ]
            ]
        ];

        $reports = Auth::check() 
            ? UserReport::where('user_id', Auth::id())->latest()->get() 
            : collect();

        return view('pages.static.help-center', compact('faqs', 'reports'));
    }

    /**
     * Store report.
     */
    public function storeReport(Request $request)
    {
        $rules = [
            'category' => ['required', 'string', 'in:umum,pembelian,penjualan,kebijakan,lainnya'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
        ];

        if (!Auth::check()) {
            $rules['name'] = ['required', 'string', 'max:100'];
            $rules['email'] = ['required', 'email', 'max:150'];
        }

        $validated = $request->validate($rules);

        $report = new UserReport($validated);
        if (Auth::check()) {
            $report->user_id = Auth::id();
            $report->name = Auth::user()->name;
            $report->email = Auth::user()->email;
        }
        $report->status = 'pending';
        $report->save();

        return redirect()->route('help-center')->with('success', 'Laporan masalah Anda berhasil dikirim! Admin akan segera memeriksa dan membantu menyelesaikannya.');
    }
}
