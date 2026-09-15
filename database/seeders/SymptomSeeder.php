<?php

namespace Database\Seeders;

use App\Models\Symptom;
use Illuminate\Database\Seeder;

class SymptomSeeder extends Seeder
{
    public const NAMES = [
        'Chest pain',
        'Shortness of breath',
        'Fever',
        'Headache',
        'Abdominal pain',
        'Cough',
        'Fatigue',
        'Nausea',
        'Dizziness',
        'Joint pain',
        'Sore throat',
        'Rash',
    ];

    public function run(): void
    {
        foreach (self::NAMES as $name) {
            Symptom::firstOrCreate(['name' => $name]);
        }
    }
}
