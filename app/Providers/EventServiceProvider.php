<?php

namespace Raank\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

/**
 * @category providers
 * @package Raank\Providers
 * @subpackage EventServiceProvider
 * @version 1.0.0
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];
}
