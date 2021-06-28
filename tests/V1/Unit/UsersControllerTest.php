<?php

namespace Raank\Tests\V1\Unit;

use Carbon\Carbon;
use Raank\Models\V1\User;
use Raank\Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Laravel\Lumen\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Raank\Repositories\V1\UserRepository;
use Raank\Http\Controllers\V1\UsersController;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @category tests
 * @package Raank\Tests
 * @package V1\Unit\UsersControllerTest
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class UsersControllerTest extends TestCase
{
    /**
     * Testing index method.
     *
     * @return void
     */
    public function testIndexMethod()
    {
        /** Create a fake user */
        User::factory()->create();

        /** Make a fake request */
        $request = Request::create('/api/v1/users', 'GET');

        /**
         * Instance Controller.
         *
         * @var UsersController $controller
         */
        $controller = (new UsersController(
            (new UserRepository())
        ));

        /** Getting index method */
        $index = $controller->index($request);

        /** Assertions to expect */
        $this->assertTrue($index instanceof JsonResponse);
        $this->assertEquals(Response::HTTP_OK, $index->getStatusCode());
    }

    /**
     * Testing store method.
     *
     * @return mixed
     */
    public function testStoreMethod()
    {
        /** Making a fake data to create */
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'document' => \sprintf(
                '%s%s%s%s',
                rand(100, 999),
                rand(100, 999),
                rand(100, 999),
                rand(10, 99)
            ),

            'password' => '123456789',
            'password_confirmation' => '123456789'
        ];

        /** Make a fake request */
        $request = Request::create('/api/v1/users', 'POST', $data);

        /**
         * Instance Controller.
         *
         * @var UsersController $controller
         */
        $controller = (new UsersController(
            (new UserRepository())
        ));

        /** Getting store method */
        $store = $controller->store($request);

        /** Assertions to expect */
        $this->assertTrue($store instanceof JsonResponse);
        $this->assertEquals(Response::HTTP_OK, $store->getStatusCode());

        return \json_decode((string) $store->getContent(), true);
    }

    /**
     * Testing searching method.
     *
     * @depends testStoreMethod
     *
     * @return void
     */
    public function testSearchMethod(...$args)
    {
        /** Getting data received from testStoreMethod */
        $data = Arr::first($args);

        /** Making a request with query builder dynamic */
        $requestParams = [
            'where' => [
                ['name', 'LIKE', Arr::get($data, 'data.name')],
            ],

            'whereNotNull' => ['email'],
            'whereNull' => ['deleted_at'],
            'orderBy' => [
                ['field' => 'created_at', 'order' => 'DESC'],
            ],

            'whereBetween' => [
                [
                    'created_at' => [
                        Carbon::now()->startOfDay()->format('Y-m-d H:i:s'),
                        Carbon::now()->endOfDay()->format('Y-m-d H:i:s')
                    ]
                ]
            ]
        ];
        
        /** Making a fake request */
        $request = Request::create(
            '/api/v1/files/search',
            'POST',
            $requestParams
        );

        /**
         * Instance a controller
         *
         * @var UsersController $controller
         */
        $controller = (new UsersController(
            (new UserRepository())
        ));

        /** Getting search method of controller */
        $search = $controller->search($request);

        /** Assertions to expect */
        $this->assertTrue($search instanceof JsonResponse);
        $this->assertEquals(Response::HTTP_OK, $search->getStatusCode());
    }

    /**
     * Testing show method.
     *
     * @depends testStoreMethod
     */
    public function testShowMethod(...$args)
    {
        /** Getting id received from testStoreMethod */
        $userId = Arr::get(
            Arr::first($args),
            'data.id'
        );

        /**
         * Instance Controller.
         *
         * @var UsersController $controller
         */
        $controller = (new UsersController(
            (new UserRepository())
        ));

        /** Getting show method */
        $show = $controller->show($userId);

        /** Assertions to expect */
        $this->assertTrue($show instanceof JsonResponse);
        $this->assertEquals(Response::HTTP_OK, $show->getStatusCode());
    }
    
    /**
     * Testing update method.
     *
     * @depends testStoreMethod
     *
     * @return mixed
     */
    public function testUpdateMethod(...$args)
    {
        /** Getting id received from testStoreMethod */
        $userId = Arr::get(
            Arr::first($args),
            'data.id'
        );

        /** Make a new data to Update */
        $data = [
            'name' => $this->faker->name,
        ];

        /** Making a fake request */
        $request = Request::create(
            '/api/v1/users/' . $userId,
            'PUT',
            $data
        );

        /**
         * Instance Controller.
         *
         * @var UsersController $controller
         */
        $controller = (new UsersController(
            (new UserRepository())
        ));

        /** Getting update method */
        $update = $controller->update($request, $userId);

        /** Assertions to expect */
        $this->assertTrue($update instanceof JsonResponse);
        $this->assertEquals(Response::HTTP_OK, $update->getStatusCode());

        return \json_decode((string) $update->getContent(), true);
    }
    
    /**
     * Testing update method.
     *
     * @depends testStoreMethod
     *
     * @return void
     */
    public function testDeleteMethod(...$args)
    {
        /** Getting id received from testStoreMethod */
        $userId = Arr::get(
            Arr::first($args),
            'data.id'
        );

        /**
         * Instance Controller.
         *
         * @var UsersController $controller
         */
        $controller = (new UsersController(
            (new UserRepository())
        ));

        /** Getting destroy method */
        $destroy = $controller->destroy($userId);

        /** Assertions to expect */
        $this->assertTrue($destroy instanceof JsonResponse);
        $this->assertEquals(Response::HTTP_OK, $destroy->getStatusCode());
    }
}
