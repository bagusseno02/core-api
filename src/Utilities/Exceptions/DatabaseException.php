<?php
/**
 * Created by PhpStorm.
 * User: alysangadji
 * Date: 14/12/18
 * Time: 10.53
 */

namespace coreapi\Utilities\Exceptions;

use Throwable;

class DatabaseException extends BaseException
{
    public function __construct(
        $errorMessage,
        $errorCode = "DB.0001",
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($errorMessage, $errorCode, $code, $previous);
    }


}