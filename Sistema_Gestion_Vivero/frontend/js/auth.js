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
