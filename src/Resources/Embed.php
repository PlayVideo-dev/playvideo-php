<?php

declare(strict_types=1);

namespace PlayVideo\Resources;

use PlayVideo\HttpClient;

class Embed
{
    public function __construct(private HttpClient $http)
    {
    }

    public function getSettings(): array
    {
        return $this->http->get('/embed/settings');
    }

    public function updateSettings(array $settings): array
    {
        return $this->http->patch('/embed/settings', $settings);
    }

    public function sign(string $videoId, ?string $baseUrl = null): array
    {
        $payload = ['videoId' => $videoId];
        if ($baseUrl !== null) {
            $payload['baseUrl'] = $baseUrl;
        }
        return $this->http->post('/embed/sign', $payload);
    }
}
