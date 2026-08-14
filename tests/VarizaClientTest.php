<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza\Tests;

use PHPUnit\Framework\TestCase;
use The6FallenAngel\Variza\VarizaClient;
use The6FallenAngel\Variza\Exception\ApiException;
use The6FallenAngel\Variza\Exception\RateLimitException;
use The6FallenAngel\Variza\Exception\ValidationException;
use The6FallenAngel\Variza\Http\HttpResponse;
use The6FallenAngel\Variza\Http\TransportInterface;
use The6FallenAngel\Variza\PayRequest;

final class VarizaClientTest extends TestCase
{
    public function test_pay_returns_pay_link_on_201(): void
    {
        $transport = new StubTransport(201, json_encode([
            'slug' => 'abc123',
            'pay_url' => 'https://variza.ir/pay/abc123',
            'amount' => 50000,
            'title' => 'Order #123',
            'quantity' => 1,
            'return_url' => 'https://shop.example/return',
            'expires_at' => '2026-08-14T12:00:00+03:30',
        ], JSON_THROW_ON_ERROR));

        $client = new VarizaClient(token: 'token-1', transport: $transport);
        $link = $client->pay(new PayRequest(amount: 50000, returnUrl: 'https://shop.example/return'));

        $this->assertSame('abc123', $link->slug);
        $this->assertSame('POST', $transport->lastMethod);
        $this->assertSame('https://variza.ir/api/v1/pay', $transport->lastUrl);
        $this->assertSame('Bearer token-1', $transport->lastHeaders['Authorization']);
    }

    public function test_pay_sends_request_body(): void
    {
        $transport = new StubTransport(201, json_encode([
            'slug' => 's',
            'pay_url' => 'https://variza.ir/pay/s',
            'amount' => 10000,
            'quantity' => 1,
        ], JSON_THROW_ON_ERROR));

        $client = new VarizaClient(token: 'token-1', transport: $transport);
        $client->pay(new PayRequest(amount: 10000, returnUrl: 'https://shop.example/return', title: 'T'));

        $body = json_decode($transport->lastBody, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(['amount' => 10000, 'title' => 'T', 'return_url' => 'https://shop.example/return'], $body);
    }

    public function test_pay_throws_validation_exception_on_422(): void
    {
        $transport = new StubTransport(422, json_encode([
            'message' => 'مبلغ الزامی است.',
            'errors' => ['amount' => ['مبلغ الزامی است.']],
        ], JSON_THROW_ON_ERROR));

        $client = new VarizaClient(token: 'token-1', transport: $transport);

        try {
            $client->pay(new PayRequest(amount: 100, returnUrl: 'https://shop.example/return'));
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(422, $e->status);
            $this->assertSame('مبلغ الزامی است.', $e->getMessage());
            $this->assertSame(['amount' => ['مبلغ الزامی است.']], $e->errors);
        }
    }

    public function test_pay_throws_rate_limit_exception_on_429(): void
    {
        $transport = new StubTransport(429, json_encode(['message' => 'Too many requests.'], JSON_THROW_ON_ERROR));

        $client = new VarizaClient(token: 'token-1', transport: $transport);

        $this->expectException(RateLimitException::class);
        $this->expectExceptionCode(429);

        $client->pay(new PayRequest(amount: 50000, returnUrl: 'https://shop.example/return'));
    }

    public function test_pay_throws_generic_api_exception_on_500(): void
    {
        $transport = new StubTransport(500, 'Internal Server Error');

        $client = new VarizaClient(token: 'token-1', transport: $transport);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(500);

        $client->pay(new PayRequest(amount: 50000, returnUrl: 'https://shop.example/return'));
    }
}

final class StubTransport implements TransportInterface
{
    public ?string $lastMethod = null;

    public ?string $lastUrl = null;

    /** @var array<string, string> */
    public array $lastHeaders = [];

    public ?string $lastBody = null;

    public function __construct(
        private readonly int $status,
        private readonly string $body,
    ) {}

    public function request(string $method, string $url, array $headers, ?string $body): HttpResponse
    {
        $this->lastMethod = $method;
        $this->lastUrl = $url;
        $this->lastHeaders = $headers;
        $this->lastBody = $body;

        return new HttpResponse($this->status, [], $this->body);
    }
}
