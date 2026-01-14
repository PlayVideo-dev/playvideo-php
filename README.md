# PlayVideo SDK for PHP

[![CI](https://github.com/PlayVideo-dev/playvideo-php/actions/workflows/ci.yml/badge.svg)](https://github.com/PlayVideo-dev/playvideo-php/actions/workflows/ci.yml)
[![Packagist Version](https://img.shields.io/packagist/v/playvideo/playvideo.svg)](https://packagist.org/packages/playvideo/playvideo)
[![Packagist Downloads](https://img.shields.io/packagist/dm/playvideo/playvideo.svg)](https://packagist.org/packages/playvideo/playvideo)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP 8.1+](https://img.shields.io/badge/php-8.1+-blue.svg)](https://www.php.net/)

Official PHP SDK for the [PlayVideo](https://playvideo.dev) API - Video hosting for developers.

## Installation

```bash
composer require playvideo/playvideo
```

**Requirements:** PHP 8.1+

## Quick Start

```php
<?php

require 'vendor/autoload.php';

use PlayVideo\PlayVideo;

$client = new PlayVideo('play_live_xxx');

// List collections
$collections = $client->collections->list();

// Upload a video
$response = $client->videos->upload('./video.mp4', 'my-collection', function ($progress) {
    echo "{$progress->percent}%\n";
});

// Watch processing progress
foreach ($client->videos->watchProgress($response['video']['id']) as $event) {
    echo "{$event->stage}: {$event->message}\n";
    if ($event->stage === 'completed') {
        echo "Video ready: {$event->playlistUrl}\n";
        break;
    }
}
```

## Resources

### Collections

```php
// List all collections
$collections = $client->collections->list();

// Create a collection
$collection = $client->collections->create([
    'name' => 'My Videos',
    'description' => 'Tutorial videos',
]);

// Get a collection with videos
$collection = $client->collections->get('my-videos');

// Delete a collection
$client->collections->delete('my-videos');
```

### Videos

```php
// List videos
$videos = $client->videos->list();

// Filter by collection or status
$videos = $client->videos->list([
    'collection' => 'my-collection',
    'status' => 'COMPLETED',
    'limit' => 50,
]);

// Get a video
$video = $client->videos->get('video-id');

// Upload with progress
$response = $client->videos->upload('./video.mp4', 'my-collection', function ($progress) {
    echo "{$progress->percent}% ({$progress->loaded}/{$progress->total})\n";
});

// Delete a video
$client->videos->delete('video-id');

// Get embed information
$embedInfo = $client->videos->getEmbedInfo('video-id');

// Watch processing progress (SSE streaming)
foreach ($client->videos->watchProgress('video-id') as $event) {
    match ($event->stage) {
        'pending' => print("Waiting in queue...\n"),
        'processing' => print("Transcoding...\n"),
        'completed' => print("Done! {$event->playlistUrl}\n"),
        'failed' => print("Failed: {$event->error}\n"),
    };
}
```

### Webhooks

```php
// List webhooks
$result = $client->webhooks->list();
$webhooks = $result['webhooks'];
$availableEvents = $result['availableEvents'];

// Create a webhook
$result = $client->webhooks->create([
    'url' => 'https://example.com/webhook',
    'events' => ['video.completed', 'video.failed'],
]);
// Save $result['webhook']['secret'] - only shown once!

// Update a webhook
$client->webhooks->update('webhook-id', [
    'events' => ['video.completed'],
    'isActive' => false,
]);

// Test a webhook
$result = $client->webhooks->test('webhook-id');

// Delete a webhook
$client->webhooks->delete('webhook-id');
```

### Embed Settings

```php
// Get embed settings
$settings = $client->embed->getSettings();

// Update embed settings
$settings = $client->embed->updateSettings([
    'primaryColor' => '#FF0000',
    'autoplay' => true,
    'muted' => true,
]);

// Generate signed embed URL
$embed = $client->embed->sign(['videoId' => 'video-id']);
echo $embed['embedUrl'];
echo $embed['embedCode']['responsive'];
```

### API Keys

```php
// List API keys
$apiKeys = $client->apiKeys->list();

// Create an API key
$result = $client->apiKeys->create(['name' => 'My App']);
// Save $result['apiKey']['key'] - only shown once!

// Delete an API key
$client->apiKeys->delete('key-id');
```

### Account

```php
// Get account info
$account = $client->account->get();

// Update allowed domains
$account = $client->account->update([
    'allowedDomains' => ['example.com', 'app.example.com'],
    'allowLocalhost' => true,
]);
```

### Usage

```php
// Get usage statistics
$usage = $client->usage->get();

echo "Plan: {$usage['plan']}\n";
echo "Videos: {$usage['usage']['videosThisMonth']}/{$usage['usage']['videosLimit']}\n";
echo "Storage: {$usage['usage']['storageUsedGB']} GB\n";
```

## Webhook Signature Verification

```php
<?php

use PlayVideo\Webhook;
use PlayVideo\Exceptions\WebhookSignatureException;

// In your webhook handler
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PLAYVIDEO_SIGNATURE'] ?? '';
$timestamp = $_SERVER['HTTP_X_PLAYVIDEO_TIMESTAMP'] ?? '';

try {
    $event = Webhook::constructEvent($payload, $signature, $timestamp, 'whsec_xxx');
    
    match ($event['event']) {
        'video.completed' => handleVideoCompleted($event['data']),
        'video.failed' => handleVideoFailed($event['data']),
        default => null,
    };
    
    http_response_code(200);
    echo 'OK';
} catch (WebhookSignatureException $e) {
    http_response_code(400);
    echo 'Invalid signature';
}
```

## Error Handling

```php
<?php

use PlayVideo\PlayVideo;
use PlayVideo\Exceptions\PlayVideoException;
use PlayVideo\Exceptions\AuthenticationException;
use PlayVideo\Exceptions\NotFoundException;
use PlayVideo\Exceptions\RateLimitException;
use PlayVideo\Exceptions\ValidationException;

$client = new PlayVideo('play_live_xxx');

try {
    $video = $client->videos->get('invalid-id');
} catch (AuthenticationException $e) {
    echo "Invalid API key\n";
} catch (NotFoundException $e) {
    echo "Video not found\n";
} catch (RateLimitException $e) {
    echo "Rate limited. Retry after {$e->retryAfter}s\n";
} catch (ValidationException $e) {
    echo "Invalid param: {$e->param}\n";
} catch (PlayVideoException $e) {
    echo "API error: {$e->getMessage()} (code: {$e->getCode()})\n";
}
```

### Exception Types

| Exception | Status | Description |
|-----------|--------|-------------|
| `AuthenticationException` | 401 | Invalid or missing API key |
| `AuthorizationException` | 403 | Insufficient permissions |
| `NotFoundException` | 404 | Resource not found |
| `ValidationException` | 400/422 | Invalid parameters |
| `ConflictException` | 409 | Resource conflict |
| `RateLimitException` | 429 | Too many requests |
| `ServerException` | 5xx | Server error |
| `NetworkException` | - | Connection failed |
| `TimeoutException` | - | Request timed out |
| `WebhookSignatureException` | - | Invalid signature |

## Configuration

```php
$client = new PlayVideo('play_live_xxx', [
    // Custom base URL (for self-hosted)
    'baseUrl' => 'https://api.yourdomain.com/api/v1',
    
    // Request timeout in seconds (default: 30)
    'timeout' => 60,
]);
```

## MCP Server

Use the PlayVideo MCP server to connect Claude/Desktop assistants to your account.

```bash
npm install -g @playvideo/playvideo-mcp
```

Repo: https://github.com/PlayVideo-dev/playvideo-mcp

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## License

MIT - see [LICENSE](LICENSE) for details.

## Links

- [Documentation](https://playvideo.dev/docs)
- [API Reference](https://playvideo.dev/docs/api)
- [Dashboard](https://playvideo.dev/dashboard)
- [GitHub](https://github.com/PlayVideo-dev/playvideo-php)
