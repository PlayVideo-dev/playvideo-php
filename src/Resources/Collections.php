<?php

declare(strict_types=1);

namespace PlayVideo\Resources;

use PlayVideo\HttpClient;

class Collections
{
    public function __construct(private HttpClient $http)
    {
    }

    public function list(): array
    {
        $data = $this->http->get('/collections');
        return $data['collections'] ?? [];
    }

    public function get(string $slug): array
    {
        return $this->http->get('/collections/' . urlencode($slug));
    }

    public function create(string $name, ?string $description = null): array
    {
        $payload = ['name' => $name];
        if ($description !== null) {
            $payload['description'] = $description;
        }
        return $this->http->post('/collections', $payload);
    }

    public function delete(string $slug): array
    {
        return $this->http->delete('/collections/' . urlencode($slug));
    }
}
