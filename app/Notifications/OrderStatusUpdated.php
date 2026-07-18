<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    protected $orderId;
    protected $status;
    protected $trackingNumber;

    public function __construct($orderId, $status, $trackingNumber = null)
    {
        $this->orderId = $orderId;
        $this->status = $status;
        $this->trackingNumber = $trackingNumber;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $statusLabels = [
            'accepted' => 'Sedang Diproses oleh Penjual',
            'shipped' => 'Telah Dikirim',
            'rejected' => 'Ditolak oleh Penjual',
        ];

        $statusTypeLabel = $statusLabels[$this->status] ?? $this->status;
        $subject = 'Update Pesanan #' . $this->orderId . ': ' . $statusTypeLabel;

        $mail = (new MailMessage)
                    ->subject($subject)
                    ->greeting('Halo, ' . $notifiable->name . '!')
                    ->line('Ada pembaruan status untuk pesanan Anda #' . $this->orderId . '.');

        if ($this->status === 'accepted') {
            $mail->line('Penjual telah menerima dan sedang memproses pesanan Anda. Penjual akan segera mengirimkan barang Anda.');
        } elseif ($this->status === 'shipped') {
            $mail->line('Pesanan Anda telah dikirim oleh penjual.');
            if ($this->trackingNumber) {
                $mail->line('Nomor Resi / Tracking: **' . $this->trackingNumber . '**');
            }
        } elseif ($this->status === 'rejected') {
            $mail->line('Mohon maaf, pesanan Anda telah ditolak oleh penjual.');
        }

        return $mail->action('Lihat Riwayat Pesanan', url('/profile/orders'))
                    ->line('Terima kasih telah berbelanja di marketplace kami!');
    }

    public function toArray($notifiable)
    {
        $statusLabels = [
            'accepted' => 'sedang diproses',
            'shipped' => 'telah dikirim',
            'rejected' => 'ditolak oleh penjual',
        ];

        $label = $statusLabels[$this->status] ?? $this->status;
        $msg = 'Status pesanan #' . $this->orderId . ' Anda kini ' . $label . '.';
        if ($this->status === 'shipped' && $this->trackingNumber) {
            $msg .= ' Resi: ' . $this->trackingNumber;
        }

        return [
            'title' => 'Update Status Pesanan #' . $this->orderId,
            'message' => $msg,
        ];
    }
}
