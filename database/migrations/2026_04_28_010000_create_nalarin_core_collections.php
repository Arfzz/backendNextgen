<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations for Nalarin Mobile Core Collections.
     */
    public function up(): void
    {
        $connection = 'pjblNextgen';

        // 1. Users Indexes
        Schema::connection($connection)->table('users', function (Blueprint $collection) {
            $collection->unique('email');
            $collection->index('role');
        });

        // 2. Packages (Scholarships)
        Schema::connection($connection)->create('packages', function (Blueprint $collection) {
            $collection->index('deadline_date');
        });

        // 3. Classes
        Schema::connection($connection)->create('classes', function (Blueprint $collection) {
            $collection->index('mentor_id');
            $collection->index('package_id');
            $collection->index('is_active');
        });

        // 4. Class Members (Enrollments)
        Schema::connection($connection)->create('class_members', function (Blueprint $collection) {
            $collection->index('class_id');
            $collection->index('student_id');
            $collection->index(['class_id', 'student_id']); // Compound index
        });

        // 5. Tasks
        Schema::connection($connection)->create('tasks', function (Blueprint $collection) {
            $collection->index('class_id');
            $collection->index('mentor_id');
            $collection->index('deadline_date');
        });

        // 6. Task Submissions
        Schema::connection($connection)->create('task_submissions', function (Blueprint $collection) {
            $collection->index('task_id');
            $collection->index('student_id');
            $collection->index(['task_id', 'student_id']);
        });

        // 7. Checkpoints
        Schema::connection($connection)->create('checkpoints', function (Blueprint $collection) {
            $collection->index('class_id');
            $collection->index('order_index');
        });

        // 8. Student Checkpoints
        Schema::connection($connection)->create('student_checkpoints', function (Blueprint $collection) {
            $collection->index('student_id');
            $collection->index('checkpoint_id');
        });

        // 9. Mentoring Sessions
        Schema::connection($connection)->create('mentoring_sessions', function (Blueprint $collection) {
            $collection->index('class_id');
            $collection->index('session_date');
        });

        // 10. Documents
        Schema::connection($connection)->create('documents', function (Blueprint $collection) {
            $collection->index('class_id');
        });

        // 11. Articles (News)
        Schema::connection($connection)->create('articles', function (Blueprint $collection) {
            $collection->unique('slug');
            $collection->index('published_at');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = 'pjblNextgen';

        Schema::connection($connection)->dropIfExists('packages');
        Schema::connection($connection)->dropIfExists('classes');
        Schema::connection($connection)->dropIfExists('class_members');
        Schema::connection($connection)->dropIfExists('tasks');
        Schema::connection($connection)->dropIfExists('task_submissions');
        Schema::connection($connection)->dropIfExists('checkpoints');
        Schema::connection($connection)->dropIfExists('student_checkpoints');
        Schema::connection($connection)->dropIfExists('mentoring_sessions');
        Schema::connection($connection)->dropIfExists('documents');
        Schema::connection($connection)->dropIfExists('articles');

    // Note: We don't drop 'users' or 'personal_access_tokens' 
    // to avoid losing all account data on rollback of this specific Nalarin migration.
    }
};
