<?php

namespace App\Services;

use App\Enums\SubmissionStatus;
use App\Models\User;
use App\Repositories\ClassMemberRepository;
use App\Repositories\ClassRepository;
use App\Repositories\MentoringSessionRepository;
use App\Repositories\TaskRepository;
use App\Repositories\TaskSubmissionRepository;
use App\Repositories\UserRepository;

class MentorDashboardService
{
    public function __construct(
        private readonly ClassRepository          $classRepo,
        private readonly ClassMemberRepository    $classMemberRepo,
        private readonly TaskRepository           $taskRepo,
        private readonly MentoringSessionRepository $mentoringRepo,
        private readonly TaskSubmissionRepository $submissionRepo,
        private readonly UserRepository           $userRepo,
    ) {}

    /**
     * Aggregate dashboard data for a mentor.
     */
    public function dashboard(User $mentor): array
    {
        $classes  = $this->classRepo->findByMentorId((string) $mentor->_id);
        $classIds = $classes->pluck('_id')->map(fn ($id) => (string) $id)->toArray();

        // Upcoming tasks (close deadlines)
        $upcomingTasks = $classIds
            ? $this->taskRepo->findUpcomingByClassIds($classIds, 3)->all()
            : [];

        // Upcoming mentoring sessions
        $upcomingSessions = $classIds
            ? $this->mentoringRepo->findUpcomingByClassIds($classIds, 3)->all()
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

        // All students in mentor's classes (with nested student info)
        $members = $classIds
            ? $this->classMemberRepo->findByClassId($classIds[0] ?? '')->all()
            : [];

        $students = collect($members)->map(function ($m) {
            $m->student = $this->userRepo->findById((string) $m->student_id);
            return $m;
        })->all();

        return [
            'mentor'              => $mentor,
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
