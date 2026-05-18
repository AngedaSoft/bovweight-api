<?php

namespace App\Services\Ml;

use RuntimeException;

class MlEstimacionException extends RuntimeException
{
    public function __construct(string $message, public readonly ?string $codigo = null, public readonly array $detalles = [])
    {
        parent::__construct($message);
    }
}
