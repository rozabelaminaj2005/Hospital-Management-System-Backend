<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\OverrideRecommendationRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\AiRecommendation;
use App\Models\RecommendationDecision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function accept(Request $request, AiRecommendation $aiRecommendation): JsonResponse
    {
        $doctor = $request->user()->doctor()->firstOrFail();
        $this->authorize('act', $aiRecommendation->symptomReport->patient);

        RecommendationDecision::create([
            'ai_recommendation_id' => $aiRecommendation->id,
            'doctor_id' => $doctor->id,
            'decision' => 'accepted',
        ]);

        $appointment = $aiRecommendation->appointment;

        if ($appointment) {
            $appointment->update(['status' => 'scheduled']);
        } else {
            $appointment = $aiRecommendation->createAppointment();
        }

        return response()->json([
            'message' => 'Recommendation accepted.',
            'data' => new AppointmentResource($appointment->load(['doctor.department', 'department'])),
        ]);
    }

    public function override(OverrideRecommendationRequest $request, AiRecommendation $aiRecommendation): JsonResponse
    {
        $doctor = $request->user()->doctor()->firstOrFail();
        $this->authorize('act', $aiRecommendation->symptomReport->patient);

        $decision = RecommendationDecision::create([
            'ai_recommendation_id' => $aiRecommendation->id,
            'doctor_id' => $doctor->id,
            'decision' => 'overridden',
            'override_reason' => $request->validated()['override_reason'],
        ]);

        return response()->json([
            'message' => 'Recommendation overridden.',
            'data' => [
                'id' => $decision->id,
                'decision' => $decision->decision,
                'overrideReason' => $decision->override_reason,
            ],
        ]);
    }
}
