<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Support\Collection;

/**
 * Mock rule-based triage classifier. Swap this class's classify() body for a
 * real ML model call later — the calling code only depends on the return shape.
 */
class SymptomClassifier
{
    private const HIGH_URGENCY_SYMPTOMS = ['Chest pain', 'Shortness of breath'];

    private const MID_URGENCY_SYMPTOMS = ['Fever', 'Headache', 'Abdominal pain'];

    public function classify(Collection $symptomNames, string $duration, ?int $painLevel): array
    {
        $names = $symptomNames->all();

        if ($this->hasAny($names, self::HIGH_URGENCY_SYMPTOMS)) {
            $urgency = 'high';
            $departmentName = 'Cardiology';
        } elseif ($this->hasAny($names, self::MID_URGENCY_SYMPTOMS)) {
            $urgency = 'mid';
            $departmentName = in_array('Abdominal pain', $names, true) ? 'Gastroenterology' : 'Neurology';
        } else {
            $urgency = 'low';
            $departmentName = 'Family Medicine';
        }

        $department = Department::where('name', $departmentName)->first()
            ?? Department::where('name', 'Family Medicine')->firstOrFail();

        $doctor = Doctor::where('department_id', $department->id)->where('active', true)->inRandomOrder()->first();

        $confidence = $this->confidence($names, $urgency);

        return [
            'urgency' => $urgency,
            'department_id' => $department->id,
            'doctor_id' => $doctor?->id,
            'confidence' => $confidence,
            'suggested_time' => now()->addDay()->setTime(9, 0),
            'reasoning' => $this->reasoning($names, $duration, $painLevel, $urgency, $departmentName),
            'diagnosis_hint' => $this->diagnosisHint($urgency, $departmentName),
        ];
    }

    private function hasAny(array $names, array $needles): bool
    {
        return count(array_intersect($names, $needles)) > 0;
    }

    private function confidence(array $names, string $urgency): float
    {
        $base = match ($urgency) {
            'high' => 90,
            'mid' => 85,
            default => 80,
        };

        $bump = min(count($names) * 2, 7);

        return min(97, $base + $bump);
    }

    private function reasoning(array $names, string $duration, ?int $painLevel, string $urgency, string $departmentName): array
    {
        $bullets = [];

        if (! empty($names)) {
            $bullets[] = 'Reported symptoms: '.implode(', ', $names).'.';
        }

        $bullets[] = match ($duration) {
            'hours' => 'Symptoms have been present for a few hours, indicating an acute onset.',
            'day' => 'Symptoms have persisted for about a day.',
            'days' => 'Symptoms have persisted for several days.',
            'week' => 'Symptoms have persisted for a week or more, suggesting a more chronic pattern.',
            default => 'Duration of symptoms was reported.',
        };

        if ($painLevel !== null) {
            $bullets[] = $painLevel >= 7
                ? "Pain level of {$painLevel}/10 indicates significant discomfort."
                : "Pain level of {$painLevel}/10 was reported.";
        }

        $bullets[] = match ($urgency) {
            'high' => 'Symptom combination is consistent with a potentially serious cardiovascular issue requiring urgent attention.',
            'mid' => 'Symptom combination warrants a prompt but non-emergency evaluation.',
            default => 'Symptoms appear low-risk and suitable for routine primary care follow-up.',
        };

        $bullets[] = "Recommended routing to {$departmentName} based on the symptom pattern above.";

        return $bullets;
    }

    private function diagnosisHint(string $urgency, string $departmentName): ?string
    {
        return match (true) {
            $urgency === 'high' && $departmentName === 'Cardiology' => 'Possible cardiac event — rule out acute coronary syndrome.',
            $departmentName === 'Gastroenterology' => 'Possible gastrointestinal cause for reported symptoms.',
            $departmentName === 'Neurology' => 'Possible neurological or systemic infection cause.',
            default => null,
        };
    }
}
