<?php

namespace Database\Seeders;

use Raank\Models\V1\User;
use Illuminate\Database\Seeder;

/**
 * @category database
 * @package Raank\Database\Seeders
 * @subpackage DatabaseSeeder
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('APP_ENV') === 'local') {
            User::factory()
                ->count(20)
                ->create();
        }
    }
}
