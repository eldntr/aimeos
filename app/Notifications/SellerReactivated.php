<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerReactivated extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Akun Merchant Anda Telah Diaktifkan Kembali')
                    ->greeting('Halo, ' . $notifiable->name . '!')
                    ->line('Kabar baik! Akun toko/merchant Anda telah diaktifkan kembali oleh administrator.')
                    ->line('Anda sekarang dapat mengakses kembali seluruh fitur dasbor penjual, mengelola produk Anda, dan melakukan transaksi seperti biasa.')
                    ->action('Buka Dasbor Penjual', url('/'))
                    ->line('Terima kasih telah bersama kami!');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Akun Toko Diaktifkan Kembali',
            'message' => 'Selamat, akun toko/merchant Anda telah diaktifkan kembali oleh administrator.',
        ];
    }
}
