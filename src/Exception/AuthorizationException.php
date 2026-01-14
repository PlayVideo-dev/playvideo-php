<?php

declare(strict_types=1);

namespace PlayVideo\Exception;

class AuthorizationException extends PlayVideoException
{
    public function __construct(string $message = 'Insufficient permissions', ?string $code = null, ?string $requestId = null)
    {
        parent::__construct($message, $code, 403, $requestId);
    }
}
