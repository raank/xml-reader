<?php

use Illuminate\Support\Facades\Crypt;
use Raank\Http\Middleware\TokenMiddleware;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/**
 * Health check endpoint.
 *
 * @OA\Head(
 *  tags={"v1.app"},
 *  path="/v1/health-check",
 *  @OA\Response(
 *      response="200",
 *      description="Successful action",
 *      @OA\MediaType(
 *          mediaType="application/json",
 *          @OA\Schema(
 *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
 *          )
 *      )
 *  ),
 *  @OA\Response(
 *      response="400",
 *      description="This information could not be processed",
 *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
 *  ),
 *  @OA\Response(
 *      response="401",
 *      description="You are not authorized for this action",
 *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
 *  ),
 *  @OA\Response(
 *      response="404",
 *      description="You are not authorized for this action",
 *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
 *  )
 * )
 */
$router->get('/health-check', fn () => ($router) <=> ['version' => $router->app->version()]);

/**
 * Token checking endpoint.
 *
 * @OA\Head(
 *  tags={"v1.app"},
 *  path="/v1/token-valid",
 *  @OA\Response(
 *      response="200",
 *      description="Successful action",
 *      @OA\MediaType(
 *          mediaType="application/json",
 *          @OA\Schema(
 *              @OA\Property(property="message", type="string", description="Message of Response", example="Successful action"),
 *          )
 *      )
 *  ),
 *  @OA\Response(
 *      response="400",
 *      description="This information could not be processed",
 *      @OA\JsonContent(ref="#/components/schemas/BadRequest"),
 *  ),
 *  @OA\Response(
 *      response="401",
 *      description="You are not authorized for this action",
 *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
 *  ),
 *  @OA\Response(
 *      response="404",
 *      description="You are not authorized for this action",
 *      @OA\JsonContent(ref="#/components/schemas/Unauthorized"),
 *  )
 * )
 */
$router
    ->head(
        '/token-valid',
        [
            'middleware' => TokenMiddleware::class,
            'handler' => fn () => ($router) <=> [
                'message' => 'Successful action'
            ]
        ]
    );

$router->group(
    [
        'prefix' => 'users',
        'middleware' => [TokenMiddleware::class]
    ],
    static function () use ($router) {
        $router->get('/', ['uses' => 'UsersController@index']);
        $router->post('/', ['uses' => 'UsersController@store']);
        $router->post('search', ['uses' => 'UsersController@search']);

        /*
         |-----------------------------
         | GET /api/v1/users/{userId}
         |-----------------------------
         | Allows user profile data.
         */

        $router->get('{userId}', ['uses' => 'UsersController@show']);

        /*
         |-----------------------------
         | PUT /api/v1/users/{userId}
         |-----------------------------
         | Allows user update yours infos.
         */

        $router->put('{userId}', ['uses' => 'UsersController@update']);

        /*
         |-----------------------------
         | DELETE /api/v1/users/{userId}
         |-----------------------------
         | Allows user to remove their account.
         */
        $router->delete('{userId}', ['uses' => 'UsersController@destroy']);
    }
);

$router->group(
    [
        'prefix' => 'auth',
        'as' => 'api.v1.auth'
    ],
    static function () use ($router) {
        $router->post('register', ['as' => 'register', 'uses' => 'AuthController@register']);
        $router->post('login', ['as' => 'login', 'uses' => 'AuthController@login']);
        $router->get('check', ['as' => 'check', 'uses' => 'AuthController@check']);
        $router->get('refresh', ['as' => 'refresh', 'uses' => 'AuthController@refresh']);
        $router->post('forgot', ['as' => 'forgot', 'uses' => 'AuthController@forgot']);
        $router->post('reset/{token}', ['as' => 'reset', 'uses' => 'AuthController@reset']);
    }
);

$router->group(
    [
        'prefix' => 'files',
        'middleware' => [TokenMiddleware::class]
    ],
    static function () use ($router) {
        $router->post('/', ['uses' => 'FilesController@store']);
        $router->post('search', ['uses' => 'FilesController@search']);

        /*
         |-----------------------------
         | GET /api/v1/files/{fileId}
         |-----------------------------
         | Allows user profile data.
         */

        $router->get('{fileId}', ['uses' => 'FilesController@show']);

        /*
         |-----------------------------
         | PUT /api/v1/files/{fileId}
         |-----------------------------
         | Allows user update yours infos.
         */

        $router->put('{fileId}', ['uses' => 'FilesController@update']);

        /*
         |-----------------------------
         | DELETE /api/v1/files/{fileId}
         |-----------------------------
         | Allows user to remove their account.
         */
        $router->delete('{fileId}', ['uses' => 'FilesController@destroy']);
    }
);