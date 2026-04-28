<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;


class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::create([
    'category_id' => 1, // Pastikan category dengan ID 1 ada
    'name'        => 'Company Profile Website',
    'description' => 'A clean and responsive company profile website.',
    'image'       => 'projects/company-profile.png',
    'url_link'    => 'https://example-company.com',
]);

Project::create([
    'category_id' => 1,
    'name'        => 'Online Store for Coffee Products',
    'description' => 'A Laravel-based e-commerce solution.',
    'image'       => 'projects/coffee-store.png',
    'url_link'    => 'https://coffee-store.com',
]);

    }
}
