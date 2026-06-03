<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Mentor;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Debug command — shows the actual state of the database
 * to diagnose why students don't appear in mentor dashboard.
 *
 * Usage: php artisan debug:mentor-dashboard {mentorEmail}
 *   e.g. php artisan debug:mentor-dashboard mentor@example.com
 */
class DebugMentorDashboard extends Command
{
    protected $signature   = 'debug:mentor-dashboard {email : Email mentor yang akan di-debug}';
    protected $description = 'Debug: show mentor beasiswa_diampu and matching students';

    public function handle(): int
    {
        $email = $this->argument('email');

        // Find mentor
        $mentor = Mentor::where('email', $email)->first()
            ?? User::where('email', $email)->where('role', UserRole::Mentor->value)->first();

        if (! $mentor) {
            $this->error("Mentor dengan email [{$email}] tidak ditemukan.");
            return self::FAILURE;
        }

        $beasiswaDiampu = $mentor->beasiswa_diampu ?? [];

        $this->info("=== MENTOR ===");
        $this->line("  Nama   : {$mentor->nama_mentor ?? $mentor->name}");
        $this->line("  Email  : {$mentor->email}");
        $this->line("  Class  : " . get_class($mentor));
        $this->line("  beasiswa_diampu: " . json_encode($beasiswaDiampu));

        if (empty($beasiswaDiampu)) {
            $this->error("  ⚠️  beasiswa_diampu KOSONG! Mentor tidak punya beasiswa diampu.");
            $this->line('');
            $this->comment('Solusi: Update beasiswa_diampu mentor di MongoDB atau via seeder.');

            // Show all mentor records in DB
            $this->info("\n=== Semua Mentor di DB ===");
            foreach (Mentor::all() as $m) {
                $this->line("  [{$m->email}] beasiswa_diampu: " . json_encode($m->beasiswa_diampu));
            }
            return self::FAILURE;
        }

        $this->info("\n=== STUDENTS QUERY ===");
        $this->line("  Mencari students dengan beasiswa_diampu IN: " . json_encode($beasiswaDiampu));

        // Show all students and their beasiswa
        $allStudents = User::where('role', UserRole::Student->value)->get();
        $this->line("  Total student users di DB: {$allStudents->count()}");

        $matched = [];
        foreach ($allStudents as $s) {
            $sb = is_array($s->beasiswa_diampu) ? $s->beasiswa_diampu : [];
            $overlap = array_intersect($beasiswaDiampu, $sb);
            $mark = ! empty($overlap) ? '✓ MATCH' : '✗ no match';
            $this->line("  {$mark} | {$s->name} | beasiswa_diampu: " . json_encode($sb));
            if (! empty($overlap)) {
                $matched[] = $s->name;
            }
        }

        $this->info("\n=== HASIL ===");
        $this->line("  Matched students: " . count($matched));
        if (! empty($matched)) {
            foreach ($matched as $n) $this->line("  ✓ {$n}");
        } else {
            $this->error("  Tidak ada student yang match!");
            $this->comment('Solusi: Jalankan: php artisan patch:student-beasiswa');
        }

        return self::SUCCESS;
    }
}
