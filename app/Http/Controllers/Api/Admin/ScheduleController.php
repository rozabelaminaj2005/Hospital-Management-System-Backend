<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $appointments = Appointment::with(['patient.user', 'doctor.department', 'department'])
            ->when($request->query('doctor_id'), fn ($q, $doctorId) => $q->where('doctor_id', $doctorId))
            ->when($request->query('department_id'), fn ($q, $departmentId) => $q->where('department_id', $departmentId))
            ->orderBy('scheduled_at')
            ->get();

        $grouped = $appointments->groupBy(fn ($appointment) => $appointment->scheduled_at->toDateString());

        $schedule = $grouped->map(fn ($items) => AppointmentResource::collection($items))->all();

        return response()->json(['data' => $schedule]);
    }
}
