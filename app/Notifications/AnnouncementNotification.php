<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $announcementData;

    /**
     * Constructor - pass announcement data.
     */
    public function __construct($announcementData)
    {
        $this->announcementData = $announcementData;
    }

    /**
     * Channels: mail + database
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Mail structure
     */
    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url') . '/announcements'; // adjust route if needed
        $receiverName = $notifiable->first_name . ' ' . $notifiable->last_name;

        return (new MailMessage)
            ->subject('📢 Announcement: ' . $this->announcementData['title'])
            ->greeting('Hello ' . $receiverName . ',')
            ->line($this->announcementData['description'])
            ->action('View Announcement', $frontendUrl)
            ->line('This is an important update. Please check it out.');
    }

    /**
     * Data to save in database notification table
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => ucfirst($this->announcementData['announcement_type']) . " " . "Announcement",
            'message' => $this->announcementData['description'],
            'announcement_id' => $this->announcementData['id'],
            'announcement_type' => $this->announcementData['announcement_type'],
        ];
    }

    /**
     * Optional: for array format (used in broadcasting etc.)
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
