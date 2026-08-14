<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza\Tests;

use PHPUnit\Framework\TestCase;
use The6FallenAngel\Variza\PayLink;

final class PayLinkTest extends TestCase
{
    public function test_from_array_maps_response_fields(): void
    {
        $link = PayLink::fromArray([
            'slug' => 'abc123',
            'pay_url' => 'https://variza.ir/pay/abc123',
            'amount' => 50000,
            'title' => 'Order #123',
            'quantity' => 1,
            'return_url' => 'https://shop.example/return',
            'expires_at' => '2026-08-14T12:00:00+03:30',
        ]);

        $this->assertSame('abc123', $link->slug);
        $this->assertSame('https://variza.ir/pay/abc123', $link->payUrl);
        $this->assertSame(50000, $link->amount);
        $this->assertSame('Order #123', $link->title);
        $this->assertSame(1, $link->quantity);
        $this->assertSame('https://shop.example/return', $link->returnUrl);
        $this->assertSame('2026-08-14T12:00:00+03:30', $link->expiresAt);
    }

    public function test_from_array_tolerates_nullable_fields(): void
    {
        $link = PayLink::fromArray([
            'slug' => 'abc123',
            'pay_url' => 'https://variza.ir/pay/abc123',
            'amount' => 50000,
            'quantity' => 1,
        ]);

        $this->assertNull($link->title);
        $this->assertNull($link->returnUrl);
        $this->assertNull($link->expiresAt);
    }
}
