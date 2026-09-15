<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Symptom;
use App\Models\SymptomReport;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\SymptomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SymptomTriageFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([DepartmentSeeder::class, SymptomSeeder::class]);

        $cardiology = Department::where('name', 'Cardiology')->firstOrFail();
        $doctorUser = User::factory()->create(['role' => 'doctor', 'name' => 'Dr. Test Cardio']);
        Doctor::create([
            'user_id' => $doctorUser->id,
            'specialty' => 'Cardiology',
            'department_id' => $cardiology->id,
            'area' => 'Central',
            'active' => true,
        ]);
    }

    private function actingAsPatient(): array
    {
        $user = User::factory()->create(['role' => 'patient']);
        $patient = Patient::create([
            'user_id' => $user->id,
            'date_of_birth' => '1990-01-01',
            'area' => 'Central',
            'registered_at' => now(),
        ]);

        $token = $user->createToken('web')->plainTextToken;

        return [$user, $patient, $token];
    }

    public function test_full_triage_to_booking_flow(): void
    {
        [$user, $patient, $token] = $this->actingAsPatient();

        $chestPain = Symptom::where('name', 'Chest pain')->firstOrFail();

        $submitResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/patient/symptom-reports', [
                'symptom_ids' => [$chestPain->id],
                'description' => 'Sudden chest pain and tightness.',
                'duration' => 'hours',
                'pain_level' => 8,
                'use_ai' => true,
            ]);

        $submitResponse->assertCreated()->assertJsonPath('data.status', 'analyzing');

        $reportId = $submitResponse->json('data.reportId');

        // The ->afterResponse() job runs as part of the test client's request
        // cycle, so the report is already classified by the time we poll it.
        $showResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/patient/symptom-reports/{$reportId}");

        $showResponse->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.aiRecommendation.urgency', 'high')
            ->assertJsonPath('data.aiRecommendation.department.name', 'Cardiology');

        $bookResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/patient/symptom-reports/{$reportId}/book");

        $bookResponse->assertCreated()->assertJsonPath('data.status', 'scheduled');

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_cannot_book_before_recommendation_completes(): void
    {
        [$user, $patient, $token] = $this->actingAsPatient();

        $report = SymptomReport::create([
            'patient_id' => $patient->id,
            'description' => 'Mild headache.',
            'duration' => 'day',
            'used_ai' => true,
            'status' => 'analyzing',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/patient/symptom-reports/{$report->id}/book");

        $response->assertStatus(422);
    }

    public function test_a_patient_cannot_view_another_patients_symptom_report(): void
    {
        [$userA, $patientA, $tokenA] = $this->actingAsPatient();
        [$userB, $patientB, $tokenB] = $this->actingAsPatient();

        $report = SymptomReport::create([
            'patient_id' => $patientA->id,
            'description' => 'Fatigue.',
            'duration' => 'days',
            'used_ai' => true,
            'status' => 'analyzing',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$tokenB}")
            ->getJson("/api/v1/patient/symptom-reports/{$report->id}");

        $response->assertStatus(403);
    }
}
