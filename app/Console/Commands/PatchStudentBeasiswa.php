<?php

namespace App\Console\Commands;

use App\Models\PaketBeasiswa;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Console\Command;

/**
 * One-time command: assign beasiswa_diampu to existing student users
 * so they appear in the mentor dashboard peserta section.
 *
 * Usage:
 *   php artisan patch:student-beasiswa
 *
 * Optionally specify a beasiswa name to assign to ALL students:
 *   php artisan patch:student-beasiswa "Beasiswa Unggulan"
 */
class PatchStudentBeasiswa extends Command
{
    protected $signature   = 'patch:student-beasiswa {beasiswa? : Nama beasiswa yang akan di-assign ke semua student}';
    protected $description = 'Patch beasiswa_diampu on existing student users for testing';

    public function handle(): int
    {
        // List available paket
        $pakets = PaketBeasiswa::all();

        if ($pakets->isEmpty()) {
            $this->error('Tidak ada PaketBeasiswa di database. Jalankan seeder terlebih dahulu.');
            return self::FAILURE;
        }

        $this->info('Paket beasiswa yang tersedia:');
        foreach ($pakets as $i => $p) {
            $this->line("  [{$i}] {$p->nama_beasiswa}");
        }

        // Determine target beasiswa name
        $namaBeasiswa = $this->argument('beasiswa');
        if (! $namaBeasiswa) {
            $index        = $this->ask('Pilih index paket yang ingin di-assign ke semua student', 0);
            $namaBeasiswa = $pakets[(int) $index]->nama_beasiswa ?? $pakets->first()->nama_beasiswa;
        }

        $this->info("Akan assign \"{$namaBeasiswa}\" ke semua student yang belum punya beasiswa_diampu...");

        $students = User::where('role', UserRole::Student->value)->get();
        $updated  = 0;

        foreach ($students as $student) {
            $existing = is_array($student->beasiswa_diampu) ? $student->beasiswa_diampu : [];

            if (! in_array($namaBeasiswa, $existing)) {
                $existing[] = $namaBeasiswa;
                $student->beasiswa_diampu = $existing;
                $student->save();
                $updated++;
                $this->line("  ✓ {$student->name} → [{$namaBeasiswa}]");
            } else {
                $this->line("  — {$student->name} already has [{$namaBeasiswa}]");
            }
        }

        $this->info("Selesai! {$updated} student diupdate.");
        return self::SUCCESS;
    }
}
