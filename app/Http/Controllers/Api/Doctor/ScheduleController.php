<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor()->firstOrFail();

        $appointments = $doctor->appointments()
            ->with(['patient.user', 'department'])
            ->orderBy('scheduled_at')
            ->get();

        $grouped = $appointments->groupBy(fn ($appointment) => $appointment->scheduled_at->toDateString());

        $schedule = $grouped->map(fn ($items) => AppointmentResource::collection($items))->all();

        return response()->json(['data' => $schedule]);
    }
}
