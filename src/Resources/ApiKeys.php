<?php

declare(strict_types=1);

namespace PlayVideo\Resources;

use PlayVideo\HttpClient;

class ApiKeys
{
    public function __construct(private HttpClient $http)
    {
    }

    public function list(): array
    {
        $data = $this->http->get('/api-keys');
        return $data['apiKeys'] ?? [];
    }

    public function create(string $name): array
    {
        return $this->http->post('/api-keys', ['name' => $name]);
    }

    public function delete(string $id): array
    {
        return $this->http->delete('/api-keys/' . urlencode($id));
    }
}
