<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'     => 'Admin Nalarin',
            'email'    => 'admin@nalarin.id',
            'password' => Hash::make('password'),
            'role'     => UserRole::Admin->value,
        ]);

        // Mentor 1
        User::create([
            'name'            => 'Dr. Budi Santoso',
            'email'           => 'mentor1@nalarin.id',
            'password'        => Hash::make('password'),
            'role'            => UserRole::Mentor->value,
            'university'      => 'Universitas Indonesia',
            'rating_score'    => 4.8,
            'students_passed' => 142,
        ]);

        // Mentor 2
        User::create([
            'name'            => 'Siti Rahayu, M.Pd',
            'email'           => 'mentor2@nalarin.id',
            'password'        => Hash::make('password'),
            'role'            => UserRole::Mentor->value,
            'university'      => 'Institut Teknologi Bandung',
            'rating_score'    => 4.6,
            'students_passed' => 98,
        ]);

        // Students
        $students = [
            ['name' => 'Andi Pratama',   'email' => 'student1@nalarin.id', 'university' => 'Universitas Gadjah Mada'],
            ['name' => 'Dian Novita',    'email' => 'student2@nalarin.id', 'university' => 'Institut Pertanian Bogor'],
            ['name' => 'Rizki Maulana',  'email' => 'student3@nalarin.id', 'university' => 'Universitas Airlangga'],
            ['name' => 'Fatimah Zahra',  'email' => 'student4@nalarin.id', 'university' => 'Universitas Diponegoro'],
            ['name' => 'Kevin Hartono',  'email' => 'student5@nalarin.id', 'university' => 'ITS Surabaya'],
        ];

        foreach ($students as $s) {
            User::create([
                'name'       => $s['name'],
                'email'      => $s['email'],
                'password'   => Hash::make('password'),
                'role'       => UserRole::Student->value,
                'university' => $s['university'],
            ]);
        }

        $this->command->info('Users seeded: 1 admin, 2 mentors, 5 students.');
    }
}
