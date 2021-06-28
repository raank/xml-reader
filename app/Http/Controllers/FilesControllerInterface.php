<?php

namespace Raank\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @category controllers-interfaces
 * @package Raank\Http\Controllers
 * @subpackage FilesControllerInterface
 * @version 1.0.0
 */
interface FilesControllerInterface
{
    /**
     * Storing a new User.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse;

    /**
     * Show specified user.
     *
     * @param integer $userId
     *
     * @return JsonResponse
     */
    public function show(int $userId): JsonResponse;

    /**
     * Destroy user specified.
     *
     * @param integer $userId
     *
     * @return JsonResponse
     */
    public function destroy(int $userId): JsonResponse;

    /**
     * Searching Users.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse;
}