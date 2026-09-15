<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\UpdateProfileRequest;
use App\Http\Resources\PatientResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $patient = $request->user()->patient()->with(['user', 'familyDoctor.department'])->firstOrFail();

        return response()->json(['data' => new PatientResource($patient)]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $patient = $user->patient()->firstOrFail();

        if (isset($data['phone']) || isset($data['email'])) {
            $user->update(array_filter([
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
            ], fn ($v) => $v !== null));
        }

        if (isset($data['area'])) {
            $patient->update(['area' => $data['area']]);
        }

        $patient->load(['user', 'familyDoctor.department']);

        return response()->json(['data' => new PatientResource($patient)]);
    }
}
