<?php

namespace Raank\Providers;

use Raank\Models\V1\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Raank\Http\Middleware\CorsMiddleware;
use Illuminate\Support\ServiceProvider;

/**
 * @category providers
 * @package Raank\Providers
 * @subpackage RouteServiceProvider
 * @version 1.0.0
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'api' => [
            'throttle:60,1'
        ],
    ];

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        app('router')
            ->group(
                [
                    'namespace' => 'Raank\Http\Controllers',
                    'prefix' => 'api',
                    'middleware' => [CorsMiddleware::class]
                ],
                static function ($router) {
                    $router->group(
                        [
                            'namespace' => 'V1',
                            'prefix' => '/v1'
                        ],
                        fn ($router) => require __DIR__ . '/../../routes/v1.php'
                    );
                }
            );
    }
}
