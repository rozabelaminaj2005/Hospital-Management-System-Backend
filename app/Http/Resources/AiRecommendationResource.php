<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiRecommendationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'urgency' => $this->urgency,
            'confidence' => (float) $this->confidence,
            'department' => new DepartmentResource($this->whenLoaded('recommendedDepartment')),
            'doctor' => $this->whenLoaded('recommendedDoctor', fn () => $this->recommendedDoctor ? new DoctorResource($this->recommendedDoctor) : null),
            'suggestedTime' => $this->suggested_time,
            'reasoning' => $this->reasoning ?? [],
            'diagnosisHint' => $this->diagnosis_hint,
            'modelVersion' => $this->model_version,
        ];
    }
}
