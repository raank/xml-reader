<?php

namespace Raank\Tests\V1\Unit;

use Carbon\Carbon;
use Raank\Models\V1\User;
use Raank\Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Raank\Repositories\V1\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @category tests
 * @package Raank\Tests
 * @package V1\Unit\UserRepositoryTest
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class UserRepositoryTest extends TestCase
{
    /**
     * Testing all method.
     *
     * @return void
     */
    public function testAllMethod()
    {
        /** Assertions to expect */
        User::factory(rand(1, 10))
            ->create();

        /**
         * Instance Repository.
         *
         * @var UserRepository $repository
         */
        $repository = (new UserRepository());

        /** Getting all method */
        $all = $repository->all();
        /**
         * Convert to array.
         *
         * PS: Converted to array because property $total is protected
         */
        $data = $all->toArray();

        /** Assertions to expect */
        $this->assertTrue($all instanceof LengthAwarePaginator);
        $this->assertTrue($data['total'] > 0);
    }

    /**
     * Testing store method.
     *
     * @return mixed
     */
    public function testStoreMethod()
    {
        /** Setting data to store */
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
            'remember_token' => Str::random(32),
            'password' => Str::random(10),
            'deleted_at' => null
        ];

        /**
         * Instance Repository.
         *
         * @var UserRepository $repository
         */
        $repository = (new UserRepository());

        /** Getting store method */
        $store = $repository->store($data);

        /** Assertions to expect */
        $this->assertTrue($store instanceof User);
        $this->assertEquals($store->name, $data['name']);

        return $store;
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
        $user = Arr::first($args);

        /** Making a request with query builder dynamic */
        $requestParams = [
            'where' => [
                ['name', 'LIKE', Arr::get($user, 'data.name')],
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

        /** Instance a fake Request */
        $request = Request::create(
            '/api/v1/users/search',
            'POST',
            $requestParams
        );

        /**
         * Instance a repository
         *
         * @var UserRepository $repository
         */
        $repository = (new UserRepository());

        /** Getting search method of controller */
        $search = $repository->search($request);

        /**
         * Convert to array.
         *
         * PS: Converted to array because property $total is protected
         */
        $data = $search->toArray();

        /** Assertions to expect */
        $this->assertTrue($search instanceof LengthAwarePaginator);
        $this->assertTrue($data['total'] > 0);
    }

    /**
     * Testing find method.
     *
     * @return void
     */
    public function testFindMethod()
    {
        /** @var User $user */
        $user = User::factory()
            ->create();

        /**
         * Instance Repository.
         *
         * @var UserRepository $repository
         */
        $repository = (new UserRepository());
        /** Getting find method */
        $find = $repository->find($user->id);

        /** Assertions to expect */
        $this->assertTrue($find instanceof User);
        $this->assertEquals($find->name, $user->name);
    }

    /**
     * Testing update method.
     *
     * @return void
     */
    public function testUpdateMethod()
    {
        /** @var User $user */
        $user = User::factory()
            ->create();

        /**
         * Instance Repository.
         *
         * @var UserRepository $repository
         */
        $repository = (new UserRepository());

        /** Making a new name to update */
        $newName = $this->faker->name;

        /** Getting update method */
        $update = $repository->update($user->id, [
            'name' => $newName
        ]);

        /** Getting find method to check a new name is updated */
        $find = $repository->find($user->id);

        /** Assertions to expect */
        $this->assertTrue($update);
        $this->assertEquals($find->name, $newName);
    }

    /**
     * Testing destroy method.
     *
     * @return void
     */
    public function testDestroyMethod()
    {
        /** @var User $user */
        $user = User::factory()
            ->create();

        /**
         * Instance Repository.
         *
         * @var UserRepository $repository
         */
        $repository = (new UserRepository());
        /** Getting destroy method */
        $destroy = $repository->destroy($user->id);
        /** Getting find method to check if already deleted */
        $find = $repository->find($user->id);

        /** Assertions to expect */
        $this->assertTrue($destroy > 0);
        $this->assertTrue(\is_null($find));
    }
}
