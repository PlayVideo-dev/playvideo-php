<?php

declare(strict_types=1);

namespace PlayVideo\Exception;

class ServerException extends PlayVideoException
{
    public function __construct(
        string $message = 'Server error',
        ?string $code = null,
        ?string $requestId = null,
        int $statusCode = 500
    ) {
        parent::__construct($message, $code, $statusCode, $requestId);
    }
}
