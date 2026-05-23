<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerApproved extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Verifikasi KTP Penjual Disetujui')
                    ->greeting('Halo, ' . $notifiable->name . '!')
                    ->line('Selamat! Dokumen KTP Anda telah diverifikasi dan disetujui oleh Administrator kami.')
                    ->line('Anda sekarang dapat mengakses dasbor penjual secara penuh dan mulai menambahkan produk ke etalase toko Anda.')
                    ->action('Buka Dasbor Penjual', url('/'))
                    ->line('Terima kasih telah bergabung bersama kami!');
    }
}
