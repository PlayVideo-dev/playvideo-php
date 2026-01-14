<?php

declare(strict_types=1);

namespace PlayVideo\Resources;

use PlayVideo\HttpClient;

class Videos
{
    public function __construct(private HttpClient $http)
    {
    }

    public function list(array $params = []): array
    {
        $query = http_build_query($params);
        $endpoint = '/videos' . ($query ? "?{$query}" : '');
        $data = $this->http->get($endpoint);
        return $data['videos'] ?? [];
    }

    public function get(string $id): array
    {
        return $this->http->get('/videos/' . urlencode($id));
    }

    public function upload(string $filePath, string $collection, ?callable $onProgress = null): array
    {
        return $this->http->upload('/videos', $filePath, $collection, $onProgress);
    }

    public function delete(string $id): array
    {
        return $this->http->delete('/videos/' . urlencode($id));
    }

    public function getEmbedInfo(string $id): array
    {
        return $this->http->get('/videos/' . urlencode($id) . '/embed');
    }

    /**
     * Watch video processing progress via SSE.
     *
     * @return \Generator<array>
     */
    public function watchProgress(string $id): \Generator
    {
        yield from $this->http->streamSse('/videos/' . urlencode($id) . '/progress');
    }
}
