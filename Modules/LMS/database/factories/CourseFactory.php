<?php

namespace Modules\LMS\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\LMS\Models\Course::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        
        $courseName = [
            'Perencanaan Tenaga Kerja Makro',
            'Perencanaan Tenaga Kerja Mikro',
            'Indeks Pembangunan Ketenagakerjaan',
            'Perencanaan Tenaga Kerja Makro (Advanced)',
            'Perencanaan Tenaga Kerja Mikro (Advanced)',
            'Indeks Pembangunan Ketenagakerjaan (Advanced)',
            'Perencanaan Tenaga Kerja Makro (Expert)',
            'Perencanaan Tenaga Kerja Mikro (Expert)',
            'Indeks Pembangunan Ketenagakerjaan (Expert)',
            'Perencanaan Tenaga Kerja Makro (Master)',
            'Perencanaan Tenaga Kerja Mikro (Master)',
            'Indeks Pembangunan Ketenagakerjaan (Master)',
            'Perencanaan Tenaga Kerja Makro (Grand Master)',
            'Perencanaan Tenaga Kerja Mikro (Grand Master)',
            'Indeks Pembangunan Ketenagakerjaan (Grand Master)',
        ];

        $name = $this->faker->unique()->randomElement($courseName);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'thumbnail' => $this->faker->imageUrl(640, 480, 'cats'),
            'description' => $this->faker->paragraph(10),
        ];
    }
}

