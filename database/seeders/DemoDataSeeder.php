<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    private const AREAS = ['North District', 'Central', 'South District'];

    /** Doctor full name => department name, one doctor per department. */
    private const DOCTORS = [
        'Dr. Elena Petrova' => 'Family Medicine',
        'Dr. Marko Iliev' => 'Cardiology',
        'Dr. Sofia Nikolova' => 'Dermatology',
        'Dr. Vera Kovachevska' => 'Neurology',
        'Dr. Filip Janev' => 'Pediatrics',
        'Dr. Ivana Damjanovska' => 'Orthopedics',
        'Dr. Aleksandar Trajkov' => 'Gastroenterology',
    ];

    private const PATIENTS = [
        ['name' => 'Anna Markovic', 'dob' => '1992-04-11'],
        ['name' => 'Petar Stojanovski', 'dob' => '1985-09-23'],
        ['name' => 'Maria Georgieva', 'dob' => '1990-01-15'],
        ['name' => 'Igor Ristovski', 'dob' => '1978-06-30'],
        ['name' => 'Elena Todorova', 'dob' => '2000-12-02'],
        ['name' => 'Nikola Jovanovski', 'dob' => '1965-03-19'],
        ['name' => 'Teodora Angelova', 'dob' => '1995-07-08'],
        ['name' => 'Stefan Dimitrov', 'dob' => '1988-11-27'],
        ['name' => 'Kristina Popova', 'dob' => '1999-05-05'],
    ];

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@medicore.test'],
            [
                'name' => 'Rozabela Fermandos',
                'password' => 'password',
                'role' => 'admin',
                'avatar_initials' => 'RF',
            ]
        );

        $doctors = [];
        $areaIndex = 0;

        foreach (self::DOCTORS as $fullName => $departmentName) {
            $department = Department::where('name', $departmentName)->firstOrFail();
            $slug = str()->slug($fullName);

            $user = User::firstOrCreate(
                ['email' => "{$slug}@medicore.test"],
                [
                    'name' => $fullName,
                    'password' => 'password',
                    'role' => 'doctor',
                    'avatar_initials' => $this->initials($fullName),
                ]
            );

            $doctors[$departmentName] = Doctor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'specialty' => $departmentName,
                    'department_id' => $department->id,
                    'area' => self::AREAS[$areaIndex % 3],
                    'active' => true,
                ]
            );

            $areaIndex++;
        }

        $familyDoctor = $doctors['Family Medicine'];
        $areaIndex = 0;

        foreach (self::PATIENTS as $i => $patientData) {
            $slug = str()->slug($patientData['name']);

            $user = User::firstOrCreate(
                ['email' => "{$slug}@medicore.test"],
                [
                    'name' => $patientData['name'],
                    'password' => 'password',
                    'role' => 'patient',
                    'avatar_initials' => $this->initials($patientData['name']),
                ]
            );

            Patient::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'date_of_birth' => $patientData['dob'],
                    'area' => self::AREAS[$areaIndex % 3],
                    'family_doctor_id' => $i === 0 ? $familyDoctor->id : ($i % 2 === 0 ? $familyDoctor->id : null),
                    'registered_at' => now()->subDays(30 - $i),
                ]
            );

            $areaIndex++;
        }
    }

    private function initials(string $name): string
    {
        $parts = array_values(array_filter(explode(' ', str_replace('Dr. ', '', $name))));

        return strtoupper(substr($parts[0] ?? 'U', 0, 1).substr($parts[1] ?? '', 0, 1));
    }
}
