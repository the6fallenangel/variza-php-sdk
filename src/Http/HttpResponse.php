<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza\Http;

/**
 * @phpstan-type HttpHeaders array<string, string>
 */
final class HttpResponse
{
    /**
     * @param  HttpHeaders  $headers
     */
    public function __construct(
        public readonly int $status,
        public readonly array $headers,
        public readonly string $body,
    ) {}
}
