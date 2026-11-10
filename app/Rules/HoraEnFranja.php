<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HoraEnFranja implements ValidationRule
{
    public function __construct(private mixed $franja) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($this->franja, config('tienda.franjas'), true)) {
            return;
        }
        [$inicio, $fin] = explode('-', $this->franja);
        if (!is_string($value) || $value < $inicio || $value > $fin) {
            $fail("Elige una hora entre {$inicio} y {$fin}, en horario de La Paz.");
        }
    }
}
