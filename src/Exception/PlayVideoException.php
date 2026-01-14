<?php

declare(strict_types=1);

namespace PlayVideo\Exception;

class PlayVideoException extends \Exception
{
    public function __construct(
        string $message,
        public readonly ?string $code = null,
        public readonly ?int $statusCode = null,
        public readonly ?string $requestId = null,
        public readonly ?string $param = null,
    ) {
        parent::__construct($message);
    }
}
