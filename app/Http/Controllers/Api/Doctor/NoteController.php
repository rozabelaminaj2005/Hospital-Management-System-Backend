<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreNoteRequest;
use App\Http\Resources\ConsultationNoteResource;
use App\Models\ConsultationNote;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(StoreNoteRequest $request, Patient $patient): JsonResponse
    {
        $this->authorize('act', $patient);

        $doctor = $request->user()->doctor()->firstOrFail();

        $appointment = $patient->appointments()
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('scheduled_at')
            ->firstOrFail();

        $note = ConsultationNote::create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'notes' => $request->validated()['notes'],
            'attachments' => $request->validated()['attachments'] ?? null,
        ]);

        return response()->json(['data' => new ConsultationNoteResource($note)], 201);
    }
}
