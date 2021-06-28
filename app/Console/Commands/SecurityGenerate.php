<?php

namespace Raank\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\Console\Input\InputOption;

/**
 * @category commands
 * @package Raank\Console\Commands
 * @subpackage SecurityGenerate
 * @version 1.0.0
 * 
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class SecurityGenerate extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'security:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Set the security environments";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info("\nTo use inter-application security, set this token in application headers.");
        $this->warn(
            \sprintf(
                "X-App-Token: %s\n",
                Crypt::encrypt(
                    env('APP_TOKEN')
                )
            )
        );
    }
}