<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'notes' => $this->notes,
            'attachments' => $this->attachments ?? [],
            'createdAt' => $this->created_at,
        ];
    }
}
