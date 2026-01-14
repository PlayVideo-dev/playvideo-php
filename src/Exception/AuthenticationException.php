<?php

declare(strict_types=1);

namespace PlayVideo\Exception;

class AuthenticationException extends PlayVideoException
{
    public function __construct(string $message = 'Invalid or missing API key', ?string $code = null, ?string $requestId = null)
    {
        parent::__construct($message, $code, 401, $requestId);
    }
}
