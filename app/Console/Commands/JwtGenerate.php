<?php

namespace Raank\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\Console\Input\InputOption;

/**
 * @category commands
 * @package Raank\Console\Commands
 * @subpackage JwtGenerate
 * @version 1.0.0
 * 
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class JwtGenerate extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'jwt:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Set the jwt secret key";

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
        $jwt = $this->getRandomKey(64);
        $path = base_path('.env');

        if (\file_exists($path)) {
            $content = \file_get_contents($path);

            if (\is_string($content)) {
                \file_put_contents(
                    $path,
                    \str_replace(
                        'JWT_SECRET=',
                        \sprintf('JWT_SECRET=%s', $jwt),
                        $content
                    )
                );

                $this->info("Application JWT_SECRET set successfully.");
            }
        }
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function getRandomKey(int $length = 32)
    {
        return Str::random($length);
    }
}