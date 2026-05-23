<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerRejected extends Notification
{
    use Queueable;

    protected $reason;

    public function __construct($reason)
    {
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Verifikasi KTP Penjual Ditolak')
                    ->greeting('Halo, ' . $notifiable->name . '.')
                    ->line('Mohon maaf, pengajuan pendaftaran penjual Anda belum dapat kami setujui karena alasan berikut:')
                    ->line('**' . $this->reason . '**')
                    ->line('Silakan unggah ulang dokumen KTP Anda yang lebih jelas dan sesuai ketentuan melalui tautan di bawah ini.')
                    ->action('Unggah Ulang KTP', url('/'))
                    ->line('Terima kasih atas pengertian Anda.');
    }
}
