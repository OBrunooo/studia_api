<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class ApplicationErrorNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Throwable $exception,
        public ?string $action = null,
        public ?string $url = null,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('[' . config('app.name') . '] Erro na aplicação')
            ->line('Ocorreu um erro na aplicação:')
            ->line('Ação: ' . ($this->action ?? 'N/A'))
            ->line('Mensagem: ' . $this->exception->getMessage())
            ->line('Arquivo: ' . $this->exception->getFile() . ':' . $this->exception->getLine())
            ->line('URL: ' . ($this->url ?? 'N/A'))
            ->line('Data: ' . now()->toDateTimeString());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->exception->getMessage(),
            'file' => $this->exception->getFile(),
            'line' => $this->exception->getLine(),
        ];
    }
}
