<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LateCountExceededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $lateCount;

    public function __construct($lateCount)
    {
        $this->lateCount = $lateCount;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // email + in-app notification
    }

//    public function toMail($notifiable)
//    {
//        return (new MailMessage)
//            ->subject('Late Attendance Alert')
//            ->greeting('Dear ' . $notifiable->name . ',')
//            ->line("You have been late {$this->lateCount} times this month.")
//            ->line("Please ensure punctuality to avoid further HR actions.")
//            ->line('Thank you for your attention.');
//    }

    public function toMail($notifiable)
    {
        $frontendUrl = config('app.frontend_url');
        $employeeName = $notifiable->first_name . ' ' . $notifiable->last_name;

        return (new MailMessage)
            ->subject('Late Attendance Alert')
            ->view('emails.late_attendance_alert', [
                'employeeName' => $employeeName,
                'lateCount' => $this->lateCount,
                'frontendUrl' => $frontendUrl,
//                'lastLateDate' => optional($this->lastLateDate)->format('d M, Y'),
            ]);
    }


    public function toArray($notifiable)
    {
        $employeeName = $notifiable->first_name . ' ' . $notifiable->last_name;
        return [
            'title' => 'Late Attendance Alert',
//            'message' => "{$employeeName}, You have been late {$this->lateCount} times this month.",
            'message' => "{$employeeName},\nYou have been late {$this->lateCount} times this month.\nPlease be punctual to avoid further HR disciplinary action. Your cooperation is highly appreciated.",




        ];
    }
}

