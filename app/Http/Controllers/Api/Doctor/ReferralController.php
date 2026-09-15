<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreReferralRequest;
use App\Http\Resources\ReferralResource;
use App\Models\Patient;
use App\Models\Referral;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function store(StoreReferralRequest $request, Patient $patient): JsonResponse
    {
        $this->authorize('act', $patient);

        $doctor = $request->user()->doctor()->firstOrFail();
        $data = $request->validated();

        $referral = Referral::create([
            'patient_id' => $patient->id,
            'from_doctor_id' => $doctor->id,
            'to_department_id' => $data['department_id'],
            'urgency' => $data['urgency'],
            'reason' => $data['reason'],
        ]);

        return response()->json(['data' => new ReferralResource($referral->load('toDepartment'))], 201);
    }
}
