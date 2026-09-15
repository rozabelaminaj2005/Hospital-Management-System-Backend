<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'urgency' => $this->urgency,
            'reason' => $this->reason,
            'status' => $this->status,
            'toDepartment' => new DepartmentResource($this->whenLoaded('toDepartment')),
            'createdAt' => $this->created_at,
        ];
    }
}
