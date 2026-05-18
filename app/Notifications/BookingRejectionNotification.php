<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRejectionNotification extends Notification
{
    use Queueable;

    protected Booking $booking;
    protected string $rejectReason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, string $rejectReason)
    {
        $this->booking = $booking;
        $this->rejectReason = $rejectReason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ปฏิเสธการจองห้องประชุม: ' . $this->booking->title)
            ->greeting('สวัสดีคุณ ' . $notifiable->name . '!')
            ->line('การจองห้องประชุมของคุณไม่ได้รับการอนุมัติ')
            ->line('---')
            ->line('**รายละเอียดการจอง**')
            ->line('**ห้องประชุม:** ' . $this->booking->resource->name)
            ->line('**หัวข้อ:** ' . $this->booking->title)
            ->line('**เวลาเริ่มต้น:** ' . $this->booking->start_time->format('d/m/Y H:i') . ' น.')
            ->line('**เวลาสิ้นสุด:** ' . $this->booking->end_time->format('d/m/Y H:i') . ' น.')
            ->line('**เหตุผลการปฏิเสธ:** ' . $this->rejectReason)
            ->line('---')
            ->line('ขอบคุณที่ใช้บริการ MRBS Workspace')
            ->salutation('ขอแสดงความนับถือ, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'title' => $this->booking->title,
            'reject_reason' => $this->rejectReason,
        ];
    }
}
