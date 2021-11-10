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
        // 判断当前用户未读消息数量是否为0
        if ($request->user()->notification_count == 0) {
            return $this->checkNotification();
        }

        $request->user()->markAsRead();
        return response('标记为已读', 204);
    }

    public function readOne(Request $request, DatabaseNotification $notification)
    {
        // 判断此条消息是否属于当前用户
        if ($request->user()->id != $notification->notifiable_id) {
            return response()->json([
                'message' => '无消息权限'
            ])->setStatusCode(401);
        }
        // 判断当前用户未读消息数量是否为0
        if ($request->user()->notification_count == 0) {
            return $this->checkNotification();
        }

        $notification->id ? $request->user()->markAsRead($notification) : $request->user()->markAsRead();
        return response('标记为已读', 204);
    }

    private function checkNotification()
    {
        return response()->json([
            'message' => '当前用户无未读消息',
        ])->setStatusCode(201);
    }
}
