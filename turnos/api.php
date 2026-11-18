<?php
require __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function salir($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$accion = $_GET['accion'] ?? '';

// --- Días con al menos un horario libre ---
if ($accion === 'dias') {
    $servicio  = $_GET['servicio'] ?? '';
    $modalidad = ($_GET['modalidad'] ?? '') === 'domicilio' ? 'domicilio' : 'espacio';
    $out = [];
    for ($i = 0; $i <= $CFG['dias_a_mostrar']; $i++) {
        $f = date('Y-m-d', strtotime("+$i days"));
        $out[$f] = fecha_valida($f) ? count(horarios_libres($f, $servicio, $modalidad)) : -1;
    }
    salir(['dias' => $out]);
}

// --- Horarios libres de un día ---
if ($accion === 'horarios') {
    $fecha     = $_GET['fecha'] ?? '';
    $servicio  = $_GET['servicio'] ?? '';
    $modalidad = ($_GET['modalidad'] ?? '') === 'domicilio' ? 'domicilio' : 'espacio';
    salir(['horarios' => horarios_libres($fecha, $servicio, $modalidad)]);
}

// --- Pedir un turno ---
if ($accion === 'reservar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?: [];
    if (!empty($in['web'])) salir(['ok' => true, 'whatsapp' => '']); // trampa anti-spam

    $fecha      = (string)($in['fecha'] ?? '');
    $hora       = (string)($in['hora'] ?? '');
    $servicio   = (string)($in['servicio'] ?? '');
    $modalidad  = ($in['modalidad'] ?? '') === 'domicilio' ? 'domicilio' : 'espacio';
    $nombre     = trim(mb_substr((string)($in['nombre'] ?? ''), 0, 80));
    $telefono   = trim(mb_substr((string)($in['telefono'] ?? ''), 0, 30));
    $barrio     = trim(mb_substr((string)($in['barrio'] ?? ''), 0, 80));
    $comentario = trim(mb_substr((string)($in['comentario'] ?? ''), 0, 500));

    if (!isset($CFG['servicios'][$servicio])) salir(['error' => 'Elegí una sesión.'], 400);
    if ($nombre === '') salir(['error' => 'Dejanos tu nombre.'], 400);
    if (strlen(preg_replace('/\D/', '', $telefono)) < 8) salir(['error' => 'Revisá el número de WhatsApp.'], 400);
    if ($modalidad === 'domicilio' && $barrio === '') salir(['error' => 'Contanos en qué barrio de CABA.'], 400);
    if (!preg_match('/^\d{2}:\d{2}$/', $hora)) salir(['error' => 'Elegí un horario.'], 400);

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $pdo = db();
    $pdo->exec('BEGIN IMMEDIATE'); // evita que dos personas tomen el mismo horario a la vez
    try {
        $st = $pdo->prepare("SELECT COUNT(*) FROM turnos WHERE ip = ? AND estado = 'pendiente' AND creado >= ?");
        $st->execute([$ip, date('Y-m-d H:i', time() - 86400)]);
        if ($st->fetchColumn() >= 3) {
            $pdo->exec('ROLLBACK');
            salir(['error' => 'Ya tenés pedidos pendientes. Escribime por WhatsApp y lo coordinamos.'], 429);
        }
        if (!in_array($hora, horarios_libres($fecha, $servicio, $modalidad), true)) {
            $pdo->exec('ROLLBACK');
            salir(['error' => 'Ese horario se acaba de ocupar. Elegí otro, por favor.'], 409);
        }
        $inicio = "$fecha $hora";
        $fin = date('Y-m-d H:i', strtotime($inicio) + $CFG['servicios'][$servicio]['minutos'] * 60);
        $pdo->prepare("INSERT INTO turnos (inicio, fin, servicio, modalidad, nombre, telefono, barrio, comentario, ip, creado)
                       VALUES (?,?,?,?,?,?,?,?,?,?)")
            ->execute([$inicio, $fin, $servicio, $modalidad, $nombre, $telefono, $barrio, $comentario, $ip, date('Y-m-d H:i')]);
        $id = $pdo->lastInsertId();
        $pdo->exec('COMMIT');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->exec('ROLLBACK');
        salir(['error' => 'No pudimos guardar el pedido. Probá de nuevo.'], 500);
    }

    $s = $CFG['servicios'][$servicio];
    $msg = "Hola Pablo, soy $nombre. Quiero reservar:\n"
         . "• {$s['nombre']}\n"
         . "• " . ucfirst(fecha_larga($fecha)) . " a las $hora h\n"
         . "• " . ($modalidad === 'domicilio' ? "A domicilio ($barrio)" : 'En tu espacio de Palermo') . "\n"
         . "• Valor: " . pesos(precio($servicio, $modalidad)) . "\n"
         . ($comentario !== '' ? "Comentario: $comentario\n" : '')
         . "(Pedido #$id)";
    salir(['ok' => true, 'whatsapp' => 'https://wa.me/' . $CFG['whatsapp'] . '?text=' . rawurlencode($msg)]);
}

salir(['error' => 'Acción no válida'], 400);
