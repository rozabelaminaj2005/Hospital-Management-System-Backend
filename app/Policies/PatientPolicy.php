<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * A doctor may act on a patient only if there is an appointment, a
     * family_doctor_id link, or a referral connecting them. Admins see
     * everything; patients may only view their own record.
     */
    public function view(User $user, Patient $patient): bool
    {
        return match ($user->role) {
            'admin' => true,
            'patient' => $user->patient && $user->patient->id === $patient->id,
            'doctor' => $this->doctorLinkedToPatient($user, $patient),
            default => false,
        };
    }

    public function act(User $user, Patient $patient): bool
    {
        return $this->view($user, $patient);
    }

    private function doctorLinkedToPatient(User $user, Patient $patient): bool
    {
        $doctor = $user->doctor;

        if (! $doctor) {
            return false;
        }

        if ($patient->family_doctor_id === $doctor->id) {
            return true;
        }

        if ($patient->appointments()->where('doctor_id', $doctor->id)->exists()) {
            return true;
        }

        if ($patient->referrals()->where('from_doctor_id', $doctor->id)->exists()) {
            return true;
        }

        return false;
    }
}
