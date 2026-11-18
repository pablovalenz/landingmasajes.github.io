<?php
require __DIR__ . '/lib.php';
$servicios = [];
foreach ($CFG['servicios'] as $k => $s) {
    $servicios[] = ['id' => $k, 'nombre' => $s['nombre'], 'detalle' => $s['detalle'], 'precio' => $s['precio']];
}
?><!DOCTYPE html>
<html lang="es-AR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reservá tu sesión · Experiencia Sensitiva</title>
<meta name="description" content="Elegí el día y el horario de tu sesión de masaje holístico en Palermo o a domicilio en CABA.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Parisienne&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#f7f3fa; --card:#ffffff; --ink:#3b2d4a; --muted:#7a6c88;
    --lila:#a98bc9; --violeta:#6e4a96; --violeta-osc:#4f3270; --suave:#ece3f4; --linea:#e2d7ec;
  }
  *{box-sizing:border-box}
  body{margin:0;font-family:Nunito,system-ui,sans-serif;background:var(--bg);color:var(--ink);line-height:1.5}
  header{text-align:center;padding:36px 20px 18px;background:linear-gradient(180deg,#efe6f6,var(--bg))}
  header .marca{font-family:Parisienne,cursive;font-size:2.6rem;color:var(--violeta);margin:0;line-height:1.1}
  header p{margin:8px auto 0;max-width:460px;color:var(--muted)}
  main{max-width:640px;margin:0 auto;padding:0 16px 60px}
  .paso{background:var(--card);border:1px solid var(--linea);border-radius:18px;padding:20px;margin-top:16px}
  .paso h2{font-size:1.05rem;margin:0 0 14px;display:flex;gap:10px;align-items:center}
  .num{width:26px;height:26px;border-radius:50%;background:var(--violeta);color:#fff;display:inline-grid;place-items:center;font-size:.85rem;flex:none}
  .bloqueado{opacity:.45;pointer-events:none}
  .opciones{display:grid;gap:10px}
  .op{display:block;text-align:left;width:100%;border:1.5px solid var(--linea);background:#fff;border-radius:14px;padding:14px 16px;cursor:pointer;font:inherit;color:inherit;transition:.15s}
  .op:hover{border-color:var(--lila)}
  .op.sel{border-color:var(--violeta);background:var(--suave)}
  .op .t{font-weight:700;display:flex;justify-content:space-between;gap:10px}
  .op .t span:last-child{color:var(--violeta);white-space:nowrap}
  .op .d{font-size:.9rem;color:var(--muted);margin-top:2px}
  .modos{grid-template-columns:1fr 1fr}
  .dias{display:flex;gap:8px;overflow-x:auto;padding-bottom:6px;scroll-snap-type:x mandatory}
  .dia{flex:none;width:66px;scroll-snap-align:start;border:1.5px solid var(--linea);background:#fff;border-radius:14px;padding:8px 4px;text-align:center;cursor:pointer;font:inherit;color:inherit}
  .dia .ds{font-size:.72rem;text-transform:uppercase;color:var(--muted);letter-spacing:.04em}
  .dia .dn{font-size:1.35rem;font-weight:700;line-height:1.2}
  .dia .dm{font-size:.72rem;color:var(--muted)}
  .dia.sel{border-color:var(--violeta);background:var(--violeta);color:#fff}
  .dia.sel .ds,.dia.sel .dm{color:#efe6f6}
  .dia:disabled{opacity:.35;cursor:default;text-decoration:line-through}
  .horas{display:grid;grid-template-columns:repeat(auto-fill,minmax(82px,1fr));gap:8px;margin-top:14px}
  .hora{border:1.5px solid var(--linea);background:#fff;border-radius:12px;padding:10px 0;font:inherit;font-weight:700;cursor:pointer;color:var(--ink)}
  .hora:hover{border-color:var(--lila)}
  .hora.sel{background:var(--violeta);border-color:var(--violeta);color:#fff}
  .vacio{color:var(--muted);font-size:.92rem;margin:14px 0 0}
  label{display:block;font-weight:600;font-size:.92rem;margin:12px 0 4px}
  input,textarea{width:100%;font:inherit;padding:11px 13px;border:1.5px solid var(--linea);border-radius:12px;background:#fff;color:var(--ink)}
  input:focus,textarea:focus{outline:none;border-color:var(--lila)}
  textarea{min-height:76px;resize:vertical}
  .resumen{background:var(--suave);border-radius:14px;padding:14px 16px;margin-top:16px;font-size:.95rem}
  .resumen b{color:var(--violeta-osc)}
  .btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;margin-top:16px;border:0;border-radius:14px;padding:15px;font:inherit;font-weight:700;font-size:1.02rem;background:var(--violeta);color:#fff;cursor:pointer}
  .btn:hover{background:var(--violeta-osc)}
  .btn:disabled{opacity:.6;cursor:wait}
  .nota{font-size:.85rem;color:var(--muted);text-align:center;margin-top:10px}
  .error{color:#a3344f;font-weight:600;margin-top:10px;min-height:1em}
  .oculto{display:none}
  .trampa{position:absolute;left:-9999px}
  footer{text-align:center;color:var(--muted);font-size:.85rem;padding:0 16px 30px}
  footer a{color:var(--violeta)}
</style>
</head>
<body>
<header>
  <h1 class="marca">Masajes Holísticos</h1>
  <p>Elegí tu sesión, el día y el horario que mejor te quede. Es tu tiempo.</p>
</header>

<main>
  <section class="paso" id="p1">
    <h2><span class="num">1</span> ¿Qué sesión querés?</h2>
    <div class="opciones" id="servicios"></div>
  </section>

  <section class="paso bloqueado" id="p2">
    <h2><span class="num">2</span> ¿Dónde?</h2>
    <div class="opciones modos">
      <button type="button" class="op" data-modo="espacio"><div class="t">En mi espacio</div><div class="d">Palermo, zona Botánico</div></button>
      <button type="button" class="op" data-modo="domicilio"><div class="t">A domicilio</div><div class="d">Dentro de CABA · +<?= h(pesos($CFG['recargo_domicilio'])) ?></div></button>
    </div>
  </section>

  <section class="paso bloqueado" id="p3">
    <h2><span class="num">3</span> Elegí el día y el horario</h2>
    <div class="dias" id="dias"></div>
    <div class="horas" id="horas"></div>
    <p class="vacio oculto" id="sinHoras"></p>
  </section>

  <section class="paso bloqueado" id="p4">
    <h2><span class="num">4</span> Tus datos</h2>
    <label for="nombre">Nombre</label>
    <input id="nombre" autocomplete="given-name" maxlength="80">
    <label for="telefono">WhatsApp</label>
    <input id="telefono" type="tel" autocomplete="tel" placeholder="11 2345 6789" maxlength="30">
    <div id="campoBarrio" class="oculto">
      <label for="barrio">Barrio</label>
      <input id="barrio" placeholder="Ej.: Belgrano" maxlength="80">
    </div>
    <label for="comentario">¿Querés contarme algo? <span style="font-weight:400;color:var(--muted)">(opcional)</span></label>
    <textarea id="comentario" maxlength="500"></textarea>
    <input class="trampa" id="web" tabindex="-1" autocomplete="off" aria-hidden="true">

    <div class="resumen" id="resumen"></div>
    <button class="btn" id="enviar" type="button">Pedir turno por WhatsApp</button>
    <p class="error" id="error" role="alert"></p>
    <p class="nota">Tu horario queda reservado y se confirma por WhatsApp.<br>Ahí conversamos cualquier detalle antes de la sesión.</p>
  </section>
</main>

<footer>
  <a href="https://instagram.com/experienciasensitivamassage" target="_blank" rel="noopener">@experienciasensitivamassage</a>
</footer>

<script>
const SERVICIOS = <?= json_encode($servicios, JSON_UNESCAPED_UNICODE) ?>;
const RECARGO = <?= (int)$CFG['recargo_domicilio'] ?>;
const st = {servicio:null, modo:null, fecha:null, hora:null};
const $ = id => document.getElementById(id);
const pesos = n => '$' + n.toLocaleString('es-AR');
const DS = ['dom','lun','mar','mié','jue','vie','sáb'];
const MS = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
const DL = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
const ML = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
const fd = f => { const [y,m,d] = f.split('-').map(Number); return new Date(y, m-1, d); };

function habilitar(n){ for(let i=2;i<=4;i++) $('p'+i).classList.toggle('bloqueado', i>n); }

$('servicios').innerHTML = SERVICIOS.map(s =>
  `<button type="button" class="op" data-id="${s.id}"><div class="t"><span>${s.nombre}</span><span>${pesos(s.precio)}</span></div><div class="d">${s.detalle}</div></button>`).join('');

$('servicios').addEventListener('click', e => {
  const b = e.target.closest('.op'); if(!b) return;
  st.servicio = b.dataset.id;
  document.querySelectorAll('#servicios .op').forEach(x => x.classList.toggle('sel', x===b));
  habilitar(st.modo ? 3 : 2);
  if (st.modo) cargarDias();
  $('p2').scrollIntoView({behavior:'smooth', block:'start'});
});

document.querySelectorAll('[data-modo]').forEach(b => b.addEventListener('click', () => {
  st.modo = b.dataset.modo;
  document.querySelectorAll('[data-modo]').forEach(x => x.classList.toggle('sel', x===b));
  $('campoBarrio').classList.toggle('oculto', st.modo !== 'domicilio');
  habilitar(3); cargarDias();
  $('p3').scrollIntoView({behavior:'smooth', block:'start'});
}));

async function cargarDias(){
  st.hora = null; $('horas').innerHTML = ''; $('sinHoras').classList.add('oculto');
  $('dias').innerHTML = '<span class="vacio">Buscando días…</span>';
  const r = await fetch(`api.php?accion=dias&servicio=${st.servicio}&modalidad=${st.modo}`).then(r=>r.json());
  const fechas = Object.entries(r.dias).filter(([,n]) => n !== -1);
  $('dias').innerHTML = fechas.map(([f,n]) => { const d = fd(f);
    return `<button type="button" class="dia" data-f="${f}" ${n===0?'disabled':''}><div class="ds">${DS[d.getDay()]}</div><div class="dn">${d.getDate()}</div><div class="dm">${MS[d.getMonth()]}</div></button>`; }).join('');
  const prev = st.fecha && r.dias[st.fecha] > 0 ? st.fecha : (fechas.find(([,n]) => n>0)||[])[0];
  if (prev) elegirDia(prev); else { $('sinHoras').textContent = 'No hay horarios libres en los próximos días. Escribime por WhatsApp y lo vemos.'; $('sinHoras').classList.remove('oculto'); }
}

$('dias').addEventListener('click', e => { const b = e.target.closest('.dia'); if(b && !b.disabled) elegirDia(b.dataset.f); });

async function elegirDia(f){
  st.fecha = f; st.hora = null; habilitar(3);
  document.querySelectorAll('.dia').forEach(x => x.classList.toggle('sel', x.dataset.f===f));
  document.querySelector(`.dia[data-f="${f}"]`)?.scrollIntoView({behavior:'smooth', inline:'nearest', block:'nearest'});
  $('horas').innerHTML = '';
  const r = await fetch(`api.php?accion=horarios&fecha=${f}&servicio=${st.servicio}&modalidad=${st.modo}`).then(r=>r.json());
  $('horas').innerHTML = r.horarios.map(h => `<button type="button" class="hora" data-h="${h}">${h}</button>`).join('');
  $('sinHoras').classList.toggle('oculto', r.horarios.length > 0);
  $('sinHoras').textContent = 'Este día ya no tiene horarios libres. Probá con otro.';
}

$('horas').addEventListener('click', e => {
  const b = e.target.closest('.hora'); if(!b) return;
  st.hora = b.dataset.h;
  document.querySelectorAll('.hora').forEach(x => x.classList.toggle('sel', x===b));
  habilitar(4); resumen();
  $('p4').scrollIntoView({behavior:'smooth', block:'start'});
});

function resumen(){
  const s = SERVICIOS.find(x => x.id===st.servicio), d = fd(st.fecha);
  const total = s.precio + (st.modo==='domicilio' ? RECARGO : 0);
  $('resumen').innerHTML = `<b>${s.nombre}</b><br>${DL[d.getDay()]} ${d.getDate()} de ${ML[d.getMonth()]} · ${st.hora} h<br>${st.modo==='domicilio'?'A domicilio':'Palermo, zona Botánico'} · <b>${pesos(total)}</b>`;
}

$('enviar').addEventListener('click', async () => {
  $('error').textContent = '';
  const btn = $('enviar'); btn.disabled = true; btn.textContent = 'Reservando…';
  try {
    const r = await fetch('api.php?accion=reservar', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({
      ...st, modalidad: st.modo, nombre:$('nombre').value, telefono:$('telefono').value,
      barrio:$('barrio').value, comentario:$('comentario').value, web:$('web').value })});
    const j = await r.json();
    if (!r.ok) { $('error').textContent = j.error || 'Algo falló, probá de nuevo.'; if (r.status===409) elegirDia(st.fecha); return; }
    btn.textContent = 'Abriendo WhatsApp…';
    if (j.whatsapp) window.location.href = j.whatsapp;
  } catch { $('error').textContent = 'Sin conexión. Probá de nuevo.'; }
  finally { setTimeout(() => { btn.disabled = false; btn.textContent = 'Pedir turno por WhatsApp'; }, 1500); }
});
</script>
</body>
</html>
