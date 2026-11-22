<?php

namespace App\Core\Enums;

enum ProductType: string
{
    case FinishedGoods = 'fg';
    case RawMaterial = 'rm';
    case PackagingMaterial = 'pm';
    case WorkInProgress = 'wip';
    case OfficeSupplies = 'atk';
    case Spareparts = 'sparepart';

    public function label(): string
    {
        return match ($this) {
            self::FinishedGoods => 'Finished Goods (FG)',
            self::RawMaterial => 'Raw Material (RM)',
            self::PackagingMaterial => 'Packaging Material (PM)',
            self::WorkInProgress => 'Work In Progress (WIP)',
            self::OfficeSupplies => 'Office Supplies (ATK)',
            self::Spareparts => 'Machine & Spareparts',
        };
    }
}
