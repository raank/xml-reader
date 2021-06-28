<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Define constants to Documentation.
|--------------------------------------------------------------------------
*/

define('APP_URL', sprintf('%s/api', env('APP_URL')));

/**
 * Informations of API.
 *
 * @OA\Info(
 *     title="raank/xml-reader",
 *     description="This is a micro authentication service and users or files crud. ",
 *     version="1.0.0",
 *     @OA\Contact(
 *          email="raank92@gmail.com"
 *     )
 * )
 *
 * Constrantes to API.
 * @OA\Schemes(format={"https", "http"})
 * @OA\Server(url=APP_URL)
 * 
 * Tags
 * @OA\Tag(name="v1.auth", description="Authentication routes")
 * @OA\Tag(name="v1.users", description="Users endpoints")
 * @OA\Tag(name="v1.files", description="The files endpoints")
 * @OA\Tag(name="v1.app", description="The application endpoints")
 *
 * Security
 * @OA\SecurityScheme(
 *  securityScheme="apiToken",
 *  type="apiKey",
 *  name="X-App-Token",
 *  in="header"
 * )
 * 
 * @OA\SecurityScheme(
 *  securityScheme="bearerAuth",
 *  type="http",
 *  scheme="bearer"
 * )
 */
