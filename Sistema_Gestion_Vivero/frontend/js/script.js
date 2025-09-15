'use strict';

// Backend API base (Hostinger shared)
const API_BASE = 'https://im-ventas-de-computadoras.com/SISTEMA-DE-GESTION-DE-VIVERO-FORESTAL-BOSQUES-DEL-SUR-/Sistema_Gestion_Vivero/backend/php/api';

async function api(path, options = {}) {
  const url = `${API_BASE}/${path}`;
  const opts = { headers: { 'Content-Type': 'application/json' }, ...options };
  if (opts.body && typeof opts.body !== 'string') opts.body = JSON.stringify(opts.body);
  const res = await fetch(url, opts);
  const data = await res.json().catch(() => ({}));
  if (!res.ok || data.error) throw new Error(data.error || `Error ${res.status}`);
  return data;
}

const state = { catalogs: {}, especies: [], lotes: [] };

function fillSelect(el, items, opts = {}) {
  el.innerHTML = '';
  if (opts.placeholder) {
    const o = document.createElement('option'); o.value = ''; o.textContent = opts.placeholder; el.appendChild(o);
  }
  for (const it of items) {
    const o = document.createElement('option');
    o.value = it[opts.value || 'id'];
    o.textContent = it[opts.label || 'nombre'];
    el.appendChild(o);
  }
}

async function loadCatalogs(seed = false) {
  const data = await api(`catalogs.php${seed ? '?seed=1' : ''}`);
  state.catalogs = data.catalogs || {};
  // Fill selects depending on catalogs
  const tipoEspecie = document.getElementById('tipo-especie');
  if (tipoEspecie) fillSelect(tipoEspecie, state.catalogs.tipo_especie || []);
  const ifFase = document.getElementById('if-fase');
  if (ifFase) fillSelect(ifFase, state.catalogs.fases_produccion || []);
  ['if-ubicacion','co-ubicacion','pl-ubicacion'].forEach(id => {
    const el = document.getElementById(id); if (el) fillSelect(el, state.catalogs.ubicaciones || [], { label: 'sector' });
  });
  const plSalud = document.getElementById('pl-salud');
  if (plSalud) fillSelect(plSalud, state.catalogs.estado_salud || []);
  const invCal = document.getElementById('inv-calidad');
  if (invCal) fillSelect(invCal, state.catalogs.clasificaciones_calidad || []);
  const invTam = document.getElementById('inv-tamano');
  if (invTam) fillSelect(invTam, state.catalogs.tamanos_plantas || [], { label: 'codigo' });
  const olEstado = document.getElementById('ol-estado');
  if (olEstado) fillSelect(olEstado, state.catalogs.estado_salud || []);
  const trTipo = document.getElementById('tr-tipo');
  if (trTipo) fillSelect(trTipo, state.catalogs.tipos_tratamiento || []);
}

async function listEspecies() {
  const data = await api('especies.php');
  state.especies = data.data || [];
  const tbody = document.getElementById('tabla-especies');
  tbody.innerHTML = state.especies.map(e => `<tr><td>${e.id}</td><td>${e.nombre_comun}</td><td>${e.nombre_cientifico}</td><td>${e.tipo_especie}</td></tr>`).join('');
  const lpEsp = document.getElementById('lp-especie'); if (lpEsp) fillSelect(lpEsp, state.especies, { label: 'nombre_comun' });
}

async function listLotes() {
  const data = await api('lotes.php');
  state.lotes = data.data || [];
  const tbody = document.getElementById('tabla-lotes');
  if (tbody) tbody.innerHTML = state.lotes.map(l => `<tr><td>${l.codigo}</td><td>${l.especie}</td><td>${l.fecha_siembra}</td><td>${l.cantidad_semillas_usadas}</td><td>${l.proveedor}</td></tr>`).join('');
  const lotesSelIds = ['if-lote','cf-lote','hist-lote','pl-lote'];
  lotesSelIds.forEach(id => { const el = document.getElementById(id); if (el) fillSelect(el, state.lotes, { value: 'id', label: 'codigo' }); });
}

