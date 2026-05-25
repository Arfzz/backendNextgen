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
                // Menambahkan index unik untuk username dan email agar login lebih aman dan cepat
                $collection->unique('username');
                $collection->unique('email');
                
                // Field lainnya akan otomatis tersimpan saat create/update karena MongoDB schema-less,
                // tapi kita definisikan di sini untuk dokumentasi.
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)
            ->table('mentors', function (Blueprint $collection) {
                $collection->dropUnique(['username']);
                $collection->dropUnique(['email']);
            });
    }
};
