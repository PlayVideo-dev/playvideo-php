<?php

declare(strict_types=1);

namespace PlayVideo\Exception;

class PlayVideoException extends \Exception
{
    public readonly ?string $errorCode;
    public readonly ?int $statusCode;
    public readonly ?string $requestId;
    public readonly ?string $param;

    public function __construct(
        string $message,
        ?string $errorCode = null,
        ?int $statusCode = null,
        ?string $requestId = null,
        ?string $param = null,
    ) {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
        $this->requestId = $requestId;
        $this->param = $param;
    }
}
