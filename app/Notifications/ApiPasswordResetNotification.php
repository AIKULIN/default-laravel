<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApiPasswordResetNotification extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
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
        return (new MailMessage)
            ->subject(env('APP_NAME'). ' - 重置密碼')
            ->view('email.reset_password', [
                'logo_url' => env('LOG_IMG_URL'),
                'line1' => '您收到此電子郵件是因為我們收到了您帳戶的密碼重置請求。',
                'action' => '重置密碼',
                'action_url' => $this->resetUrl($notifiable),
                'line2' => '如果您未請求密碼重置，則無需採取進一步措施。'
            ]);
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl(mixed $notifiable): string
    {
        $token = $this->token;
        $email = urlencode($notifiable->getEmailForPasswordReset());

        return env('APP_URL'). "/reset-password/{$token}?email={$email}";
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
