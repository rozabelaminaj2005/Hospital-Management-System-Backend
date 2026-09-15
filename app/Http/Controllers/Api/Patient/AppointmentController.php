<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $patient = $request->user()->patient()->firstOrFail();

        $appointments = $patient->appointments()
            ->with(['doctor.department', 'department'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('scheduled_at')
            ->get();

        return response()->json(['data' => AppointmentResource::collection($appointments)]);
    }
}
