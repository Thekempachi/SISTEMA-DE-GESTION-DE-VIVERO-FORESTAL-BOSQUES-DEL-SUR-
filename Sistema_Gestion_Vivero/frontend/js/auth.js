import { api } from './api.js';

async function login(username, password) {
  return api('auth.php?action=login', { method: 'POST', body: { username, password } });
}

async function me() {
  try {
    const res = await api('auth.php?action=me');
    return res.user;
  } catch (e) {
    return null;
  }
}

async function logout() {
  return api('auth.php?action=logout', { method: 'POST' });
}

// Redirect to index if already logged in
window.addEventListener('DOMContentLoaded', async () => {
  // Opcional: ayuda de depuración
  try { console.debug('API_BASE =', (await import('./api.js')).API_BASE); } catch {}

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

  // Botón para crear usuario admin de prueba (seed minimal)
  const seedBtn = document.getElementById('seed-admin-btn');
  const seedMsg = document.getElementById('seed-admin-msg');
  if (seedBtn && seedMsg) {
    seedBtn.addEventListener('click', async () => {
      seedMsg.textContent = 'Creando usuario admin...';
      try {
        await api('catalogs.php?seed=1');
        seedMsg.textContent = 'Listo: usuario admin/admin123 creado (si no existía).';
        const u = document.getElementById('username');
        const p = document.getElementById('password');
        if (u && p) { u.value = 'admin'; p.value = 'admin123'; }
      } catch (e) {
        seedMsg.textContent = e.message;
      }
    });
  }
});

