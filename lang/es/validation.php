<?php

return [
    'required' => 'El campo :attribute es obligatorio.', 'present' => 'Falta el campo :attribute.',
    'string' => 'El campo :attribute debe ser texto.', 'email' => 'Ingresa un correo válido.',
    'integer' => 'El campo :attribute debe ser un número entero.', 'numeric' => 'El campo :attribute debe ser un número.',
    'boolean' => 'El valor de :attribute no es válido.', 'in' => 'Selecciona un valor válido para :attribute.',
    'array' => 'La lista de :attribute no es válida.', 'regex' => 'El formato de :attribute no es válido.',
    'image' => 'El archivo :attribute debe ser una imagen.', 'file' => 'El campo :attribute debe ser un archivo.',
    'mimes' => 'El archivo :attribute debe ser de tipo: :values.', 'uploaded' => 'No se pudo subir :attribute. Revisa su tamaño.',
    'json' => 'Los datos de :attribute no son válidos.', 'date' => 'Ingresa una fecha válida para :attribute.',
    'date_format' => 'El campo :attribute debe tener formato :format.',
    'after_or_equal' => ':attribute debe ser igual o posterior a :date.',
    'before_or_equal' => ':attribute debe ser igual o anterior a :date.',
    'confirmed' => 'Las contraseñas no coinciden.', 'unique' => 'Ese :attribute ya está registrado.',
    'decimal' => ':attribute debe tener entre :min y :max decimales.',
    'min' => ['numeric' => ':attribute debe ser como mínimo :min.', 'string' => ':attribute debe tener al menos :min caracteres.', 'array' => 'Agrega al menos :min elementos en :attribute.', 'file' => ':attribute debe pesar al menos :min KB.'],
    'max' => ['numeric' => ':attribute no debe superar :max.', 'string' => ':attribute no debe superar :max caracteres.', 'array' => ':attribute admite hasta :max elementos.', 'file' => ':attribute no debe pesar más de :max KB.'],
    'between' => ['numeric' => ':attribute debe estar entre :min y :max.', 'string' => ':attribute debe tener entre :min y :max caracteres.', 'array' => ':attribute debe tener entre :min y :max elementos.', 'file' => ':attribute debe pesar entre :min y :max KB.'],
    'attributes' => [
        'nombre' => 'nombre', 'correo' => 'correo', 'contraseña' => 'contraseña', 'nombre_producto' => 'nombre del producto',
        'variantes' => 'variantes', 'variantes.*.talla' => 'talla', 'variantes.*.color' => 'color', 'variantes.*.stock' => 'stock',
        'telefono' => 'teléfono', 'direccion_entrega' => 'dirección', 'precio' => 'precio', 'cantidad' => 'cantidad',
        'total' => 'total', 'detalle' => 'detalle', 'vence' => 'validez de la cotización', 'entrega_estimada' => 'entrega estimada',
        'para_cuando' => 'fecha requerida', 'color_prenda' => 'color', 'descripcion_diseno' => 'descripción del diseño',
        'tamano_diseno' => 'tamaño del diseño', 'imagenes_diseno' => 'diseños', 'imagenes_diseno.*' => 'imagen del diseño',
        'imagenes.*' => 'foto del producto', 'checkout_token' => 'confirmación del pedido', 'metodo_pago' => 'forma de pago',
    ],
];
