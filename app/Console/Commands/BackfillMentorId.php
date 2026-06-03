<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\Mentor;
use App\Models\MentoringSession;
use App\Models\Task;
use Illuminate\Console\Command;

/**
 * Backfill mentor_id on existing tasks, mentoring_sessions, and documents
 * by matching their paket_beasiswa name to the mentor's beasiswa_diampu.
 *
 * Usage: php artisan backfill:mentor-id
 */
class BackfillMentorId extends Command
{
    protected $signature   = 'backfill:mentor-id';
    protected $description = 'Backfill mentor_id on tasks, mentoring_sessions, and documents from paket_beasiswa name';

    private function normalizeArray(mixed $value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && str_starts_with(trim($value), '[')) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    public function handle(): int
    {
        $mentors = Mentor::all();

        if ($mentors->isEmpty()) {
            $this->error('No mentors found.');
            return self::FAILURE;
        }

        // Build map: "YAPI ITB" => mentor_id
        $beasiswaToMentorId = [];
        foreach ($mentors as $mentor) {
            $beasiswas = $this->normalizeArray($mentor->beasiswa_diampu ?? []);
            foreach ($beasiswas as $nama) {
                $beasiswaToMentorId[$nama] = (string) $mentor->_id;
            }
        }

        $this->info('Beasiswa → Mentor map: ' . json_encode($beasiswaToMentorId));

        // ── Tasks ──────────────────────────────────────────────────────────
        $tasks   = Task::whereNull('mentor_id')->orWhere('mentor_id', '')->get();
        $updated = 0;
        foreach ($tasks as $task) {
            $paket = $task->paket_beasiswa;
            if ($paket && isset($beasiswaToMentorId[$paket])) {
                $task->mentor_id = $beasiswaToMentorId[$paket];
                $task->save();
                $updated++;
            }
        }
        $this->info("Tasks backfilled: {$updated}");

        // ── Mentoring Sessions ─────────────────────────────────────────────
        $sessions  = MentoringSession::whereNull('mentor_id')->orWhere('mentor_id', '')->get();
        $updated2  = 0;
        foreach ($sessions as $session) {
            $paket = $session->paket_beasiswa;
            if ($paket && isset($beasiswaToMentorId[$paket])) {
                $session->mentor_id = $beasiswaToMentorId[$paket];
                $session->save();
                $updated2++;
            }
        }
        $this->info("Mentoring sessions backfilled: {$updated2}");

        // ── Documents ──────────────────────────────────────────────────────
        $docs     = Document::whereNull('mentor_id')->orWhere('mentor_id', '')->get();
        $updated3 = 0;
        foreach ($docs as $doc) {
            $paket = $doc->paket_beasiswa;
            if ($paket && isset($beasiswaToMentorId[$paket])) {
                $doc->mentor_id = $beasiswaToMentorId[$paket];
                $doc->save();
                $updated3++;
            }
        }
        $this->info("Documents backfilled: {$updated3}");

        $this->info('Done!');
        return self::SUCCESS;
    }
}
