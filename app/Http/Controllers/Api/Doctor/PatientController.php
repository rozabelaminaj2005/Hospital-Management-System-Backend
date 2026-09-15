<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicalRecordResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\SymptomReportResource;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor()->firstOrFail();

        $patients = Patient::with(['user', 'familyDoctor.department'])
            ->where(function ($query) use ($doctor) {
                $query->where('family_doctor_id', $doctor->id)
                    ->orWhereHas('appointments', fn ($q) => $q->where('doctor_id', $doctor->id))
                    ->orWhereHas('referrals', fn ($q) => $q->where('from_doctor_id', $doctor->id));
            })
            ->when($request->query('q'), function ($query, $q) {
                $query->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
            })
            ->when($request->query('status') === 'pending', function ($query) use ($doctor) {
                $query->whereHas('symptomReports.aiRecommendation', function ($q) use ($doctor) {
                    $q->where('recommended_doctor_id', $doctor->id)->whereDoesntHave('decisions');
                });
            })
            ->get();

        return response()->json(['data' => PatientResource::collection($patients)]);
    }

    public function show(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('view', $patient);

        $patient->load(['user', 'familyDoctor.department', 'medicalRecords.doctor.department']);

        $currentReport = $patient->symptomReports()
            ->with(['symptoms', 'aiRecommendation.recommendedDepartment', 'aiRecommendation.recommendedDoctor.department'])
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'data' => [
                'patient' => new PatientResource($patient),
                'history' => MedicalRecordResource::collection($patient->medicalRecords),
                'currentVisit' => $currentReport ? new SymptomReportResource($currentReport) : null,
            ],
        ]);
    }
}
