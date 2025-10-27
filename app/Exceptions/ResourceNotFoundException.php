<?php

namespace App\Exceptions;

class ResourceNotFoundException extends BaseException
{
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
