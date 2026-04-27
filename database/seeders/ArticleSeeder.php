<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title'        => '5 Tips Menulis Esai Beasiswa yang Memenangkan Seleksi',
                'content'      => 'Esai beasiswa adalah kesempatan Anda untuk menunjukkan siapa diri Anda sesungguhnya kepada komite seleksi...',
                'published_at' => now()->subDays(1),
            ],
            [
                'title'        => 'Panduan Lengkap Mendaftar Beasiswa Unggulan Kemdikbud 2026',
                'content'      => 'Beasiswa Unggulan adalah program beasiswa dari Kemdikbud yang ditujukan untuk putra-putri terbaik Indonesia...',
                'published_at' => now()->subDays(3),
            ],
            [
                'title'        => 'Kesalahan Fatal yang Sering Dilakukan Pelamar Beasiswa LPDP',
                'content'      => 'Banyak pelamar yang gagal pada tahap seleksi awal karena kesalahan administratif yang sebenarnya bisa dihindari...',
                'published_at' => now()->subDays(5),
            ],
            [
                'title'        => 'Cara Mempersiapkan Diri untuk Sesi Wawancara Beasiswa',
                'content'      => 'Sesi wawancara seringkali menjadi tahap yang paling mendebarkan dalam proses seleksi beasiswa...',
                'published_at' => now()->subDays(7),
            ],
            [
                'title'        => 'Kisah Sukses: Dari Pelosok Daerah Meraih Beasiswa Prestasi',
                'content'      => 'Cerita inspiratif dari penerima beasiswa yang mampu membuktikan bahwa keterbatasan bukan penghalang...',
                'published_at' => now()->subDays(10),
            ],
        ];

        foreach ($articles as $a) {
            Article::create([
                'title'        => $a['title'],
                'slug'         => Str::slug($a['title']),
                'image_url'    => null,
                'content'      => $a['content'],
                'published_at' => $a['published_at'],
            ]);
        }

        $this->command->info('Articles seeded: 5 articles.');
    }
}
