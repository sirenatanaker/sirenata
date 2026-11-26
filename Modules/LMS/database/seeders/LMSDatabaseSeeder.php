<?php

namespace Modules\LMS\Database\Seeders;

use Illuminate\Database\Seeder;

class LMSDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CourseSeeder::class,
            CourseSectionSeeder::class,
            PostTestSeeder::class,
            LibrarySeeder::class, // <-- Tambahkan baris ini
        ]);
    }
}