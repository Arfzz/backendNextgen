<?php

namespace Database\Seeders;

use App\Enums\ClassMemberStatus;
use App\Enums\SubmissionStatus;
use App\Models\Checkpoint;
use App\Models\ClassMember;
use App\Models\Document;
use App\Models\Kelas;
use App\Models\MentoringSession;
use App\Models\Package;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $mentor1  = User::where('email', 'mentor1@nalarin.id')->first();
        $mentor2  = User::where('email', 'mentor2@nalarin.id')->first();
        $students = User::where('role', 'student')->get();
        $package1 = Package::first();
        $package2 = Package::skip(1)->first();

        if (! $mentor1 || ! $package1) {
            $this->command->warn('Run UserSeeder and PackageSeeder first.');
            return;
        }

        // ── Class 1: Beasiswa Unggulan – Batch 1 ─────────────────────────
        $class1 = Kelas::create([
            'mentor_id'  => (string) $mentor1->_id,
            'package_id' => (string) $package1->_id,
            'name'       => 'Batch 1 – Beasiswa Unggulan 2026',
            'is_active'  => true,
        ]);

        // Enroll 3 students
        foreach ($students->take(3) as $student) {
            ClassMember::create([
                'class_id'            => (string) $class1->_id,
                'student_id'          => (string) $student->_id,
                'progress_percentage' => rand(20, 70),
                'fase_passed'         => rand(0, 2),
                'status'              => ClassMemberStatus::Ongoing->value,
                'joined_at'           => now()->subDays(rand(10, 30)),
            ]);
        }

        // Checkpoints
        $cp1 = Checkpoint::create(['class_id' => (string) $class1->_id, 'title' => 'Seleksi Administrasi', 'schedule_date' => now()->addDays(7),  'order_index' => 1]);
        $cp2 = Checkpoint::create(['class_id' => (string) $class1->_id, 'title' => 'Tes Tertulis',         'schedule_date' => now()->addDays(21), 'order_index' => 2]);
        $cp3 = Checkpoint::create(['class_id' => (string) $class1->_id, 'title' => 'Wawancara Final',      'schedule_date' => now()->addDays(42), 'order_index' => 3]);

        // Tasks
        $task1 = Task::create(['class_id' => (string) $class1->_id, 'mentor_id' => (string) $mentor1->_id, 'title' => 'Draft Esai Diri',          'description' => 'Tulis draft pertama esai tentang diri kamu.', 'deadline_date' => now()->addDays(5)]);
        $task2 = Task::create(['class_id' => (string) $class1->_id, 'mentor_id' => (string) $mentor1->_id, 'title' => 'Surat Rekomendasi',         'description' => 'Mintalah surat rekomendasi dari dosen. Upload scan PDF.', 'deadline_date' => now()->addDays(14)]);
        $task3 = Task::create(['class_id' => (string) $class1->_id, 'mentor_id' => (string) $mentor1->_id, 'title' => 'Proposal Studi',            'description' => 'Buat proposal studi minimal 5 halaman.', 'deadline_date' => now()->addDays(28)]);

        // Submit task1 for student[0]
        $student0 = $students->first();
        TaskSubmission::create([
            'task_id'      => (string) $task1->_id,
            'student_id'   => (string) $student0->_id,
            'file_url'     => 'http://localhost/storage/submissions/sample_esai_diri.pdf',
            'status'       => SubmissionStatus::Submitted->value,
            'submitted_at' => now()->subDay(),
        ]);

        // Mentoring sessions
        MentoringSession::create(['class_id' => (string) $class1->_id, 'title' => 'Mentoring #1 – Pengenalan Program', 'session_date' => now()->addDays(3),  'link' => 'https://meet.google.com/abc-defg-hij']);
        MentoringSession::create(['class_id' => (string) $class1->_id, 'title' => 'Mentoring #2 – Review Esai',        'session_date' => now()->addDays(17), 'link' => 'https://meet.google.com/klm-nopq-rst']);

        // Documents
        Document::create(['class_id' => (string) $class1->_id, 'title' => 'Template Esai Diri',        'file_url' => 'http://localhost/storage/documents/template_esai.pdf',   'uploaded_by' => (string) $mentor1->_id, 'uploaded_at' => now()->subDays(5)]);
        Document::create(['class_id' => (string) $class1->_id, 'title' => 'Contoh Proposal Studi 2025', 'file_url' => 'http://localhost/storage/documents/contoh_proposal.pdf', 'uploaded_by' => (string) $mentor1->_id, 'uploaded_at' => now()->subDays(3)]);

        $this->command->info("Class '{$class1->name}' seeded with checkpoints, tasks, mentoring, and documents.");

        // ── Class 2: Beasiswa Presiden – Batch 1 ─────────────────────────
        if ($mentor2 && $package2) {
            $class2 = Kelas::create([
                'mentor_id'  => (string) $mentor2->_id,
                'package_id' => (string) $package2->_id,
                'name'       => 'Batch 1 – Beasiswa Presiden RI 2026',
                'is_active'  => true,
            ]);

            foreach ($students->slice(3) as $student) {
                ClassMember::create([
                    'class_id'            => (string) $class2->_id,
                    'student_id'          => (string) $student->_id,
                    'progress_percentage' => rand(10, 50),
                    'fase_passed'         => 0,
                    'status'              => ClassMemberStatus::Ongoing->value,
                    'joined_at'           => now()->subDays(rand(5, 20)),
                ]);
            }

            Task::create(['class_id' => (string) $class2->_id, 'mentor_id' => (string) $mentor2->_id, 'title' => 'Curriculum Vitae Akademik', 'description' => 'Buat CV akademik Anda sesuai format yang disediakan.', 'deadline_date' => now()->addDays(10)]);
            MentoringSession::create(['class_id' => (string) $class2->_id, 'title' => 'Kick-off Mentoring', 'session_date' => now()->addDays(2), 'link' => 'https://zoom.us/j/1234567890']);

            $this->command->info("Class '{$class2->name}' seeded.");
        }
    }
}
