<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza\Tests;

use PHPUnit\Framework\TestCase;
use The6FallenAngel\Variza\Expiry;
use The6FallenAngel\Variza\PayRequest;

final class PayRequestTest extends TestCase
{
    public function test_to_array_includes_required_fields(): void
    {
        $request = new PayRequest(amount: 50000, returnUrl: 'https://shop.example/return');

        $this->assertSame([
            'amount' => 50000,
            'return_url' => 'https://shop.example/return',
        ], $request->toArray());
    }

    public function test_to_array_includes_optional_fields_when_set(): void
    {
        $request = new PayRequest(
            amount: 50000,
            returnUrl: 'https://shop.example/return',
            title: 'Order #123',
            cardLast4: '1234',
            expiresIn: Expiry::OneHour,
        );

        $this->assertSame([
            'amount' => 50000,
            'title' => 'Order #123',
            'return_url' => 'https://shop.example/return',
            'card_last_4' => '1234',
            'expires_in' => '1h',
        ], $request->toArray());
    }
}
