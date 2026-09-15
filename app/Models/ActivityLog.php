<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_log';

    protected $fillable = [
        'actor_type',
        'actor_name',
        'action',
        'meta',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'logged_at' => 'datetime',
        ];
    }

    public static function record(string $actorType, string $actorName, string $action, ?array $meta = null): self
    {
        return self::create([
            'actor_type' => $actorType,
            'actor_name' => $actorName,
            'action' => $action,
            'meta' => $meta,
            'logged_at' => now(),
        ]);
    }
}
