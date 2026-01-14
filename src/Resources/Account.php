<?php

declare(strict_types=1);

namespace PlayVideo\Resources;

use PlayVideo\HttpClient;

class Account
{
    public function __construct(private HttpClient $http)
    {
    }

    public function get(): array
    {
        return $this->http->get('/account');
    }

    public function update(array $params): array
    {
        return $this->http->patch('/account', $params);
    }
}
