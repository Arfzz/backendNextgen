<?php

namespace Database\Seeders;

use App\Enums\ClassMemberStatus;
use App\Enums\UserRole;
use App\Models\ClassMember;
use App\Models\Kelas;
use App\Models\Mentor;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        // ── Bersihkan data lama ─────────────────────────────────────────
        Testimonial::truncate();

        // ── 1. Buat / ambil Mentor dari tabel mentors (CMS) ────────────
        $mentor1 = Mentor::firstOrCreate(
            ['nama_mentor' => 'Dr. Budi Santoso'],
            [
                'pendidikan'      => 'S3 Ilmu Komputer, Universitas Indonesia',
                'awardee'         => ['LPDP 2018', 'Beasiswa Unggulan 2016'],
                'beasiswa_diampu' => ['Beasiswa Unggulan', 'Beasiswa Presiden'],
                'username'        => 'budi.santoso',
                'email'           => 'budi.santoso@nextgen.id',
                'rating'          => 5.0,
            ]
        );

        $mentor2 = Mentor::firstOrCreate(
            ['nama_mentor' => 'Siti Rahayu, M.Pd'],
            [
                'pendidikan'      => 'S2 Pendidikan, Institut Teknologi Bandung',
                'awardee'         => ['LPDP 2020', 'Fulbright 2019'],
                'beasiswa_diampu' => ['Beasiswa Presiden', 'LPDP'],
                'username'        => 'siti.rahayu',
                'email'           => 'siti.rahayu@nextgen.id',
                'rating'          => 5.0,
            ]
        );

        $this->command->info("✅ Mentors (CMS): {$mentor1->nama_mentor}, {$mentor2->nama_mentor}");

        // ── 2. Buat Peserta (User dengan role student) ─────────────────
        $pesertaData = [
            ['name' => 'Andi Pratama',  'email' => 'andi@peserta.id',   'university' => 'Universitas Gadjah Mada'],
            ['name' => 'Dian Novita',   'email' => 'dian@peserta.id',   'university' => 'Institut Pertanian Bogor'],
            ['name' => 'Rizki Maulana', 'email' => 'rizki@peserta.id',  'university' => 'Universitas Airlangga'],
            ['name' => 'Fatimah Zahra', 'email' => 'fatimah@peserta.id','university' => 'Universitas Diponegoro'],
            ['name' => 'Kevin Hartono', 'email' => 'kevin@peserta.id',  'university' => 'ITS Surabaya'],
        ];

        $peserta = [];
        foreach ($pesertaData as $p) {
            $peserta[] = User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'name'       => $p['name'],
                    'password'   => Hash::make('password123'),
                    'role'       => UserRole::Student->value,
                    'university' => $p['university'],
                ]
            );
        }

        $this->command->info('✅ Peserta: ' . count($peserta) . ' user student dibuat/ditemukan.');

        // ── 3. Buat Kelas yang menghubungkan Mentor → Kelas ───────────
        // Perhatian: mentor_id di Kelas mengacu ke _id dari koleksi mentors (CMS),
        // bukan dari users — sesuai dengan flow TestimonialFormController.
        $kelas1 = Kelas::firstOrCreate(
            ['name' => 'Batch 2 – Beasiswa Unggulan 2026'],
            [
                'mentor_id'  => (string) $mentor1->_id,
                'package_id' => '',
                'is_active'  => true,
            ]
        );

        $kelas2 = Kelas::firstOrCreate(
            ['name' => 'Batch 1 – Beasiswa Presiden RI 2026'],
            [
                'mentor_id'  => (string) $mentor2->_id,
                'package_id' => '',
                'is_active'  => true,
            ]
        );

        $this->command->info("✅ Kelas: '{$kelas1->name}', '{$kelas2->name}'");

        // ── 4. Daftarkan Peserta ke Kelas (ClassMember) ────────────────
        // Peserta 1–3 → kelas1 (mentor Budi)
        foreach (array_slice($peserta, 0, 3) as $p) {
            ClassMember::firstOrCreate(
                [
                    'class_id'   => (string) $kelas1->_id,
                    'student_id' => (string) $p->_id,
                ],
                [
                    'progress_percentage' => rand(20, 80),
                    'fase_passed'         => rand(0, 2),
                    'status'              => ClassMemberStatus::Ongoing->value,
                    'joined_at'           => now()->subDays(rand(10, 60)),
                ]
            );
        }

        // Peserta 4–5 → kelas2 (mentor Siti)
        foreach (array_slice($peserta, 3) as $p) {
            ClassMember::firstOrCreate(
                [
                    'class_id'   => (string) $kelas2->_id,
                    'student_id' => (string) $p->_id,
                ],
                [
                    'progress_percentage' => rand(10, 50),
                    'fase_passed'         => 0,
                    'status'              => ClassMemberStatus::Ongoing->value,
                    'joined_at'           => now()->subDays(rand(5, 30)),
                ]
            );
        }

        // Peserta 1 juga terdaftar di kelas2 (untuk menguji multiple-class flow)
        ClassMember::firstOrCreate(
            [
                'class_id'   => (string) $kelas2->_id,
                'student_id' => (string) $peserta[0]->_id,
            ],
            [
                'progress_percentage' => 35,
                'fase_passed'         => 0,
                'status'              => ClassMemberStatus::Ongoing->value,
                'joined_at'           => now()->subDays(15),
            ]
        );

        $this->command->info('✅ ClassMember: peserta terhubung ke kelas masing-masing.');

        // ── 5. Contoh Testimoni (optional, sudah approved) ─────────────
        $sampleTestimonials = [
            [
                'user_id'   => (string) $peserta[1]->_id, // Dian
                'mentor_id' => (string) $mentor1->_id,
                'rating'    => 5.0,
                'content'   => 'Pak Budi sangat sabar dan detail dalam membimbing. Berkat bimbingannya esai saya jauh lebih baik dan akhirnya lolos seleksi!',
                'status'    => 'is_approved',
                'show_web'  => true,
                'show_mobile' => true,
            ],
            [
                'user_id'   => (string) $peserta[2]->_id, // Rizki
                'mentor_id' => (string) $mentor1->_id,
                'rating'    => 4.0,
                'content'   => 'Mentor yang responsif dan selalu memberikan feedback konstruktif. Sangat membantu persiapan beasiswa saya.',
                'status'    => 'is_approved',
                'show_web'  => true,
                'show_mobile' => false,
            ],
            [
                'user_id'   => (string) $peserta[3]->_id, // Fatimah
                'mentor_id' => (string) $mentor2->_id,
                'rating'    => 5.0,
                'content'   => 'Bu Siti luar biasa! Penjelasannya mudah dipahami dan selalu mendukung kami untuk tidak menyerah.',
                'status'    => 'is_approved',
                'show_web'  => true,
                'show_mobile' => true,
            ],
            [
                'user_id'   => (string) $peserta[4]->_id, // Kevin
                'mentor_id' => (string) $mentor2->_id,
                'rating'    => 4.0,
                'content'   => 'Bimbingan yang terstruktur dan materi yang lengkap. Sangat puas dengan program ini.',
                'status'    => 'pending',
                'show_web'  => false,
                'show_mobile' => false,
            ],
        ];

        foreach ($sampleTestimonials as $t) {
            Testimonial::firstOrCreate(
                ['user_id' => $t['user_id'], 'mentor_id' => $t['mentor_id']],
                $t
            );
        }

        // Recalculate ratings for both mentors
        $this->recalcRating($mentor1);
        $this->recalcRating($mentor2);

        $this->command->info('✅ Sample testimonials seeded dan rating mentor diperbarui.');
        $this->command->newLine();
        $this->command->line('─────────────────────────────────────────────────');
        $this->command->line('🔑 Akun Peserta untuk Testing Form Testimoni:');
        $this->command->line('─────────────────────────────────────────────────');
        $this->command->table(
            ['Nama', 'Email', 'Password', 'Kelas'],
            [
                ['Andi Pratama',  'andi@peserta.id',    'password123', 'Kelas 1 & 2 (multi-class)'],
                ['Dian Novita',   'dian@peserta.id',    'password123', 'Kelas 1 – Dr. Budi Santoso'],
                ['Rizki Maulana', 'rizki@peserta.id',   'password123', 'Kelas 1 – Dr. Budi Santoso'],
                ['Fatimah Zahra', 'fatimah@peserta.id', 'password123', 'Kelas 2 – Siti Rahayu, M.Pd'],
                ['Kevin Hartono', 'kevin@peserta.id',   'password123', 'Kelas 2 – Siti Rahayu, M.Pd'],
            ]
        );
        $this->command->line('─────────────────────────────────────────────────');
        $this->command->line('📌 Andi@peserta.id terdaftar di 2 kelas → akan muncul pilihan mentor.');
    }

    private function recalcRating(Mentor $mentor): void
    {
        $approved = Testimonial::where('mentor_id', (string) $mentor->_id)
            ->where('status', 'is_approved')
            ->get();

        $mentor->rating = $approved->count() > 0
            ? round($approved->avg('rating'), 1)
            : 5.0;

        $mentor->save();
    }
}
