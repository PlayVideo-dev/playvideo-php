<?php

declare(strict_types=1);

namespace PlayVideo;

use PlayVideo\Resources\Collections;
use PlayVideo\Resources\Videos;
use PlayVideo\Resources\Webhooks;
use PlayVideo\Resources\Embed;
use PlayVideo\Resources\ApiKeys;
use PlayVideo\Resources\Account;
use PlayVideo\Resources\Usage;
use PlayVideo\HttpClient;

/**
 * PlayVideo API client.
 *
 * @example
 * ```php
 * $client = new PlayVideo('play_live_xxx');
 * $collections = $client->collections->list();
 * $video = $client->videos->upload('./video.mp4', 'my-collection');
 * ```
 */
class PlayVideo
{
    private HttpClient $http;

    public readonly Collections $collections;
    public readonly Videos $videos;
    public readonly Webhooks $webhooks;
    public readonly Embed $embed;
    public readonly ApiKeys $apiKeys;
    public readonly Account $account;
    public readonly Usage $usage;

    /**
     * Create a new PlayVideo client.
     *
     * @param string $apiKey Your PlayVideo API key
     * @param array{baseUrl?: string, timeout?: float} $options Client options
     */
    public function __construct(string $apiKey, array $options = [])
    {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('API key is required');
        }

        $this->http = new HttpClient($apiKey, $options);

        $this->collections = new Collections($this->http);
        $this->videos = new Videos($this->http);
        $this->webhooks = new Webhooks($this->http);
        $this->embed = new Embed($this->http);
        $this->apiKeys = new ApiKeys($this->http);
        $this->account = new Account($this->http);
        $this->usage = new Usage($this->http);
    }
}
