<?php
// =====================================================
//  CONFIGURACIÓN DE LA TURNERA — Experiencia Sensitiva
//  Todo lo que quieras cambiar está en este archivo.
// =====================================================

date_default_timezone_set('America/Argentina/Buenos_Aires');

return [
    // Número de WhatsApp en formato internacional, sin "+", espacios ni guiones.
    // Ejemplo Argentina celular: 5491112345678
    'whatsapp' => '5491130626922',

    // La contraseña del panel (turnos/admin.php) NO va acá, porque este código es público.
    // Se escribe en el archivo turnos-data/clave.txt (fuera de public_html, en Hostinger).
    'admin_password' => trim((string)@file_get_contents(dirname(__DIR__, 2) . '/turnos-data/clave.txt')),

    // Días de atención: 1 = lunes ... 7 = domingo
    'dias' => [1, 2, 3, 4, 5, 6],
    'hora_inicio' => '10:00',   // primer turno posible
    'hora_fin'    => '21:00',   // la sesión tiene que terminar a esta hora como máximo

    'paso_minutos'      => 30,  // cada cuánto se ofrece un horario (10:00, 10:30, 11:00...)
    'anticipacion_min'  => 120, // no se puede reservar con menos de 2 h de anticipación
    'dias_a_mostrar'    => 30,  // cuántos días hacia adelante se pueden ver

    // Descanso que se deja libre después de cada sesión.
    // A domicilio se deja antes y después (para el traslado).
    'descanso_espacio'   => 30,
    'descanso_domicilio' => 60,

    'recargo_domicilio' => 20000,

    // "minutos" = tiempo que se bloquea en la agenda (incluye la charla previa).
    'servicios' => [
        'mini' => [
            'nombre'  => 'Mini sesión',
            'detalle' => '60 min · Una pausa para soltar tensiones y descubrir la experiencia.',
            'minutos' => 60,
            'precio'  => 45000,
        ],
        'holistico' => [
            'nombre'  => 'Masaje holístico sensitivo',
            'detalle' => '90 min de masaje · 2 h en total con la charla previa. La experiencia esencial.',
            'minutos' => 120,
            'precio'  => 60000,
        ],
        'viaje' => [
            'nombre'  => 'Viaje expansivo',
            'detalle' => '120 min · Más movimientos, más pausas, más tiempo para soltar.',
            'minutos' => 120,
            'precio'  => 70000,
        ],
        'full' => [
            'nombre'  => 'Full Sensaciones',
            'detalle' => '120 min · La experiencia más completa e intensa. Los detalles los conversamos por mensaje privado.',
            'minutos' => 120,
            'precio'  => 85000,
        ],
    ],

    // Dónde se guarda la base de datos: fuera de public_html, para que no sea
    // accesible desde la web ni se borre al publicar el sitio desde Git.
    'db_path' => dirname(__DIR__, 2) . '/turnos-data/turnos.sqlite',
];
