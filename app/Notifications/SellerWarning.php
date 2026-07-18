<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerWarning extends Notification
{
    use Queueable;

    protected $productLabel;
    protected $reason;

    public function __construct($productLabel, $reason)
    {
        $this->productLabel = $productLabel;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Peringatan Moderasi Produk: Produk Dinonaktifkan')
                    ->greeting('Halo, ' . $notifiable->name . '.')
                    ->line('Kami ingin menginformasikan bahwa produk Anda yang bernama **' . $this->productLabel . '** telah dinonaktifkan (banned) oleh Admin Marketplace karena melanggar ketentuan layanan kami.')
                    ->line('Alasan pelanggaran:')
                    ->line('**' . $this->reason . '**')
                    ->line('Mohon perbaiki detail produk Anda atau pastikan produk Anda tidak melanggar ketentuan hukum/hak cipta yang berlaku sebelum menghubungi tim admin untuk pengaktifan kembali.')
                    ->action('Buka Halaman Merchant', url('/'))
                    ->line('Terima kasih atas kerja sama Anda dalam menjaga ekosistem marketplace yang sehat.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Produk Dinonaktifkan (Banned)',
            'message' => 'Produk "' . $this->productLabel . '" Anda dinonaktifkan oleh administrator. Alasan: ' . $this->reason,
        ];
    }
}
