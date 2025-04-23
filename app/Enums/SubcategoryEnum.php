<?php

namespace App\Enums;

enum SubcategoryEnum: string
{
    case SUB_01 = 'Coadyuvante';
    case SUB_02 = 'Equipos Agro';
    case SUB_03 = 'Bioestimulantes';
    case SUB_04 = 'Correctores nutricionales';
    case SUB_05 = 'Nutricional';
    case SUB_06 = 'Regulador de crecimiento';
    case SUB_07 = 'Feromona';
    case SUB_08 = 'Fugnicida';
    case SUB_09 = 'Herbicida';
    case SUB_10 = 'Insecticida';

    public function category(): int
    {
        return match($this){
            self::SUB_01, self::SUB_02 => 1,
            self::SUB_03, self::SUB_04, self::SUB_05, self::SUB_06 => 2,
            self::SUB_07, self::SUB_08, self::SUB_09, self::SUB_10 => 3,
        };
    }
}
