<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'notes' => $this->notes,
            'tags' => $this->tags ?? [],
            'recordedAt' => $this->recorded_at?->toDateString(),
            'doctor' => $this->whenLoaded('doctor', fn () => $this->doctor ? new DoctorResource($this->doctor) : null),
        ];
    }
}
