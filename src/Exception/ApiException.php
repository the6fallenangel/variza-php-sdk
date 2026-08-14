<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza\Exception;

final class ApiException extends VarizaException
{
    /**
     * @param array<string, mixed> $errors
     */
    public function __construct(
        public readonly int $status,
        public readonly array $errors = [],
        public readonly string $responseBody = '',
        string $message = '',
    ) {
        parent::__construct($message !== '' ? $message : "Variza API request failed with status {$status}", $status);
    }
}
