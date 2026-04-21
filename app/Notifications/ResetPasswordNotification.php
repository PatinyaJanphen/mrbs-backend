<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
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
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $url = $frontendUrl . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('การตั้งรหัสผ่านใหม่')
            ->greeting('สวัสดี!')
            ->line('คุณได้รับอีเมลฉบับนี้เนื่องจากเราได้รับคำขอรีเซ็ตรหัสผ่านสำหรับบัญชีของคุณ')
            ->action('ตั้งค่ารหัสผ่านใหม่', $url)
            ->line('ลิงก์สำหรับตั้งรหัสผ่านใหม่นี้จะหมดอายุภายใน ' . config('auth.passwords.' . config('auth.defaults.passwords') . '.expire') . ' นาที')
            ->line('หากคุณไม่ได้ร้องขอการรีเซ็ตรหัสผ่าน คุณไม่จำเป็นต้องดำเนินการใดๆ เพิ่มเติม')
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
            //
        ];
    }
}
