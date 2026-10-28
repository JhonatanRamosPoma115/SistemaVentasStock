<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class EstadosPedido
{
    public const NORMALES = [
        'pendiente' => ['confirmado', 'cancelado'],
        'confirmado' => ['en_preparacion', 'cancelado'],
        'en_preparacion' => ['entregado', 'cancelado'],
        'entregado' => [], 'cancelado' => [],
    ];
    public const PERSONALIZADOS = [
        'pendiente_revision' => ['rechazado'],
        'cotizado' => ['rechazado'],
        'aprobado' => ['en_proceso'],
        'en_proceso' => ['entregado'],
        'entregado' => [], 'rechazado' => [],
    ];
    public const ETIQUETAS = [
        'pendiente' => 'Recibido', 'confirmado' => 'Confirmado', 'en_preparacion' => 'En preparación',
        'entregado' => 'Entregado', 'cancelado' => 'Cancelado', 'pendiente_revision' => 'En revisión',
        'cotizado' => 'Cotización disponible', 'aprobado' => 'Cotización aceptada', 'en_proceso' => 'En producción', 'rechazado' => 'Rechazado',
    ];

    public static function validar(string $actual, string $nuevo, bool $personalizado = false): void
    {
        $map = $personalizado ? self::PERSONALIZADOS : self::NORMALES;
        if (!array_key_exists($nuevo, $map) || ($actual !== $nuevo && !in_array($nuevo, $map[$actual] ?? [], true))) {
            throw ValidationException::withMessages(['estado' => 'Ese cambio de estado no está permitido.']);
        }
    }

    public static function historial(string $estado, string $usuario, ?string $nota = null): array
    {
        return ['estado' => $estado, 'fecha' => now()->toDateTimeString(), 'usuario_id' => $usuario, 'nota' => $nota];
    }
}
