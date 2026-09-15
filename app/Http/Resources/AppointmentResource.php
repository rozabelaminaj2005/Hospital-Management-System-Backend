<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient' => $this->whenLoaded('patient', fn () => $this->patient ? new PatientResource($this->patient) : null),
            'doctor' => $this->whenLoaded('doctor', fn () => $this->doctor ? new DoctorResource($this->doctor) : null),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'scheduledAt' => $this->scheduled_at,
            'durationMinutes' => $this->duration_minutes,
            'status' => $this->status,
            'urgency' => $this->urgency,
            'reason' => $this->reason,
            'location' => $this->location,
            'aiRecommendationId' => $this->ai_recommendation_id,
        ];
    }
}
