<?php

declare(strict_types=1);

namespace PlayVideo\Exception;

class ConflictException extends PlayVideoException
{
    public function __construct(string $message = 'Resource conflict', ?string $code = null, ?string $requestId = null)
    {
        parent::__construct($message, $code, 409, $requestId);
    }
}
