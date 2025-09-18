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

    // Buscar diferentes patrones de ruta
    const patterns = [
      '/Sistema_Gestion_Vivero/frontend/',
      '/SISTEMA-DE-GESTI-N-DE-VIVERO-FORESTAL-BOSQUES-DEL-SUR-/Sistema_Gestion_Vivero/frontend/',
      '/Sistema_Gestion_Vivero/frontend/',
      '/frontend/'
    ];

    for (const pattern of patterns) {
      const index = path.indexOf(pattern);
      if (index !== -1) {
        const basePath = path.substring(0, index);
        const backendPath = `${basePath}/Sistema_Gestion_Vivero/backend/php/api`;
        return backendPath;
      }
    }

    // Fallback: intentar construir ruta basada en la estructura conocida
    if (path.includes('Sistema_Gestion_Vivero')) {
      const parts = path.split('/');
      const projectIndex = parts.findIndex(part => part.includes('Sistema_Gestion_Vivero') || part.includes('SISTEMA-DE-GESTI'));
      if (projectIndex !== -1) {
        const basePath = parts.slice(0, projectIndex + 1).join('/');
        return `${basePath}/Sistema_Gestion_Vivero/backend/php/api`;
      }
    }

    // Último fallback - ruta relativa simple
    return '../backend/php/api';
  } catch (error) {
    console.error('Error constructing API_BASE:', error);
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

