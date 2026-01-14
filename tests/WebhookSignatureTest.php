<?php

declare(strict_types=1);

namespace PlayVideo\Tests;

use PHPUnit\Framework\TestCase;
use PlayVideo\Webhook\Signature;
use PlayVideo\Exception\WebhookSignatureException;

class WebhookSignatureTest extends TestCase
{
    private string $secret = 'whsec_test_secret_key';

    private function computeSignature(string $payload, int $timestamp, string $secret): string
    {
        $signedPayload = "{$timestamp}.{$payload}";
        return hash_hmac('sha256', $signedPayload, $secret);
    }

    public function testValidSignature(): void
    {
        $payload = json_encode(['event' => 'video.completed', 'data' => ['videoId' => 'vid1']]);
        $timestamp = (int)(microtime(true) * 1000);
        $signature = $this->computeSignature($payload, $timestamp, $this->secret);

        // Should not throw
        Signature::verify($payload, "sha256={$signature}", (string)$timestamp, $this->secret);
        $this->assertTrue(true);
    }

    public function testValidSignatureWithIntTimestamp(): void
    {
        $payload = json_encode(['event' => 'video.completed']);
        $timestamp = (int)(microtime(true) * 1000);
        $signature = $this->computeSignature($payload, $timestamp, $this->secret);

        Signature::verify($payload, "sha256={$signature}", $timestamp, $this->secret);
        $this->assertTrue(true);
    }

    public function testMissingSignature(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('Missing');

        Signature::verify('payload', '', '123456', $this->secret);
    }

    public function testMissingTimestamp(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('Missing');

        Signature::verify('payload', 'sha256=abc', '', $this->secret);
    }

    public function testMissingSecret(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('Missing');

        Signature::verify('payload', 'sha256=abc', '123456', '');
    }

    public function testInvalidTimestamp(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('Invalid timestamp');

        Signature::verify('payload', 'sha256=abc', 'not-a-number', $this->secret);
    }

    public function testOldTimestamp(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('too old');

        $payload = json_encode(['event' => 'video.completed']);
        $oldTimestamp = (int)((microtime(true) - 400) * 1000);
        $signature = $this->computeSignature($payload, $oldTimestamp, $this->secret);

        Signature::verify($payload, "sha256={$signature}", (string)$oldTimestamp, $this->secret, 300);
    }

    public function testCustomTolerance(): void
    {
        $payload = json_encode(['event' => 'video.completed']);
        $oldTimestamp = (int)((microtime(true) - 500) * 1000);
        $signature = $this->computeSignature($payload, $oldTimestamp, $this->secret);

        // Should fail with 5 minute tolerance
        $this->expectException(WebhookSignatureException::class);
        Signature::verify($payload, "sha256={$signature}", (string)$oldTimestamp, $this->secret, 300);
    }

    public function testCustomTolerancePass(): void
    {
        $payload = json_encode(['event' => 'video.completed']);
        $oldTimestamp = (int)((microtime(true) - 500) * 1000);
        $signature = $this->computeSignature($payload, $oldTimestamp, $this->secret);

        // Should pass with 10 minute tolerance
        Signature::verify($payload, "sha256={$signature}", (string)$oldTimestamp, $this->secret, 600);
        $this->assertTrue(true);
    }

    public function testInvalidSignatureFormat(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('Invalid signature format');

        $timestamp = (int)(microtime(true) * 1000);
        Signature::verify('payload', 'invalid_format', (string)$timestamp, $this->secret);
    }

    public function testNonSha256Algorithm(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('Invalid signature format');

        $timestamp = (int)(microtime(true) * 1000);
        Signature::verify('payload', 'md5=abc123', (string)$timestamp, $this->secret);
    }

    public function testSignatureMismatch(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('Signature mismatch');

        $timestamp = (int)(microtime(true) * 1000);
        Signature::verify('payload', 'sha256=invalid_signature', (string)$timestamp, $this->secret);
    }

    public function testTamperedPayload(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('Signature mismatch');

        $originalPayload = json_encode(['event' => 'video.completed', 'data' => ['videoId' => 'vid1']]);
        $tamperedPayload = json_encode(['event' => 'video.completed', 'data' => ['videoId' => 'vid2']]);
        $timestamp = (int)(microtime(true) * 1000);
        $signature = $this->computeSignature($originalPayload, $timestamp, $this->secret);

        Signature::verify($tamperedPayload, "sha256={$signature}", (string)$timestamp, $this->secret);
    }

    public function testWrongSecret(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('Signature mismatch');

        $payload = json_encode(['event' => 'video.completed']);
        $timestamp = (int)(microtime(true) * 1000);
        $signature = $this->computeSignature($payload, $timestamp, $this->secret);

        Signature::verify($payload, "sha256={$signature}", (string)$timestamp, 'wrong_secret');
    }

    public function testConstructEvent(): void
    {
        $eventData = [
            'event' => 'video.completed',
            'timestamp' => (int)(microtime(true) * 1000),
            'data' => ['videoId' => 'vid123'],
        ];
        $payload = json_encode($eventData);
        $timestamp = (int)(microtime(true) * 1000);
        $signature = $this->computeSignature($payload, $timestamp, $this->secret);

        $event = Signature::constructEvent($payload, "sha256={$signature}", (string)$timestamp, $this->secret);

        $this->assertEquals('video.completed', $event['event']);
        $this->assertEquals('vid123', $event['data']['videoId']);
    }

    public function testConstructEventInvalidSignature(): void
    {
        $this->expectException(WebhookSignatureException::class);

        $timestamp = (int)(microtime(true) * 1000);
        Signature::constructEvent('{"event":"video.completed"}', 'sha256=invalid', (string)$timestamp, $this->secret);
    }

    public function testConstructEventInvalidJson(): void
    {
        $this->expectException(WebhookSignatureException::class);
        $this->expectExceptionMessage('Invalid JSON');

        $payload = 'not valid json';
        $timestamp = (int)(microtime(true) * 1000);
        $signature = $this->computeSignature($payload, $timestamp, $this->secret);

        Signature::constructEvent($payload, "sha256={$signature}", (string)$timestamp, $this->secret);
    }
}
