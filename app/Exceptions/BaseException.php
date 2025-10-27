<?php

namespace App\Exceptions;

use Exception;

abstract class BaseException extends Exception
{
    protected string $title;
    protected string $errorCode;
    protected int $statusCode;

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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getContext(): array
    {
        return [
            'title' => $this->title,
            'error_code' => $this->errorCode,
            'status_code' => $this->statusCode,
        ];
    }
}
