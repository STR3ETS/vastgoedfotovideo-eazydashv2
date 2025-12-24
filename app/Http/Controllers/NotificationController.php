<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $items = $user->notifications()
            ->latest()
            ->limit(25)
            ->get()
            ->map(function ($n) {
                return [
                    'id'         => $n->id,
                    'read_at'    => $n->read_at ? $n->read_at->toDateTimeString() : null,
                    'created_at' => $n->created_at?->diffForHumans(),
                    'data'       => $n->data,
                ];
            });

        return response()->json([
            'success'      => true,
            'unread_count' => $user->unreadNotifications()->count(),
            'items'        => $items,
        ]);
    }

    public function read(Request $request, string $id)
    {
        $user = $request->user();
        $n = $user->notifications()->where('id', $id)->firstOrFail();

        if (!$n->read_at) $n->markAsRead();

        return response()->json([
            'success'      => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function readAll(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'success'      => true,
            'unread_count' => 0,
        ]);
    }
}
