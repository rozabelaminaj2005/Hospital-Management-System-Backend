<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_patient_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '555-0100',
            'date_of_birth' => '1990-01-01',
            'area' => 'Central',
            'password' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.email', 'jane@example.com')
            ->assertJsonPath('data.user.role', 'patient')
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'role' => 'patient']);
        $this->assertDatabaseHas('patients', ['area' => 'Central']);
    }

    public function test_registration_requires_valid_fields(): void
    {
        $response = $this->postJson('/api/v1/register', []);

        $response->assertStatus(422)->assertJsonStructure(['errors']);
    }

    public function test_a_user_can_login_with_correct_credentials(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password123',
            'role' => 'patient',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_login_fails_with_incorrect_credentials(): void
    {
        User::factory()->create([
            'email' => 'login2@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'login2@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_fetch_me(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('web')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->getJson('/api/v1/me');

        $response->assertOk()->assertJsonPath('data.email', $user->email);
    }

    public function test_unauthenticated_user_cannot_fetch_me(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    public function test_a_user_can_logout(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('web')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/v1/logout');

        $response->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
