<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDoctorRequest;
use App\Http\Requests\Admin\UpdateDoctorRequest;
use App\Http\Requests\Admin\UpdateDoctorStatusRequest;
use App\Http\Resources\DoctorResource;
use App\Mail\NewDoctorAccountMail;
use App\Models\ActivityLog;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $doctors = Doctor::with(['user', 'department'])
            ->when($request->query('q'), function ($query, $q) {
                $query->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
            })
            ->when($request->query('department'), function ($query, $department) {
                $query->whereHas('department', fn ($d) => $d->where('name', $department));
            })
            ->get();

        return response()->json(['data' => DoctorResource::collection($doctors)]);
    }

    public function store(StoreDoctorRequest $request): JsonResponse
    {
        $data = $request->validated();
        $temporaryPassword = Str::password(12);
        $fullName = trim("{$data['first_name']} {$data['last_name']}");

        $doctor = DB::transaction(function () use ($data, $fullName, $temporaryPassword) {
            $user = User::create([
                'name' => $fullName,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $temporaryPassword,
                'role' => 'doctor',
                'avatar_initials' => Str::upper(Str::substr($data['first_name'], 0, 1).Str::substr($data['last_name'], 0, 1)),
            ]);

            return Doctor::create([
                'user_id' => $user->id,
                'specialty' => $data['specialty'],
                'department_id' => $data['department_id'],
                'area' => $data['area'],
                'active' => ($data['status'] ?? 'active') === 'active',
            ]);
        });

        Mail::to($data['email'])->queue(new NewDoctorAccountMail($data['email'], $temporaryPassword));

        ActivityLog::record('admin', $request->user()->name, "Created doctor {$fullName}", ['doctor_id' => $doctor->id]);

        return response()->json(['data' => new DoctorResource($doctor->load(['user', 'department']))], 201);
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor): JsonResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($doctor, $data) {
            $userUpdates = array_filter([
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ], fn ($v) => $v !== null);

            if (isset($data['first_name']) || isset($data['last_name'])) {
                $first = $data['first_name'] ?? explode(' ', $doctor->user->name)[0];
                $last = $data['last_name'] ?? '';
                $userUpdates['name'] = trim("{$first} {$last}");
            }

            if (! empty($userUpdates)) {
                $doctor->user->update($userUpdates);
            }

            $doctor->update(array_filter([
                'specialty' => $data['specialty'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'area' => $data['area'] ?? null,
            ], fn ($v) => $v !== null));
        });

        ActivityLog::record('admin', $request->user()->name, "Updated doctor {$doctor->user->name}", ['doctor_id' => $doctor->id]);

        return response()->json(['data' => new DoctorResource($doctor->fresh(['user', 'department']))]);
    }

    public function updateStatus(UpdateDoctorStatusRequest $request, Doctor $doctor): JsonResponse
    {
        $doctor->update(['active' => $request->validated()['active']]);

        ActivityLog::record(
            'admin',
            $request->user()->name,
            ($doctor->active ? 'Activated' : 'Deactivated')." doctor {$doctor->user->name}",
            ['doctor_id' => $doctor->id]
        );

        return response()->json(['data' => new DoctorResource($doctor->load(['user', 'department']))]);
    }
}
