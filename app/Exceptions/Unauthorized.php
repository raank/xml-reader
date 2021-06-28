<?php

namespace Raank\Exceptions;

use Exception;
use Illuminate\Http\Response;

/**
 * @category exceptions
 * @package Raank\Exceptions
 * @subpackage Unauthorized
 * @version 1.0.0
 * 
 * @OA\Schema(
 *   schema="Unauthorized",
 *   description="You are not authorized for this action",
 *   @OA\Property(property="message", type="string", description="Message of Response", example="You are not authorized for this action")
 * )
 */
class Unauthorized extends Exception
{
    /**
     * The constructor method.
     *
     * @param array|string|null $message
     */
    public function __construct($message = null)
    {
        parent::__construct(
            $message ?? __('Unauthorized'),
            Response::HTTP_UNAUTHORIZED
        );
    }
}