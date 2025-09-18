// API client y utilidades compartidas (ES Modules)
'use strict';

// Backend API base - Configuración simplificada
export const API_BASE = '../../backend/php/api';

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

