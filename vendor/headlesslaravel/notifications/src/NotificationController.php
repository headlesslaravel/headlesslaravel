<?php

namespace HeadlessLaravel\Notifications;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Routing\Controller;
use Illuminate\Validation\UnauthorizedException;

class NotificationController extends Controller
{
    public function all()
    {
        return response()->json(
            auth()->user()->notifications()->paginate()
        );
    }

    public function unread()
    {
        return response()->json(
            auth()->user()->unreadNotifications()->paginate()
        );
    }

    public function read()
    {
        return response()->json(
            auth()->user()->readNotifications()->paginate()
        );
    }

    public function count()
    {
        return response()->json([
            'unread' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(DatabaseNotification $notification)
    {
        throw_if(
            $notification->notifiable_id != auth()->id(),
            UnauthorizedException::class
        );

        return response()->json([
            'success' => $notification->markAsRead()
        ]);
    }

    public function clear()
    {
        $deleted = DatabaseNotification::where([
            'notifiable_type' => get_class(auth()->user()),
            'notifiable_id' => auth()->id(),
        ])->delete();

        return response()->json([
            'deleted' => $deleted,
            'success' => true
        ]);
    }

    public function destroy(DatabaseNotification $notification)
    {
        throw_if(
            $notification->notifiable_id != auth()->id(),
            UnauthorizedException::class
        );

        return response()->json([
            'deleted' => $notification->id,
            'success' => $notification->delete()
        ]);
    }
}
