<?php

declare(strict_types=1);

namespace PlayVideo\Webhook;

use PlayVideo\Exception\WebhookSignatureException;

class Signature
{
    /**
     * Verify a webhook signature.
     *
     * @param string|array $payload The raw webhook payload
     * @param string $signature The X-PlayVideo-Signature header
     * @param string|int $timestamp The X-PlayVideo-Timestamp header
     * @param string $secret Your webhook secret
     * @param int $tolerance Maximum age in seconds (default: 300)
     * @throws WebhookSignatureException
     */
    public static function verify(
        string|array $payload,
        string $signature,
        string|int $timestamp,
        string $secret,
        int $tolerance = 300
    ): bool {
        if (empty($signature) || empty($timestamp) || empty($secret)) {
            throw new WebhookSignatureException('Missing required parameters');
        }

        // Validate timestamp is numeric
        if (is_string($timestamp) && !is_numeric($timestamp)) {
            throw new WebhookSignatureException('Invalid timestamp');
        }

        $ts = is_string($timestamp) ? (int) $timestamp : $timestamp;
        $now = (int) (microtime(true) * 1000);
        $age = abs($now - $ts) / 1000;

        if ($age > $tolerance) {
            throw new WebhookSignatureException("Timestamp too old ({$age}s > {$tolerance}s)");
        }

        $sigParts = explode('=', $signature);
        if (count($sigParts) !== 2 || $sigParts[0] !== 'sha256') {
            throw new WebhookSignatureException('Invalid signature format');
        }

        $payloadStr = is_array($payload) ? json_encode($payload, JSON_UNESCAPED_SLASHES) : $payload;
        $signedPayload = "{$ts}.{$payloadStr}";
        $computedSig = hash_hmac('sha256', $signedPayload, $secret);

        if (!hash_equals($sigParts[1], $computedSig)) {
            throw new WebhookSignatureException('Signature mismatch');
        }

        return true;
    }

    /**
     * Construct and verify a webhook event.
     *
     * @return array The parsed event
     * @throws WebhookSignatureException
     */
    public static function constructEvent(
        string|array $payload,
        string $signature,
        string|int $timestamp,
        string $secret,
        int $tolerance = 300
    ): array {
        self::verify($payload, $signature, $timestamp, $secret, $tolerance);

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new WebhookSignatureException('Invalid JSON payload: ' . json_last_error_msg());
            }
            return $decoded;
        }

        return $payload;
    }
}
