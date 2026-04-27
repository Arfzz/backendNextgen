<?php

namespace App\Services;

use App\Models\Checkpoint;
use App\Models\MentoringSession;
use App\Models\Task;
use App\Repositories\ClassMemberRepository;

class CalendarService
{
    public function __construct(
        private readonly ClassMemberRepository $classMemberRepo,
    ) {}

    /**
     * Return all calendar events (tasks, checkpoints, mentoring) for a given month.
     */
    public function getEvents(string $userId, int $month, int $year): array
    {
        $membership = $this->classMemberRepo->findActiveByStudentId($userId);
        if (! $membership) {
            return [];
        }

        $classId = (string) $membership->class_id;

        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $tasks = Task::where('class_id', $classId)
            ->whereBetween('deadline_date', [$start, $end])
            ->get()
            ->map(fn ($t) => [
                'type'  => 'task',
                'id'    => (string) $t->_id,
                'title' => $t->title,
                'date'  => $t->deadline_date?->toDateString(),
            ]);

        $checkpoints = Checkpoint::where('class_id', $classId)
            ->whereBetween('schedule_date', [$start, $end])
            ->get()
            ->map(fn ($c) => [
                'type'  => 'checkpoint',
                'id'    => (string) $c->_id,
                'title' => $c->title,
                'date'  => $c->schedule_date?->toDateString(),
            ]);

        $sessions = MentoringSession::where('class_id', $classId)
            ->whereBetween('session_date', [$start, $end])
            ->get()
            ->map(fn ($s) => [
                'type'  => 'mentoring',
                'id'    => (string) $s->_id,
                'title' => $s->title,
                'date'  => \Carbon\Carbon::parse($s->session_date)->toDateString(),
                'link'  => $s->link,
            ]);

        return $tasks->merge($checkpoints)->merge($sessions)
            ->sortBy('date')
            ->values()
            ->all();
    }
}
