<?php

namespace App\Enums;

enum RoomStatus: string
{
    case PENDING = 'Pending';
    case FAILED = 'failed';
    case COMPLETED = 'completed';

    /**
     * Retourne toutes les valeurs possibles de l'enum
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}