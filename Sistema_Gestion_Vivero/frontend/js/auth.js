import { API_BASE, api } from './api.js';

// Exportar funciones para uso en otros módulos
export { login, me, logout, handleLogout };

async function login(username, password) {
  try {

    const res = await fetch(`${API_BASE}/auth.php?action=login`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password })
    });

    let data;
    try {
      data = await res.json();
    } catch (jsonError) {
      throw new Error('Respuesta inválida del servidor');
    }

    if (!res.ok) {
      const errorMsg = data.error || `Error HTTP ${res.status}`;
      throw new Error(errorMsg);
    }

    if (!data.ok) {
      const errorMsg = data.error || 'Error en el login';
      throw new Error(errorMsg);
    }

    console.debug('Login exitoso para:', username);
    return data;
  } catch (e) {
    console.error('Error en login():', e);

    // Re-throw con mensaje más específico
    if (e.message.includes('Failed to fetch')) {
      throw new Error('No se pudo conectar al servidor. Verifique su conexión.');
    } else if (e.message.includes('NetworkError')) {
      throw new Error('Error de red. Verifique su conexión a internet.');
    } else {
      throw e;
    }
  }
}

async function me() {
  try {
    const res = await fetch(`${API_BASE}/auth.php?action=me`, {
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

/**
 * Cierra la sesión del usuario de manera segura
 * @returns {Promise<boolean>} - true si el logout fue exitoso, false si hubo error
 */
async function logout() {
  try {
    console.debug('Iniciando proceso de logout...');
    
    // Llamar a la API de logout
    const response = await api('auth.php?action=logout', { method: 'POST' });
    
    console.debug('Logout API response:', response);
    
    // Limpiar cualquier dato sensible del almacenamiento local
    clearSessionData();
    
    return true;
  } catch (error) {
    console.error('Error durante el logout:', error.message);
    
    // Incluso si hay error en la API, limpiar datos locales
    clearSessionData();
    
    return false;
  }
}

/**
 * Limpia todos los datos de sesión del almacenamiento local
 */
function clearSessionData() {
  try {
    // Limpiar localStorage
    localStorage.removeItem('userSession');
    localStorage.removeItem('authToken');
    localStorage.removeItem('userPreferences');
    
    // Limpiar sessionStorage
    sessionStorage.clear();
    
    console.debug('Datos de sesión limpiados correctamente');
  } catch (error) {
    console.error('Error limpiando datos de sesión:', error);
  }
}

/**
 * Maneja el proceso completo de logout con UI feedback profesional
 * @param {HTMLElement} [button] - Botón que activó el logout (para mostrar estado)
 */
async function handleLogout(button = null) {
  const iconElement = button?.querySelector('.logout-icon');
  const textElement = button?.querySelector('.logout-text');
  
  // Mostrar estado de carga si hay un botón
  if (button) {
    button.disabled = true;
    button.classList.add('loading');
    
    if (textElement) {
      textElement.textContent = 'Cerrando...';
    }
    
    // Remover focus para evitar estados extraños
    button.blur();
  }
  
  try {
    console.debug('Iniciando proceso de logout...');
    const success = await logout();
    
    if (success) {
      console.debug('Logout exitoso, mostrando feedback...');
      
      // Mostrar estado de éxito
      if (button) {
        button.classList.remove('loading');
        button.classList.add('success');
        
        if (iconElement) iconElement.textContent = '✓';
        if (textElement) textElement.textContent = '¡Sesión cerrada!';
      }
      
      // Pequeña pausa para que el usuario vea el feedback de éxito
      await new Promise(resolve => setTimeout(resolve, 800));
      
      console.debug('Redirigiendo a login.html...');
      window.location.href = './login.html';
    } else {
      // Si falló el logout, mostrar error y redirigir por seguridad
      console.warn('Logout falló, redirigiendo por seguridad');
      
      if (button) {
        button.classList.remove('loading');
        button.classList.add('error');
        
        if (iconElement) iconElement.textContent = '⚠️';
        if (textElement) textElement.textContent = 'Error';
        
        await new Promise(resolve => setTimeout(resolve, 500));
      }
      
      window.location.href = './login.html';
    }
  } catch (error) {
    console.error('Error crítico en handleLogout:', error);
    
    // Mostrar estado de error
    if (button) {
      button.classList.remove('loading');
      button.classList.add('error');
      
      if (iconElement) iconElement.textContent = '❌';
      if (textElement) textElement.textContent = 'Error crítico';
      
      await new Promise(resolve => setTimeout(resolve, 500));
    }
    
    // En caso de error crítico, redirigir igualmente por seguridad
    window.location.href = './login.html';
  }
}

// Redirect to index if already logged in
window.addEventListener('DOMContentLoaded', async () => {
  // Diagnóstico: mostrar URL de API
  try {
    console.debug('API_BASE:', API_BASE);
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
  const usernameInput = document.getElementById('username');
  const passwordInput = document.getElementById('password');

  form.addEventListener('submit', async (ev) => {
    ev.preventDefault();

    // Limpiar mensajes anteriores
    msg.textContent = '';
    msg.className = 'login-msg muted';

    // Validación básica del frontend
    const username = usernameInput.value.trim();
    const password = passwordInput.value;

    if (!username) {
      showMessage('Por favor ingrese su usuario', 'error');
      usernameInput.focus();
      return;
    }

    if (!password) {
      showMessage('Por favor ingrese su contraseña', 'error');
      passwordInput.focus();
      return;
    }

    showMessage('Ingresando...', '');

    try {
      await login(username, password);
      showMessage('¡Bienvenido!', 'success');

      // Pequeña pausa para mostrar el mensaje de éxito
      setTimeout(() => {
        window.location.href = './index.html';
      }, 800);
    } catch (e) {
      console.error('Error de login:', e);

      // Mensajes de error más amigables
      let errorMessage = 'Error desconocido. Intente nuevamente.';

      if (e.message.includes('Failed to fetch') || e.message.includes('NetworkError')) {
        errorMessage = 'Error de conexión. Verifique su conexión a internet.';
      } else if (e.message.includes('401') || e.message.includes('No autenticado') || e.message.includes('Credenciales inválidas')) {
        errorMessage = 'Usuario o contraseña incorrectos';
        passwordInput.value = '';
        passwordInput.focus();
      } else if (e.message.includes('500')) {
        errorMessage = 'Error del servidor. Intente nuevamente más tarde.';
      } else if (e.message) {
        errorMessage = e.message;
      }

      showMessage(errorMessage, 'error');
    }
  });

  function showMessage(text, type) {
    msg.textContent = text;
    msg.className = 'login-msg muted';

    if (type === 'error') {
      msg.classList.add('error');
    } else if (type === 'success') {
      msg.classList.add('success');
    }
  }


});

