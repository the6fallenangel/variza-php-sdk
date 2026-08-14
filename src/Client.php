<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza;

use The6FallenAngel\Variza\Exception\ApiException;
use The6FallenAngel\Variza\Exception\RateLimitException;
use The6FallenAngel\Variza\Exception\ValidationException;
use The6FallenAngel\Variza\Http\TransportFactory;
use The6FallenAngel\Variza\Http\TransportInterface;

final class Client
{
    public const BASE_URL = 'https://variza.ir/api/v1';

    private readonly TransportInterface $transport;

    public function __construct(
        private string $token,
        ?TransportInterface $transport = null,
    ) {
        $this->transport = $transport ?? TransportFactory::create();
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function pay(PayRequest $request): PayLink
    {
        $response = $this->transport->request(
            method: 'POST',
            url: self::BASE_URL.'/pay',
            headers: [
                'Authorization' => 'Bearer '.$this->token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            body: json_encode($request->toArray(), JSON_THROW_ON_ERROR),
        );

        if ($response->status === 201) {
            return PayLink::fromArray($this->decode($response->body));
        }

        throw $this->exceptionFor($response->status, $response->body);
    }

    /**
     * @throws \JsonException
     *
     * @return array<string, mixed>
     */
    private function decode(string $body): array
    {
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new ApiException(status: 0, message: 'Invalid JSON response from Variza API.');
        }

        return $data;
    }

    private function exceptionFor(int $status, string $body): ApiException
    {
        $message = 'Variza API request failed.';

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            $message = is_array($data) && isset($data['message'])
                ? (string) $data['message']
                : $message;
        } catch (\JsonException) {
            $data = null;
        }

        $errors = is_array($data) && isset($data['errors'])
            ? (array) $data['errors']
            : [];

        return match ($status) {
            422 => new ValidationException(status: $status, errors: $errors, responseBody: $body, message: $message),
            429 => new RateLimitException(status: $status, errors: $errors, responseBody: $body, message: $message),
            default => new ApiException(status: $status, errors: $errors, responseBody: $body, message: $message),
        };
    }
}