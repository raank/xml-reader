<?php

namespace Raank\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @category controllers-interfaces
 * @package Raank\Http\Controllers
 * @subpackage AuthControllerInterface
 * @version 1.0.0
 */
interface AuthControllerInterface
{
    /**
     * Registering users.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse;

    /**
     * Login users.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse;

    /**
     * Forgot Password.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function forgot(Request $request): JsonResponse;

    /**
     * Reset password of users.
     *
     * @param Request $request
     * @param string $token
     *
     * @return JsonResponse
     */
    public function reset(Request $request, string $token): JsonResponse;

    /**
     * Check token.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse;

    /**
     * Refresh token.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse;
}