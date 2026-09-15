<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Mail\PasswordResetLinkMail;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'role' => 'patient',
                'avatar_initials' => $this->initials($data['full_name']),
            ]);

            Patient::create([
                'user_id' => $user->id,
                'date_of_birth' => $data['date_of_birth'],
                'area' => $data['area'],
                'registered_at' => now(),
            ]);

            return $user;
        });

        $token = $user->createToken('web')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();
        $token = $user->createToken('web')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['patient.familyDoctor.department', 'doctor.department']);

        return response()->json(['data' => new UserResource($user)]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->validated()['email'];
        $user = User::where('email', $email)->first();

        if ($user) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => bcrypt($token), 'created_at' => now()]
            );

            Mail::to($email)->queue(new PasswordResetLinkMail($email, $token));
        }

        return response()->json([
            'message' => 'If an account with that email exists, a password reset link has been sent.',
        ]);
    }

    private function initials(string $name): string
    {
        $parts = array_filter(explode(' ', trim($name)));
        $initials = collect($parts)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->take(2)->implode('');

        return $initials ?: 'U';
    }
}
