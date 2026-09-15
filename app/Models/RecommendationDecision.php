<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_recommendation_id',
        'doctor_id',
        'decision',
        'override_reason',
    ];

    public function aiRecommendation(): BelongsTo
    {
        return $this->belongsTo(AiRecommendation::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
