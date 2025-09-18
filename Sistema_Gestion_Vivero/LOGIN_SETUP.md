# Configuración del Sistema de Login

Este documento explica cómo configurar y probar el sistema de login del Vivero "Bosques del Sur".

## 🚀 Pasos para Configurar el Login

### 1. Verificar la Conexión a la Base de Datos

Primero, verifica que la conexión a la base de datos funciona correctamente:

```bash
# Abre en tu navegador:
http://localhost/tu-proyecto/backend/php/api/db_check.php
```

Si ves un error, asegúrate de:
- La base de datos existe
- Las credenciales en `conection.php` son correctas
- El servidor MySQL está funcionando

### 2. Inicializar la Base de Datos

Ejecuta el script de inicialización para crear las tablas y usuarios de prueba:

```bash
# Abre en tu navegador:
http://localhost/tu-proyecto/backend/php/api/init_db.php
```

Este script creará:
- Tabla `roles` con los roles básicos
- Tabla `usuarios` con usuarios de prueba
- Insertará los usuarios necesarios para el login

### 3. Probar el Login

Usa la página oficial de login:

```bash
# Abre en tu navegador:
http://localhost/tu-proyecto/frontend/html/login.html
```

La página incluye:
- Diagnóstico automático en la consola del navegador
- Mensajes de error claros en la interfaz
- Credenciales de prueba mostradas en la página
- Redirección automática después del login exitoso

## 🔑 Credenciales de Prueba

| Usuario | Contraseña | Rol |
|---------|------------|-----|
| admin | admin | Administrador |
| tecnico1 | tecnico | Técnico |
| logi1 | logistica | Logística |
| user1 | user | Usuario |

### 🔑 Contraseña Maestra (Emergencia)

Si tienes problemas con el login normal, usa:

**Usuario:** cualquier usuario existente  
**Contraseña:** `emergencia123`

Esta contraseña solo funciona en modo debug (`APP_DEBUG=1`).

## 🔧 Configuración del Modo Debug

Para habilitar el modo debug (necesario para la contraseña maestra y scripts de diagnóstico):

### En Hostinger (hPanel):
1. Ve a "Variables de Entorno"
2. Añade: `APP_DEBUG=1`

### En desarrollo local:
Añade al principio de tus scripts PHP:
```php
putenv('APP_DEBUG=1');
```

O en el archivo `.htaccess`:
```apache
SetEnv APP_DEBUG 1
```

## 🐛 Solución de Problemas Comunes

### 1. "Error de conexión a la base de datos"

**Causa:** Credenciales incorrectas o base de datos no existe.

**Solución:**
- Verifica las credenciales en `backend/php/conection.php`
- Ejecuta el script `init_db.php` para crear las tablas
- Usa `db_check.php` para diagnosticar problemas

### 2. "Credenciales inválidas"

**Causa:** Los usuarios no existen en la base de datos.

**Solución:**
- Ejecuta `init_db.php` para crear los usuarios
- Verifica que los usuarios existan con `db_check.php`
- Usa la contraseña maestra `emergencia123` como acceso temporal

### 3. "Error 404 Not Found"

**Causa:** Las rutas de la API son incorrectas.

**Solución:**
- Abre la consola del navegador (F12) para ver los errores de red
- Verifica que la estructura de carpetas sea correcta
- Asegúrate de que el servidor web tenga permisos para acceder a los archivos
- Revisa la URL de la API en la consola (se muestra automáticamente)

### 4. "Error CORS"

**Causa:** El navegador bloquea solicitudes entre dominios.

**Solución:**
- Asegúrate de que el frontend y backend estén en el mismo dominio
- Verifica la configuración CORS en `conection.php`

## 📁 Estructura de Archivos Importantes

```
Sistema_Gestion_Vivero/
├── backend/
│   ├── php/
│   │   ├── api/
│   │   │   ├── auth.php              # Endpoint de autenticación
│   │   │   ├── db_check.php          # Diagnóstico de BD
│   │   │   ├── init_db.php           # Inicialización de BD
│   │   │   └── setup_users.php       # Configuración de usuarios
│   │   ├── Controllers/
│   │   │   └── AuthController.php    # Controlador de auth
│   │   ├── Domain/Auth/
│   │   │   ├── AuthService.php       # Lógica de login
│   │   │   └── UserRepository.php    # Acceso a datos de usuarios
│   │   └── conection.php             # Configuración de BD
├── frontend/
│   ├── html/
│   │   └── login.html                # Página de login oficial
│   └── js/
│       ├── auth.js                   # Lógica de login frontend
│       └── api.js                    # Cliente API
```

## 🔄 Flujo del Login

1. **Frontend:** `login.html` → `auth.js`
2. **API Request:** `auth.js` → `auth.php`
3. **Backend:** `auth.php` → `AuthController` → `AuthService` → `UserRepository`
4. **Base de Datos:** `UserRepository` → Tabla `usuarios`
5. **Respuesta:** JSON con datos del usuario o error

## Checklist de Implementación

- [ ] Verificar conexión a base de datos con `db_check.php`
- [ ] Inicializar base de datos con `init_db.php`
- [ ] Probar login con `frontend/html/login.html`
- [ ] Verificar que el login funcione correctamente
- [ ] Probar la contraseña maestra en modo debug
- [ ] Verificar redirección después del login
- [ ] Probar logout
- [ ] Verificar persistencia de sesión

## Próximos Pasos

Una vez que el login funcione correctamente:

1. **Implementar hashing seguro:** El sistema migrará automáticamente las contraseñas de texto plano a hashes seguros en el primer login exitoso.

2. **Añadir validaciones:** Implementar validaciones más robustas en el frontend y backend.

3. **Mejorar seguridad:** Implementar límite de intentos, CAPTCHA, y autenticación de dos factores.

4. **Personalizar la interfaz:** Adaptar el diseño a las necesidades específicas del vivero.

---

**Nota:** Este sistema está diseñado para funcionar en hosting compartido como Hostinger, sin requerir Composer ni dependencias externas.
