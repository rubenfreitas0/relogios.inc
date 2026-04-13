<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verificar Email - Relogios Inc')
            ->greeting("Ola, {$notifiable->firstname}!")
            ->line('Obrigado por te registares na Relogios Inc.')
            ->line('Clica no botao abaixo para verificar o teu email:')
            ->action('Verificar Email', $verificationUrl)
            ->line('Este link expira em 60 minutos.')
            ->line('Se nao criaste esta conta, ignora este email.')
            ->salutation('Cumprimentos, Relogios Inc');
    }

    protected function verificationUrl($notifiable): string
    {
        $temporarySignedUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $notifiable->getKey()]
        );

        $parsedUrl = parse_url($temporarySignedUrl);
        $query = $parsedUrl['query'] ?? '';

        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

        return "{$frontendUrl}/verificar-email/{$notifiable->getKey()}?{$query}";
    }
}
