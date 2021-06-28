<?php

namespace Raank\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Raank\Exceptions\Exists;
use Raank\Exceptions\Notfound;
use Raank\Exceptions\Validation;
use Illuminate\Http\JsonResponse;
use Raank\Exceptions\Unauthorized;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @category exceptions
 * @package Raank\Exceptions
 * @subpackage Handler
 * @version 1.0.0
 * 
 * @OA\Schema(
 *   schema="BadRequest",
 *   description="This information could not be processed",
 *   @OA\Property(property="message", type="string", description="Message of Response", example="This information could not be processed")
 * )
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class
    ];

    /**
     * A list of the exception types to response like a json.
     *
     * @var array
     */
    protected $httpReports = [
        Unauthorized::class,
        Validation::class,
        Notfound::class,
        DecryptException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request     $request
     * @param  \Throwable  $exception
     *
     * @return JsonResponse|Response
     */
    public function render($request, Throwable $exception)
    {
        if (in_array(get_class($exception), $this->httpReports)) {
            $code = $exception->getCode();

            return response()
                ->json([
                    'message' => $exception->getMessage(),
                    'trace' => $exception->__toString()
                ], $code > 0 ? $code : Response::HTTP_BAD_REQUEST);
        }

        if ($exception instanceof ValidationException) {
            $response = $exception->getResponse();

            return response()
                ->json(
                    [
                        'message' => $exception->getMessage(),
                        'errors' => isset($response->original) ? $response->original : $response,
                    ],
                    $exception->status ?? Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()
                ->json([
                    'message' => __('Notfound'),
                ], Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()
                ->json(
                    [
                        'message' => __('ErrorMethod'),
                        'trace' => [
                            'trace' => $exception->getMessage(),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine()
                        ]
                    ],
                    Response::HTTP_METHOD_NOT_ALLOWED
                );
        }

        return parent::render($request, $exception);
    }
}
