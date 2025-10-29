<?php

namespace App\Exceptions;

use Exception;

/**
 * Base exception class for application-specific exceptions.
 *
 * Provides common properties and methods for custom exceptions.
 */
abstract class BaseException extends Exception
{
    /** @var string */
    protected string $title;

    /** @var string */
    protected string $errorCode;

    /** @var int */
    protected int $statusCode;

    /**
     * Create a new BaseException instance.
     *
     * @param string $message The exception message.
     * @param string $title The exception title.
     * @param string $errorCode The error code.
     * @param int $statusCode The HTTP status code.
     * @param \Throwable|null $previous The previous exception.
     */
    public function __construct(
        string $message = '',
        string $title = 'Error',
        string $errorCode = 'ERROR',
        int $statusCode = 400,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->title = $title;
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
    }

    /**
     * Get the exception title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the error code.
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the exception context.
     *
     * @return array
     */
    public function getContext(): array
    {
        return [
            'title' => $this->title,
            'error_code' => $this->errorCode,
            'status_code' => $this->statusCode,
        ];
    }
}
