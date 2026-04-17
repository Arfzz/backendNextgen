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
            ->create('tests', function (Blueprint $collection) {
                $collection->index('name');
                $collection->index('email');
                $collection->index('created_at');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)
            ->dropIfExists('tests');
    }
};