async function listFasesByLote(loteId) {
  if (!loteId) return [];
  const data = await api(`fases.php?action=by_lote&lote_id=${encodeURIComponent(loteId)}`);
  const rows = data.data || [];
  const tbody = document.getElementById('tabla-fases');
  if (tbody && document.getElementById('hist-lote').value == loteId) {
    tbody.innerHTML = rows.map(r => `<tr><td>${r.fase_nombre}</td><td>${r.estado}</td><td>${r.fecha_inicio ?? ''}</td><td>${r.fecha_fin ?? ''}</td><td>${r.stock_inicial}</td><td>${r.stock_disponible}</td></tr>`).join('');
  }
  return rows;
}

async function listPlantas(limit = 10) {
  const data = await api('plantas.php');
  const rows = (data.data || []).slice(0, limit);
  const tbody = document.getElementById('tabla-plantas');
  if (tbody) tbody.innerHTML = rows.map(p => `<tr><td>${p.id}</td><td>${p.codigo_qr}</td><td>${p.especie}</td><td>${p.altura_actual_cm ?? ''}</td></tr>`).join('');
}

async function listInventario() {
  const data = await api('inventario.php');
  const rows = data.data || [];
  const tbody = document.getElementById('tabla-inventario');
  if (tbody) tbody.innerHTML = rows.map(r => {
    const cal = (state.catalogs.clasificaciones_calidad || []).find(x => x.id == r.clasificacion_calidad_id)?.nombre || '';
    const tam = (state.catalogs.tamanos_plantas || []).find(x => x.id == r.tamano_id)?.codigo || '';
    return `<tr><td>${r.planta_id}</td><td>${r.codigo_qr}</td><td>${r.especie}</td><td>${cal}</td><td>${tam}</td></tr>`;
  }).join('');
}

async function listOrdenes() {
  try {
    const data = await api('despachos.php');
    const rows = data.data || [];
    const tbody = document.getElementById('tabla-ordenes');
    if (tbody) tbody.innerHTML = rows.map(o => `<tr><td>${o.numero}</td><td>${o.destino}</td><td>${o.fecha}</td><td>${o.responsable_despacho_id}</td><td>${(o.lineas||[]).length}</td></tr>`).join('');
  } catch (e) {
    // ignore if sin datos
  }
}

