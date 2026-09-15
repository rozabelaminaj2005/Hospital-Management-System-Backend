<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReassignPatientRequest;
use App\Http\Requests\Admin\StorePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\ActivityLog;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $patients = Patient::with(['user', 'familyDoctor.department'])
            ->when($request->query('q'), function ($query, $q) {
                $query->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
            })
            ->when($request->query('area'), fn ($query, $area) => $query->where('area', $area))
            ->when($request->query('doctor'), fn ($query, $doctorId) => $query->where('family_doctor_id', $doctorId))
            ->get();

        return response()->json(['data' => PatientResource::collection($patients)]);
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $data = $request->validated();
        $password = $data['password'] ?? Str::password(12);

        $patient = DB::transaction(function () use ($data, $password) {
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $password,
                'role' => 'patient',
            ]);

            return Patient::create([
                'user_id' => $user->id,
                'date_of_birth' => $data['date_of_birth'],
                'area' => $data['area'],
                'registered_at' => now(),
            ]);
        });

        ActivityLog::record('admin', $request->user()->name, "Registered patient {$data['full_name']}", ['patient_id' => $patient->id]);

        return response()->json(['data' => new PatientResource($patient->load('user'))], 201);
    }

    public function reassign(ReassignPatientRequest $request, Patient $patient): JsonResponse
    {
        $data = $request->validated();

        $patient->update(['family_doctor_id' => $data['doctor_id']]);

        ActivityLog::record(
            'admin',
            $request->user()->name,
            "Reassigned patient {$patient->user->name}",
            ['patient_id' => $patient->id, 'doctor_id' => $data['doctor_id'], 'reason' => $data['reason'] ?? null]
        );

        return response()->json(['data' => new PatientResource($patient->fresh(['user', 'familyDoctor.department']))]);
    }
}
