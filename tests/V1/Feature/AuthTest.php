<?php

namespace Raank\Tests\V1\Api;

use Raank\Models\V1\User;
use Raank\Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Http\Response;

/**
 * @category tests
 * @package Raank\Tests
 * @package V1\Api\AuthTest
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class AuthTest extends TestCase
{
    /**
     * A basic test environment.
     *
     * @return void
     */
    public function testEnvironment()
    {
        $this->assertEquals('testing', env('APP_ENV'));
    }

    /**
     * Testing register endpoint.
     *
     * @return array
     */
    public function testRegister(): array
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => '123@mudar',
            'password_confirmation' => '123@mudar'
        ];

        $this->json('POST', '/api/v1/auth/register', $data)
            ->seeJson([
                'message' => __('Success')
            ])->seeStatusCode(Response::HTTP_CREATED);

        return $data;
    }

    /**
     * Testing register endpoint with errors.
     *
     * @param mixed ...$args
     * 
     * @depends testRegister
     *
     * @return array
     */
    public function testRegisterError(...$args): array
    {
        $data = Arr::first($args);

        $this->json('POST', '/api/v1/auth/register', $data)
            ->seeJson([
                'message' => __('DataInvalid')
            ])->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        return $data;
    }

    /**
     * Testing login endpoint.
     *
     * @depends testRegister
     *
     * @return array
     */
    public function testLogin(...$args)
    {
        $data = Arr::first($args);
        $credentials = Arr::only($data, ['email', 'password']);

        $this->json('POST', '/api/v1/auth/login', $credentials)
            ->seeJson([
                'message' => __('Success')
            ])->seeStatusCode(Response::HTTP_OK);
    }

    /**
     * Testing login endpoint with error on email.
     *
     * @return array
     */
    public function testLoginErrorEmail()
    {
        $this
            ->json(
                'POST',
                '/api/v1/auth/login',
                [
                    'email' => 'email@example.com',
                    'password' => '1234'
                ]
            )
            ->seeJson([
                'message' => __('DataInvalid')
            ])->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Teting check token.
     *
     * @depends testRegister
     *
     * @return void
     */
    public function testCheckAndRefresh(...$args)
    {
        $data = Arr::first($args);
        $credentials = Arr::only($data, ['email', 'password']);

        $login = $this
            ->json(
                'POST',
                '/api/v1/auth/login',
                $credentials
            );

        $this
            ->seeJson([
                'message' => __('Success')
            ])
            ->seeStatusCode(Response::HTTP_OK);

        if ($login->response->getStatusCode() === Response::HTTP_OK && is_string($login->response->getContent())) {
            $response = json_decode($login->response->getContent(), true);
    
            $token = $response['data']['auth']['token'];
            
            $this
                ->json(
                    'HEAD',
                    '/api/v1/auth/check',
                    [],
                    ['Authorization' => 'Bearer ' . $token]
                )
                ->seeStatusCode(Response::HTTP_OK);

            $this
                ->json(
                    'HEAD',
                    '/api/v1/auth/refresh',
                    [],
                    ['Authorization' => 'Bearer ' . $token]
                )
                ->seeStatusCode(Response::HTTP_OK);

        }
    }

    /**
     * Teting check token.
     *
     * @depends testRegister
     *
     * @return void
     */
    public function testForgot(...$args)
    {
        $data = Arr::first($args);
        $email = Arr::get($data, 'email');

        $this
            ->json(
                'POST',
                '/api/v1/auth/forgot',
                compact('email')
            )
            ->seeJson([
                'message' => __('Success')
            ])
            ->seeStatusCode(Response::HTTP_OK);
    }

    /**
     * Teting check token.
     *
     * @depends testRegister
     *
     * @return void
     */
    public function testResetPassword(...$args)
    {
        $data = Arr::first($args);
        $email = Arr::get($data, 'email');
        $rememberToken = \Raank\Models\V1\User::where('email', '=', $email)
            ->first()
            ->remember_token;

        $this
            ->json(
                'POST',
                '/api/v1/auth/reset/' . $rememberToken,
                [
                    'password' => '12345',
                    'password_confirmation' => '12345'
                ]
            )
            ->seeJson([
                'message' => __('Success')
            ])
            ->seeStatusCode(Response::HTTP_OK);
    }
}
