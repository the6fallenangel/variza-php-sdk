<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza\Http;

/**
 * @phpstan-type HttpHeaders array<string, string>
 */
interface TransportInterface
{
    /**
     * @param  HttpHeaders  $headers
     */
    public function request(string $method, string $url, array $headers, ?string $body): HttpResponse;
}
