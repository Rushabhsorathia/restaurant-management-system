<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class ApiResetPasswordNotification extends ResetPasswordNotification
{
    use Queueable;

    public function toMail($notifiable): MailMessage
    {
        $url = url('/reset-password/'.$this->token.'?email='.urlencode($notifiable->getEmailForPasswordReset()));

        return (new MailMessage)
            ->subject('Reset Your RMS Password')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.users.expire', 60)])
            ->line('If you did not request a password reset, no further action is required.');
    }
}
