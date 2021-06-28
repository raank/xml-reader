<?php

namespace Raank\Exceptions;

use Exception;
use Illuminate\Http\Response;

/**
 * @category exceptions
 * @package Raank\Exceptions
 * @subpackage Validation
 * @version 1.0.0
 * 
 * @OA\Schema(
 *  schema="Validation",
 *  description="There is some incorrect information",
 *  @OA\Property(property="message", type="string", description="Message of Response"),
 *  @OA\Property(property="errors", type="object", description="Errors of Request"),
 *  example={
 *      "message": "There is some incorrect information",
 *      "errors": {
 *          "field": {
 *              "Message of Validation"
 *          }
 *      }
 *  }
 * )
 */
class Validation extends Exception
{
    /**
     * The constructor method.
     *
     * @param array|string|null $message
     */
    public function __construct($message = null)
    {
        parent::__construct(
            $message ?? __('DataInvalid'),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}