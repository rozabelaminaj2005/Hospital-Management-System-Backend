<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone' => $this->phone,
            'avatarInitials' => $this->avatar_initials,
            'patient' => $this->whenLoaded('patient', fn () => $this->patient ? new PatientResource($this->patient) : null),
            'doctor' => $this->whenLoaded('doctor', fn () => $this->doctor ? new DoctorResource($this->doctor) : null),
            'createdAt' => $this->created_at,
        ];
    }
}
