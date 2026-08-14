<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza\Http;

final class TransportFactory
{
    public static function create(): TransportInterface
    {
        return function_exists('curl_init')
            ? new CurlTransport()
            : new StreamTransport();
    }
}
