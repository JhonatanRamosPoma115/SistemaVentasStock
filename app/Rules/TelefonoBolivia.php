<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TelefonoBolivia implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/\A(?:\+591 ?)?[0-9]{8}\z/', $value)) {
            $fail('Ingresa un teléfono de 8 dígitos. Puedes añadir +591 al inicio, por ejemplo: +591 70012345.');
        }
    }
}
