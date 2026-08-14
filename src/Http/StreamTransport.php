<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza\Http;

use The6FallenAngel\Variza\Exception\VarizaException;

/**
 * @phpstan-type HttpHeaders array<string, string>
 */
final class StreamTransport implements TransportInterface
{
    public function request(string $method, string $url, array $headers, ?string $body): HttpResponse
    {
        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => $this->headerBlock($headers),
                'content' => $body ?? '',
                'ignore_errors' => true,
                'timeout' => 15,
            ],
        ]);

        $rawBody = @file_get_contents($url, false, $context);

        if ($rawBody === false) {
            throw new VarizaException('Stream request failed for '.$url);
        }

        $status = 0;
        foreach ($http_response_header ?? [] as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $matches) === 1) {
                $status = (int) $matches[1];
            }
        }

        return new HttpResponse($status, [], $rawBody);
    }

    /**
     * @param  HttpHeaders  $headers
     */
    private function headerBlock(array $headers): string
    {
        $lines = array_map(
            static fn (string $name, string $value): string => $name.': '.$value,
            array_keys($headers),
            $headers,
        );

        return implode("\r\n", $lines)."\r\n";
    }
}
