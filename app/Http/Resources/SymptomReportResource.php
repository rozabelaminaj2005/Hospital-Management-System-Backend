<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SymptomReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'description' => $this->description,
            'duration' => $this->duration,
            'painLevel' => $this->pain_level,
            'usedAi' => $this->used_ai,
            'symptoms' => SymptomResource::collection($this->whenLoaded('symptoms')),
            'aiRecommendation' => $this->whenLoaded('aiRecommendation', fn () => $this->aiRecommendation ? new AiRecommendationResource($this->aiRecommendation) : null),
            'createdAt' => $this->created_at,
        ];
    }
}
