<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendGeofenceNotification(Car $car, $latitude, $longitude, $recordedAt)
    {
        $recipients = $this->getNotificationRecipients($car);

        foreach ($recipients as $user) {
            // 1. Store in database
            Notification::create([
                'user_id' => $user->id,
                'car_id' => $car->id,
                'type' => 'geofence_exit',
                'title' => "🚨 {$car->car_name} Alert",
                'message' => "Vehicle exited geofence",
                'data' => [
                    'latitude' => (string) $latitude,
                    'longitude' => (string) $longitude,
                ],
                'created_at' => $recordedAt ?? now(),
            ]);

            // 2. Send FCM push notification (if token exists)
            if ($user->fcm_token && $user->push_notifications_enabled) {
                $this->sendFcmNotification($user, $car, $latitude, $longitude);
            }
        }
    }

    private function sendFcmNotification($user, $car, $latitude, $longitude)
    {
        $message = [
            'notification' => [
                'title' => "🚨 {$car->car_name} Alert",
                'body' => "Vehicle exited geofence",
            ],
            'data' => [
                'type' => 'geofence_exit',
                'car_id' => (string) $car->id,
                'latitude' => (string) $latitude,
                'longitude' => (string) $longitude,
            ],
            'android' => [
                'priority' => 'high',
            ],
            'token' => $user->fcm_token,
        ];

        try {
            app('firebase.messaging')->send($message);
            Log::info("FCM sent to user {$user->id}");
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'NotFound')) {
                $user->update(['fcm_token' => null]);
            }
            Log::error('FCM Error', ['error' => $e->getMessage()]);
        }
    }

    private function getNotificationRecipients(Car $car)
    {
        if ($car->company_id) {
            return $car->company->users()
                ->where('is_active', true)
                ->get();
        }

        return collect([$car->user]);
    }
}