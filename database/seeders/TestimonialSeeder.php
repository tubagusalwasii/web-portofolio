<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Testimonial;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       Testimonial::create([
    'project_id'  => 1,
    'client_name' => 'John Doe',
    'company'     => 'ABC Corp',
    'content'     => 'We are very satisfied with the professionalism and quality delivered.',
    'photo'       => 'testimonials/john-doe.jpg',
]);

Testimonial::create([
    'project_id'  => 2,
    'client_name' => 'Sarah Lee',
    'company'     => 'Coffee Hub',
    'content'     => 'They built exactly what we needed and exceeded expectations!',
    'photo'       => 'testimonials/sarah-lee.jpg',
]);
    }
}
