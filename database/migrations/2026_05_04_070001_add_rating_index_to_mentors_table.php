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
            ->table('mentors', function (Blueprint $collection) {
                // Adding rating index for sorting and filtering
                $collection->index('rating');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)
            ->table('mentors', function (Blueprint $collection) {
                $collection->dropIndex(['rating']);
            });
    }
};
