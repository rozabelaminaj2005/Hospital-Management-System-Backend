<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LabOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'testName' => $this->test_name,
            'status' => $this->status,
            'result' => $this->result,
            'createdAt' => $this->created_at,
        ];
    }
}
