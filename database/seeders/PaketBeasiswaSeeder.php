<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaketBeasiswa;

class PaketBeasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaketBeasiswa::truncate();

        $data = [
            [
                'nama_beasiswa' => 'Beasiswa Unggulan',
                'fase_checkpoint' => 4,
                'persyaratan' => 7,
                'deadline' => '2026-03-24',
                'harga' => 79000,
            ],
            [
                'nama_beasiswa' => 'Tanoto Scholarship',
                'fase_checkpoint' => 4,
                'persyaratan' => 7,
                'deadline' => '2026-03-24',
                'harga' => 79000,
            ],
            [
                'nama_beasiswa' => 'Beasiswa KIPK',
                'fase_checkpoint' => 4,
                'persyaratan' => 7,
                'deadline' => '2026-03-24',
                'harga' => 79000,
            ],
            [
                'nama_beasiswa' => 'Beasiswa Pertamina',
                'fase_checkpoint' => 4,
                'persyaratan' => 7,
                'deadline' => '2026-03-24',
                'harga' => 79000,
            ],
            [
                'nama_beasiswa' => 'BSI Scholarship Unggulan',
                'fase_checkpoint' => 4,
                'persyaratan' => 7,
                'deadline' => '2026-03-24',
                'harga' => 79000,
            ],
            [
                'nama_beasiswa' => 'BSI Scholarship Inspirasi',
                'fase_checkpoint' => 4,
                'persyaratan' => 7,
                'deadline' => '2026-03-24',
                'harga' => 79000,
            ],
        ];

        foreach ($data as $item) {
            PaketBeasiswa::create($item);
        }
    }
}
