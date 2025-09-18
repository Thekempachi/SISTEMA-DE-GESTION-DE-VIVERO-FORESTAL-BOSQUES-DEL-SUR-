'use strict';

import { api, fillSelect, state } from './api.js';

// --- Dashboard helpers ---
async function renderKPIs() {
  try {
    // Especies y Lotes ya están en state tras listEspecies/listLotes
    const kEs = document.getElementById('kpi-especies'); if (kEs) kEs.textContent = (state.especies || []).length;
    const kLo = document.getElementById('kpi-lotes'); if (kLo) kLo.textContent = (state.lotes || []).length;

    // Plantas: consultamos directamente para contar
    try {
      const p = await api('plantas.php');
      const countP = (p.data || []).length;
      const kPl = document.getElementById('kpi-plantas'); if (kPl) kPl.textContent = countP;
    } catch { /* ignore */ }

    // Órdenes de despacho: consultamos para contar
    try {
      const d = await api('despachos.php');
      const countO = (d.data || []).length;
      const kOr = document.getElementById('kpi-ordenes'); if (kOr) kOr.textContent = countO;
    } catch { /* ignore */ }
  } catch (error) {
    // Error rendering KPIs
  }
}

async function renderResumenInventario() {
  try {
    const tbody = document.getElementById('inv-resumen');
    if (!tbody) return;
    
    try {
      const data = await api('inventario.php');
      const rows = data.data || [];
      const map = new Map();
      for (const r of rows) {
        const key = r.clasificacion_calidad_id ?? 'Sin clasificar';
        map.set(key, (map.get(key) || 0) + 1);
      }
      const calNames = Object.fromEntries((state.catalogs.clasificaciones_calidad || []).map(x => [x.id, x.nombre]));
      const lines = [];
      for (const [key, val] of map.entries()) {
        const name = calNames[key] || (key === 'Sin clasificar' ? 'Sin clasificar' : `#${key}`);
        lines.push(`<tr><td>${name}</td><td>${val}</td></tr>`);
      }
      tbody.innerHTML = lines.sort((a,b)=>a.localeCompare(b)).join('');
    } catch (error) {
      tbody.innerHTML = '';
    }
  } catch (error) {
    // Error rendering inventory summary
  }
}

function renderUltimosLotes(limit = 5) {
  try {
    const tbody = document.getElementById('ult-lotes');
    if (!tbody) return;
    
    const rows = (state.lotes || []).slice().sort((a,b) => (b.fecha_siembra||'').localeCompare(a.fecha_siembra||''));
    const last = rows.slice(0, limit);
    tbody.innerHTML = last.map(l => `<tr><td>${l.codigo}</td><td>${l.especie}</td><td>${l.fecha_siembra||''}</td></tr>`).join('');
  } catch (error) {
    // Error rendering latest lots
  }
}

function renderPlantFicha(p, extra = {}) {
  try {
    const box = document.getElementById('pl-ficha');
    if (!box) return;
    
    const rows = [];
    rows.push(`<div><strong>ID:</strong> ${p.id ?? ''}</div>`);
    rows.push(`<div><strong>QR:</strong> ${p.codigo_qr ?? ''}</div>`);
    if (p.especie) rows.push(`<div><strong>Especie:</strong> ${p.especie}</div>`);
    if (p.altura_actual_cm != null) rows.push(`<div><strong>Altura:</strong> ${p.altura_actual_cm} cm</div>`);
    if (p.estado_salud) rows.push(`<div><strong>Salud:</strong> ${p.estado_salud}</div>`);
    if (p.ubicacion) rows.push(`<div><strong>Ubicación:</strong> ${p.ubicacion}</div>`);
    if (extra.calName) rows.push(`<div><strong>Clasificación:</strong> ${extra.calName}</div>`);
    if (extra.tamName) rows.push(`<div><strong>Tamaño:</strong> ${extra.tamName}</div>`);
    box.innerHTML = `<h4>Ficha de la Planta</h4><div class="grid">${rows.map(r=>`<div class="card">${r}</div>`).join('')}</div>`;
  } catch (error) {
    // Error rendering plant profile
  }
}

