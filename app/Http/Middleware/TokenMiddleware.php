<?php

namespace Raank\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * @category middlewares
 * @package Raank\Http\Middleware
 * @subpackage TokenMiddleware
 * @version 1.0.0
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class TokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request  $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $key = 'x-app-token';
        $token = env('APP_TOKEN');

        $appToken = $request
            ->headers
            ->get($key);

        try {
            if (
                !is_null($appToken)
                && is_string($appToken)
                && Crypt::decrypt($appToken) === $token
            ) {
                return $next($request);
            }
        } catch (DecryptException $th) {
            Log::error('Error on decryption', [
                'string' => $appToken
            ]);
        }

        return $this->unauthorized();
    }

    /**
     * The unauthorized action.
     *
     * @return mixed
     */
    private function unauthorized()
    {
        return response()
            ->json([
                'message' => __('Unauthorized')
            ], Response::HTTP_UNAUTHORIZED);
    }
}
