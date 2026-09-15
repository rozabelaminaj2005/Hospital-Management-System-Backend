<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StoreSymptomReportRequest;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\SymptomReportResource;
use App\Http\Resources\SymptomResource;
use App\Jobs\ClassifySymptomReport;
use App\Models\Department;
use App\Models\Symptom;
use App\Models\SymptomReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SymptomController extends Controller
{
    public function options(): JsonResponse
    {
        return response()->json([
            'data' => [
                'symptoms' => SymptomResource::collection(Symptom::orderBy('name')->get()),
                'departments' => DepartmentResource::collection(Department::orderBy('name')->get()),
            ],
        ]);
    }

    public function store(StoreSymptomReportRequest $request): JsonResponse
    {
        $data = $request->validated();
        $patient = $request->user()->patient()->firstOrFail();

        $report = SymptomReport::create([
            'patient_id' => $patient->id,
            'description' => $data['description'],
            'duration' => $data['duration'],
            'pain_level' => $data['pain_level'] ?? null,
            'used_ai' => $data['use_ai'],
            'status' => 'analyzing',
        ]);

        $report->symptoms()->sync($data['symptom_ids']);

        ClassifySymptomReport::dispatch($report->id)->afterResponse();

        return response()->json([
            'data' => [
                'reportId' => $report->id,
                'status' => $report->status,
            ],
        ], 201);
    }

    public function show(Request $request, SymptomReport $symptomReport): JsonResponse
    {
        $patient = $request->user()->patient()->firstOrFail();

        if ($symptomReport->patient_id !== $patient->id) {
            abort(403);
        }

        $symptomReport->load(['symptoms', 'aiRecommendation.recommendedDepartment', 'aiRecommendation.recommendedDoctor.department']);

        return response()->json(['data' => new SymptomReportResource($symptomReport)]);
    }

    public function book(Request $request, SymptomReport $symptomReport): JsonResponse
    {
        $patient = $request->user()->patient()->firstOrFail();

        if ($symptomReport->patient_id !== $patient->id) {
            abort(403);
        }

        $recommendation = $symptomReport->aiRecommendation;

        if (! $recommendation) {
            return response()->json([
                'errors' => ['symptom_report' => ['This report does not have a completed recommendation yet.']],
            ], 422);
        }

        $appointment = $recommendation->createAppointment();
        $appointment->load(['doctor.department', 'department']);

        return response()->json(['data' => new AppointmentResource($appointment)], 201);
    }
}
