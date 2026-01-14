<?php

declare(strict_types=1);

namespace PlayVideo\Resources;

use PlayVideo\HttpClient;

class Webhooks
{
    public function __construct(private HttpClient $http)
    {
    }

    public function list(): array
    {
        return $this->http->get('/webhooks');
    }

    public function get(string $id): array
    {
        return $this->http->get('/webhooks/' . urlencode($id));
    }

    public function create(string $url, array $events): array
    {
        return $this->http->post('/webhooks', [
            'url' => $url,
            'events' => $events,
        ]);
    }

    public function update(string $id, array $params): array
    {
        return $this->http->patch('/webhooks/' . urlencode($id), $params);
    }

    public function test(string $id): array
    {
        return $this->http->post('/webhooks/' . urlencode($id) . '/test');
    }

    public function delete(string $id): array
    {
        return $this->http->delete('/webhooks/' . urlencode($id));
    }
}
