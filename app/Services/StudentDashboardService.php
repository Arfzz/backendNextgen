<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\ArticleRepository;
use App\Repositories\CheckpointRepository;
use App\Repositories\ClassMemberRepository;
use App\Repositories\ClassRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\MentoringSessionRepository;
use App\Repositories\PackageRepository;
use App\Repositories\TaskRepository;
use App\Repositories\TaskSubmissionRepository;
use App\Repositories\UserRepository;

class StudentDashboardService
{
    public function __construct(
        private readonly ClassMemberRepository    $classMemberRepo,
        private readonly ClassRepository          $classRepo,
        private readonly PackageRepository        $packageRepo,
        private readonly TaskRepository           $taskRepo,
        private readonly MentoringSessionRepository $mentoringRepo,
        private readonly ArticleRepository        $articleRepo,
        private readonly UserRepository           $userRepo,
        private readonly CheckpointRepository     $checkpointRepo,
        private readonly DocumentRepository       $documentRepo,
        private readonly TaskSubmissionRepository $submissionRepo,
    ) {}

    /**
     * Aggregate data for the Student Home Dashboard.
     */
    public function home(User $student): array
    {
        // Active class membership
        $membership = $this->classMemberRepo->findActiveByStudentId((string) $student->_id);
        $globalProgress = $membership?->progress_percentage ?? 0;

        // Student's class IDs for activity lookups
        $classIds = $membership
            ? [(string) $membership->class_id]
            : [];

        // Upcoming activities (upcoming tasks + upcoming mentoring sessions)
        $upcomingTasks = $classIds
            ? $this->taskRepo->findUpcomingByClassIds($classIds, 2)->all()
            : [];
        $upcomingSessions = $classIds
            ? $this->mentoringRepo->findUpcomingByClassIds($classIds, 2)->all()
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

        return [
            'user'                => $student,
            'global_progress'     => $globalProgress,
            'upcoming_activities' => $upcomingActivities,
            'articles'            => $this->articleRepo->latest(5)->all(),
            'packages'            => $this->packageRepo->all()->all(),
            'mentors'             => $this->userRepo->getMentors(6)->all(),
        ];
    }

    /**
     * Aggregate data for the Student Class Dashboard.
     */
    public function classDashboard(User $student): array
    {
        $membership = $this->classMemberRepo->findActiveByStudentId((string) $student->_id);

        if (! $membership) {
            return ['enrolled' => false];
        }

        $classId = (string) $membership->class_id;
        $kelas   = $this->classRepo->findById($classId);
        $package = $kelas ? $this->packageRepo->findById((string) $kelas->package_id) : null;

        // Checkpoints with completion status
        $checkpoints = $this->checkpointRepo->findByClassId($classId);
        $checkpointIds = $checkpoints->pluck('_id')->map(fn ($id) => (string) $id)->toArray();
        $completedIds  = $this->checkpointRepo->getCompletedCheckpointIds(
            (string) $student->_id,
            $checkpointIds
        );

        $checkpointsWithStatus = $checkpoints->map(function ($cp) use ($completedIds) {
            $cp->is_completed = in_array((string) $cp->_id, $completedIds);
            return $cp;
        });

        // Tasks with student submission
        $tasks = $this->taskRepo->findByClassId($classId);
        $submissions = $this->submissionRepo->findByStudentId((string) $student->_id)
            ->keyBy('task_id');

        $totalTasks     = $tasks->count();
        $completedTasks = 0;

        $tasksWithSubmission = $tasks->map(function ($task) use ($submissions, &$completedTasks) {
            $taskId = (string) $task->_id;
            $sub    = $submissions->get($taskId);
            if ($sub && in_array($sub->status?->value ?? $sub->status, ['submitted', 'graded'])) {
                $completedTasks++;
            }
            $task->submission = $sub;
            return $task;
        });

        return [
            'enrolled'     => true,
            'membership'   => $membership,
            'package_info' => $package ? [
                'id'            => (string) $package->_id,
                'title'         => $package->title,
                'deadline_date' => $package->deadline_date?->toDateString(),
            ] : null,
            'checkpoints'      => $checkpointsWithStatus->values()->all(),
            'tasks_summary'    => "{$completedTasks}/{$totalTasks}",
            'tasks'            => $tasksWithSubmission->values()->all(),
            'mentoring_sessions' => $this->mentoringRepo->findByClassId($classId)->all(),
            'documents'        => $this->documentRepo->findByClassId($classId)->all(),
        ];
    }
}
