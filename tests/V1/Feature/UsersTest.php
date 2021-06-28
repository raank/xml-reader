<?php

namespace Raank\Tests\V1\Api;

use Carbon\Carbon;
use Faker\Factory;
use Raank\Models\V1\User;
use Raank\Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @category tests
 * @package Raank\Tests
 * @package V1\Api\UsersTest
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class UsersTest extends TestCase
{
    /**
     * Testing index route with pagination.
     *
     * @return void
     */
    public function testIndex()
    {
        $this
            ->json(
                'GET',
                '/api/v1/users',
                [],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => __('Success'),
                'current_page' => 1
            ])
            ->seeStatusCode(Response::HTTP_OK);
    }

    /**
     * Testing storing user route.
     *
     * @return array|null
     */
    public function testStore(): ?array
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'document' => \sprintf(
                '%s%s%s%s',
                rand(100, 999),
                rand(100, 999),
                rand(100, 999),
                rand(10, 99)
            ),
            'password' => '12345',
            'password_confirmation' => '12345'
        ];

        $this
            ->json(
                'POST',
                '/api/v1/users',
                $data,
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => __('Success'),
            ])
            ->seeStatusCode(Response::HTTP_OK);

        return $this->getContent(
            $this->response
        );
    }

    /**
     * Testing search route with pagination.
     *
     * @depends testStore
     *
     * @return void
     */
    public function testSearch(...$args)
    {
        $data = Arr::first($args);
        $itemName = Arr::get($data, 'data.name');

        $this
            ->json(
                'POST',
                '/api/v1/users/search',
                [
                    'where' => [
                        ['name', 'LIKE', $itemName]
                    ],
                    
                    'whereNotNull' => ['email'],
                    'whereNull' => ['deleted_at'],
                    'orderBy' => [
                        ['field' => 'created_at', 'order' => 'DESC']
                    ],

                    'whereBetween' => [
                        [
                            'created_at' => [
                                Carbon::now()->startOfDay()->format('Y-m-d H:i:s'),
                                Carbon::now()->endOfDay()->format('Y-m-d H:i:s')
                            ]
                        ]
                    ]
                ],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => __('Success'),
                'current_page' => 1,
            ])
            ->seeStatusCode(Response::HTTP_OK);

        return $this->getContent(
            $this->response
        );
    }

    /**
     * Testing show user route.
     *
     * @depends testStore
     *
     * @return array
     */
    public function testShow(...$args)
    {
        $data = Arr::first($args);
        $itemId = Arr::get($data, 'data.id');

        $this
            ->json(
                'GET',
                '/api/v1/users/' . $itemId,
                [],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => __('Success')
            ])
            ->seeStatusCode(Response::HTTP_OK);
    
        return $this->getContent(
            $this->response
        );
    }

    /**
     * Testing show user error route.
     *
     * @return void
     */
    public function testShowNotFound()
    {
        $this
            ->json(
                'GET',
                '/api/v1/users/231231',
                [],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => __('Notfound')
            ])
            ->seeStatusCode(Response::HTTP_NOT_FOUND);
    }

    /**
     * Testing update user route.
     *
     * @depends testShow
     *
     * @return void
     */
    public function testUpdate(...$args)
    {
        $data = Arr::first($args);
        $itemId = Arr::get($data, 'data.id');

        $this
            ->json(
                'PUT',
                '/api/v1/users/' . $itemId,
                [
                    'name' => $this->faker->name
                ],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => __('Success'),
            ])
            ->seeStatusCode(Response::HTTP_OK);
    }

    /**
     * Testing destroy user route.
     *
     * @depends testShow
     *
     * @return void
     */
    public function testDestroy(...$args)
    {
        $data = Arr::first($args);
        $itemId = Arr::get($data, 'data.id');

        $this
            ->json(
                'DELETE',
                '/api/v1/users/' . $itemId,
                [],
                [
                    'x-app-token' => $this->appToken
                ]
            )
            ->seeJson([
                'message' => __('Success'),
            ])
            ->seeStatusCode(Response::HTTP_OK);
    }
}
