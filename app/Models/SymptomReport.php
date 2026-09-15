<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SymptomReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'description',
        'duration',
        'pain_level',
        'used_ai',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'used_ai' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function symptoms(): BelongsToMany
    {
        return $this->belongsToMany(Symptom::class, 'symptom_report_symptom');
    }

    public function aiRecommendation(): HasOne
    {
        return $this->hasOne(AiRecommendation::class);
    }
}
