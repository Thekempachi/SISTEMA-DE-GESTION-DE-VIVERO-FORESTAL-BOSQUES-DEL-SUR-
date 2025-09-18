// API client y utilidades compartidas (ES Modules)
'use strict';

// Backend API base - Configuración robusta para diferentes entornos
export const API_BASE = (() => {
  // Si estamos en desarrollo local, usar ruta relativa
  if (location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
    return '../backend/php/api';
  }
  
  // Para producción, detectar la estructura de directorios
  try {
    const path = location.pathname;
    // Si la ruta contiene 'frontend', construir ruta relativa al backend
    if (path.includes('/frontend/')) {
      // Contar los niveles de directorio para volver a la raíz
      const depth = (path.match(/\//g) || []).length - 1;
      const upPath = '../'.repeat(depth - 1) + 'backend/php/api';
      return upPath;
    }
    
    // Fallback: usar ruta relativa desde la raíz
    return './backend/php/api';
  } catch (_) {
    // Último recurso: usar ruta relativa simple
    return '../backend/php/api';
  }
})();

export async function api(path, options = {}) {
  const url = `${API_BASE}/${path}`;
  const opts = { headers: { 'Content-Type': 'application/json' }, credentials: 'include', ...options };
  if (opts.body && typeof opts.body !== 'string') opts.body = JSON.stringify(opts.body);
  const res = await fetch(url, opts);
  let data = null;
  let text = '';
  try {
    data = await res.json();
  } catch (_) {
    try { text = await res.text(); } catch (_) {}
  }
  if (!res.ok || (data && data.error)) {
    const msg = (data && data.error) || text || `HTTP ${res.status}`;
    throw new Error(msg);
  }
  return data ?? {};
}

export const state = { catalogs: {}, especies: [], lotes: [] };

export function fillSelect(el, items, opts = {}) {
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

