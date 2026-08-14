<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza\Tests;

use PHPUnit\Framework\TestCase;
use The6FallenAngel\Variza\Exception\InvalidSignatureException;
use The6FallenAngel\Variza\VarizaWebhookVerifier;

final class VarizaWebhookVerifierTest extends TestCase
{
    private const SECRET = 'webhook-secret';

    public function test_verify_accepts_valid_signature(): void
    {
        $body = '{"event":"payment.paid","amount":50000}';
        $signature = 'sha256='.hash_hmac('sha256', $body, self::SECRET);

        $this->assertTrue(VarizaWebhookVerifier::verify($body, $signature, self::SECRET));
    }

    public function test_verify_rejects_tampered_body(): void
    {
        $body = '{"event":"payment.paid","amount":50000}';
        $signature = 'sha256='.hash_hmac('sha256', $body, self::SECRET);

        $this->assertFalse(VarizaWebhookVerifier::verify($body.' ', $signature, self::SECRET));
    }

    public function test_verify_rejects_wrong_secret(): void
    {
        $body = '{"event":"payment.paid","amount":50000}';
        $signature = 'sha256='.hash_hmac('sha256', $body, 'other-secret');

        $this->assertFalse(VarizaWebhookVerifier::verify($body, $signature, self::SECRET));
    }

    public function test_verify_accepts_signature_without_prefix(): void
    {
        $body = '{"event":"payment.paid","amount":50000}';
        $signature = hash_hmac('sha256', $body, self::SECRET);

        $this->assertTrue(VarizaWebhookVerifier::verify($body, $signature, self::SECRET));
    }

    public function test_verify_returns_false_for_empty_signature(): void
    {
        $this->assertFalse(VarizaWebhookVerifier::verify('{"a":1}', '', self::SECRET));
    }

    public function test_assert_valid_throws_on_bad_signature(): void
    {
        $this->expectException(InvalidSignatureException::class);

        VarizaWebhookVerifier::assertValid('{"a":1}', 'sha256=invalid', self::SECRET);
    }
}
