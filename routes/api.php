<?php

use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Api\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Api\Admin\PatientController as AdminPatientController;
use App\Http\Controllers\Api\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Doctor\AppointmentController as DoctorAppointmentController;
use App\Http\Controllers\Api\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Api\Doctor\LabOrderController;
use App\Http\Controllers\Api\Doctor\NoteController;
use App\Http\Controllers\Api\Doctor\PatientController as DoctorPatientController;
use App\Http\Controllers\Api\Doctor\PrescriptionController;
use App\Http\Controllers\Api\Doctor\RecommendationController;
use App\Http\Controllers\Api\Doctor\ReferralController;
use App\Http\Controllers\Api\Doctor\ScheduleController as DoctorScheduleController;
use App\Http\Controllers\Api\Patient\AppointmentController as PatientAppointmentController;
use App\Http\Controllers\Api\Patient\DashboardController as PatientDashboardController;
use App\Http\Controllers\Api\Patient\MedicalRecordController;
use App\Http\Controllers\Api\Patient\ProfileController;
use App\Http\Controllers\Api\Patient\SymptomController;
use App\Http\Controllers\Api\Shared\DepartmentController;
use App\Http\Controllers\Api\Shared\NotificationController;
use App\Http\Controllers\Api\Shared\SymptomController as SharedSymptomController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/departments', [DepartmentController::class, 'index']);
        Route::get('/symptoms', [SharedSymptomController::class, 'index']);
        Route::get('/notifications', [NotificationController::class, 'index']);

        Route::middleware('role:patient')->prefix('patient')->group(function () {
            Route::get('/dashboard', PatientDashboardController::class);
            Route::get('/profile', [ProfileController::class, 'show']);
            Route::put('/profile', [ProfileController::class, 'update']);
            Route::get('/symptoms/options', [SymptomController::class, 'options']);
            Route::post('/symptom-reports', [SymptomController::class, 'store']);
            Route::get('/symptom-reports/{symptomReport}', [SymptomController::class, 'show']);
            Route::post('/symptom-reports/{symptomReport}/book', [SymptomController::class, 'book']);
            Route::get('/appointments', [PatientAppointmentController::class, 'index']);
            Route::get('/medical-records', [MedicalRecordController::class, 'index']);
        });

        Route::middleware('role:doctor')->prefix('doctor')->group(function () {
            Route::get('/dashboard', DoctorDashboardController::class);
            Route::get('/patients', [DoctorPatientController::class, 'index']);
            Route::get('/patients/{patient}', [DoctorPatientController::class, 'show']);
            Route::post('/patients/{patient}/notes', [NoteController::class, 'store']);
            Route::post('/recommendations/{aiRecommendation}/accept', [RecommendationController::class, 'accept']);
            Route::post('/recommendations/{aiRecommendation}/override', [RecommendationController::class, 'override']);
            Route::post('/patients/{patient}/referrals', [ReferralController::class, 'store']);
            Route::post('/patients/{patient}/prescriptions', [PrescriptionController::class, 'store']);
            Route::post('/patients/{patient}/lab-orders', [LabOrderController::class, 'store']);
            Route::post('/patients/{patient}/appointments', [DoctorAppointmentController::class, 'store']);
            Route::get('/schedule', DoctorScheduleController::class);
        });

        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/dashboard', AdminDashboardController::class);
            Route::get('/doctors', [AdminDoctorController::class, 'index']);
            Route::post('/doctors', [AdminDoctorController::class, 'store']);
            Route::put('/doctors/{doctor}', [AdminDoctorController::class, 'update']);
            Route::patch('/doctors/{doctor}/status', [AdminDoctorController::class, 'updateStatus']);
            Route::get('/patients', [AdminPatientController::class, 'index']);
            Route::post('/patients', [AdminPatientController::class, 'store']);
            Route::patch('/patients/{patient}/reassign', [AdminPatientController::class, 'reassign']);
            Route::get('/schedule', AdminScheduleController::class);
            Route::get('/departments', [AdminDepartmentController::class, 'index']);
        });
    });
});
