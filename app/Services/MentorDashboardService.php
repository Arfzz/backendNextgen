<?php

namespace App\Services;

use App\Enums\SubmissionStatus;
use App\Enums\UserRole;
use App\Models\Graduation;
use App\Models\User;
use App\Models\PaketBeasiswa;
use App\Models\CheckpointSubmission;
use App\Repositories\MentoringSessionRepository;
use App\Repositories\TaskRepository;
use App\Repositories\TaskSubmissionRepository;
use App\Repositories\UserRepository;

class MentorDashboardService
{
    public function __construct(
        private readonly TaskRepository             $taskRepo,
        private readonly MentoringSessionRepository $mentoringRepo,
        private readonly TaskSubmissionRepository   $submissionRepo,
        private readonly UserRepository             $userRepo,
    ) {}

    /**
     * Normalize a value that may be a PHP array, a JSON-encoded string,
     * or null → always returns a PHP array.
     * MongoDB sometimes stores casted array fields as JSON strings if the
     * original insert bypassed Eloquent (e.g. seeder or direct insert).
     */
    private function normalizeArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && str_starts_with(trim($value), '[')) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * Aggregate dashboard data for a mentor.
     */
    public function dashboard($mentor): array
    {
        // ── Normalize mentor beasiswa_diampu (may be JSON string in DB) ──
        $beasiswaDiampu = $this->normalizeArray($mentor->beasiswa_diampu ?? []);

        // Calculate students passed — read from `graduations` collection
        // keyed by mentor_id so it reflects all beasiswas the mentor ever handled.
        $passedCount = 0;
        if (! empty($beasiswaDiampu)) {
            $passedCount = Graduation::where('status', 'lulus')
                ->where(function ($q) use ($beasiswaDiampu) {
                    foreach ($beasiswaDiampu as $beasiswa) {
                        $escaped = preg_quote($beasiswa, '/');
                        $q->orWhere('beasiswa_name', 'regex', "/{$escaped}/i");
                    }
                })
                ->count();
        }
        $mentor->students_passed = $passedCount;


        // ── Upcoming Tasks ────────────────────────────────────────────────
        $upcomingTasks = ! empty($beasiswaDiampu)
            ? $this->taskRepo->findUpcomingByBeasiswa($beasiswaDiampu, 5)->all()
            : [];

        // ── Upcoming Mentoring Sessions ───────────────────────────────────
        $upcomingSessions = ! empty($beasiswaDiampu)
            ? $this->mentoringRepo->findUpcomingByBeasiswa($beasiswaDiampu, 5)->all()
            : [];

        // Merge & sort by date, limiting each to 2 to ensure both show up in UI
        $upcomingActivities = collect($upcomingTasks)
            ->take(2)
            ->map(fn ($t) => [
                'type'  => 'task',
                'id'    => (string) $t->_id,
                'title' => $t->title,
                'date'  => $t->deadline_date,
            ])
            ->merge(
                collect($upcomingSessions)->take(2)->map(fn ($s) => [
                    'type'  => 'mentoring',
                    'id'    => (string) $s->_id,
                    'title' => $s->title,
                    'date'  => $s->session_date,
                    'link'  => $s->link,
                ])
            )
            ->sortBy('date')
            ->values()
            ->all();

        // ── Students: match by beasiswa_diampu ───────────────────────────
        $students = [];

        if (! empty($beasiswaDiampu)) {
            $allStudents = User::where('role', UserRole::Student->value)
                ->where(function ($q) use ($beasiswaDiampu) {
                    foreach ($beasiswaDiampu as $beasiswa) {
                        $escaped = preg_quote($beasiswa, '/');
                        $q->orWhere('beasiswa_diampu', 'regex', "/{$escaped}/i");
                    }
                })
                ->get();

            foreach ($allStudents as $student) {
                $studentBeasiswa = $this->normalizeArray($student->beasiswa_diampu ?? []);

                if (empty($studentBeasiswa) && is_string($student->beasiswa_diampu)) {
                    foreach ($beasiswaDiampu as $beasiswa) {
                        if (str_contains($student->beasiswa_diampu, $beasiswa)) {
                            $studentBeasiswa[] = $beasiswa;
                        }
                    }
                }

                $sharedBeasiswa = array_values(array_intersect($beasiswaDiampu, $studentBeasiswa));

                if (empty($sharedBeasiswa)) {
                    $sharedBeasiswa = $beasiswaDiampu; // at least one matched
                }

                // Calculate Fase Percentage for Donut Bar
                $fasePercentage = 0;
                $faseCompletedTotal = 0;
                $faseTotalItems = 0;
                
                $pakets = PaketBeasiswa::where(function($q) use ($sharedBeasiswa) {
                    foreach ($sharedBeasiswa as $b) {
                        $q->orWhere('nama_beasiswa', 'regex', "/".preg_quote($b, '/')."/i");
                    }
                })->get();

                foreach ($pakets as $paket) {
                    $rawFase = $paket->fase_checkpoint ?? '[]';
                    $faseParsed = $this->normalizeArray($rawFase);
                    $faseTotalItems += count($faseParsed);
                    
                    $faseCompletedTotal += \App\Models\CheckpointSubmission::where('student_id', (string) $student->_id)
                        ->where(function($q) use ($paket) {
                            $q->where('paket_beasiswa', $paket->nama_beasiswa)
                              ->orWhere('class_id', (string) $paket->_id);
                        })->count();
                }

                if ($faseTotalItems > 0) {
                    $fasePercentage = (int) round(($faseCompletedTotal / $faseTotalItems) * 100);
                } else {
                    $fasePercentage = $student->progress_percentage ?? 0;
                }

                $students[] = [
                    'student_id'      => (string) $student->_id,
                    'name'            => $student->name,
                    'profile_picture' => \App\Http\Resources\UserResource::resolveUrl($student->profile_picture),
                    'paket'           => implode(', ', $sharedBeasiswa),
                    'progress'        => $fasePercentage,
                    'university'      => $student->university ?? '',
                ];
            }
        }

        return [
            'mentor_profile'      => $mentor,
            'upcoming_activities' => $upcomingActivities,
            'students'            => $students,
        ];
    }

    /**
     * Mentor sends feedback/ulasan — sets status to 'reviewed'.
     * Student can still resubmit after this.
     */
    public function reviewSubmission(string $submissionId, array $data): mixed
    {
        $submission = $this->submissionRepo->findById($submissionId);

        if (! $submission) {
            return null;
        }

        $this->submissionRepo->update($submission, [
            'feedback' => $data['feedback'] ?? null,
            'status'   => SubmissionStatus::Reviewed->value,
        ]);

        return $submission->fresh();
    }

    /**
     * Mentor marks submission as complete (graded/done).
     * After this, student cannot resubmit.
     */
    public function completeSubmission(string $submissionId): mixed
    {
        $submission = $this->submissionRepo->findById($submissionId);

        if (! $submission) {
            return null;
        }

        $this->submissionRepo->update($submission, [
            'status'       => SubmissionStatus::Graded->value,
            'is_completed' => true,
        ]);

        return $submission->fresh();
    }

    /**
     * Grade a task submission (legacy — kept for backward compat).
     */
    public function gradeSubmission(string $submissionId, array $data): mixed
    {
        return $this->reviewSubmission($submissionId, $data);
    }
}
