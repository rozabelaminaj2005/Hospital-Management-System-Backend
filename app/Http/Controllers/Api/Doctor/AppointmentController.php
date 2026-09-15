<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(StoreAppointmentRequest $request, Patient $patient): JsonResponse
    {
        $this->authorize('act', $patient);

        $doctor = $request->user()->doctor()->firstOrFail();
        $data = $request->validated();

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $data['department_id'],
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => $data['duration_minutes'] ?? 30,
            'status' => 'scheduled',
            'urgency' => 'low',
            'reason' => $data['reason'] ?? null,
            'location' => $data['location'] ?? null,
        ]);

        return response()->json(['data' => new AppointmentResource($appointment->load(['doctor.department', 'department']))], 201);
    }
}
