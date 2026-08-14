<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza;

use The6FallenAngel\Variza\Exception\InvalidSignatureException;

final class VarizaWebhookVerifier
{
    private const SIGNATURE_PREFIX = 'sha256=';

    public static function verify(string $rawBody, string $signatureHeader, string $secret): bool
    {
        $provided = str_starts_with($signatureHeader, self::SIGNATURE_PREFIX)
            ? substr($signatureHeader, strlen(self::SIGNATURE_PREFIX))
            : $signatureHeader;

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $provided);
    }

    /**
     * @throws InvalidSignatureException
     */
    public static function assertValid(string $rawBody, string $signatureHeader, string $secret): void
    {
        if (! self::verify($rawBody, $signatureHeader, $secret)) {
            throw new InvalidSignatureException();
        }
    }
}