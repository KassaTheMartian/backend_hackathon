<?php

namespace App\Exceptions;

/**
 * Exception for business logic errors.
 */
class BusinessException extends BaseException
{
    /**
     * Create a new BusinessException instance.
     *
     * @param string $message The exception message.
     * @param string $title The exception title.
     * @param string $errorCode The error code.
     * @param int $statusCode The HTTP status code.
     * @param \Throwable|null $previous The previous exception.
     */
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