async function renderDashboard() {
  await renderKPIs();
  await renderResumenInventario();
  renderUltimosLotes();
}

async function ensureAuth() {
  try {
    const res = await api('auth.php?action=me');
    if (!res || !res.user) throw new Error('No auth');
    return res.user;
  } catch (e) {
    // Not authenticated: go to login
    window.location.href = './login.html';
    throw e;
  }
}

async function loadCatalogs(seed = false) {
  try {
    const data = await api(`catalogs.php${seed ? '?seed=1' : ''}`);
    state.catalogs = data.catalogs || {};
  } catch (error) {
    state.catalogs = {};
  }

  // Fill selects depending on catalogs (con valores por defecto si faltan)
  const tipoEspecie = document.getElementById('tipo-especie');
  if (tipoEspecie) fillSelect(tipoEspecie, state.catalogs.tipo_especie || []);
  const ifFase = document.getElementById('if-fase');
  if (ifFase) fillSelect(ifFase, state.catalogs.fases_produccion || []);
  ['if-ubicacion','co-ubicacion','pl-ubicacion'].forEach(id => {
    const el = document.getElementById(id); if (el) fillSelect(el, state.catalogs.ubicaciones || [], { label: 'sector' });
  });
  const plSalud = document.getElementById('pl-salud');
  if (plSalud) fillSelect(plSalud, state.catalogs.estado_salud || []);
  // Nuevos selects para planta: clasificación y tamaño
  const plCal = document.getElementById('pl-calidad');
  if (plCal) fillSelect(plCal, [{ id: '', nombre: '— sin asignar —' }, ...(state.catalogs.clasificaciones_calidad || [])]);
  const plTam = document.getElementById('pl-tamano');
  if (plTam) fillSelect(plTam, [{ id: '', codigo: '— sin asignar —' }, ...(state.catalogs.tamanos_plantas || [])], { label: 'codigo' });
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
  if (tbody) tbody.innerHTML = state.especies.map(e => `<tr><td>${e.id}</td><td>${e.nombre_comun}</td><td>${e.nombre_cientifico}</td><td>${e.tipo_especie}</td></tr>`).join('');
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
  try {
    
    // Helper function to safely get elements and add event listeners
    const safeBind = (elementId, eventType, handler) => {
      try {
        const element = document.getElementById(elementId);
        if (element) {
          element.addEventListener(eventType, handler);
          return true;
        }
        return false;
      } catch (error) {
        return false;
      }
    };
    
    // Seed
    safeBind('seed-btn', 'click', async () => {
      try {
        const msg = document.getElementById('seed-msg');
        if (msg) msg.textContent = 'Inicializando...';
        await loadCatalogs(true);
        if (msg) msg.textContent = 'Listo';
        await listEspecies();
        await listLotes();
      } catch (e) {
        const msg = document.getElementById('seed-msg');
        if (msg) msg.textContent = e.message;
      }
    });

    // Especies
    safeBind('form-especie', 'submit', async (ev) => {
      try {
        ev.preventDefault();
        const fe = document.getElementById('form-especie');
        if (!fe) return;
        
        const fd = new FormData(fe);
        const body = Object.fromEntries(fd.entries());
        body.tipo_especie_id = parseInt(body.tipo_especie_id);
        
        const msg = document.getElementById('especie-msg');
        if (msg) msg.textContent = 'Guardando...';
        
        await api('especies.php', { method: 'POST', body });
        if (msg) msg.textContent = 'Guardado';
        fe.reset();
        await listEspecies();
      } catch (e) {
        const msg = document.getElementById('especie-msg');
        if (msg) msg.textContent = e.message;
      }
    });

    // Lote
    safeBind('form-lote', 'submit', async (ev) => {
      try {
        ev.preventDefault();
        const fl = document.getElementById('form-lote');
        if (!fl) return;
        
        const fd = new FormData(fl);
        const body = {
          proveedor: {
            nombre: fd.get('p_nombre'), contacto: fd.get('p_contacto'), telefono: fd.get('p_telefono'), email: fd.get('p_email'),
          },
          lote_semillas: { procedencia: fd.get('ls_procedencia'), certificado: fd.get('ls_certificado'), tasa_germinacion: parseFloat(fd.get('ls_tasa')||0) || null, observaciones: fd.get('ls_observaciones') },
          lote_produccion: { especie_id: parseInt(fd.get('lp_especie_id')), fecha_siembra: fd.get('lp_fecha'), cantidad_semillas_usadas: parseInt(fd.get('lp_cantidad')), notas: fd.get('lp_notas') }
        };
        
        const msg = document.getElementById('lote-msg');
        if (msg) msg.textContent = 'Creando...';
        
        await api('lotes.php?action=create_all', { method: 'POST', body });
        if (msg) msg.textContent = 'Creado';
        fl.reset();
        await listLotes();
      } catch (e) {
        const msg = document.getElementById('lote-msg');
        if (msg) msg.textContent = e.message;
      }
    });

    // Iniciar fase
    safeBind('form-iniciar-fase', 'submit', async (ev) => {
      try {
        ev.preventDefault();
        const lote = document.getElementById('if-lote')?.value;
        const fase = document.getElementById('if-fase')?.value;
        const ubi = document.getElementById('if-ubicacion')?.value || null;
        const stock = parseInt(document.getElementById('if-stock')?.value || '0');
        
        const msg = document.getElementById('if-msg');
        if (msg) msg.textContent = 'Iniciando...';
        
        await api('fases.php?action=start', { method: 'POST', body: { lote_produccion_id: parseInt(lote), fase_id: parseInt(fase), ubicacion_id: ubi ? parseInt(ubi) : null, stock_inicial: stock, responsable_id: 1 } });
        if (msg) msg.textContent = 'Iniciada';
        await listFasesByLote(lote);
      } catch (e) {
        const msg = document.getElementById('if-msg');
        if (msg) msg.textContent = e.message;
      }
    });

    // Cambios de lote para historial y cierre
    safeBind('hist-lote', 'change', async () => {
      try {
        const histLote = document.getElementById('hist-lote');
        if (histLote) await listFasesByLote(histLote.value);
      } catch (error) {
        // Error handling hist-lote change
      }
    });
    
    safeBind('cf-lote', 'change', async () => {
      try {
        const cfLote = document.getElementById('cf-lote');
        if (!cfLote) return;
        
        const rows = await listFasesByLote(cfLote.value);
        const active = rows.filter(r => String(r.en_progreso) === '1');
        const cfLoteFase = document.getElementById('cf-lote-fase');
        if (cfLoteFase) fillSelect(cfLoteFase, active, { value: 'id', label: 'fase_nombre' });
      } catch (error) {
        // Error handling cf-lote change
      }
    });
    
    safeBind('form-cerrar-fase', 'submit', async (ev) => {
      try {
        ev.preventDefault();
        const lote = document.getElementById('cf-lote')?.value;
        const lf = document.getElementById('cf-lote-fase')?.value;
        const avanzan = parseInt(document.getElementById('cf-avanzan')?.value);
        const perdidas = parseInt(document.getElementById('cf-perdidas')?.value || '0');
        const descartes = parseInt(document.getElementById('cf-descartes')?.value || '0');
        const obs = document.getElementById('cf-observaciones')?.value || null;
        
        const msg = document.getElementById('cf-msg');
        if (msg) msg.textContent = 'Cerrando...';
        
        await api('fases.php?action=close', { method: 'POST', body: { lote_fase_id: parseInt(lf), plantas_avanzan: avanzan, plantas_perdidas: perdidas, plantas_descartadas: descartes, observaciones: obs, responsable_id: 1 } });
        if (msg) msg.textContent = 'Cerrada';
        await listFasesByLote(lote);
      } catch (e) {
        const msg = document.getElementById('cf-msg');
        if (msg) msg.textContent = e.message;
      }
    });

    // Tratamiento
    safeBind('form-tratamiento', 'submit', async (ev) => {
      try {
        ev.preventDefault();
        const msg = document.getElementById('tr-msg');
        if (msg) msg.textContent = 'Registrando...';
        
        await api('tratamientos.php', { method: 'POST', body: {
          lote_fase_id: parseInt(document.getElementById('tr-lote-fase')?.value),
          tipo_tratamiento_id: parseInt(document.getElementById('tr-tipo')?.value),
          fecha: document.getElementById('tr-fecha')?.value || new Date().toISOString().slice(0,16).replace('T',' '),
          producto: document.getElementById('tr-producto')?.value,
          dosis: document.getElementById('tr-dosis')?.value,
          motivo: document.getElementById('tr-motivo')?.value,
          observaciones: document.getElementById('tr-observaciones')?.value,
          usuario_id: 1,
        }});
        
        if (msg) msg.textContent = 'Registrado';
        const ftr = document.getElementById('form-tratamiento');
        if (ftr) ftr.reset();
      } catch (e) {
        const msg = document.getElementById('tr-msg');
        if (msg) msg.textContent = e.message;
      }
    });

    // Condición
    safeBind('form-condicion', 'submit', async (ev) => {
      try {
        ev.preventDefault();
        const msg = document.getElementById('co-msg');
        if (msg) msg.textContent = 'Registrando...';
        
        await api('condiciones.php', { method: 'POST', body: {
          lote_fase_id: parseInt(document.getElementById('co-lote-fase')?.value),
          ubicacion_id: parseInt(document.getElementById('co-ubicacion')?.value || '0') || null,
          fecha: document.getElementById('co-fecha')?.value || new Date().toISOString().slice(0,16).replace('T',' '),
          temperatura: parseFloat(document.getElementById('co-temp')?.value || '0') || null,
          humedad: parseFloat(document.getElementById('co-hum')?.value || '0') || null,
          precipitaciones: parseFloat(document.getElementById('co-prec')?.value || '0') || null,
          observaciones: document.getElementById('co-obs')?.value,
        }});
        
        if (msg) msg.textContent = 'Registrado';
        const fco = document.getElementById('form-condicion');
        if (fco) fco.reset();
      } catch (e) {
        const msg = document.getElementById('co-msg');
        if (msg) msg.textContent = e.message;
      }
    });

    // Planta
    safeBind('form-planta', 'submit', async (ev) => {
      try {
        ev.preventDefault();
        const msg = document.getElementById('pl-msg');
        if (msg) msg.textContent = 'Etiquetando...';
        
        const lote = parseInt(document.getElementById('pl-lote')?.value);
        const salud = parseInt(document.getElementById('pl-salud')?.value);
        const altura = parseFloat(document.getElementById('pl-altura')?.value || '0') || null;
        const ubic = parseInt(document.getElementById('pl-ubicacion')?.value || '0') || null;
        const calId = parseInt(document.getElementById('pl-calidad')?.value || '0') || null;
        const tamId = parseInt(document.getElementById('pl-tamano')?.value || '0') || null;
        
        const res = await api('plantas.php', { method: 'POST', body: { lote_produccion_id: lote, estado_salud_id: salud, altura_actual_cm: altura, ubicacion_id: ubic } });
        
        if (msg) msg.textContent = `Planta creada. QR: ${res.codigo_qr}`;
        const qrBox = document.getElementById('pl-qr');
        if (qrBox) {
          qrBox.innerHTML = '';
          // QRCode está disponible globalmente desde index.html
          new QRCode(qrBox, { text: res.codigo_qr, width: 128, height: 128 });
        }

        // Inventario inicial (opcional) si se seleccionó clasificación/tamaño
        if (calId || tamId) {
          await api('inventario.php', { method: 'POST', body: {
            planta_id: res.id,
            clasificacion_calidad_id: calId,
            tamano_id: tamId,
          }});
        }

        // Obtener detalles actualizados de la planta y renderizar ficha
        const det = await api(`plantas.php?id=${res.id}`);
        const p = det.data || { id: res.id, codigo_qr: res.codigo_qr };
        const calName = (state.catalogs.clasificaciones_calidad || []).find(x => x.id == calId)?.nombre || '';
        const tamName = (state.catalogs.tamanos_plantas || []).find(x => x.id == tamId)?.codigo || '';
        renderPlantFicha(p, { calName, tamName });
        await listPlantas();
      } catch (e) {
        const msg = document.getElementById('pl-msg');
        if (msg) msg.textContent = e.message;
      }
    });

    // Inventario
    safeBind('form-inventario', 'submit', async (ev) => {
      try {
        ev.preventDefault();
        const msg = document.getElementById('inv-msg');
        if (msg) msg.textContent = 'Actualizando...';
        
        await api('inventario.php', { method: 'POST', body: {
          planta_id: parseInt(document.getElementById('inv-planta-id')?.value),
          clasificacion_calidad_id: parseInt(document.getElementById('inv-calidad')?.value || '0') || null,
          tamano_id: parseInt(document.getElementById('inv-tamano')?.value || '0') || null,
        }});
        
        if (msg) msg.textContent = 'Actualizado';
        await listInventario();
      } catch (e) {
        const msg = document.getElementById('inv-msg');
        if (msg) msg.textContent = e.message;
      }
    });

    // Despachos
    safeBind('form-orden', 'submit', async (ev) => {
      try {
        ev.preventDefault();
        const msg = document.getElementById('od-msg');
        if (msg) msg.textContent = 'Creando...';
        
        const res = await api('despachos.php?action=create_order', { method: 'POST', body: {
          destino_id: parseInt(document.getElementById('od-destino')?.value),
          fecha: document.getElementById('od-fecha')?.value || new Date().toISOString().slice(0,16).replace('T',' '),
          responsable_despacho_id: parseInt(document.getElementById('od-resp')?.value || '1') || 1,
          personal_transporte: document.getElementById('od-trans')?.value,
          notas: document.getElementById('od-notas')?.value,
        }});
        
        if (msg) msg.textContent = `Creada ${res.numero}`;
        const olOrden = document.getElementById('ol-orden');
        if (olOrden) olOrden.value = res.orden_despacho_id;
        await listOrdenes();
      } catch (e) {
        const msg = document.getElementById('od-msg');
        if (msg) msg.textContent = e.message;
      }
    });
    
    safeBind('form-orden-linea', 'submit', async (ev) => {
      try {
        ev.preventDefault();
        const msg = document.getElementById('ol-msg');
        if (msg) msg.textContent = 'Agregando...';
        
        await api('despachos.php?action=add_line', { method: 'POST', body: {
          orden_despacho_id: parseInt(document.getElementById('ol-orden')?.value),
          planta_id: parseInt(document.getElementById('ol-planta')?.value),
          cantidad: parseInt(document.getElementById('ol-cant')?.value),
          estado_al_despacho_id: parseInt(document.getElementById('ol-estado')?.value),
          observaciones: document.getElementById('ol-obs')?.value,
        }});
        
        if (msg) msg.textContent = 'Agregada';
        await listOrdenes();
      } catch (e) {
        const msg = document.getElementById('ol-msg');
        if (msg) msg.textContent = e.message;
      }
    });
    
    // Forms bound successfully
  } catch (error) {
    console.error('Error in bindForms:', error);
  }
}

