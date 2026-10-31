<?php

namespace App\Services\Cards;

class InsufficientWalletBalance extends \DomainException
{
    public function __construct(public readonly string $wallet, public readonly float $available, public readonly float $required)
    {
        parent::__construct(sprintf(
            'Insufficient %s balance. Available: KES %s, required: KES %s.',
            $wallet,
            number_format($available, 2),
            number_format($required, 2)
        ));
    }
}
