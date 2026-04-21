<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmationNotification extends Notification
{
    use Queueable;

    protected Booking $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
        // Format dates for Google Calendar (UTC Ymd\THis\Z)
        $start = $this->booking->start_time;
        $end = $this->booking->end_time;

        $startTime = $start->utc()->format('Ymd\THis\Z');
        $endTime = $end->utc()->format('Ymd\THis\Z');

        $title = urlencode($this->booking->title);
        $location = urlencode($this->booking->resource->name);
        $details = urlencode("จองผ่านระบบ MRBS Workspace\nสถานะ: ยืนยันแล้ว");

        $calendarUrl = "https://www.google.com/calendar/render?action=TEMPLATE&text={$title}&dates={$startTime}/{$endTime}&details={$details}&location={$location}";

        return (new MailMessage)
            ->subject('ยืนยันการจองห้องประชุม: ' . $this->booking->title)
            ->greeting('สวัสดีคุณ ' . $notifiable->name . '!')
            ->line('การจองห้องประชุมของคุณได้รับการยืนยันเรียบร้อยแล้ว')
            ->line('---')
            ->line('**รายละเอียดการจอง**')
            ->line('**ห้องประชุม:** ' . $this->booking->resource->name)
            ->line('**หัวข้อ:** ' . $this->booking->title)
            ->line('**เวลาเริ่มต้น:** ' . $this->booking->start_time->format('d/m/Y H:i'))
            ->line('**เวลาสิ้นสุด:** ' . $this->booking->end_time->format('d/m/Y H:i'))
            ->line('---')
            ->action('เพิ่มลงใน Google Calendar', $calendarUrl)
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
        ];
    }
}
