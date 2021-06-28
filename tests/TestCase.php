<?php

namespace Raank\Tests;

use Faker\Factory;
use Raank\Models\V1\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Crypt;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

/**
 * @package tests
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * The factory of Faker.
     *
     * @var mixed
     */
    protected $faker;

    /**
     * The constructor method.
     */
    public function __construct()
    {
        $this->faker = Factory::create();

        parent::__construct();
    }
    
    /**
     * The app token to Access.
     *
     * @var string
     */
    protected $appToken;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->appToken = Crypt::encrypt(env('APP_TOKEN'));
    }

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * Making a array to response.
     *
     * @param TestResponse $response
     *
     * @return mixed|null
     */
    public function getContent(TestResponse $response)
    {
        $content = $response->getContent();

        if (\is_string($content)) {
            return \json_decode(
                (string) $response->getContent(),
                true
            );
        }
        
        return null;
    }
}
