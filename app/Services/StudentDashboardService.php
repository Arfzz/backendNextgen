<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Graduation;
use App\Models\Mentor;
use App\Models\MentoringSession;
use App\Models\PaketBeasiswa;
use App\Models\Task;
use App\Models\User;
use App\Repositories\ArticleRepository;
use App\Repositories\CheckpointRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\MentoringSessionRepository;
use App\Repositories\PackageRepository;
use App\Repositories\TaskRepository;
use App\Repositories\TaskSubmissionRepository;
use App\Repositories\UserRepository;

class StudentDashboardService
{
    public function __construct(
        private readonly PackageRepository          $packageRepo,
        private readonly TaskRepository             $taskRepo,
        private readonly MentoringSessionRepository $mentoringRepo,
        private readonly ArticleRepository          $articleRepo,
        private readonly UserRepository             $userRepo,
        private readonly CheckpointRepository       $checkpointRepo,
        private readonly DocumentRepository         $documentRepo,
        private readonly TaskSubmissionRepository   $submissionRepo,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER: Normalize beasiswa_diampu which may be JSON string or real array
    // ─────────────────────────────────────────────────────────────────────────
    private function normalizeArray(mixed $value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && str_starts_with(trim($value), '[')) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * Find the mentor (from collection 'mentors') who handles one of the
     * student's beasiswa packages.
     * Returns the first matching Mentor model or null.
     */
    private function findMentorForStudent(array $beasiswaDiampu): ?Mentor
    {
        if (empty($beasiswaDiampu)) return null;

        // Each beasiswa name may be stored as JSON string in mentor's field too.
        // Try regex match for each beasiswa.
        foreach ($beasiswaDiampu as $beasiswa) {
            $escaped = preg_quote($beasiswa, '/');
            $mentor  = Mentor::where('beasiswa_diampu', 'regex', "/{$escaped}/i")->first();
            if ($mentor) return $mentor;
        }
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HOME DASHBOARD: upcoming activities from student's beasiswa
    // ─────────────────────────────────────────────────────────────────────────
    public function home(User $student): array
    {
        $beasiswaDiampu = $this->normalizeArray($student->beasiswa_diampu ?? []);
        $globalProgress = $student->progress_percentage ?? 0;

        $upcomingActivities = [];

        if (! empty($beasiswaDiampu)) {
            $mentor   = $this->findMentorForStudent($beasiswaDiampu);
            $mentorId = $mentor ? (string) $mentor->_id : null;

            // Tasks query
            $taskQuery = Task::whereIn('paket_beasiswa', $beasiswaDiampu)
                ->orderBy('deadline_date')
                ->limit(3);
            if ($mentorId) $taskQuery->where('mentor_id', $mentorId);
            $upcomingTasks = $taskQuery->get();

            // Mentoring query
            $mentoringQuery = MentoringSession::whereIn('paket_beasiswa', $beasiswaDiampu)
                ->orderBy('session_date')
                ->limit(3);
            if ($mentorId) $mentoringQuery->where('mentor_id', $mentorId);
            $upcomingSessions = $mentoringQuery->get();

            $upcomingActivities = collect($upcomingTasks)
                ->map(fn ($t) => [
                    'type'  => 'task',
                    'id'    => (string) $t->_id,
                    'title' => $t->title,
                    'date'  => $t->deadline_date,
                ])
                ->merge(
                    collect($upcomingSessions)->map(fn ($s) => [
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
        }

        $taskCompleted = 0;
        $taskTotal = 0;
        $fasePercentage = 0;
        $daysLeft = 0;

        if (! empty($beasiswaDiampu)) {
            // Find PaketBeasiswa
            $paket = PaketBeasiswa::whereIn('nama_beasiswa', $beasiswaDiampu)->first();
            if (! $paket) {
                foreach ($beasiswaDiampu as $nama) {
                    $escaped = preg_quote($nama, '/');
                    $paket   = PaketBeasiswa::where('nama_beasiswa', 'regex', "/{$escaped}/i")->first();
                    if ($paket) break;
                }
            }

            if ($paket) {
                $classId = (string) $paket->_id;
                $namaBeasiswa = $paket->nama_beasiswa;

                // 1. Ditutup ... hari lagi (Days Left)
                if (!empty($paket->deadline)) {
                    try {
                        $deadlineDate = \Carbon\Carbon::parse($paket->deadline);
                        $now = \Carbon\Carbon::now();
                        if ($deadlineDate->isAfter($now)) {
                            $daysLeft = (int) $deadlineDate->diffInDays($now);
                        }
                    } catch (\Throwable $e) {}
                }

                // 2. Progress Fase Dilewati
                $rawFase = $paket->fase_checkpoint ?? '[]';
                $faseParsed = $this->normalizeArray($rawFase);
                $faseTotal = count($faseParsed);
                
                $faseCompleted = \App\Models\CheckpointSubmission::where('student_id', (string) $student->_id)
                    ->where(function($q) use ($classId, $namaBeasiswa) {
                        $q->where('paket_beasiswa', $namaBeasiswa)
                          ->orWhere('class_id', $classId);
                    })->count();
                
                if ($faseTotal > 0) {
                    $fasePercentage = (int) round(($faseCompleted / $faseTotal) * 100);
                }

                // 3. Progress Penugasan (Tasks)
                $mentor   = $this->findMentorForStudent($beasiswaDiampu);
                $mentorId = $mentor ? (string) $mentor->_id : null;

                $tasksQuery = Task::where(function ($q) use ($classId, $namaBeasiswa) {
                    $q->where('paket_beasiswa', $namaBeasiswa)
                      ->orWhere('class_id', $classId);
                });
                if ($mentorId) {
                    $tasksQuery->where('mentor_id', $mentorId);
                }
                $tasks = $tasksQuery->get();
                $taskTotal = $tasks->count();

                $submissions = $this->submissionRepo->findByStudentId((string) $student->_id)->keyBy('task_id');

                foreach ($tasks as $task) {
                    $sub = $submissions->get((string) $task->_id);
                    if ($sub && in_array($sub->status?->value ?? $sub->status, ['graded'])) {
                        $taskCompleted++;
                    }
                }
            }
        }

        return [
            'user'                => $student,
            'global_progress'     => $globalProgress,
            'task_completed'      => $taskCompleted,
            'task_total'          => $taskTotal,
            'fase_percentage'     => $fasePercentage,
            'days_left'           => $daysLeft,
            'upcoming_activities' => $upcomingActivities,
            'articles'            => $this->articleRepo->latest(5)->all(),
            'packages'            => $this->packageRepo->all()->all(),
            'mentors'             => $this->userRepo->getMentors(6)->all(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLASS DASHBOARD: penugasan, mentoring, dokumen — filtered by mentor_id
    // ─────────────────────────────────────────────────────────────────────────
    public function classDashboard(User $student): array
    {
        $beasiswaDiampu = $this->normalizeArray($student->beasiswa_diampu ?? []);

        if (empty($beasiswaDiampu)) {
            return ['enrolled' => false];
        }

        // Resolve paket
        $paket = PaketBeasiswa::whereIn('nama_beasiswa', $beasiswaDiampu)->first();

        // Fallback: try regex for JSON-string stored names
        if (! $paket) {
            foreach ($beasiswaDiampu as $nama) {
                $escaped = preg_quote($nama, '/');
                $paket   = PaketBeasiswa::where('nama_beasiswa', 'regex', "/{$escaped}/i")->first();
                if ($paket) break;
            }
        }

        if (! $paket) {
            return ['enrolled' => false];
        }

        $classId      = (string) $paket->_id;
        $namaBeasiswa = $paket->nama_beasiswa;

        // ── Find the mentor for this student's beasiswa ─────────────────────
        $mentor   = $this->findMentorForStudent($beasiswaDiampu);
        $mentorId = $mentor ? (string) $mentor->_id : null;

        // ── Tasks: by paket_beasiswa name AND mentor_id ─────────────────────
        // Dual-query: new records use paket_beasiswa, legacy use class_id
        $tasksQuery = Task::where(function ($q) use ($classId, $namaBeasiswa) {
            $q->where('paket_beasiswa', $namaBeasiswa)
              ->orWhere('class_id', $classId);
        });
        if ($mentorId) {
            $tasksQuery->where('mentor_id', $mentorId);
        }
        $tasks = $tasksQuery->orderBy('deadline_date')->get();

        // ── Submissions for this student ────────────────────────────────────
        $submissions    = $this->submissionRepo
            ->findByStudentId((string) $student->_id)
            ->keyBy('task_id');

        $totalTasks     = $tasks->count();
        $completedTasks = 0;

        $tasksWithSubmission = $tasks->map(function ($task) use ($submissions, &$completedTasks) {
            $taskId = (string) $task->_id;
            $sub    = $submissions->get($taskId);
            if ($sub && in_array($sub->status?->value ?? $sub->status, ['graded'])) {
                $completedTasks++;
                $task->is_fase_passed = true;
            } else {
                $task->is_fase_passed = false;
            }
            $task->submission = $sub;
            return $task;
        });

        // ── Mentoring sessions: by paket_beasiswa AND mentor_id ─────────────
        $mentoringQuery = MentoringSession::where(function ($q) use ($classId, $namaBeasiswa) {
            $q->where('paket_beasiswa', $namaBeasiswa)
              ->orWhere('class_id', $classId);
        });
        if ($mentorId) {
            $mentoringQuery->where('mentor_id', $mentorId);
        }
        $mentoringSessions = $mentoringQuery->orderBy('session_date')->get();

        // ── Documents: by paket_beasiswa AND mentor_id ──────────────────────
        $documentsQuery = Document::where(function ($q) use ($classId, $namaBeasiswa) {
            $q->where('paket_beasiswa', $namaBeasiswa)
              ->orWhere('class_id', $classId);
        });
        if ($mentorId) {
            $documentsQuery->where('mentor_id', $mentorId);
        }
        $documents = $documentsQuery->orderBy('uploaded_at', 'desc')->get();

        // ── Progress ────────────────────────────────────────────────────────
        $progressPercentage = $totalTasks > 0
            ? (int) round(($completedTasks / $totalTasks) * 100)
            : ($student->progress_percentage ?? 0);

        $membership = [
            'id'                  => (string) $student->_id,
            'class_id'            => $classId,
            'student_id'          => (string) $student->_id,
            'progress_percentage' => $progressPercentage,
            'fase_passed'         => $completedTasks,
            'status'              => 'ongoing',
            'mentor_id'           => $mentorId,
            'mentor_name'         => $mentor?->nama_mentor ?? $mentor?->name,
        ];

        // ── Fase Checkpoint: decoded from PaketBeasiswa.fase_checkpoint ─────────
        // Stored as JSON string e.g. "[\"Seleksi Wawancara\",\"Pengumuman\",\"Seleksi Berkas\"]"
        $rawFase       = $paket->fase_checkpoint ?? '[]';
        $faseParsed    = $this->normalizeArray($rawFase);
        $checkpoints   = collect($faseParsed)
            ->values()
            ->map(fn ($title, $idx) => [
                'id'           => (string) ($idx + 1),
                'title'        => $title,
                'order_index'  => $idx + 1,
                'schedule_date'=> '',          // no date on paket-level checkpoints
                'is_completed' => ($idx + 1) <= $completedTasks,
            ])
            ->values()
            ->all();

        // ── All checkpoints completed? ───────────────────────────────────────
        $totalFase             = count($faseParsed);
        $allCheckpointsCompleted = $totalFase > 0
            ? (\App\Models\CheckpointSubmission::where('student_id', (string) $student->_id)
                ->whereIn('paket_beasiswa', $beasiswaDiampu)
                ->distinct('checkpoint_title')->count('checkpoint_title') >= $totalFase)
            : false;

        // ── Graduation status: read from `graduations` collection (not user fields) ──
        // Only check for the CURRENT active beasiswa to prevent stale status from
        // a previous beasiswa showing up as the graduation banner.
        $graduationRecord = Graduation::where('student_id', (string) $student->_id)
            ->where('beasiswa_name', $namaBeasiswa)
            ->orderBy('updated_at', 'desc')
            ->first();

        $graduationStatus = $graduationRecord?->status ?? null;

        return [
            'enrolled'                    => true,
            'membership'                  => $membership,
            'package_info'                => [
                'id'            => $classId,
                'title'         => $namaBeasiswa,
                'deadline_date' => optional($paket->deadline)->toDateString() ?? (string) ($paket->deadline ?? ''),
            ],
            'checkpoints'                 => $checkpoints,
            'tasks_summary'               => "{$completedTasks}/{$totalTasks}",
            'tasks'                       => $tasksWithSubmission->values()->all(),
            'mentoring_sessions'          => $mentoringSessions->values()->all(),
            'documents'                   => $documents->values()->all(),
            'all_checkpoints_completed'   => $allCheckpointsCompleted,
            'mentor_id'                   => $mentorId,
            'graduation_status'           => $graduationStatus,
        ];
    }
}
