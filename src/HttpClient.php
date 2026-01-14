<?php

declare(strict_types=1);

namespace PlayVideo;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use PlayVideo\Exception\PlayVideoException;
use PlayVideo\Exception\AuthenticationException;
use PlayVideo\Exception\AuthorizationException;
use PlayVideo\Exception\NotFoundException;
use PlayVideo\Exception\ValidationException;
use PlayVideo\Exception\ConflictException;
use PlayVideo\Exception\RateLimitException;
use PlayVideo\Exception\ServerException;
use PlayVideo\Exception\NetworkException;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    private const DEFAULT_BASE_URL = 'https://api.playvideo.dev/api/v1';
    private const DEFAULT_TIMEOUT = 30.0;

    private Client $client;
    private string $apiKey;
    private string $baseUrl;

    public function __construct(string $apiKey, array $options = [])
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($options['baseUrl'] ?? self::DEFAULT_BASE_URL, '/');
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $options['timeout'] ?? self::DEFAULT_TIMEOUT,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function get(string $endpoint): array
    {
        return $this->request('GET', $endpoint);
    }

    public function post(string $endpoint, ?array $body = null): array
    {
        return $this->request('POST', $endpoint, $body);
    }

    public function patch(string $endpoint, ?array $body = null): array
    {
        return $this->request('PATCH', $endpoint, $body);
    }

    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    public function upload(string $endpoint, string $filePath, string $collection, ?callable $onProgress = null): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $multipart = [
            [
                'name' => 'file',
                'contents' => fopen($filePath, 'r'),
                'filename' => basename($filePath),
            ],
            [
                'name' => 'collection',
                'contents' => $collection,
            ],
        ];

        try {
            $response = $this->client->post($endpoint, [
                'multipart' => $multipart,
                'timeout' => 600.0, // 10 minute timeout for uploads
            ]);

            if ($onProgress) {
                $onProgress([
                    'loaded' => filesize($filePath),
                    'total' => filesize($filePath),
                    'percent' => 100,
                ]);
            }

            return $this->handleResponse($response);
        } catch (RequestException $e) {
            throw $this->handleException($e);
        } catch (GuzzleException $e) {
            throw new NetworkException("Network error: {$e->getMessage()}");
        }
    }

    /**
     * Stream SSE events from an endpoint.
     *
     * @return \Generator<array>
     */
    public function streamSse(string $endpoint): \Generator
    {
        try {
            $response = $this->client->get($endpoint, [
                'headers' => [
                    'Accept' => 'text/event-stream',
                ],
                'stream' => true,
            ]);

            $body = $response->getBody();
            $buffer = '';

            while (!$body->eof()) {
                $buffer .= $body->read(1024);
                $lines = explode("\n", $buffer);
                $buffer = array_pop($lines);

                foreach ($lines as $line) {
                    if (str_starts_with($line, 'data: ')) {
                        $data = json_decode(substr($line, 6), true);
                        if ($data !== null) {
                            yield $data;
                            
                            if (in_array($data['stage'] ?? '', ['completed', 'failed', 'timeout'])) {
                                return;
                            }
                        }
                    }
                }
            }
        } catch (RequestException $e) {
            throw $this->handleException($e);
        } catch (GuzzleException $e) {
            throw new NetworkException("Network error: {$e->getMessage()}");
        }
    }

    private function request(string $method, string $endpoint, ?array $body = null): array
    {
        try {
            $options = [];
            if ($body !== null) {
                $options['json'] = $body;
            }

            $response = $this->client->request($method, $endpoint, $options);
            return $this->handleResponse($response);
        } catch (RequestException $e) {
            throw $this->handleException($e);
        } catch (GuzzleException $e) {
            throw new NetworkException("Network error: {$e->getMessage()}");
        }
    }

    private function handleResponse(ResponseInterface $response): array
    {
        $body = $response->getBody()->getContents();
        return json_decode($body, true) ?? [];
    }

    private function handleException(RequestException $e): PlayVideoException
    {
        $response = $e->getResponse();
        
        if ($response === null) {
            return new NetworkException("Network error: {$e->getMessage()}");
        }

        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true) ?? [];
        $message = $body['message'] ?? $body['error'] ?? "HTTP {$statusCode}";
        $requestId = $response->getHeader('x-request-id')[0] ?? null;

        return match ($statusCode) {
            401 => new AuthenticationException($message, $body['code'] ?? null, $requestId),
            403 => new AuthorizationException($message, $body['code'] ?? null, $requestId),
            404 => new NotFoundException($message, $body['code'] ?? null, $requestId),
            409 => new ConflictException($message, $body['code'] ?? null, $requestId),
            429 => new RateLimitException($message, $body['code'] ?? null, $requestId),
            400, 422 => new ValidationException($message, $body['code'] ?? null, $requestId, $body['param'] ?? null),
            default => $statusCode >= 500
                ? new ServerException($message, $body['code'] ?? null, $requestId, $statusCode)
                : new PlayVideoException($message, $body['code'] ?? null, $statusCode, $requestId),
        };
    }
}
