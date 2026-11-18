<?php
// Lógica compartida: base de datos y cálculo de horarios libres.

$CFG = require __DIR__ . '/config.php';

function db(): PDO {
    global $CFG;
    static $pdo = null;
    if ($pdo) return $pdo;
    $dir = dirname($CFG['db_path']);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $ht = $dir . '/.htaccess';
    if (!file_exists($ht)) file_put_contents($ht, "Require all denied\nDeny from all\n");
    $pdo = new PDO('sqlite:' . $CFG['db_path']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA busy_timeout = 5000');
    $pdo->exec("CREATE TABLE IF NOT EXISTS turnos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        inicio TEXT NOT NULL,          -- 'YYYY-MM-DD HH:MM'
        fin TEXT NOT NULL,
        servicio TEXT NOT NULL,
        modalidad TEXT NOT NULL,       -- 'espacio' | 'domicilio'
        nombre TEXT NOT NULL,
        telefono TEXT NOT NULL,
        barrio TEXT,
        comentario TEXT,
        estado TEXT NOT NULL DEFAULT 'pendiente', -- pendiente | confirmado | cancelado
        ip TEXT,
        creado TEXT NOT NULL
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS bloqueos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        inicio TEXT NOT NULL,
        fin TEXT NOT NULL,
        motivo TEXT
    )");
    return $pdo;
}

function ts(string $fechaHora): int {
    return (new DateTime($fechaHora))->getTimestamp();
}

// Intervalo que ocupa una sesión en la agenda, con su descanso.
function ocupado(string $inicio, string $fin, string $modalidad): array {
    global $CFG;
    $s = ts($inicio); $e = ts($fin);
    if ($modalidad === 'domicilio') {
        $b = $CFG['descanso_domicilio'] * 60;
        return [$s - $b, $e + $b];
    }
    return [$s, $e + $CFG['descanso_espacio'] * 60];
}

// Devuelve los intervalos [desde, hasta] ya tomados en una fecha (y alrededores).
function intervalos_tomados(string $fecha): array {
    $pdo = db();
    $desde = date('Y-m-d', strtotime("$fecha -1 day")) . ' 00:00';
    $hasta = date('Y-m-d', strtotime("$fecha +1 day")) . ' 23:59';
    $res = [];
    $st = $pdo->prepare("SELECT inicio, fin, modalidad FROM turnos
        WHERE estado != 'cancelado' AND inicio <= ? AND fin >= ?");
    $st->execute([$hasta, $desde]);
    foreach ($st as $r) $res[] = ocupado($r['inicio'], $r['fin'], $r['modalidad']);
    $st = $pdo->prepare("SELECT inicio, fin FROM bloqueos WHERE inicio <= ? AND fin >= ?");
    $st->execute([$hasta, $desde]);
    foreach ($st as $r) $res[] = [ts($r['inicio']), ts($r['fin'])];
    return $res;
}

function fecha_valida(string $fecha): bool {
    global $CFG;
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$d || $d->format('Y-m-d') !== $fecha) return false;
    if (!in_array((int)$d->format('N'), $CFG['dias'], true)) return false;
    $hoy = date('Y-m-d');
    $max = date('Y-m-d', strtotime("+{$CFG['dias_a_mostrar']} days"));
    return $fecha >= $hoy && $fecha <= $max;
}

// Horarios de inicio libres para un servicio + modalidad en una fecha.
function horarios_libres(string $fecha, string $servicio, string $modalidad): array {
    global $CFG;
    if (!fecha_valida($fecha) || !isset($CFG['servicios'][$servicio])) return [];
    $dur = $CFG['servicios'][$servicio]['minutos'] * 60;
    $tomados = intervalos_tomados($fecha);
    $t   = ts("$fecha {$CFG['hora_inicio']}");
    $fin = ts("$fecha {$CFG['hora_fin']}");
    $min = time() + $CFG['anticipacion_min'] * 60;
    $libres = [];
    for (; $t + $dur <= $fin; $t += $CFG['paso_minutos'] * 60) {
        if ($t < $min) continue;
        [$a, $b] = ocupado(date('Y-m-d H:i', $t), date('Y-m-d H:i', $t + $dur), $modalidad);
        $choca = false;
        foreach ($tomados as [$x, $y]) {
            if ($a < $y && $x < $b) { $choca = true; break; }
        }
        if (!$choca) $libres[] = date('H:i', $t);
    }
    return $libres;
}

function precio(string $servicio, string $modalidad): int {
    global $CFG;
    return $CFG['servicios'][$servicio]['precio'] + ($modalidad === 'domicilio' ? $CFG['recargo_domicilio'] : 0);
}

function pesos(int $n): string {
    return '$' . number_format($n, 0, ',', '.');
}

function fecha_larga(string $fecha): string {
    $dias = [1=>'lunes','martes','miércoles','jueves','viernes','sábado','domingo'];
    $meses = [1=>'enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $t = strtotime($fecha);
    return $dias[(int)date('N', $t)] . ' ' . date('j', $t) . ' de ' . $meses[(int)date('n', $t)];
}

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
