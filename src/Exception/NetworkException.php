<?php

declare(strict_types=1);

namespace PlayVideo\Exception;

class NetworkException extends PlayVideoException
{
    public function __construct(string $message = 'Network error')
    {
        parent::__construct($message);
    }
}
