<?php

namespace App\Modules\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->notifications()->latest()->get()
        );
    }

    public function unread(Request $request)
    {
        return response()->json(
            $request->user()->notifications()->unread()->get()
        );
    }

    public function markAsRead($id)
    {
        $n = Notification::findOrFail($id);
        $n->markAsRead();

        return response()->json(['message' => 'Marked as read']);
    }
}
