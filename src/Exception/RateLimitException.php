<?php

declare(strict_types=1);

namespace PlayVideo\Exception;

class RateLimitException extends PlayVideoException
{
    public function __construct(
        string $message = 'Too many requests',
        ?string $code = null,
        ?string $requestId = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $code, 429, $requestId);
    }
}
