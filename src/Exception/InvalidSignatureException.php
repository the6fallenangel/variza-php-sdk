<?php

declare(strict_types=1);

namespace The6FallenAngel\Variza\Exception;

final class InvalidSignatureException extends VarizaException
{
    public function __construct(string $message = 'The webhook signature is invalid.')
    {
        parent::__construct($message);
    }
}
