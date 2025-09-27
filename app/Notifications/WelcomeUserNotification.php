<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\User;
use App\Mail\WelcomeUserMailable;
use Illuminate\Broadcasting\PrivateChannel;

class WelcomeUserNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    protected $notifiableUserId;
    protected $url;
    /**
     * Create a new notification instance.
     */
    public function __construct(int $notifiableUserId, string $url)
    {
        $this->notifiableUserId = $notifiableUserId;
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail',];
    }

    public function broadcastOn(): array
    {
        // The $this->notifiable is the recipient of the notification (the User model).
        return [
            new PrivateChannel('users.' . $this->notifiableUserId),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): WelcomeUserMailable
    {
        $url = url(config('app.frontend_url') . $this->url);

        return (new WelcomeUserMailable($notifiable->fullname, $url))
            ->to($notifiable->email);
    }

    public function toBroadcast(object $notifiable): array
    {
        $unreadCount = $notifiable->unreadNotifications()->count();

        return [
            'unread_count' => $unreadCount,
        ];

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Welcome to ' . config('app.name') . '!',
            'url' => url(config('app.frontend_url') . $this->url),
        ];
    }
}