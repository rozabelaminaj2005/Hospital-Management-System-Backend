<?php

namespace App\Http\Controllers\Api\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\SymptomResource;
use App\Models\Symptom;
use Illuminate\Http\JsonResponse;

class SymptomController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => SymptomResource::collection(Symptom::orderBy('name')->get()),
        ]);
    }
}
