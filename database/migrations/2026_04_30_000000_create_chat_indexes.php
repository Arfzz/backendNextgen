<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = 'pjblNextgen';

        Schema::connection($connection)->table('conversations', function (Blueprint $collection) {
            $collection->index('student_id');
            $collection->index('mentor_id');
            $collection->index('last_message_at');
            $collection->index(['student_id', 'mentor_id']);
        });

        Schema::connection($connection)->table('messages', function (Blueprint $collection) {
            $collection->index('conversation_id');
            $collection->index('sender_id');
            $collection->index('created_at');
        });
    }

    public function down(): void
    {
        $connection = 'pjblNextgen';
        Schema::connection($connection)->dropIfExists('conversations');
        Schema::connection($connection)->dropIfExists('messages');
    }
};
