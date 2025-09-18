// API client y utilidades compartidas (ES Modules)
'use strict';

// Backend API base - Configuración simplificada
export const API_BASE = '../../backend/php/api';

export async function api(endpoint, options = {}) {
  const url = `${API_BASE}/${endpoint}`;
  const config = {
    credentials: 'include',
    headers: { 'Content-Type': 'application/json' },
    ...options
  };
  if (config.body && typeof config.body === 'object') {
    config.body = JSON.stringify(config.body);
  }
  
  const res = await fetch(url, config);
  
  // Verificar si es un error de autenticación
  if (res.status === 401) {
    const text = await res.text();
    let errorData;
    try {
      errorData = JSON.parse(text);
    } catch {
      errorData = { error: 'No autenticado' };
    }
    
    // Si no estamos en login y hay error de auth, redirigir
    if (!window.location.pathname.includes('login.html') && !endpoint.includes('auth.php')) {
      console.log('Sesión expirada o inválida, redirigiendo al login...');
      
      // Mostrar mensaje si es sesión expirada
      if (errorData.expired) {
        alert('Su sesión ha expirado. Por favor, inicie sesión nuevamente.');
      }
      
      // Limpiar datos locales
      localStorage.clear();
      sessionStorage.clear();
      
      // Redirigir al login
      window.location.href = './login.html';
      return;
    }
    
    throw new Error(errorData.error || 'No autenticado');
  }
  
  if (!res.ok) {
    const text = await res.text();
    let errorMsg = `HTTP ${res.status}`;
    try {
      const errorData = JSON.parse(text);
      errorMsg = errorData.error || errorMsg;
    } catch {
      errorMsg = text || errorMsg;
    }
    throw new Error(errorMsg);
  }
  
  return await res.json();
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

