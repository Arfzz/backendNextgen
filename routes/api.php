<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Mentor\CalendarController as MentorCalendarController;
use App\Http\Controllers\Mentor\CheckpointController as MentorCheckpointController;
use App\Http\Controllers\Mentor\ClassController as MentorClassController;
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\DocumentController as MentorDocumentController;
use App\Http\Controllers\Mentor\MentoringController as MentorMentoringController;
use App\Http\Controllers\Mentor\StudentCheckpointController as MentorStudentCheckpointController;
use App\Http\Controllers\Mentor\StudentController as MentorStudentController;
use App\Http\Controllers\Mentor\StudentProgressController as MentorStudentProgressController;
use App\Http\Controllers\Mentor\SubmissionController as MentorSubmissionController;
use App\Http\Controllers\Mentor\TaskController as MentorTaskController;
use App\Http\Controllers\Payment\OrderController;
use App\Http\Controllers\Payment\WebhookController;
use App\Http\Controllers\Student\CalendarController;
use App\Http\Controllers\Student\CheckpointSubmissionController as StudentCheckpointSubmissionController;
use App\Http\Controllers\Student\ClassDashboardController;
use App\Http\Controllers\Student\HomeController;
use App\Http\Controllers\Student\PackageController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
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

// File serve — bypasses artisan serve symlink issue on Windows
// GET /api/v1/files/serve?path=checkpoint_submissions/uuid.pdf
Route::get('v1/files/serve', [\App\Http\Controllers\Api\FileServeController::class, 'serve'])->name('api.files.serve');

// Testimonial API
Route::get('testimonial', [\App\Http\Controllers\Api\TestimonialController::class, 'index'])->name('api.testimonial.index');
Route::post('testimonial', [\App\Http\Controllers\Api\TestimonialController::class, 'store'])->name('api.testimonial.store');
Route::get('mentor/{mentorId}/rating', [\App\Http\Controllers\Api\TestimonialController::class, 'mentorRating'])->name('api.mentor.rating');

Route::prefix('dashboard/charts')->name('api.dashboard.charts.')->group(function () {
    Route::get('/mentor-vs-peserta', [\App\Http\Controllers\Api\DashboardChartController::class, 'mentorVsPeserta'])->name('mentor-vs-peserta');
    Route::get('/top-beasiswa', [\App\Http\Controllers\Api\DashboardChartController::class, 'topBeasiswa'])->name('top-beasiswa');
    Route::get('/total-penjualan', [\App\Http\Controllers\Api\DashboardChartController::class, 'totalPenjualan'])->name('total-penjualan');
    Route::get('/status-transaksi', [\App\Http\Controllers\Api\DashboardChartController::class, 'statusTransaksi'])->name('status-transaksi');
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

        // Chat — old private messaging (kept for backward compat)
        Route::prefix('chat')->name('chat.')->group(function () {
            Route::get('/conversations', [\App\Http\Controllers\Api\ChatController::class, 'index'])->name('index');
            Route::get('/conversations/{target_user_id}', [\App\Http\Controllers\Api\ChatController::class, 'show'])->name('show');
            Route::post('/conversations/{target_user_id}', [\App\Http\Controllers\Api\ChatController::class, 'store'])->name('store');
        });

        // Chat Rooms — new group + private chat system
        Route::prefix('chat/rooms')->name('chat.rooms.')->group(function () {
            Route::get('/', [ChatController::class, 'rooms'])->name('index');
            Route::post('/private/{targetUserId}', [ChatController::class, 'openPrivate'])->name('private');
            Route::get('/{roomId}/messages', [ChatController::class, 'messages'])->name('messages');
            Route::post('/{roomId}/messages', [ChatController::class, 'sendMessage'])->name('send');
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

            // Profile (includes beasiswa_diampu management)
            Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile.show');
            Route::match(['put', 'post'], '/profile', [StudentProfileController::class, 'update'])->name('profile.update');

            // Checkpoint submissions
            Route::post('/checkpoints/submit', [StudentCheckpointSubmissionController::class, 'submit'])->name('checkpoints.submit');
            Route::get('/checkpoints/my-submissions', [StudentCheckpointSubmissionController::class, 'mySubmissions'])->name('checkpoints.my-submissions');

            // Graduation & Testimonial
            Route::post('/graduation', [\App\Http\Controllers\Student\GraduationController::class, 'submit'])->name('graduation.submit');
            Route::get('/graduation/status', [\App\Http\Controllers\Student\GraduationController::class, 'notificationStatus'])->name('graduation.status');
            Route::post('/graduation/mark-notified', [\App\Http\Controllers\Student\GraduationController::class, 'markNotified'])->name('graduation.mark-notified');
        });

    // ── Mentor Routes ─────────────────────────────────────────────────────
    Route::prefix('mentor')
        ->name('mentor.')
        ->middleware(['auth:sanctum', 'role:mentor'])
        ->group(function () {
            // Profile
            Route::get('/profile', [\App\Http\Controllers\Mentor\ProfileController::class, 'show'])->name('profile.show');
            Route::match(['put', 'post'], '/profile', [\App\Http\Controllers\Mentor\ProfileController::class, 'update'])->name('profile.update');

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

            // Submission review/complete
            Route::post('/submissions/{submission_id}/grade', [MentorSubmissionController::class, 'grade'])->name('submissions.grade');
            Route::post('/submissions/{submission_id}/review', [MentorSubmissionController::class, 'review'])->name('submissions.review');
            Route::post('/submissions/{submission_id}/complete', [MentorSubmissionController::class, 'complete'])->name('submissions.complete');

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
            Route::put('/documents/{document_id}', [MentorDocumentController::class, 'update'])->name('documents.update');
            Route::delete('/documents/{document_id}', [MentorDocumentController::class, 'destroy'])->name('documents.destroy');

            // Checkpoint
            Route::post('/classes/{class_id}/checkpoints', [MentorCheckpointController::class, 'store'])->name('checkpoints.store');

            // Student checkpoint submissions (for peserta_detail_page)
            Route::get('/students/{student_id}/checkpoints', [MentorStudentCheckpointController::class, 'index'])->name('students.checkpoints');

            // Mentor set student graduation status
            Route::post('/students/{student_id}/graduation-status', [\App\Http\Controllers\Mentor\StudentProgressController::class, 'setGraduationStatus'])->name('students.graduation-status');

            // Mentor calendar (task + mentoring by mentor_id, no checkpoints)
            Route::get('/calendar', [MentorCalendarController::class, 'index'])->name('mentor.calendar');
        }); // ← close mentor group
}); // ← close main v1 middleware group

// ── Payment / Midtrans ─────────────────────────────────────────────────────
// Webhook: no auth, no CSRF (Midtrans server calls this)
Route::post('webhook/midtrans', [WebhookController::class, 'handle'])->name('webhook.midtrans');

// Order endpoints (student must be logged in)
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('/orders',                 [OrderController::class, 'create'])->name('orders.create');
    Route::get('/orders/{orderId}/status', [OrderController::class, 'status'])->name('orders.status');
});
