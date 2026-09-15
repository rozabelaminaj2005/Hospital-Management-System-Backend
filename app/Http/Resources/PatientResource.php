<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'fullName' => $this->whenLoaded('user', fn () => $this->user->name),
            'email' => $this->whenLoaded('user', fn () => $this->user->email),
            'phone' => $this->whenLoaded('user', fn () => $this->user->phone),
            'dateOfBirth' => $this->date_of_birth?->toDateString(),
            'area' => $this->area,
            'bloodType' => $this->blood_type,
            'allergies' => $this->allergies ?? [],
            'conditions' => $this->conditions ?? [],
            'familyDoctor' => $this->whenLoaded('familyDoctor', fn () => $this->familyDoctor ? new DoctorResource($this->familyDoctor) : null),
            'registeredAt' => $this->registered_at,
        ];
    }
}
