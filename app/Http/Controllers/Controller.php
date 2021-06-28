<?php

namespace Raank\Http\Controllers;

use Exception;
use Raank\Models\V1\User;
use Illuminate\Http\Response;
use Raank\Exceptions\Notfound;
use Raank\Exceptions\Validation;
use Illuminate\Http\JsonResponse;
use Raank\Exceptions\Unauthorized;
use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Lumen\Routing\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @category controllers
 * @package Raank\Http\Controllers
 * @subpackage Controller
 * @version 1.0.0
 * 
 * @OA\Schema(
 *  schema="v1.pagination.links",
 *  type="object",
 *  description="List of Links",
 *  @OA\Property(property="url", type="string", description="URL of Link", example="http://localhost"),
 *  @OA\Property(property="label", type="string", description="Label of Link", example="my-label"),
 *  @OA\Property(property="active", type="boolean", description="Link is active", example=true),
 * )
 */
class Controller extends BaseController
{
    /**
     * Checking if exists an document.
     *
     * @param mixed $value
     * @param string $class
     *
     * @throws NotFoundHttpException
     *
     * @return mixed
     */
    public function exists($value, string $class = User::class)
    {
        if (!$class::where((new $class())->getKeyName(), '=', $value)->first()) {
            throw new NotFoundHttpException('What you are looking for was not found!');
        }
    }

    /**
     * The invalid credentials exception.
     *
     * @throws Validation
     *
     * @return mixed
     */
    public function invalidCredentials()
    {
        throw new Validation(
            __('InvalidCredentials')
        );
    }

    /**
     * The unauthorized exception.
     *
     * @throws Unauthorized
     *
     * @return mixed
     */
    public function unauthorized()
    {
        throw new Unauthorized();
    }

    /**
     * The notfound exception.
     *
     * @throws Notfound
     *
     * @return mixed
     */
    public function notfound()
    {
        throw new Notfound();
    }

    /**
     * The unprocessable exception.
     *
     * @throws Validation
     *
     * @return mixed
     */
    public function unprocessable(string $message = null)
    {
        throw new Validation(
            $message ?? 'Unprocessable Entity'
        );
    }
}
