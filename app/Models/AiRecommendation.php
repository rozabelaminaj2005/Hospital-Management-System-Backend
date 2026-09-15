<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AiRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'symptom_report_id',
        'urgency',
        'confidence',
        'recommended_department_id',
        'recommended_doctor_id',
        'suggested_time',
        'reasoning',
        'diagnosis_hint',
        'model_version',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => 'float',
            'suggested_time' => 'datetime',
            'reasoning' => 'array',
        ];
    }

    public function symptomReport(): BelongsTo
    {
        return $this->belongsTo(SymptomReport::class);
    }

    public function recommendedDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'recommended_department_id');
    }

    public function recommendedDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'recommended_doctor_id');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(RecommendationDecision::class);
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class);
    }

    public function createAppointment(): Appointment
    {
        $symptomReport = $this->symptomReport;

        return Appointment::create([
            'patient_id' => $symptomReport->patient_id,
            'doctor_id' => $this->recommended_doctor_id,
            'department_id' => $this->recommended_department_id,
            'scheduled_at' => $this->suggested_time ?? now()->addDay(),
            'status' => 'scheduled',
            'urgency' => $this->urgency,
            'reason' => $symptomReport->description,
            'ai_recommendation_id' => $this->id,
        ]);
    }
}
