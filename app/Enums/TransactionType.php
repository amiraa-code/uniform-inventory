<?php

namespace App\Enums;

enum TransactionType: string
{
    case In = 'in';
    case Out = 'out';
    case Allocated = 'allocated';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Stock In',
            self::Out => 'Stock Out',
            self::Allocated => 'Allocated',
        };
    }

    /**
     * Returns true for movement types that reduce stock.
     */
    public function isOutgoing(): bool
    {
        return $this !== self::In;
    }
}