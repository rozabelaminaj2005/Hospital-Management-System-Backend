<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicalRecordResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $patient = $request->user()->patient()->firstOrFail();

        $records = $patient->medicalRecords()
            ->with('doctor.department')
            ->when($request->query('tags'), function ($query, $tags) {
                $tags = is_array($tags) ? $tags : explode(',', $tags);
                foreach ($tags as $tag) {
                    $query->whereJsonContains('tags', $tag);
                }
            })
            ->orderByDesc('recorded_at')
            ->get();

        return response()->json(['data' => MedicalRecordResource::collection($records)]);
    }
}
