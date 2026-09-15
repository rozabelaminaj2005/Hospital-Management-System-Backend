<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreLabOrderRequest;
use App\Http\Resources\LabOrderResource;
use App\Models\LabOrder;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabOrderController extends Controller
{
    public function store(StoreLabOrderRequest $request, Patient $patient): JsonResponse
    {
        $this->authorize('act', $patient);

        $doctor = $request->user()->doctor()->firstOrFail();

        $appointment = $patient->appointments()->where('doctor_id', $doctor->id)->orderByDesc('scheduled_at')->first();

        $labOrder = LabOrder::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment?->id,
            'test_name' => $request->validated()['test_name'],
            'status' => 'ordered',
        ]);

        return response()->json(['data' => new LabOrderResource($labOrder)], 201);
    }
}
