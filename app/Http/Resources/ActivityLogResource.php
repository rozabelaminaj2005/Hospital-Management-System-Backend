<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'actorType' => $this->actor_type,
            'actorName' => $this->actor_name,
            'action' => $this->action,
            'meta' => $this->meta,
            'loggedAt' => $this->logged_at,
        ];
    }
}
