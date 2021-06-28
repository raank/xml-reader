<?php

namespace Raank\Console;

use Raank\Console\Commands\JwtGenerate;
use Raank\Console\Commands\KeyGenerate;
use Raank\Console\Commands\TokenGenerate;
use Raank\Console\Commands\SecurityGenerate;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        KeyGenerate::class,
        TokenGenerate::class,
        JwtGenerate::class,
        SecurityGenerate::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(
            static fn () => Log::info('Health Check')
        )->dailyAt('01:00');
    }
}
