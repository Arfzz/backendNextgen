<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'title'         => 'Beasiswa Unggulan',
                'deadline_date' => '2026-08-31',
                'price'         => 299000,
                'old_price'     => 499000,
                'features'      => [
                    'Mentoring 1-on-1 intensif',
                    'Akses materi eksklusif',
                    '3 fase checkpoint',
                    'Review esai pribadi',
                    'Simulasi wawancara',
                ],
                'cover_image' => null,
            ],
            [
                'title'         => 'Beasiswa Presiden RI',
                'deadline_date' => '2026-09-30',
                'price'         => 499000,
                'old_price'     => 749000,
                'features'      => [
                    'Pendampingan berkas lengkap',
                    'Mentoring group & private',
                    '5 fase checkpoint',
                    'Konsultasi LPDP specialist',
                    'Akses komunitas alumni',
                    'Sertifikat kelulusan',
                ],
                'cover_image' => null,
            ],
            [
                'title'         => 'Beasiswa Daerah Khusus',
                'deadline_date' => '2026-07-15',
                'price'         => 149000,
                'old_price'     => 249000,
                'features'      => [
                    'Group mentoring mingguan',
                    'Template berkas siap pakai',
                    '2 fase checkpoint',
                ],
                'cover_image' => null,
            ],
        ];

        foreach ($packages as $p) {
            Package::create($p);
        }

        $this->command->info('Packages seeded: 3 packages.');
    }
}
