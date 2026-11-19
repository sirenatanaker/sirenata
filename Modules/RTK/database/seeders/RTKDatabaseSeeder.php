<?php

namespace Modules\RTK\Database\Seeders;

use Illuminate\Database\Seeder;

class RTKDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RTKNSeeder::class,
            RTKDProvinceSeeder::class,
            RTKDProvinceApprovedSeeder::class,
            RTKDRegencySeeder::class,
        ]);
    }
}
