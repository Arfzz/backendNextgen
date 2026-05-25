<?php

namespace App\Services;

use App\Enums\SubmissionStatus;
use App\Models\User;
use App\Models\PaketBeasiswa;
use App\Repositories\ClassRepository;
use App\Repositories\MentoringSessionRepository;
use App\Repositories\TaskRepository;
use App\Repositories\TaskSubmissionRepository;
use App\Repositories\UserRepository;

class MentorDashboardService
{
    public function __construct(
        private readonly ClassRepository          $classRepo,
        private readonly TaskRepository           $taskRepo,
        private readonly MentoringSessionRepository $mentoringRepo,
        private readonly TaskSubmissionRepository $submissionRepo,
        private readonly UserRepository           $userRepo,
    ) {}

    /**
     * Aggregate dashboard data for a mentor.
     * Students are matched by overlapping beasiswa_diampu between mentor and student.
     */
    public function dashboard($mentor): array
    {
        $beasiswaDiampu = $mentor->beasiswa_diampu ?? [];

        // Resolve paket objects for this mentor's beasiswa list
        $pakets   = PaketBeasiswa::whereIn('nama_beasiswa', $beasiswaDiampu)->get();
        $paketIds = $pakets->pluck('_id')->map(fn ($id) => (string) $id)->toArray();

        // Upcoming tasks (close deadlines)
        $upcomingTasks = $paketIds
            ? $this->taskRepo->findUpcomingByClassIds($paketIds, 3)->all()
            : [];

        // Upcoming mentoring sessions
        $upcomingSessions = $paketIds
            ? $this->mentoringRepo->findUpcomingByClassIds($paketIds, 3)->all()
            : [];

        $upcomingActivities = collect($upcomingTasks)
            ->map(fn ($t) => [
                'type'  => 'task',
                'id'    => (string) $t->_id,
                'title' => $t->title,
                'date'  => $t->deadline_date?->toDateString(),
            ])
            ->merge(
                collect($upcomingSessions)->map(fn ($s) => [
                    'type'  => 'mentoring',
                    'id'    => (string) $s->_id,
                    'title' => $s->title,
                    'date'  => $s->session_date?->toDateString(),
                    'link'  => $s->link,
                ])
            )
            ->sortBy('date')
            ->values()
            ->all();

        // ── Students: match by beasiswa_diampu overlap ──────────────────
        // Find all student-role users who have AT LEAST ONE beasiswa in common
        // with this mentor's beasiswa_diampu list.
        $students = [];

        if (! empty($beasiswaDiampu)) {
            // MongoDB: whereIn on an array field checks if the array field contains
            // any of the given values (equivalent to { beasiswa_diampu: { $in: [...] } })
            $matchedStudents = User::where('role', 'student')
                ->whereIn('beasiswa_diampu', $beasiswaDiampu)
                ->get();

            foreach ($matchedStudents as $student) {
                // Determine which paket labels to show (intersection)
                $studentBeasiswa = $student->beasiswa_diampu ?? [];
                $sharedBeasiswa  = array_values(array_intersect($beasiswaDiampu, $studentBeasiswa));
                $paketLabel      = implode(', ', $sharedBeasiswa);

                // Progress is stored on the user after each submission
                $progress = $student->progress_percentage ?? 0;

                $students[] = [
                    'student_id'      => (string) $student->_id,
                    'name'            => $student->name,
                    'profile_picture' => $student->profile_picture,
                    'paket'           => $paketLabel,
                    'progress'        => $progress,
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
     * Grade a task submission.
     */
    public function gradeSubmission(string $submissionId, array $data): mixed
    {
        $submission = $this->submissionRepo->findById($submissionId);

        if (! $submission) {
            return null;
        }

        $this->submissionRepo->update($submission, [
            'score'    => $data['score'],
            'feedback' => $data['feedback'] ?? null,
            'status'   => SubmissionStatus::Graded->value,
        ]);

        return $submission->fresh();
    }
}