function bindForms() {
  // Seed
  const seedBtn = document.getElementById('seed-btn');
  if (seedBtn) seedBtn.addEventListener('click', async () => {
    const msg = document.getElementById('seed-msg');
    msg.textContent = 'Inicializando...';
    try { await loadCatalogs(true); msg.textContent = 'Listo'; await listEspecies(); await listLotes(); } catch (e) { msg.textContent = e.message; }
  });

  // Especies
  const fe = document.getElementById('form-especie');
  if (fe) fe.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const fd = new FormData(fe);
    const body = Object.fromEntries(fd.entries());
    body.tipo_especie_id = parseInt(body.tipo_especie_id);
    const msg = document.getElementById('especie-msg'); msg.textContent = 'Guardando...';
    try { await api('especies.php', { method: 'POST', body }); msg.textContent = 'Guardado'; fe.reset(); await listEspecies(); } catch (e) { msg.textContent = e.message; }
  });

  // Lote
  const fl = document.getElementById('form-lote');
  if (fl) fl.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const fd = new FormData(fl);
    const body = {
      proveedor: {
        nombre: fd.get('p_nombre'), contacto: fd.get('p_contacto'), telefono: fd.get('p_telefono'), email: fd.get('p_email'),
      },
      lote_semillas: { procedencia: fd.get('ls_procedencia'), certificado: fd.get('ls_certificado'), tasa_germinacion: parseFloat(fd.get('ls_tasa')||0) || null, observaciones: fd.get('ls_observaciones') },
      lote_produccion: { especie_id: parseInt(fd.get('lp_especie_id')), fecha_siembra: fd.get('lp_fecha'), cantidad_semillas_usadas: parseInt(fd.get('lp_cantidad')), notas: fd.get('lp_notas') }
    };
    const msg = document.getElementById('lote-msg'); msg.textContent = 'Creando...';
    try { await api('lotes.php?action=create_all', { method: 'POST', body }); msg.textContent = 'Creado'; fl.reset(); await listLotes(); } catch (e) { msg.textContent = e.message; }
  });

  // Iniciar fase
  const fif = document.getElementById('form-iniciar-fase');
  if (fif) fif.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const lote = document.getElementById('if-lote').value;
    const fase = document.getElementById('if-fase').value;
    const ubi = document.getElementById('if-ubicacion').value || null;
    const stock = parseInt(document.getElementById('if-stock').value || '0');
    const msg = document.getElementById('if-msg'); msg.textContent = 'Iniciando...';
    try { await api('fases.php?action=start', { method: 'POST', body: { lote_produccion_id: parseInt(lote), fase_id: parseInt(fase), ubicacion_id: ubi ? parseInt(ubi) : null, stock_inicial: stock, responsable_id: 1 } }); msg.textContent = 'Iniciada'; await listFasesByLote(lote); } catch (e) { msg.textContent = e.message; }
  });

  // Cambios de lote para historial y cierre
  const histLote = document.getElementById('hist-lote');
  if (histLote) histLote.addEventListener('change', async () => { await listFasesByLote(histLote.value); });
  const cfLote = document.getElementById('cf-lote');
  if (cfLote) cfLote.addEventListener('change', async () => {
    const rows = await listFasesByLote(cfLote.value);
    const active = rows.filter(r => String(r.en_progreso) === '1');
    fillSelect(document.getElementById('cf-lote-fase'), active, { value: 'id', label: 'fase_nombre' });
  });
  const fcf = document.getElementById('form-cerrar-fase');
  if (fcf) fcf.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const lote = document.getElementById('cf-lote').value;
    const lf = document.getElementById('cf-lote-fase').value;
    const avanzan = parseInt(document.getElementById('cf-avanzan').value);
    const perdidas = parseInt(document.getElementById('cf-perdidas').value || '0');
    const descartes = parseInt(document.getElementById('cf-descartes').value || '0');
    const obs = document.getElementById('cf-observaciones').value || null;
    const msg = document.getElementById('cf-msg'); msg.textContent = 'Cerrando...';
    try { await api('fases.php?action=close', { method: 'POST', body: { lote_fase_id: parseInt(lf), plantas_avanzan: avanzan, plantas_perdidas: perdidas, plantas_descartadas: descartes, observaciones: obs, responsable_id: 1 } }); msg.textContent = 'Cerrada'; await listFasesByLote(lote); } catch (e) { msg.textContent = e.message; }
  });

  // Tratamiento
  const ftr = document.getElementById('form-tratamiento');
  if (ftr) ftr.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const msg = document.getElementById('tr-msg'); msg.textContent = 'Registrando...';
    try {
      await api('tratamientos.php', { method: 'POST', body: {
        lote_fase_id: parseInt(document.getElementById('tr-lote-fase').value),
        tipo_tratamiento_id: parseInt(document.getElementById('tr-tipo').value),
        fecha: document.getElementById('tr-fecha').value || new Date().toISOString().slice(0,16).replace('T',' '),
        producto: document.getElementById('tr-producto').value,
        dosis: document.getElementById('tr-dosis').value,
        motivo: document.getElementById('tr-motivo').value,
        observaciones: document.getElementById('tr-observaciones').value,
        usuario_id: 1,
      }});
      msg.textContent = 'Registrado'; ftr.reset();
    } catch (e) { msg.textContent = e.message; }
  });

  // Condición
  const fco = document.getElementById('form-condicion');
  if (fco) fco.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const msg = document.getElementById('co-msg'); msg.textContent = 'Registrando...';
    try {
      await api('condiciones.php', { method: 'POST', body: {
        lote_fase_id: parseInt(document.getElementById('co-lote-fase').value),
        ubicacion_id: parseInt(document.getElementById('co-ubicacion').value || '0') || null,
        fecha: document.getElementById('co-fecha').value || new Date().toISOString().slice(0,16).replace('T',' '),
        temperatura: parseFloat(document.getElementById('co-temp').value || '0') || null,
        humedad: parseFloat(document.getElementById('co-hum').value || '0') || null,
        precipitaciones: parseFloat(document.getElementById('co-prec').value || '0') || null,
        observaciones: document.getElementById('co-obs').value,
      }});
      msg.textContent = 'Registrado'; fco.reset();
    } catch (e) { msg.textContent = e.message; }
  });

  // Planta
  const fpl = document.getElementById('form-planta');
  if (fpl) fpl.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const msg = document.getElementById('pl-msg'); msg.textContent = 'Etiquetando...';
    try {
      const lote = parseInt(document.getElementById('pl-lote').value);
      const salud = parseInt(document.getElementById('pl-salud').value);
      const altura = parseFloat(document.getElementById('pl-altura').value || '0') || null;
      const ubic = parseInt(document.getElementById('pl-ubicacion').value || '0') || null;
      const res = await api('plantas.php', { method: 'POST', body: { lote_produccion_id: lote, estado_salud_id: salud, altura_actual_cm: altura, ubicacion_id: ubic } });
      msg.textContent = `Planta creada. QR: ${res.codigo_qr}`;
      const qrBox = document.getElementById('pl-qr');
      qrBox.innerHTML = '';
      new QRCode(qrBox, { text: res.codigo_qr, width: 128, height: 128 });
      await listPlantas();
    } catch (e) { msg.textContent = e.message; }
  });

  // Inventario
  const finv = document.getElementById('form-inventario');
  if (finv) finv.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const msg = document.getElementById('inv-msg'); msg.textContent = 'Actualizando...';
    try {
      await api('inventario.php', { method: 'POST', body: {
        planta_id: parseInt(document.getElementById('inv-planta-id').value),
        clasificacion_calidad_id: parseInt(document.getElementById('inv-calidad').value || '0') || null,
        tamano_id: parseInt(document.getElementById('inv-tamano').value || '0') || null,
      }});
      msg.textContent = 'Actualizado'; await listInventario();
    } catch (e) { msg.textContent = e.message; }
  });

  // Despachos
  const fod = document.getElementById('form-orden');
  if (fod) fod.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const msg = document.getElementById('od-msg'); msg.textContent = 'Creando...';
    try {
      const res = await api('despachos.php?action=create_order', { method: 'POST', body: {
        destino_id: parseInt(document.getElementById('od-destino').value),
        fecha: document.getElementById('od-fecha').value || new Date().toISOString().slice(0,16).replace('T',' '),
        responsable_despacho_id: parseInt(document.getElementById('od-resp').value || '1') || 1,
        personal_transporte: document.getElementById('od-trans').value,
        notas: document.getElementById('od-notas').value,
      }});
      msg.textContent = `Creada ${res.numero}`;
      document.getElementById('ol-orden').value = res.orden_despacho_id;
      await listOrdenes();
    } catch (e) { msg.textContent = e.message; }
  });
  const fol = document.getElementById('form-orden-linea');
  if (fol) fol.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const msg = document.getElementById('ol-msg'); msg.textContent = 'Agregando...';
    try {
      await api('despachos.php?action=add_line', { method: 'POST', body: {
        orden_despacho_id: parseInt(document.getElementById('ol-orden').value),
        planta_id: parseInt(document.getElementById('ol-planta').value),
        cantidad: parseInt(document.getElementById('ol-cant').value),
        estado_al_despacho_id: parseInt(document.getElementById('ol-estado').value),
        observaciones: document.getElementById('ol-obs').value,
      }});
      msg.textContent = 'Agregada'; await listOrdenes();
    } catch (e) { msg.textContent = e.message; }
  });
}

window.addEventListener('DOMContentLoaded', async () => {
  try {
    await loadCatalogs();
    await listEspecies();
    await listLotes();
    await listPlantas();
    await listInventario();
    await listOrdenes();
  } catch (e) {
    console.warn('Init error', e);
  }
  bindForms();
});
