<?php

namespace Raank\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\Console\Input\InputOption;

/**
 * @category commands
 * @package Raank\Console\Commands
 * @subpackage KeyGenerate
 * @version 1.0.0
 * 
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class KeyGenerate extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'key:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Set the application key";

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
        $key = $this->getRandomKey();
        $path = base_path('.env');

        if (\file_exists($path)) {
            $content = \file_get_contents($path);

            if (\is_string($content)) {
                \file_put_contents(
                    $path,
                    \str_replace(
                        'APP_KEY=',
                        \sprintf('APP_KEY=%s', $key),
                        $content
                    )
                );

                $this->info("Application APP_KEY set successfully.");
            }
        }
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function getRandomKey()
    {
        return Str::random(32);
    }
}