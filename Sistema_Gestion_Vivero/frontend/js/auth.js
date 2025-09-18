import { api } from './api.js';

async function login(username, password) {
  try {
    console.debug('Intentando login con:', { username });
    const res = await fetch(`${api.API_BASE}/auth.php?action=login`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password })
    });
    
    const data = await res.json().catch(() => ({ error: 'Error de respuesta del servidor' }));
    
    if (!res.ok) {
      throw new Error(data.error || `Error ${res.status}`);
    }
    
    if (!data.ok) {
      throw new Error(data.error || 'Error en el login');
    }
    
    console.debug('Login exitoso para:', username);
    return data;
  } catch (e) {
    console.debug('Error en login():', e.message);
    throw e;
  }
}

async function me() {
  try {
    const res = await fetch(`${api.API_BASE}/auth.php?action=me`, {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });
    
    if (res.status === 401) {
      // No autenticado - comportamiento esperado, no es error
      return null;
    }
    
    if (!res.ok) {
      // Otro tipo de error
      const data = await res.json().catch(() => ({}));
      throw new Error(data.error || `HTTP ${res.status}`);
    }
    
    const data = await res.json();
    return data.user;
  } catch (e) {
    // Silenciar errores de red o 401 que son esperados
    if (e.message.includes('Failed to fetch') || e.message.includes('401')) {
      return null;
    }
    console.debug('Error en me():', e.message);
    return null;
  }
}

async function logout() {
  return api('auth.php?action=logout', { method: 'POST' });
}

// Redirect to index if already logged in
window.addEventListener('DOMContentLoaded', async () => {
  // Diagnóstico: mostrar URL de API
  try {
    console.debug('API_BASE:', api.API_BASE);
    console.debug('Hostname:', location.hostname);
    console.debug('Pathname:', location.pathname);
  } catch (e) {
    console.debug('Error en diagnóstico:', e);
  }

  const user = await me();
  if (user) {
    window.location.href = './index.html';
    return;
  }
  const form = document.getElementById('login-form');
  const msg = document.getElementById('login-msg');
  form.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    msg.textContent = 'Ingresando...';
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    try {
      await login(username, password);
      msg.textContent = 'OK';
      window.location.href = './index.html';
    } catch (e) {
      msg.textContent = e.message;
    }
  });


});