window.addEventListener('DOMContentLoaded', async () => {
  try {
    
    // Esperar a que la navegación esté lista
    await new Promise(resolve => setTimeout(resolve, 200));
    
    let user;
    try {
      user = await ensureAuth();
      const userNameEl = document.getElementById('user-name');
      if (userNameEl && user) {
        // Display user's name, fallback to username
        userNameEl.textContent = `Hola, ${user.nombre || user.username}`;
      }
    } catch (authError) {
      return; // Detener la inicialización si no hay autenticación
    }
    
    try {
      await loadCatalogs();
    } catch (catalogError) {
      // Error loading catalogs
    }
    
    try {
      await listEspecies();
    } catch (especiesError) {
      // Error loading especies
    }
    
    try {
      await listLotes();
    } catch (lotesError) {
      // Error loading lotes
    }
    
    try {
      await listPlantas();
    } catch (plantasError) {
      // Error loading plantas
    }
    
    try {
      await listInventario();
    } catch (inventarioError) {
      // Error loading inventario
    }
    
    try {
      await listOrdenes();
    } catch (ordenesError) {
      // Error loading ordenes
    }
    
    try {
      await renderDashboard();
    } catch (dashboardError) {
      // Error rendering dashboard
    }
    
    // Application initialized successfully
    
  } catch (e) {
    // Fatal initialization error
  }
  
  try {
    bindForms();
  } catch (bindError) {
    // Error binding forms
  }

  // Logout button - Implementación profesional mejorada
  const logoutBtn = document.getElementById('logout-btn');
  if (logoutBtn) {
    // Importar la función handleLogout de auth.js
    import('./auth.js').then(({ handleLogout }) => {
      logoutBtn.addEventListener('click', async (event) => {
        event.preventDefault();
        // Logout button clicked by user
        
        // Obtener información del usuario para mensaje personalizado
        const userNameElement = document.getElementById('user-name');
        const userName = userNameElement?.textContent || 'usuario';
        
        // Confirmación profesional con mensaje personalizado
        const confirmLogout = confirm(
          `¿Estás seguro de que deseas cerrar la sesión de ${userName}?\n\n` +
          'Se cerrará tu sesión actual y serás redirigido a la página de inicio.'
        );
        
        if (confirmLogout) {
          try {
            // Usuario confirmó logout. Iniciando proceso...
            await handleLogout(logoutBtn);
          } catch (error) {
            // Error inesperado en logout
            
            // Mostrar feedback de error al usuario
            const iconElement = logoutBtn.querySelector('.logout-icon');
            const textElement = logoutBtn.querySelector('.logout-text');
            
            if (iconElement) iconElement.textContent = '❌';
            if (textElement) textElement.textContent = 'Error';
            
            logoutBtn.classList.add('error');
            
            // Esperar un momento y redirigir igualmente por seguridad
            setTimeout(() => {
              console.warn('Redirigiendo por seguridad después de error crítico');
              window.location.href = './login.html';
            }, 1000);
          }
        } else {
          // Usuario canceló el logout
          // Devolver el foco al botón si el usuario canceló
          logoutBtn.focus();
        }
      });
      
      // Añadir soporte para teclado (Escape key para cancelar)
      logoutBtn.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          // Escape key pressed - canceling logout focus
          logoutBtn.blur();
        }
      });
      
      // Logout button event listener attached successfully
    }).catch(error => {
      // Error importing auth module
      
      // Fallback robusto: usar implementación básica si falla la importación
      logoutBtn.addEventListener('click', () => {
        const confirmLogout = confirm(
          '¿Estás seguro de que deseas cerrar la sesión?\n\n' +
          'Ocurrió un error al cargar el sistema de logout seguro. ' +
          'Serás redirigido a la página de inicio.'
        );
        
        if (confirmLogout) {
          window.location.href = './login.html';
        }
      });
    });
  } else {
    // Critical: Logout button (#logout-btn) not found on the page.
    
    // Fallback de emergencia: intentar redirigir automáticamente después de un tiempo
    setTimeout(() => {
      if (!document.getElementById('logout-btn')) {
        // Logout button still not found - possible UI corruption
      }
    }, 5000);
  }
});
