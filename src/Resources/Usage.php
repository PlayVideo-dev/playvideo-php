<?php

declare(strict_types=1);

namespace PlayVideo\Resources;

use PlayVideo\HttpClient;

class Usage
{
    public function __construct(private HttpClient $http)
    {
    }

    public function get(): array
    {
        return $this->http->get('/usage');
    }
}
