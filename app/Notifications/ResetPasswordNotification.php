<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Atur Ulang Password - Raket Murah Jogja')
            ->markdown('emails.password-reset', [
                'url' => $this->resetUrl($notifiable),
                'userName' => $notifiable->name,
                'expire' => $expire,
            ]);
    }
}
