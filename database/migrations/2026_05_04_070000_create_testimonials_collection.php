<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pjblNextgen';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)
            ->create('testimonials', function (Blueprint $collection) {
                // Indexes for performance
                $collection->index('user_id');
                $collection->index('mentor_id');
                $collection->index('status');
                $collection->index('show_mobile');
                $collection->index('show_web');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)
            ->dropIfExists('testimonials');
    }
};
