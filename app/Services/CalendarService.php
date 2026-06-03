<?php

namespace App\Services;

use App\Models\MentoringSession;
use App\Models\PaketBeasiswa;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CalendarService
{
    // ── Student calendar ──────────────────────────────────────────────────
    /**
     * Return task + mentoring events for the logged-in student.
     * Matching logic: student.beasiswa_diampu → paket.nama_beasiswa
     *   → tasks/sessions where class_id = paket._id  OR  paket_beasiswa = nama
     * Checkpoints excluded.
     */
    public function getEvents(string $userId, int $month, int $year): array
    {
        $student = User::find($userId);
        if (! $student) return [];

        $beasiswaDiampu = is_array($student->beasiswa_diampu)
            ? $student->beasiswa_diampu
            : (json_decode($student->beasiswa_diampu ?? '[]', true) ?? []);

        if (empty($beasiswaDiampu)) {
            Log::debug('CalendarService@student: no beasiswa_diampu', ['user_id' => $userId]);
            return [];
        }

        // Resolve paket IDs from names
        $pakets   = PaketBeasiswa::whereIn('nama_beasiswa', $beasiswaDiampu)->get();
        $paketIds = $pakets->pluck('_id')->map(fn ($id) => (string) $id)->toArray();

        Log::debug('CalendarService@student', [
            'user_id'       => $userId,
            'beasiswa'      => $beasiswaDiampu,
            'paket_ids'     => $paketIds,
            'month'         => $month,
            'year'          => $year,
        ]);

        if (empty($paketIds) && empty($beasiswaDiampu)) return [];

        // Resolve mentor for this student (same logic as StudentDashboardService)
        $mentorId = null;
        foreach ($beasiswaDiampu as $beasiswa) {
            $escaped  = preg_quote($beasiswa, '/');
            $mentor   = \App\Models\Mentor::where('beasiswa_diampu', 'regex', "/{$escaped}/i")->first();
            if ($mentor) {
                $mentorId = (string) $mentor->_id;
                break;
            }
        }

        // Tasks: match by class_id OR paket_beasiswa name, optionally filtered by mentor_id
        $tasksQuery = Task::where(function ($q) use ($paketIds, $beasiswaDiampu) {
            $q->whereIn('class_id', $paketIds)
              ->orWhereIn('paket_beasiswa', $beasiswaDiampu);
        });
        if ($mentorId) $tasksQuery->where('mentor_id', $mentorId);

        $tasks = collect($tasksQuery->get()
          ->filter(fn ($t) => $this->isInMonth($t->deadline_date, $month, $year))
          ->map(fn ($t) => [
              'type'  => 'task',
              'id'    => (string) $t->_id,
              'title' => $t->title,
              'date'  => $this->toDateString($t->deadline_date),
          ])->values()->all());

        // Mentoring sessions: match by class_id OR paket_beasiswa, optionally filtered by mentor_id
        $sessionsQuery = MentoringSession::where(function ($q) use ($paketIds, $beasiswaDiampu) {
            $q->whereIn('class_id', $paketIds)
              ->orWhereIn('paket_beasiswa', $beasiswaDiampu);
        });
        if ($mentorId) $sessionsQuery->where('mentor_id', $mentorId);

        $sessions = collect($sessionsQuery->get()
          ->filter(fn ($s) => $this->isInMonth($s->session_date, $month, $year))
          ->map(fn ($s) => [
              'type'  => 'mentoring',
              'id'    => (string) $s->_id,
              'title' => $s->title,
              'date'  => $this->toDateString($s->session_date),
              'link'  => $s->link,
          ])->values()->all());

        return $tasks->merge($sessions)->sortBy('date')->values()->all();
    }

    // ── Mentor calendar ───────────────────────────────────────────────────
    /**
     * Return task + mentoring events for the logged-in mentor.
     * Matching logic: mentor_id = mentor._id (string)
     * Checkpoints excluded.
     */
    public function getMentorEvents(string $mentorId, int $month, int $year): array
    {
        Log::debug('CalendarService@mentor', [
            'mentor_id' => $mentorId,
            'month'     => $month,
            'year'      => $year,
        ]);

        $tasks = collect(Task::where('mentor_id', $mentorId)->get()
            ->filter(fn ($t) => $this->isInMonth($t->deadline_date, $month, $year))
            ->map(fn ($t) => [
                'type'  => 'task',
                'id'    => (string) $t->_id,
                'title' => $t->title,
                'date'  => $this->toDateString($t->deadline_date),
            ])->values()->all());

        $sessions = collect(MentoringSession::where('mentor_id', $mentorId)->get()
            ->filter(fn ($s) => $this->isInMonth($s->session_date, $month, $year))
            ->map(fn ($s) => [
                'type'  => 'mentoring',
                'id'    => (string) $s->_id,
                'title' => $s->title,
                'date'  => $this->toDateString($s->session_date),
                'link'  => $s->link,
            ])->values()->all());

        return $tasks->merge($sessions)->sortBy('date')->values()->all();
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function isInMonth(?string $dateStr, int $month, int $year): bool
    {
        if (! $dateStr) return false;
        try {
            $d = Carbon::parse($dateStr);
            return $d->month === $month && $d->year === $year;
        } catch (\Throwable) {
            return false;
        }
    }

    /** Normalize any date string to yyyy-MM-dd for Flutter. */
    private function toDateString(?string $dateStr): string
    {
        if (! $dateStr) return '';
        try {
            return Carbon::parse($dateStr)->toDateString();
        } catch (\Throwable) {
            return $dateStr;
        }
    }
}
