<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class PendingEmailChangeNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly User $user, private readonly string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute('account.email.confirm', now()->addMinutes(60), [
            'user' => $this->user->id,
            'token' => $this->token,
        ]);

        return (new MailMessage)
            ->subject('Confirme seu novo e-mail no Vyrko')
            ->greeting('Confirme seu e-mail')
            ->line('Recebemos uma solicitação para alterar o e-mail da sua conta Vyrko.')
            ->line('Clique no botão abaixo para confirmar o novo endereço.')
            ->action('Confirmar novo e-mail', $url)
            ->line('Se você não solicitou essa alteração, ignore esta mensagem.');
    }
}
