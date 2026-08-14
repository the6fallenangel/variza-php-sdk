<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza;

final class PayLink
{
    public function __construct(
        public readonly string $slug,
        public readonly string $payUrl,
        public readonly int $amount,
        public readonly ?string $title,
        public readonly int $quantity,
        public readonly ?string $returnUrl,
        public readonly ?string $expiresAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            slug: (string) ($data['slug'] ?? ''),
            payUrl: (string) ($data['pay_url'] ?? ''),
            amount: (int) ($data['amount'] ?? 0),
            title: isset($data['title']) ? (string) $data['title'] : null,
            quantity: (int) ($data['quantity'] ?? 0),
            returnUrl: isset($data['return_url']) ? (string) $data['return_url'] : null,
            expiresAt: isset($data['expires_at']) ? (string) $data['expires_at'] : null,
        );
    }
}
