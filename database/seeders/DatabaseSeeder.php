<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Order matters — Users and Packages must exist before Classes.
     */
    public function run(): void
    {
        // Bersihkan data lama agar tidak terjadi bentrok Duplicate Key saat db:seed dijalankan ulang
        \App\Models\User::truncate();
        \App\Models\Package::truncate();
        \App\Models\Article::truncate();
        \App\Models\Kelas::truncate();
        \App\Models\ClassMember::truncate();
        \App\Models\Task::truncate();
        \App\Models\TaskSubmission::truncate();
        \App\Models\Checkpoint::truncate();
        \App\Models\StudentCheckpoint::truncate();
        \App\Models\MentoringSession::truncate();
        \App\Models\Document::truncate();
        \App\Models\PersonalAccessToken::truncate();

        $this->call([
            UserSeeder::class,
            PackageSeeder::class,
            ArticleSeeder::class,
            ClassSeeder::class,
        ]);
    }
}

