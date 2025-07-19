<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveStatusUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $leaveRequest;
    public $status;
    public $hrName;
    public $employeeName;

    public function __construct($leaveRequest, $status, $hrName, $employeeName)
    {
        $this->leaveRequest = $leaveRequest;
        $this->status = $status;
        $this->hrName = $hrName;
        $this->employeeName = $employeeName;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $frontendUrl = config('app.frontend_url');

        return (new MailMessage)
            ->subject("Your Leave Request has been {$this->status}")
            ->view('emails.leave-status-update', [
                'leaveRequest' => $this->leaveRequest,
                'status' => $this->status,
                'hrName' => $this->hrName,
                'employeeName' => $this->employeeName,
                'frontendUrl' => $frontendUrl,
            ]);
    }



    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Leave Request ' . $this->status,
            'message' => $this->employeeName . ", your leave request from " .
                $this->leaveRequest->start_date . " to " . $this->leaveRequest->end_date .
                " has been " . $this->status,
            'leave_id' => $this->leaveRequest->id,
        ];
    }

}

