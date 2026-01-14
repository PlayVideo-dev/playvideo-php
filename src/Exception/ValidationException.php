<?php

declare(strict_types=1);

namespace PlayVideo\Exception;

class ValidationException extends PlayVideoException
{
    public function __construct(string $message = 'Invalid request parameters', ?string $code = null, ?string $requestId = null, ?string $param = null)
    {
        parent::__construct($message, $code, 400, $requestId, $param);
    }
}
