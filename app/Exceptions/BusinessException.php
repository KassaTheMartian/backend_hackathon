<?php

namespace App\Exceptions;

class BusinessException extends BaseException
{
    public function __construct(
        string $message = 'Business logic error',
        string $title = 'Business Error',
        string $errorCode = 'BUSINESS_ERROR',
        int $statusCode = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $title, $errorCode, $statusCode, $previous);
    }
}
