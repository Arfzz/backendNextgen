<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = 'pjblNextgen';

        Schema::connection($connection)->table('notifications', function (Blueprint $collection) {
            $collection->index('user_id');
            $collection->index('is_read');
            $collection->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::connection('pjblNextgen')->dropIfExists('notifications');
    }
};
