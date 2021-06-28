<?php

namespace Raank\Tests\V1\Unit;

use Raank\Models\V1\User;
use Raank\Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Hashing\BcryptHasher as Hash;

/**
 * @category tests
 * @package Raank\Tests
 * @package V1\Unit\AuthTest
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class AuthTest extends TestCase
{
    /**
     * A test auth attempts credentials.
     *
     * @return void
     */
    public function testUserAuth()
    {
        /** Setting a default password */
        $password = Str::random(10);

        /** @var User $user */
        $user = User::factory()
            ->create([
                'password' => $password
            ]);

        /** Attempts Credentials */
        $token = Auth::attempt([
            'email' => $user->email,
            'password' => $password
        ]);

        /** Assertions to expect */
        $this->assertTrue(!empty($token));
    }

    /**
     * A test hashing functions passwords.
     *
     * @return void
     */
    public function testHashPassword()
    {
        /** Setting a default password */
        $password = Str::random(10);
        /** Setting default password with hashing */
        $hashing = (new Hash)->make($password);

        /** Assertions to expect */
        $this->assertTrue($hashing !== $password);
        $this->assertTrue((new Hash)->check($password, $hashing));
    }

    /**
     * A test of hash passwords in Model.
     *
     * @return void
     */
    public function testUserPasswordHashing()
    {
        /** Setting default password */
        $password = Str::random(10);

        /** @var User $user */
        $user = User::factory()
            ->create([
                'password' => $password
            ]);

        /** Assertions to expect */
        $this->assertTrue(
            (new Hash)->check(
                $password,
                $user->password
            )
        );
    }
}
