<?php

namespace Raank\Tests\V1\Unit;

use Raank\Models\V1\User;
use Raank\Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Hashing\BcryptHasher as Hash;

/**
 * @category tests
 * @package Raank\Tests
 * @package V1\Unit\SecurityTest
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class SecurityTest extends TestCase
{
    /**
     * Testing token on header, expecting success.
     *
     * @return void
     */
    public function testTokenAccessIsValid()
    {
        /** Setting a default password */
        $token = env('APP_TOKEN');

        /** Making a request */
        $this->head(
            '/api/v1/token-valid',
            [],
            [
                'x-app-token' => Crypt::encrypt($token)
            ]
        )
            ->seeStatusCode(Response::HTTP_OK);
    }

    /**
     * Testing token invalid, expecting unauthorized.
     *
     * @return void
     */
    public function testTokenAccessIsNotValid()
    {
        /** Making a request */
        $this->head(
            '/api/v1/token-valid',
            [],
            [
                'x-app-token' => 'abcd'
            ]
        )
            ->seeStatusCode(Response::HTTP_UNAUTHORIZED);
    }
    
     /**
     * Testing without on header, expecting unauthorized.
     *
     * @return void
     */
    public function testTokenAccessWithout()
    {
        /** Making a request */
        $this->head(
            '/api/v1/token-valid',
            [],
            []
        )
            ->seeStatusCode(Response::HTTP_UNAUTHORIZED);
    }

    public function testUnauthorized()
    {
        /** Making a request */
        $this->head(
            '/api/v1/users',
            [],
            []
        )
            ->seeStatusCode(Response::HTTP_UNAUTHORIZED);
    }

    public function testNotfound()
    {
        /** Making a request */
        $this->head(
            '/api/v1/testing-notfound',
            [],
            []
        )
            ->seeStatusCode(Response::HTTP_NOT_FOUND);
    }
}
