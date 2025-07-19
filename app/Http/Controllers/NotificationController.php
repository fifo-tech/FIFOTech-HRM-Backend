<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); // logged-in user

        return response()->json([
            'notifications' => $user->unreadNotifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? '',
                    'message' => $notification->data['message'] ?? '',
                    'leave_id' => $notification->data['leave_id'] ?? null,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

//    public function markAllAsRead(Request $request)
//    {
//        $request->user()->unreadNotifications->markAsRead();
//
//        return response()->json(['message' => 'All notifications marked as read']);
//    }
    public function markAsRead($id, Request $request)
    {
        $notification = $request->user()->unreadNotifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read']);
    }
}

