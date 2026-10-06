<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\LMS\Database\Seeders\LMSDatabaseSeeder;
use Modules\Roles\Database\Seeders\RolesDatabaseSeeder;
use Modules\User\Database\Seeders\UserDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            \Modules\Roles\Database\Seeders\RolesDatabaseSeeder::class,
            \Modules\User\Database\Seeders\UserDatabaseSeeder::class,
            \Modules\User\Database\Seeders\UserScopeSeeder::class,
            \Modules\Roles\Database\Seeders\RegionUserSeeder::class,
            \Modules\LMS\Database\Seeders\LMSDatabaseSeeder::class,
            \Modules\Faq\Database\Seeders\FaqDatabaseSeeder::class,
            \Modules\RTK\Database\Seeders\RTKDatabaseSeeder::class,
            \Modules\MasterData\Database\Seeders\MasterDataDatabaseSeeder::class,
        ]);
    }
}
