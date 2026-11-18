<?php
require __DIR__ . '/lib.php';
session_start();

if (isset($_GET['salir'])) { session_destroy(); header('Location: admin.php'); exit; }

$errorLogin = '';
if (isset($_POST['clave'])) {
    if (strlen($CFG['admin_password']) >= 6 && hash_equals($CFG['admin_password'], (string)$_POST['clave'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
        header('Location: admin.php'); exit;
    }
    sleep(1);
    $errorLogin = strlen($CFG['admin_password']) < 6
        ? 'Falta la contraseña: creá turnos-data/clave.txt (mínimo 6 caracteres).' : 'Contraseña incorrecta.';
}

if (empty($_SESSION['admin'])) { ?>
<!DOCTYPE html><html lang="es-AR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex"><title>Panel · Turnera</title>
<style>body{font-family:system-ui,sans-serif;background:#f7f3fa;display:grid;place-items:center;min-height:100vh;margin:0;color:#3b2d4a}
form{background:#fff;padding:28px;border-radius:16px;border:1px solid #e2d7ec;width:300px}
input,button{width:100%;font:inherit;padding:11px;border-radius:10px;border:1.5px solid #e2d7ec;margin-top:10px;box-sizing:border-box}
button{background:#6e4a96;color:#fff;border:0;font-weight:700;cursor:pointer}.e{color:#a3344f;font-size:.9rem}</style></head>
<body><form method="post"><b>Panel de turnos</b><input type="password" name="clave" placeholder="Contraseña" autofocus>
<button>Entrar</button><p class="e"><?= h($errorLogin) ?></p></form></body></html>
<?php exit; }

$pdo = db();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) { http_response_code(403); exit('Sesión vencida, recargá.'); }
    $a = $_POST['accion'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($a === 'confirmar') $pdo->prepare("UPDATE turnos SET estado='confirmado' WHERE id=?")->execute([$id]);
    if ($a === 'cancelar')  $pdo->prepare("UPDATE turnos SET estado='cancelado' WHERE id=?")->execute([$id]);
    if ($a === 'desbloquear') $pdo->prepare("DELETE FROM bloqueos WHERE id=?")->execute([$id]);
    if ($a === 'bloquear') {
        $f = $_POST['fecha'] ?? '';
        $todo = !empty($_POST['todo_dia']);
        $d = $todo ? '00:00' : ($_POST['desde'] ?? '');
        $hst = $todo ? '23:59' : ($_POST['hasta'] ?? '');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f) && preg_match('/^\d{2}:\d{2}$/', $d) && preg_match('/^\d{2}:\d{2}$/', $hst) && $d < $hst) {
            $pdo->prepare("INSERT INTO bloqueos (inicio, fin, motivo) VALUES (?,?,?)")
                ->execute(["$f $d", "$f $hst", mb_substr(trim($_POST['motivo'] ?? ''), 0, 80)]);
        } else $msg = 'Revisá la fecha y el horario del bloqueo.';
    }
    if (!$msg) { header('Location: admin.php'); exit; }
}

$desde = date('Y-m-d') . ' 00:00';
$turnos = $pdo->prepare("SELECT * FROM turnos WHERE inicio >= ? AND estado != 'cancelado' ORDER BY inicio");
$turnos->execute([$desde]);
$porDia = [];
foreach ($turnos as $t) $porDia[substr($t['inicio'], 0, 10)][] = $t;
$bloqueos = $pdo->prepare("SELECT * FROM bloqueos WHERE fin >= ? ORDER BY inicio");
$bloqueos->execute([$desde]);
$bloqueos = $bloqueos->fetchAll();
$pend = 0; foreach ($porDia as $l) foreach ($l as $t) if ($t['estado'] === 'pendiente') $pend++;
$csrf = h($_SESSION['csrf']);

function wa_cliente(string $tel): string {
    $n = preg_replace('/\D/', '', $tel);
    if (strlen($n) === 10) $n = '549' . $n;              // 11 2345 6789
    elseif (str_starts_with($n, '0')) $n = '549' . ltrim($n, '0');
    elseif (str_starts_with($n, '54') && !str_starts_with($n, '549')) $n = '549' . substr($n, 2);
    return 'https://wa.me/' . $n;
}
?>
<!DOCTYPE html>
<html lang="es-AR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex"><title>Panel · Turnera</title>
<style>
 body{font-family:system-ui,sans-serif;background:#f7f3fa;color:#3b2d4a;margin:0}
 .w{max-width:760px;margin:0 auto;padding:20px 14px 50px}
 h1{font-size:1.3rem;display:flex;justify-content:space-between;align-items:center}
 h1 a{font-size:.85rem;color:#6e4a96}
 h2{font-size:1rem;margin:26px 0 8px;color:#6e4a96;text-transform:capitalize}
 .t{background:#fff;border:1px solid #e2d7ec;border-left:5px solid #a98bc9;border-radius:12px;padding:12px 14px;margin-bottom:8px}
 .t.pendiente{border-left-color:#e0a03a}.t.confirmado{border-left-color:#4b9a6a}
 .top{display:flex;justify-content:space-between;gap:10px;font-weight:700}
 .tag{font-size:.75rem;padding:2px 8px;border-radius:20px;background:#fbefd9;color:#8a5a10;font-weight:700}
 .confirmado .tag{background:#dff1e6;color:#2d6a45}
 .info{font-size:.9rem;color:#6b5d79;margin-top:4px}
 .acc{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}
 .acc form{margin:0}
 button,.b{font:inherit;font-size:.85rem;padding:7px 12px;border-radius:9px;border:1.5px solid #e2d7ec;background:#fff;cursor:pointer;color:#3b2d4a;text-decoration:none}
 .ok{background:#4b9a6a;border-color:#4b9a6a;color:#fff}.wa{background:#25d366;border-color:#25d366;color:#fff}
 .card{background:#fff;border:1px solid #e2d7ec;border-radius:12px;padding:14px}
 .card input{font:inherit;padding:8px;border:1.5px solid #e2d7ec;border-radius:9px}
 .fila{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
 .msg{color:#a3344f;font-weight:600}
 .vacio{color:#7a6c88}
</style></head>
<body><div class="w">
<h1>Turnos <?= $pend ? "· <span class='tag'>$pend pendiente" . ($pend > 1 ? 's' : '') . "</span>" : '' ?> <a href="?salir">Salir</a></h1>
<?php if ($msg) echo "<p class='msg'>" . h($msg) . "</p>"; ?>

<?php if (!$porDia) echo "<p class='vacio'>No hay turnos próximos.</p>"; ?>
<?php foreach ($porDia as $dia => $lista): ?>
  <h2><?= h(fecha_larga($dia)) ?></h2>
  <?php foreach ($lista as $t): $s = $CFG['servicios'][$t['servicio']] ?? ['nombre' => $t['servicio']]; ?>
  <div class="t <?= h($t['estado']) ?>">
    <div class="top"><span><?= h(substr($t['inicio'], 11)) ?>–<?= h(substr($t['fin'], 11)) ?> · <?= h($t['nombre']) ?></span><span class="tag"><?= h($t['estado']) ?></span></div>
    <div class="info"><?= h($s['nombre']) ?> · <?= $t['modalidad'] === 'domicilio' ? 'A domicilio (' . h($t['barrio']) . ')' : 'En el espacio' ?>
      · <?= h(pesos(precio($t['servicio'], $t['modalidad']))) ?> · Tel. <?= h($t['telefono']) ?> · Pedido #<?= (int)$t['id'] ?>
      <?php if ($t['comentario']) echo '<br>“' . h($t['comentario']) . '”'; ?></div>
    <div class="acc">
      <a class="b wa" href="<?= h(wa_cliente($t['telefono'])) ?>" target="_blank" rel="noopener">WhatsApp</a>
      <?php if ($t['estado'] === 'pendiente'): ?>
      <form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="ok" name="accion" value="confirmar">Confirmar</button></form>
      <?php endif; ?>
      <form method="post" onsubmit="return confirm('¿Cancelar este turno? El horario vuelve a quedar libre.')"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button name="accion" value="cancelar">Cancelar</button></form>
    </div>
  </div>
  <?php endforeach; ?>
<?php endforeach; ?>

<h2>Bloquear horarios</h2>
<div class="card">
  <form method="post" class="fila">
    <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="accion" value="bloquear">
    <input type="date" name="fecha" required min="<?= date('Y-m-d') ?>">
    <input type="time" name="desde" value="10:00" step="1800"> a <input type="time" name="hasta" value="21:00" step="1800">
    <label><input type="checkbox" name="todo_dia" value="1"> Todo el día</label>
    <input name="motivo" placeholder="Motivo (opcional)" maxlength="80">
    <button class="ok">Bloquear</button>
  </form>
  <?php foreach ($bloqueos as $b): ?>
    <form method="post" class="fila" style="margin-top:10px">
      <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
      <span><?= h(fecha_larga(substr($b['inicio'], 0, 10))) ?>, <?= h(substr($b['inicio'], 11)) ?>–<?= h(substr($b['fin'], 11)) ?> <?= $b['motivo'] ? '· ' . h($b['motivo']) : '' ?></span>
      <button name="accion" value="desbloquear">Quitar</button>
    </form>
  <?php endforeach; ?>
</div>
</div></body></html>
