<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'area',
        'blood_type',
        'allergies',
        'conditions',
        'family_doctor_id',
        'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'allergies' => 'array',
            'conditions' => 'array',
            'registered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function familyDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'family_doctor_id');
    }

    public function symptomReports(): HasMany
    {
        return $this->hasMany(SymptomReport::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class);
    }
}
