<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Alarm;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendGeofenceNotification(Car $car, Alarm $alarm)
    {
        $recipients = $this->getNotificationRecipients($car);

        foreach ($recipients as $user) {
            if (!$user->fcm_token || !$user->push_notifications_enabled) {
                continue;
            }

            $message = [
                'notification' => [
                    'title' => "🚨 {$car->car_name} Alert",
                    'body' => "Vehicle exited geofence at {$alarm->recorded_at->format('H:i:s')}",
                ],
                'data' => [
                    'type' => 'geofence_exit',
                    'car_id' => $car->id,
                    'alarm_id' => $alarm->id,
                    'latitude' => $alarm->latitude,
                    'longitude' => $alarm->longitude,
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
    }

    private function getNotificationRecipients(Car $car)
    {
        if ($car->company_id) {
            return $car->company->users()
                ->where('is_active', true)
                ->whereNotNull('fcm_token')
                ->get();
        }

        return collect([$car->user]);
    }
}