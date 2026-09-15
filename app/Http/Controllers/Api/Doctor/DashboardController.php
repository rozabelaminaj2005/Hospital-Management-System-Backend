<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\AiRecommendationResource;
use App\Http\Resources\AppointmentResource;
use App\Models\AiRecommendation;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor()->firstOrFail();

        $todayAppointments = $doctor->appointments()
            ->with(['patient.user', 'department'])
            ->whereDate('scheduled_at', now()->toDateString())
            ->orderBy('scheduled_at')
            ->get();

        $pendingRecommendations = AiRecommendation::where('recommended_doctor_id', $doctor->id)
            ->whereDoesntHave('decisions')
            ->with(['symptomReport.patient.user', 'recommendedDepartment'])
            ->orderByDesc('created_at')
            ->get();

        $totalPatients = Patient::where('family_doctor_id', $doctor->id)
            ->orWhereHas('appointments', fn ($q) => $q->where('doctor_id', $doctor->id))
            ->orWhereHas('referrals', fn ($q) => $q->where('from_doctor_id', $doctor->id))
            ->count();

        $urgentCount = $pendingRecommendations->where('urgency', 'high')->count();

        return response()->json([
            'data' => [
                'stats' => [
                    'today' => $todayAppointments->count(),
                    'total' => $totalPatients,
                    'pending' => $pendingRecommendations->count(),
                    'urgent' => $urgentCount,
                ],
                'schedule' => AppointmentResource::collection($todayAppointments),
                'pending' => AiRecommendationResource::collection($pendingRecommendations),
            ],
        ]);
    }
}
