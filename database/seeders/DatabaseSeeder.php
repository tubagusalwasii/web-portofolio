<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Category;
use App\Models\User;
use App\Models\Experience;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Admin User
        User::updateOrCreate(
            ['email' => 'tubagusalwasiii@gmail.com'],
            [
                'name' => 'Admin Bagus',
                'password' => Hash::make('password'),
            ]
        );

        // 1. Categories
        $mobileCat = Category::firstOrCreate(['name' => 'Mobile Development']);
        $aiCat = Category::firstOrCreate(['name' => 'Machine Learning']);
        $uiuxCat = Category::firstOrCreate(['name' => 'UI/UX Design']);

        // 2. Projects (Hapus dulu agar tidak duplikat saat di-seed ulang)
        DB::table('projects')->truncate();
        DB::table('projects')->insert([
            [
                'category_id' => $mobileCat->id,
                'name' => 'APP Gotani',
                'description' => 'Aplikasi android yang menggunakan bahasa kotlin. Aplikasi ini berisi marketplace sayur.',
                'image' => 'assets/projek/projek1.png',
                'url_link' => 'https://github.com/budichyn2003/capstone.git',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $aiCat->id,
                'name' => 'Deteksi Aksara Jawa',
                'description' => 'Program machine learning ini memprediksi huruf aksara jawa menggunakan gambar python.',
                'image' => 'assets/projek/projek2.png',
                'url_link' => 'https://github.com/tubagusalwasii/Pengenalan-Pola-Aksara-Jawa-Menggunakan-CNN.git',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 3. Certificates
        DB::table('certificates')->truncate();
        $certs = [];
        for ($i = 1; $i <= 13; $i++) {
            $certs[] = [
                'title' => "Sertifikat $i",
                'image' => "assets/sertifikat/sertifikat$i.png",
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('certificates')->insert($certs);

        // 4. Experiences
        DB::table('experiences')->truncate();
        DB::table('experiences')->insert([
            [
                'title' => 'Android Developer (Bangkit Academy)',
                'company' => 'Google, GoTo, Traveloka',
                'start_date' => '2023-02-01',
                'end_date' => '2023-07-01',
                'is_current' => false,
                'description' => 'Mengembangkan aplikasi android menggunakan Kotlin dan integrasi API.',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Mahasiswa Teknik Informatika',
                'company' => 'Universitas Islam Sultan Agung',
                'start_date' => '2020-09-01',
                'end_date' => null,
                'is_current' => true,
                'description' => 'Fokus pada pengembangan software, UI/UX, dan machine learning.',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 5. Site Settings
        DB::table('site_settings')->truncate();
        DB::table('site_settings')->insert([
            [
                'hero_title' => "Tubagus Alwasi'i",
                'hero_typing' => json_encode(["UI/UX Designer", "Mobile Developer", "Machine Learning Enthusiast"]),
                'about_description' => "Saya Tubagus Alwasi’i Mahasiswa Semester 8, Teknik Informatika, Universitas Islam Sultan Agung Semarang. Saya memiliki minat besar di bidang teknologi informasi, khususnya dalam menciptakan solusi inovatif melalui pengembangan aplikasi. Saya memiliki pengalaman sebagai Android Developer di program Bangkit pada tahun 2023. Saya senang belajar hal baru, berkolaborasi dalam tim, dan berbagi pengetahuan untuk menghasilkan produk yang impactful. Saya berfokus pada pengembangan aplikasi web, mobile, ui/ux dan machine learning.",
                'cv_link' => 'assets/TubagusAlwasiCV.pdf',
                'hero_image' => 'assets/profil2.jpeg',
                'about_image' => 'assets/profil.jpeg',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
