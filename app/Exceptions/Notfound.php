<?php

namespace Raank\Exceptions;

use Exception;
use Illuminate\Http\Response;

/**
 * @category exceptions
 * @package Raank\Exceptions
 * @subpackage Notfound
 * @version 1.0.0
 * 
 * @OA\Schema(
 *   schema="Notfound",
 *   description="This information could not be found",
 *   @OA\Property(property="message", type="string", description="Message of Response", example="This information could not be found")
 * )
 */
class Notfound extends Exception
{
    /**
     * The constructor method.
     *
     * @param array|string|null $message
     */
    public function __construct($message = null)
    {
        parent::__construct(
            $message ?? __('Notfound'),
            Response::HTTP_NOT_FOUND
        );
    }
}