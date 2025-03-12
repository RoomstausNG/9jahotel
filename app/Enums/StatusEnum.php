<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StatusEnum: string implements HasLabel
{
    case Active = 'active';
    case Available = 'available';
    case Dirty = 'dirty';
    case Occupied = 'occupied';
    case Repair = 'Repair';
    case Paid = 'paid';
    case Unpaid = 'unpaid';
    case Checkout = 'checkout';

    public function getLabel(): ?string
    {
        return $this->name;

        // return match ($this) {
        //     self::Available => 'Available',
        //     self::Dirty => 'Dirty',
        //     self::Occupied => 'Occupied',
        //     self::Repair => 'Repair',
        // };
    }
}