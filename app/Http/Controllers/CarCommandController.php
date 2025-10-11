<?php
// app/Http/Controllers/CarCommandController.php

namespace App\Http\Controllers;

use App\Models\CarCommand;
use App\Http\Resources\CarCommandResource;
use Illuminate\Http\Request;

class CarCommandController extends Controller
{
    /**
     * Send a command to a car
     */
    public function sendCommand(Request $request)
    {
        $car = $request->car;

        $request->validate([
            'command_type' => 'required|in:fuel_cutoff,microphone_control,alarm_control',
            'command_value' => 'required|string|max:50'
        ]);

        $command = CarCommand::create([
            'car_id' => $car->id,
            'command_type' => $request->command_type,
            'command_value' => $request->command_value,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Command sent successfully',
            'command' => new CarCommandResource($command)
        ]);
    }

    /**
     * Get command history for a car
     */
    public function getCommands(Request $request)
    {
        $car = $request->car;

        $commands = CarCommand::where('car_id', $car->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get();

        return response()->json([
            'commands' => CarCommandResource::collection($commands)
        ]);
    }

    /**
     * Get pending commands for ESP32
     */
    public function getPendingCommands(Request $request)
    {
        $request->validate([
            'imei' => 'required|string'
        ]);

        $car = \App\Models\Car::where('imei', $request->imei)->first();

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'Car not found'
            ], 404);
        }

        $commands = CarCommand::where('car_id', $car->id)
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'asc')
                    ->get();

        // Mark as sent
        CarCommand::where('car_id', $car->id)
                 ->where('status', 'pending')
                 ->update(['status' => 'sent', 'sent_at' => now()]);

        return response()->json([
            'commands' => CarCommandResource::collection($commands)
        ]);
    }

    /**
     * Update command status from ESP32
     */
    public function updateCommandStatus(Request $request, $commandId)
    {
        $request->validate([
            'imei' => 'required|string',
            'status' => 'required|in:executed,failed',
            'response_message' => 'sometimes|string'
        ]);

        $car = \App\Models\Car::where('imei', $request->imei)->first();

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'Car not found'
            ], 404);
        }

        $command = CarCommand::where('id', $commandId)
                    ->where('car_id', $car->id)
                    ->first();

        if (!$command) {
            return response()->json([
                'success' => false,
                'message' => 'Command not found'
            ], 404);
        }

        $command->update([
            'status' => $request->status,
            'response_message' => $request->response_message,
            'executed_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Command status updated'
        ]);
    }
}