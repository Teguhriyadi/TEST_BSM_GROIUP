<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public string $token;
    public string $email;

    public function __construct(string $token, string $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $this->email,
        ]);

        return (new MailMessage)
            ->subject(Lang::get('Permintaan Reset Password - Koperasi Simpan Pinjam'))
            ->greeting('Halo ' . $notifiable->nama . ',')
            ->line(Lang::get('Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.'))
            ->action(Lang::get('Reset Password'), $resetUrl)
            ->line(Lang::get('Link reset password ini akan kedaluwarsa dalam :count menit.', ['count' => 60]))
            ->line(Lang::get('Jika Anda tidak merasa meminta reset password, abaikan email ini. Akun Anda tetap aman.'))
            ->salutation('Salam hangat,')
            ->salutation(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'token' => $this->token,
            'email' => $this->email,
        ];
    }
}
