<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public const NAMES = [
        'Family Medicine',
        'Cardiology',
        'Dermatology',
        'Neurology',
        'Pediatrics',
        'Orthopedics',
        'Gastroenterology',
    ];

    public function run(): void
    {
        foreach (self::NAMES as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
}
