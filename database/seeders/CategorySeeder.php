<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Web Development'],
            ['name' => 'Mobile Development'],
            ['name' => 'Data Science'],
            ['name' => 'Machine Learning'],
            ['name' => 'DevOps'],
        ];

        foreach ($data as $category) {
            Category::create($category);
        }
    }
}
