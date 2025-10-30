<?php

namespace App\Exceptions;

/**
 * Exception for resource not found errors.
 */
class ResourceNotFoundException extends BaseException
{
    /**
     * Create a new ResourceNotFoundException instance.
     *
     * @param string $resource The resource name.
     * @param string|null $message The exception message.
     * @param \Throwable|null $previous The previous exception.
     */
    public function __construct(
        string $resource = 'Resource',
        string $message = null,
        ?\Throwable $previous = null
    ) {
        $message = $message ?? "{$resource} not found";
        parent::__construct(
            $message,
            'Resource Not Found',
            'RESOURCE_NOT_FOUND',
            404,
            $previous
        );
    }
}
