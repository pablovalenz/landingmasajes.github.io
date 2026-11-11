/**
 * Métricas de la web — Experiencia Sensitiva
 *
 * Para activar cada herramienta, completá su código entre las comillas:
 *   - GA_ID:      código de Google Analytics 4 (empieza con "G-")
 *   - CLARITY_ID: código del proyecto de Microsoft Clarity
 * Si un código queda vacío, esa herramienta no se carga.
 */
(function () {
  var GA_ID = "G-LP271GF55D";
  var CLARITY_ID = "";

  var CLAVE_AVISO = "es_aviso_cookies"; // "aceptado" | "rechazado"

  function leerEleccion() {
    try { return localStorage.getItem(CLAVE_AVISO); } catch (e) { return null; }
  }
  function guardarEleccion(valor) {
    try { localStorage.setItem(CLAVE_AVISO, valor); } catch (e) {}
  }

  // ---------- Google Analytics 4 ----------
  window.dataLayer = window.dataLayer || [];
  window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };

  function cargarAnalytics() {
    if (!GA_ID || window.__gaCargado) return;
    window.__gaCargado = true;
    var s = document.createElement("script");
    s.async = true;
    s.src = "https://www.googletagmanager.com/gtag/js?id=" + GA_ID;
    document.head.appendChild(s);
    gtag("js", new Date());
    gtag("config", GA_ID);
  }

  // ---------- Microsoft Clarity ----------
  function cargarClarity() {
    if (!CLARITY_ID || window.__clarityCargado) return;
    window.__clarityCargado = true;
    (function (c, l, a, r, i, t, y) {
      c[a] = c[a] || function () { (c[a].q = c[a].q || []).push(arguments); };
      t = l.createElement(r); t.async = 1; t.src = "https://www.clarity.ms/tag/" + i;
      y = l.getElementsByTagName(r)[0]; y.parentNode.insertBefore(t, y);
    })(window, document, "clarity", "script", CLARITY_ID);
  }

  function activarMetricas() {
    cargarAnalytics();
    cargarClarity();
  }

  // ---------- Clics en WhatsApp ----------
  // Registra cada clic a WhatsApp con el servicio y la sección desde donde se tocó.
  function textoLimpio(el) {
    return el ? el.textContent.replace(/\s+/g, " ").trim() : "";
  }

  document.addEventListener("click", function (ev) {
    var link = ev.target.closest ? ev.target.closest('a[href*="wa.me"]') : null;
    if (!link) return;

    var tarjeta = link.closest(".card");
    var servicio = tarjeta ? textoLimpio(tarjeta.querySelector(".title")) : "General";
    var seccion = link.closest("section, footer");
    var ubicacion = seccion ? (seccion.id || seccion.tagName.toLowerCase()) : "otra";

    if (window.gtag && window.__gaCargado) {
      gtag("event", "click_whatsapp", {
        servicio: servicio,
        ubicacion: ubicacion,
        transport_type: "beacon"
      });
    }
    if (window.clarity && window.__clarityCargado) {
      window.clarity("event", "click_whatsapp");
      window.clarity("set", "servicio", servicio);
    }
  }, true);

  // ---------- Aviso de cookies ----------
  function mostrarAviso() {
    var aviso = document.createElement("div");
    aviso.className = "aviso-cookies";
    aviso.setAttribute("role", "dialog");
    aviso.setAttribute("aria-label", "Aviso de cookies");
    aviso.innerHTML =
      '<p>Usamos cookies para saber cuántas personas visitan la web y mejorarla. ' +
      'No identificamos a nadie. <a href="privacidad.html">Más información</a></p>' +
      '<div class="aviso-cookies-botones">' +
      '<button type="button" class="aviso-rechazar">Rechazar</button>' +
      '<button type="button" class="aviso-aceptar">Aceptar</button>' +
      "</div>";
    document.body.appendChild(aviso);

    aviso.querySelector(".aviso-aceptar").addEventListener("click", function () {
      guardarEleccion("aceptado");
      aviso.remove();
    });
    aviso.querySelector(".aviso-rechazar").addEventListener("click", function () {
      guardarEleccion("rechazado");
      aviso.remove();
      // Recargar para que no quede nada midiendo en esta visita.
      if (window.__gaCargado || window.__clarityCargado) location.reload();
    });
  }

  // Las métricas se activan salvo que la persona haya elegido "Rechazar".
  var eleccion = leerEleccion();
  if (eleccion !== "rechazado") activarMetricas();

  function alCargar() {
    if (!eleccion) mostrarAviso();
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", alCargar);
  } else {
    alCargar();
  }
})();
