<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Mentor\CheckpointController as MentorCheckpointController;
use App\Http\Controllers\Mentor\ClassController as MentorClassController;
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\DocumentController as MentorDocumentController;
use App\Http\Controllers\Mentor\MentoringController as MentorMentoringController;
use App\Http\Controllers\Mentor\StudentController as MentorStudentController;
use App\Http\Controllers\Mentor\StudentProgressController as MentorStudentProgressController;
use App\Http\Controllers\Mentor\SubmissionController as MentorSubmissionController;
use App\Http\Controllers\Mentor\TaskController as MentorTaskController;
use App\Http\Controllers\Student\CalendarController;
use App\Http\Controllers\Student\ClassDashboardController;
use App\Http\Controllers\Student\HomeController;
use App\Http\Controllers\Student\PackageController;
use App\Http\Controllers\Student\TaskController as StudentTaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Legacy API routes (preserved — not removed)
|--------------------------------------------------------------------------
*/
Route::apiResource('paket-beasiswa', \App\Http\Controllers\Api\PaketBeasiswaController::class)->names('api.paket-beasiswa');
Route::apiResource('mentor', \App\Http\Controllers\Api\MentorController::class)->names('api.mentor');
Route::apiResource('artikel', \App\Http\Controllers\Api\ArtikelController::class)->names('api.artikel');
Route::apiResource('users', \App\Http\Controllers\Api\UserController::class)->names('api.users');

// Portfolio Stats API
Route::get('portfolio/stats', [\App\Http\Controllers\Api\PortfolioController::class, 'stats'])->name('api.portfolio.stats');

// Testimonial API
Route::get('testimonial', [\App\Http\Controllers\Api\TestimonialController::class, 'index'])->name('api.testimonial.index');
Route::post('testimonial', [\App\Http\Controllers\Api\TestimonialController::class, 'store'])->name('api.testimonial.store');
Route::get('mentor/{mentorId}/rating', [\App\Http\Controllers\Api\TestimonialController::class, 'mentorRating'])->name('api.mentor.rating');

Route::prefix('dashboard/charts')->name('api.dashboard.charts.')->group(function () {
    Route::get('/mentor-vs-peserta', [\App\Http\Controllers\Api\DashboardChartController::class, 'mentorVsPeserta'])->name('mentor-vs-peserta');
    Route::get('/top-beasiswa', [\App\Http\Controllers\Api\DashboardChartController::class, 'topBeasiswa'])->name('top-beasiswa');
    Route::get('/total-penjualan', [\App\Http\Controllers\Api\DashboardChartController::class, 'totalPenjualan'])->name('total-penjualan');
    Route::get('/total-pendapatan', [\App\Http\Controllers\Api\DashboardChartController::class, 'totalPendapatan'])->name('total-pendapatan');
});

/*
|--------------------------------------------------------------------------
| Nalarin API v1
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    // ── Authentication (Public) ───────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });

    // ── Shared Auth Routes (Student & Mentor) ────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Calendar
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\NotificationController::class, 'index'])->name('index');
            Route::post('/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markRead'])->name('read');
            Route::post('/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllRead'])->name('read-all');
        });

        // Chat — Private Messaging
        Route::prefix('chat')->name('chat.')->group(function () {
            Route::get('/conversations', [\App\Http\Controllers\Api\ChatController::class, 'index'])->name('index');
            Route::get('/conversations/{target_user_id}', [\App\Http\Controllers\Api\ChatController::class, 'show'])->name('show');
            Route::post('/conversations/{target_user_id}', [\App\Http\Controllers\Api\ChatController::class, 'store'])->name('store');
        });
    });

    // ── Student Routes ────────────────────────────────────────────────────
    Route::prefix('student')
        ->name('student.')
        ->middleware(['auth:sanctum', 'role:student'])
        ->group(function () {
            // Home dashboard
            Route::get('/home', [HomeController::class, 'index'])->name('home');

            // Class dashboard
            Route::get('/my-class-dashboard', [ClassDashboardController::class, 'index'])->name('class-dashboard');

            // Packages
            Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
            Route::get('/packages/{id}', [PackageController::class, 'show'])->name('packages.show');

            // Tasks
            Route::get('/tasks/{task_id}', [StudentTaskController::class, 'show'])->name('tasks.show');
            Route::post('/tasks/{task_id}/submit', [StudentTaskController::class, 'submit'])->name('tasks.submit');
        });

    // ── Mentor Routes ─────────────────────────────────────────────────────
    Route::prefix('mentor')
        ->name('mentor.')
        ->middleware(['auth:sanctum', 'role:mentor'])
        ->group(function () {
            // Dashboard
            Route::get('/dashboard', [MentorDashboardController::class, 'index'])->name('dashboard');

            // Classes (mentor_kelas_page.dart)
            Route::get('/classes', [MentorClassController::class, 'index'])->name('classes.index');
            Route::get('/classes/{class_id}/content', [MentorClassController::class, 'content'])->name('classes.content');
            Route::get('/classes/{class_id}/students', [MentorClassController::class, 'students'])->name('classes.students');

            // Student full progress (peserta_detail_page.dart)
            Route::get('/students/{student_id}/progress', [MentorStudentProgressController::class, 'show'])->name('students.progress');

            // Student submissions view (old endpoint — kept for compatibility)
            Route::get('/students/{student_id}/submissions', [MentorStudentController::class, 'submissions'])->name('students.submissions');

            // Grade a submission
            Route::post('/submissions/{submission_id}/grade', [MentorSubmissionController::class, 'grade'])->name('submissions.grade');

            // Task submissions list
            Route::get('/tasks/{task_id}/submissions', [MentorTaskController::class, 'submissions'])->name('tasks.submissions');

            // Task CRUD
            Route::post('/classes/{class_id}/tasks', [MentorTaskController::class, 'store'])->name('tasks.store');
            Route::put('/tasks/{task_id}', [MentorTaskController::class, 'update'])->name('tasks.update');
            Route::delete('/tasks/{task_id}', [MentorTaskController::class, 'destroy'])->name('tasks.destroy');

            // Mentoring CRUD
            Route::post('/classes/{class_id}/mentoring', [MentorMentoringController::class, 'store'])->name('mentoring.store');
            Route::put('/mentoring/{session_id}', [MentorMentoringController::class, 'update'])->name('mentoring.update');
            Route::delete('/mentoring/{session_id}', [MentorMentoringController::class, 'destroy'])->name('mentoring.destroy');

            // Document CRUD
            Route::post('/classes/{class_id}/documents', [MentorDocumentController::class, 'store'])->name('documents.store');
            Route::delete('/documents/{document_id}', [MentorDocumentController::class, 'destroy'])->name('documents.destroy');

            // Checkpoint
            Route::post('/classes/{class_id}/checkpoints', [MentorCheckpointController::class, 'store'])->name('checkpoints.store');
        });
});

