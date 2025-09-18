// API client y utilidades compartidas (ES Modules)
'use strict';

// Backend API base - Configuración robusta para diferentes entornos
export const API_BASE = (() => {
  // Si estamos en desarrollo local, usar ruta relativa
  if (location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
    return '../backend/php/api';
  }
  
  // Para producción, construir la ruta al backend de forma más robusta
  try {
    const path = location.pathname;
    const frontendPathIndex = path.indexOf('/frontend/');
    if (frontendPathIndex !== -1) {
      // Construye la ruta al backend relativa a la raíz del sitio
      const basePath = path.substring(0, frontendPathIndex);
      return `${basePath}/backend/php/api`;
    }
    
    // Fallback si no se encuentra /frontend/
    return '../backend/php/api';
  } catch (_) {
    // Último recurso en caso de error
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

