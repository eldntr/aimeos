<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerSuspended extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Akun Merchant Anda Ditangguhkan (Suspended)')
                    ->greeting('Halo, ' . $notifiable->name . '.')
                    ->line('Kami ingin menginformasikan bahwa akun toko/merchant Anda saat ini telah ditangguhkan (suspend) sementara oleh administrator.')
                    ->line('Selama masa penangguhan ini, Anda tidak dapat mengakses fitur pengelolaan toko, menambah atau mengubah produk, mengelola voucher, atau melakukan penarikan dana.')
                    ->line('Jika Anda merasa ini adalah kesalahan atau ingin mengajukan banding, silakan hubungi tim Customer Support kami.')
                    ->action('Hubungi Customer Support', url('/help-center'))
                    ->line('Terima kasih atas perhatian Anda.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Akun Toko Ditangguhkan',
            'message' => 'Akun toko/merchant Anda ditangguhkan sementara oleh administrator.',
        ];
    }
}
