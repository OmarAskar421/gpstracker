<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get paginated notifications for authenticated user
     * 
     * GET /api/notifications?page=1&per_page=20
     */
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'car_id' => 'sometimes|exists:cars,id'
        ]);

        $perPage = $request->input('per_page', 20);
        $user = $request->user();

        $query = Notification::where('user_id', $user->id)
            ->with('car:id,car_name') // Eager load car name
            ->orderBy('created_at', 'desc');

        // Optional filter by car_id
        if ($request->has('car_id')) {
            $query->where('car_id', $request->car_id);
        }

        $notifications = $query->paginate($perPage);

        return NotificationResource::collection($notifications);
    }

    /**
     * Delete a notification
     * 
     * DELETE /api/notifications/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Delete all notifications for user
     * 
     * DELETE /api/notifications
     */
    public function destroyAll(Request $request)
    {
        $user = $request->user();

        $deleted = Notification::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} notifications"
        ]);
    }
}