<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza;

final class PayRequest
{
    public function __construct(
        public readonly int $amount,
        public readonly string $returnUrl,
        public readonly ?string $title = null,
        public readonly ?string $cardLast4 = null,
        public readonly ?Expiry $expiresIn = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount,
            'title' => $this->title,
            'return_url' => $this->returnUrl,
            'card_last_4' => $this->cardLast4,
            'expires_in' => $this->expiresIn?->value,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
