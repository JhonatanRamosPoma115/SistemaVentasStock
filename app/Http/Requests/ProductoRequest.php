<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->rol === 'admin'; }
    public function rules(): array
    {
        return [
            'nombre_producto' => 'required|string|max:150', 'descripcion' => 'nullable|string|max:3000',
            'composicion' => 'nullable|string|max:300', 'cuidados' => 'nullable|string|max:1000',
            'precio' => 'required|numeric|min:0.01|max:99999.99|decimal:0,2',
            'variantes' => 'required|array|min:1|max:50',
            'variantes.*' => 'array:id,talla,color,stock',
            'variantes.*.id' => 'nullable|string|max:50',
            'variantes.*.talla' => 'required|in:XS,S,M,L,XL,XXL,3XL',
            'variantes.*.color' => 'required|string|max:50',
            'variantes.*.stock' => 'required|integer|min:0|max:99999',
            'revision' => 'required|integer|min:0', 'activo' => 'required|boolean',
            'motivo_stock' => 'nullable|string|max:500',
            'imagenes' => 'nullable|array|max:5',
            'imagenes.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5048',
        ];
    }
    public function messages(): array
    {
        return ['variantes.required' => 'Agrega al menos una talla y color.', 'variantes.*.color.required' => 'Cada variante necesita un color.', 'variantes.*.stock.integer' => 'El stock debe ser un número entero.', 'precio.min' => 'El precio debe ser mayor que cero.'];
    }
}
