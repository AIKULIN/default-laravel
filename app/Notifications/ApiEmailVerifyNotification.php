<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class ApiEmailVerifyNotification extends Notification
{
    use Queueable;

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
            ->subject(Lang::get('驗證 Email'))
            ->line(Lang::get('請單擊下面的按鈕以驗證您的電子郵件地址。'))
            ->action(Lang::get('驗證 Email'), $this->url($notifiable))
            ->line(Lang::get('如果您沒有創建帳戶，請忽略此信。'));
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function url(mixed $notifiable): string
    {
        //判斷資料來源，切換驗證回網址
        if ($notifiable->getTable() === 'creator_users') {
            $source = 'creator';
        } else {
            $source = 'users';
        }

        $urlQuery = [
            'expires' => Carbon::now()->addMinutes(env('VERIFY_EMAIL_EXPIRE', 60))->timestamp,
            'email' => $notifiable->getEmailForVerification(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ];

        $urlQuery['signature'] = hash_hmac('sha256', http_build_query($urlQuery), env('APP_KEY'));

        return env('APP_URL'). "/api/auth/verify?" . http_build_query($urlQuery);
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
