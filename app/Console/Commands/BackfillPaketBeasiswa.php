<?php

namespace App\Console\Commands;

use App\Models\MentoringSession;
use App\Models\PaketBeasiswa;
use App\Models\Task;
use Illuminate\Console\Command;

class BackfillPaketBeasiswa extends Command
{
    protected $signature   = 'backfill:paket-beasiswa';
    protected $description = 'Backfill paket_beasiswa field on tasks and mentoring_sessions from their class_id (PaketBeasiswa._id)';

    public function handle(): int
    {
        // Build a lookup map: paket._id (string) => nama_beasiswa
        $pakets = PaketBeasiswa::all();
        $map    = [];
        foreach ($pakets as $p) {
            $map[(string) $p->_id] = $p->nama_beasiswa;
        }

        if (empty($map)) {
            $this->warn('No PaketBeasiswa records found. Aborting.');
            return self::FAILURE;
        }

        // ── Tasks ────────────────────────────────────────────────────────────
        $tasks   = Task::whereNull('paket_beasiswa')->orWhere('paket_beasiswa', '')->get();
        $updated = 0;

        foreach ($tasks as $task) {
            $classId = (string) $task->class_id;
            if (isset($map[$classId])) {
                $task->paket_beasiswa = $map[$classId];
                $task->save();
                $updated++;
            }
        }

        $this->info("Tasks backfilled: {$updated}");

        // ── Mentoring Sessions ───────────────────────────────────────────────
        $sessions = MentoringSession::whereNull('paket_beasiswa')->orWhere('paket_beasiswa', '')->get();
        $updated2 = 0;

        foreach ($sessions as $session) {
            $classId = (string) $session->class_id;
            if (isset($map[$classId])) {
                $session->paket_beasiswa = $map[$classId];
                $session->save();
                $updated2++;
            }
        }

        $this->info("Mentoring sessions backfilled: {$updated2}");
        $this->info('Done!');

        return self::SUCCESS;
    }
}
