<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza\Http;

use The6FallenAngel\Variza\Exception\VarizaException;

/**
 * @phpstan-type HttpHeaders array<string, string>
 */
final class CurlTransport implements TransportInterface
{
    public function request(string $method, string $url, array $headers, ?string $body): HttpResponse
    {
        $handle = curl_init($url);

        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->headerLines($headers),
            CURLOPT_TIMEOUT => 15,
        ]);

        if ($body !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
        }

        $rawBody = curl_exec($handle);

        if ($rawBody === false) {
            $error = curl_error($handle);
            curl_close($handle);

            throw new VarizaException('cURL request failed: '.$error);
        }

        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        return new HttpResponse($status, [], $rawBody);
    }

    /**
     * @param  HttpHeaders  $headers
     * @return list<string>
     */
    private function headerLines(array $headers): array
    {
        return array_map(
            static fn (string $name, string $value): string => $name.': '.$value,
            array_keys($headers),
            $headers,
        );
    }
}
