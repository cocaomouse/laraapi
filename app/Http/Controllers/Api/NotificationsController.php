<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Resources\NotificationResource;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate();

        return NotificationResource::collection($notifications);
    }

    public function stats(Request $request)
    {
        return response()->json([
            'unread_count' => $request->user()->notification_count,
        ]);
    }

    public function read(Request $request)
    {
        $request->user()->markAsRead();

        return response('标记为已读',204);
    }

    public function readOne(Request $request,DatabaseNotification $notification)
    {
        $notification->id ? $request->user()->markAsRead($notification) : $request->user()->markAsRead();

        return response('标记为已读',204);
    }
}
