<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Producto;
use App\Models\Diseno;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Usuario Administrador por defecto
        if (!User::where('correo', 'admin@emunah.com')->exists()) {
            User::create([
                'nombre'     => 'Administrador General',
                'correo'     => 'admin@emunah.com',
                'contraseña' => Hash::make('admin123'),
                'rol'        => 'admin',
            ]);
        }

        // 2. Usuario Cliente de prueba
        if (!User::where('correo', 'cliente@gmail.com')->exists()) {
            User::create([
                'nombre'     => 'Juan Pérez',
                'correo'     => 'cliente@gmail.com',
                'contraseña' => Hash::make('cliente123'),
                'rol'        => 'cliente',
            ]);
        }

        // 3. Productos muestra si la colección está vacía
        if (Producto::count() === 0) {
            Producto::create([
                'nombre_producto' => 'Polera Fe Inquebrantable',
                'descripcion'     => "Diseño inspirado en Hebreos 11:1.\nEstampado DTF de alta resistencia sobre algodón 100%.",
                'talla'           => 'M',
                'color'           => 'Negro',
                'precio'          => 80.00,
                'stock'           => 15,
                'imagen'          => null,
            ]);

            Producto::create([
                'nombre_producto' => 'Polera Todo lo Puedo en Cristo',
                'descripcion'     => "Versículo Filipenses 4:13 en tipografía moderna.\nIdeal para jóvenes y grupos de jóvenes.",
                'talla'           => 'L',
                'color'           => 'Blanco',
                'precio'          => 85.00,
                'stock'           => 20,
                'imagen'          => null,
            ]);

            Producto::create([
                'nombre_producto' => 'Polera Gracia y Verdad',
                'descripcion'     => "Mensaje basado en Juan 1:14.\nDiseño minimalista en serigrafía de precisión.",
                'talla'           => 'M',
                'color'           => 'Azul',
                'precio'          => 75.00,
                'stock'           => 8,
                'imagen'          => null,
            ]);

            Producto::create([
                'nombre_producto' => 'Polera León de Judá',
                'descripcion'     => "Ilustración artística del León de Judá (Apocalipsis 5:5).\nImpresión premium a todo color.",
                'talla'           => 'XL',
                'color'           => 'Gris',
                'precio'          => 90.00,
                'stock'           => 3, // Stock bajo para activar alerta en dashboard
                'imagen'          => null,
            ]);
        }

        // 4. Diseños predefinidos para la galería
        if (Diseno::count() === 0) {
            Diseno::create([
                'nombre'      => 'Cruz y Esperanza',
                'versiculo'   => '1 Corintios 1:18',
                'descripcion' => 'Diseño geométrico de la cruz con tipografía sutil.',
                'categoria'   => 'diseños',
                'imagen'      => null,
                'activo'      => true,
                'fecha'       => now()->toDateString(),
            ]);

            Diseno::create([
                'nombre'      => 'El Señor es mi Pastor',
                'versiculo'   => 'Salmo 23:1',
                'descripcion' => 'Frase bíblica en caligrafía artística.',
                'categoria'   => 'mensajes',
                'imagen'      => null,
                'activo'      => true,
                'fecha'       => now()->toDateString(),
            ]);
        }
    }
}
