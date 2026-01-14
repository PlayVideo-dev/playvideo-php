<?php

declare(strict_types=1);

namespace PlayVideo\Exception;

class NotFoundException extends PlayVideoException
{
    public function __construct(string $message = 'Resource not found', ?string $code = null, ?string $requestId = null)
    {
        parent::__construct($message, $code, 404, $requestId);
    }
}
