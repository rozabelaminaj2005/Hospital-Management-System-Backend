<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Models\AiRecommendation;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $today = now()->toDateString();

        $daily = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);

            return [
                'date' => $date->toDateString(),
                'weekday' => $date->format('D'),
                'count' => Appointment::whereDate('scheduled_at', $date->toDateString())->count(),
            ];
        })->values();

        $urgencyCounts = AiRecommendation::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('urgency, count(*) as total')
            ->groupBy('urgency')
            ->pluck('total', 'urgency');

        $urgencyTotal = max($urgencyCounts->sum(), 1);
        $urgencyMix = collect(['low', 'mid', 'high'])->map(fn ($level) => [
            'urgency' => $level,
            'percentage' => round((($urgencyCounts[$level] ?? 0) / $urgencyTotal) * 100, 1),
        ]);

        $activity = ActivityLog::orderByDesc('logged_at')->limit(20)->get();

        return response()->json([
            'data' => [
                'stats' => [
                    'doctors' => Doctor::count(),
                    'patients' => Patient::count(),
                    'today' => Appointment::whereDate('scheduled_at', $today)->count(),
                    'urgent' => Appointment::where('urgency', 'high')->whereDate('scheduled_at', '>=', $today)->count(),
                    'pending' => AiRecommendation::whereDoesntHave('decisions')->count(),
                ],
                'daily' => $daily,
                'urgencyMix' => $urgencyMix,
                'activity' => ActivityLogResource::collection($activity),
            ],
        ]);
    }
}
