<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $leaveData;

    /**
     * Constructor - pass leave data.
     */
    public function __construct($leaveData)
    {
        $this->leaveData = $leaveData;
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
//    public function toMail(object $notifiable): MailMessage
//    {
//        $frontendUrl = config('app.frontend_url');
//        return (new MailMessage)
//            ->subject('New Leave Request Submitted')
//            ->greeting('Hello ' . $notifiable->name . ',')
//            ->line($this->leaveData['employee_name'] . ' has submitted a leave request.')
//            ->line('From: ' . $this->leaveData['from_date'])
//            ->line('To: ' . $this->leaveData['to_date'])
//            ->action('View Request', $frontendUrl . '/leave-requests-list')
//            ->line('Thank you for using our HRM system!');
//    }
    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = config('app.frontend_url');
        $adminName = $notifiable->first_name . ' ' . $notifiable->last_name;

        return (new MailMessage)
            ->subject('Leave Request From ' . $this->leaveData['employee_name'])
            ->markdown('emails.leave-request', [
                'leaveData' => $this->leaveData,
                'frontendUrl' => $frontendUrl,
                'adminName' => $adminName,
            ]);
    }



    /**
     * Data to save in database notification table
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Leave Request',
            'message' => $this->leaveData['employee_name'] . ' has submitted a leave request from ' .
                $this->leaveData['from_date'] . ' to ' . $this->leaveData['to_date'],
            'leave_id' => $this->leaveData['id'],
        ];
    }

    /**
     * Optional: data if sent via array (not used here)
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

}


//namespace App\Notifications;
//
//use Illuminate\Bus\Queueable;
//use Illuminate\Contracts\Queue\ShouldQueue;
//use Illuminate\Notifications\Notification;
//use Illuminate\Notifications\Messages\MailMessage;
//use Illuminate\Notifications\Messages\BroadcastMessage;
//
//class LeaveRequestNotification extends Notification implements ShouldQueue
//{
//    use Queueable;
//
//    protected $data;
//
//    public function __construct($data)
//    {
//        $this->data = $data;
//    }
//
//    // Channels to send notification
//    public function via($notifiable)
//    {
//        return ['mail', 'database', 'broadcast'];
//    }
//
//    // Email message
//    public function toMail($notifiable)
//    {
//        return (new MailMessage)
//            ->subject('New Leave Request')
//            ->greeting('Hello ' . $notifiable->name)
//            ->line($this->data['employee_name'] . ' has submitted a leave request.')
//            ->line('From: ' . $this->data['from_date'])
//            ->line('To: ' . $this->data['to_date'])
//            ->action('View Request', url('/admin/leave-requests'))
//            ->line('Thank you for using our application!');
//    }
//
//    // Store in database
//    public function toDatabase($notifiable)
//    {
//        return [
//            'title' => 'New Leave Request',
//            'message' => $this->data['employee_name'] . ' submitted a leave request.',
//            'from_date' => $this->data['from_date'],
//            'to_date' => $this->data['to_date'],
//        ];
//    }
//
//    // Broadcast (for real-time notifications)
//    public function toBroadcast($notifiable)
//    {
//        return new BroadcastMessage([
//            'title' => 'New Leave Request',
//            'message' => $this->data['employee_name'] . ' submitted a leave request.',
//            'from_date' => $this->data['from_date'],
//            'to_date' => $this->data['to_date'],
//        ]);
//    }
//}
