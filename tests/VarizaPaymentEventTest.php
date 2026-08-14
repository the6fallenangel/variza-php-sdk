<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza\Tests;

use PHPUnit\Framework\TestCase;
use The6FallenAngel\Variza\VarizaPaymentEvent;

final class VarizaPaymentEventTest extends TestCase
{
    public function test_from_json_maps_webhook_payload(): void
    {
        $event = VarizaPaymentEvent::fromJson(
            '{"event":"payment.paid","slug":"abc123","attempt_code":"AT-1","amount":50000,"status":"paid","sent_at":"2026-08-14T12:00:00+03:30"}'
        );

        $this->assertSame('payment.paid', $event->event);
        $this->assertSame('abc123', $event->slug);
        $this->assertSame('AT-1', $event->attemptCode);
        $this->assertSame(50000, $event->amount);
        $this->assertSame('paid', $event->status);
        $this->assertSame('2026-08-14T12:00:00+03:30', $event->sentAt);
    }

    public function test_is_payment_paid(): void
    {
        $this->assertTrue(VarizaPaymentEvent::fromJson('{"event":"payment.paid","slug":"x","attempt_code":"1","amount":1000,"status":"paid","sent_at":"2026-08-14T12:00:00+03:30"}')->isPaymentPaid());
        $this->assertFalse(VarizaPaymentEvent::fromJson('{"event":"other","slug":"x","attempt_code":"1","amount":1000,"status":"paid","sent_at":"2026-08-14T12:00:00+03:30"}')->isPaymentPaid());
    }
}
