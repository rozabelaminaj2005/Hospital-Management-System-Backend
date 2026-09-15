<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medication' => $this->medication,
            'dosage' => $this->dosage,
            'instructions' => $this->instructions,
            'createdAt' => $this->created_at,
        ];
    }
}
