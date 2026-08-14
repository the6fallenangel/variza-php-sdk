<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza;

final class PaymentEvent
{
    public const EVENT_PAYMENT_PAID = 'payment.paid';

    public const STATUS_PAID = 'paid';

    public function __construct(
        public readonly string $event,
        public readonly string $slug,
        public readonly string $attemptCode,
        public readonly int $amount,
        public readonly string $status,
        public readonly string $sentAt,
    ) {}

    public function isPaymentPaid(): bool
    {
        return $this->event === self::EVENT_PAYMENT_PAID;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * @throws \JsonException
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return new self(
            event: (string) ($data['event'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            attemptCode: (string) ($data['attempt_code'] ?? ''),
            amount: (int) ($data['amount'] ?? 0),
            status: (string) ($data['status'] ?? ''),
            sentAt: (string) ($data['sent_at'] ?? ''),
        );
    }
}