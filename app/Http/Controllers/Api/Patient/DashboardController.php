<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\DoctorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $patient = $request->user()->patient()->with('familyDoctor.department')->firstOrFail();

        $nextAppointment = $patient->appointments()
            ->with(['doctor.department', 'department'])
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->first();

        $visits = $patient->appointments()->where('status', 'completed')->count();
        $lastVisit = $patient->appointments()->where('status', 'completed')->orderByDesc('scheduled_at')->value('scheduled_at');
        $medicalRecordsCount = $patient->medicalRecords()->count();

        return response()->json([
            'data' => [
                'familyDoctor' => $patient->familyDoctor ? new DoctorResource($patient->familyDoctor) : null,
                'nextAppointment' => $nextAppointment ? new AppointmentResource($nextAppointment) : null,
                'stats' => [
                    'visits' => $visits,
                    'lastVisit' => $lastVisit,
                    'medicalRecords' => $medicalRecordsCount,
                ],
            ],
        ]);
    }
}
