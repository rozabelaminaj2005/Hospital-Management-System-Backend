<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StorePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function store(StorePrescriptionRequest $request, Patient $patient): JsonResponse
    {
        $this->authorize('act', $patient);

        $doctor = $request->user()->doctor()->firstOrFail();
        $data = $request->validated();

        $appointment = $patient->appointments()->where('doctor_id', $doctor->id)->orderByDesc('scheduled_at')->first();

        $prescription = Prescription::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment?->id,
            'medication' => $data['medication'],
            'dosage' => $data['dosage'],
            'instructions' => $data['instructions'] ?? null,
        ]);

        return response()->json(['data' => new PrescriptionResource($prescription)], 201);
    }
}
