<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class PeriodoSemanal
{
    public static function limites(?string $fecha = null): array
    {
        $dia = $fecha ? CarbonImmutable::createFromFormat('!Y-m-d', $fecha, 'America/La_Paz') : CarbonImmutable::now('America/La_Paz');
        $inicio = $dia->startOfWeek(CarbonImmutable::MONDAY)->startOfDay();
        return [$inicio, $inicio->addWeek()];
    }
}
