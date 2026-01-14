<?php

declare(strict_types=1);

namespace PlayVideo\Exception;

class WebhookSignatureException extends PlayVideoException
{
    public function __construct(string $message = 'Invalid webhook signature')
    {
        parent::__construct($message, 'webhook_signature_error');
    }
}
