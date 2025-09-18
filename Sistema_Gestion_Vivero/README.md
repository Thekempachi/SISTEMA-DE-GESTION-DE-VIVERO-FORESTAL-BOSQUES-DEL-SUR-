# Sistema de Gestión de Vivero – Despliegue en Hostinger (Compartido)

Esta guía describe cómo desplegar y ejecutar el proyecto en un hosting compartido (Hostinger) sin dependencias adicionales (sin Composer, sin `.env`). El backend es PHP puro y el frontend son archivos estáticos HTML/JS.

## Estructura a subir a `public_html/`

Sube el repositorio completo manteniendo esta estructura de carpetas (rutas relativas a `public_html/`):

- `.../Sistema_Gestion_Vivero/frontend/html/`
  - `index.html`
  - `login.html`
- `.../Sistema_Gestion_Vivero/frontend/js/`
  - `api.js` (apunta en relativo a la API del backend)
  - `main.js`, `auth.js`
- `.../Sistema_Gestion_Vivero/frontend/css/`
  - `styles.css` (estilos unificados y utilidades, con tema oscuro opcional)
- `.../Sistema_Gestion_Vivero/backend/php/api/`
  - `*.php` (endpoints de la API)
- `.../Sistema_Gestion_Vivero/backend/php/Controllers/`
- `.../Sistema_Gestion_Vivero/backend/php/service/`
- `.../Sistema_Gestion_Vivero/backend/php/repository/`
- `.../Sistema_Gestion_Vivero/backend/php/conection.php`

Sugerencia: no necesitas subir `composer.json`, `vendor/` ni `.env`. El sistema no depende de ellos en el hosting compartido.

## Configuración de Base de Datos

Edita el archivo `backend/php/conection.php` y coloca tus credenciales reales en las constantes:

```php
define('DB_HOST', 'tu_host_mysql');
define('DB_NAME', 'tu_nombre_bd');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_password');
```

### Variables de entorno (opcional, para debug)

En Hostinger hPanel, puedes definir variables de entorno para activar el modo debug:
- `APP_DEBUG=1` (activa mensajes detallados de error y endpoints de diagnóstico)
- `APP_DEBUG=0` (o no definida, modo producción)

**Importante:** El modo debug (`APP_DEBUG=1`) solo debe usarse temporalmente para diagnóstico, ya que expone información sensible.

El archivo ya maneja CORS básico y sesiones PHP. No es necesario configurar Composer ni `.env`.

## URLs principales (mismo dominio)

- Frontend
  - `https://TU-DOMINIO/.../Sistema_Gestion_Vivero/frontend/html/login.html`
  - `https://TU-DOMINIO/.../Sistema_Gestion_Vivero/frontend/html/index.html`
- Backend API (referenciada de forma relativa por `frontend/js/api.js`)
  - `https://TU-DOMINIO/.../Sistema_Gestion_Vivero/backend/php/api/`

El archivo `frontend/js/api.js` calcula la ruta de la API en relativo partiendo de `login.html`/`index.html`, por lo que no necesitas modificar URLs al cambiar de dominio.

## Inicialización (catálogos y usuario admin)

### Opción A: Desde el frontend
1. Visita `login.html`
2. Pulsa el botón "Crear usuario admin de prueba"
3. Se crearán catálogos base y el usuario admin si no existe:
   - Usuario: `admin`
   - Contraseña: `admin123`
4. Los campos se autocompletarán para iniciar sesión

### Opción B: Directamente por URL
Abre:
- `https://TU-DOMINIO/.../Sistema_Gestion_Vivero/backend/php/api/catalogs.php?seed=1`

### Opción C: Desde el Dashboard
1. Visita `index.html` (Dashboard)
2. Pulsa "Inicializar catálogos"

## Autenticación y Seguridad

- Inicia sesión en `login.html`. Si no hay sesión activa, `index.html` redirige automáticamente a `login.html`.
- Sesiones PHP con cookies (seguras por defecto). Las llamadas `fetch` del frontend incluyen credenciales.
- Operaciones protegidas por sesión y rol (`require_role()` en controladores):
  - POST/PUT: Admin (1) y Técnico (2)
  - DELETE: solo Admin (1)
  - Módulos: Especies, Lotes, Fases, Plantas, Inventario, Tratamientos, Condiciones, Despachos
- Lecturas (GET) quedan públicas por compatibilidad. Si deseas forzar autenticación en GET, podemos activarlo.

### Migración automática de contraseñas

El sistema soporta usuarios con contraseñas en texto plano (migración automática):
1. Si un usuario tiene contraseña en texto plano en `password_hash`, al iniciar sesión:
   - El sistema verifica la contraseña en texto plano
   - Si es correcta, la migra automáticamente a un hash seguro (`password_hash`)
   - Las siguientes sesiones usan el hash seguro
2. Esto permite migrar usuarios existentes sin perder acceso

### Endpoints de diagnóstico (solo con APP_DEBUG=1)

#### `backend/php/api/db_check.php`
Verifica el estado del sistema:
- Versión de PHP
- Drivers PDO disponibles
- Conexión a base de datos
- Cantidad de usuarios
- Detalles del usuario admin (si existe)

**Uso:**
```bash
curl "https://TU-DOMINIO/.../backend/php/api/db_check.php"
```

#### `backend/php/api/set_password.php`
Permite asignar/actualizar contraseñas de forma segura (solo en debug):

**Uso:**
```bash
curl -i -H "Content-Type: application/json" -X POST \
  -d '{"username":"admin","new_password":"NuevaClaveFuerte#2025"}' \
  "https://TU-DOMINIO/.../backend/php/api/set_password.php"
```

**Importante:** Desactiva `APP_DEBUG` después de usar estos endpoints.

## Uso (flujo básico)

1. Abrir `login.html` y autenticarse.
2. En `index.html`:
   - Dashboard: KPIs (Especies, Lotes, Plantas, Órdenes), Resumen de inventario, Últimos lotes
   - Especies: crear/listar
   - Lotes: crear lote (proveedor + semillas + producción)
   - Fases: iniciar/cerrar y ver historial
   - Plantas: etiquetar (QR) y listar
   - Inventario: actualizar clasificación/tamaño
   - Tratamientos/Condiciones: registrar y listar
   - Despachos: crear orden y agregar líneas

## Notas para Hosting Compartido

- PHP 8.x recomendado.
- No se requiere CLI ni ejecución de servidores locales.
- Mantén los permisos por defecto (archivos 644, carpetas 755).
- Si frontend y backend están en el mismo dominio (recomendado), no tendrás problemas de CORS.

## Apariencia (CSS)

- `frontend/css/styles.css` contiene estilos unificados (sin CSS inline) con utilidades y mejoras de accesibilidad (`:focus-visible`).
- Tema oscuro opcional: agrega `theme-dark` al `body` para activar el esquema oscuro.

## Solución de problemas rápida

### Errores comunes

#### 500 Internal Server Error
1. **Activa el modo debug:**
   - En Hostinger hPanel → Environment Variables → `APP_DEBUG=1`
2. **Revisa el log de errores:**
   - Hostinger hPanel → Error Logs
3. **Usa el endpoint de diagnóstico:**
   ```bash
   curl "https://TU-DOMINIO/.../backend/php/api/db_check.php"
   ```
4. **Verifica:**
   - Extensión `pdo_mysql` activada en Hostinger
   - Credenciales de BD correctas en `conection.php`
   - PHP versión 8.x

#### 401 Unauthorized
- Usuario o contraseña incorrectos
- Si es usuario existente con contraseña en texto plano, verifica que coincida exactamente
- Revisa la tabla `usuarios` en phpMyAdmin

#### 403 Forbidden
- El usuario no tiene el rol adecuado para la operación
- Revisa `rol_id` en la tabla `usuarios` (1=Admin, 2=Técnico)

#### No mantiene sesión
- Confirma que frontend y backend estén en el mismo dominio
- Verifica que `credentials: 'include'` esté en `frontend/js/api.js` (ya está configurado)
- Limpia cookies del navegador y prueba de nuevo

#### Dashboard vacío
- Pulsa "Inicializar catálogos" en `index.html`
- Revisa que existan datos en las tablas:
  - `usuarios`
  - `roles`
  - `especies`
  - `lotes`
  - `fases_lote`

### Pasos de diagnóstico completos

1. **Verificar estructura de archivos:**
   ```bash
   ls -la public_html/.../Sistema_Gestion_Vivero/
   ```

2. **Probar conexión a BD:**
   ```bash
   curl "https://TU-DOMINIO/.../backend/php/api/db_check.php"
   ```

3. **Crear usuario admin:**
   ```bash
   curl "https://TU-DOMINIO/.../backend/php/api/catalogs.php?seed=1"
   ```

4. **Verificar usuario en BD:**
   ```sql
   SELECT id, username, password_hash, rol_id FROM usuarios WHERE username = 'admin';
   ```

5. **Probar login:**
   - Abre `login.html`
   - Usuario: `admin`, Contraseña: `admin123`

6. **Si falla login con usuarios existentes:**
   - Verifica que la contraseña en `password_hash` sea texto plano (no empiece con `$`)
   - Usa `set_password.php` para asignar una nueva contraseña segura

### Migración de usuarios existentes

Si tienes usuarios con contraseñas en texto plano:
1. **Verifica el estado actual:**
   ```sql
   SELECT id, username, password_hash FROM usuarios;
   ```
2. **Para cambiar contraseñas a valores seguros:**
   ```bash
   curl -i -H "Content-Type: application/json" -X POST \
     -d '{"username":"usuario","new_password":"NuevaClaveFuerte#2025"}' \
     "https://TU-DOMINIO/.../backend/php/api/set_password.php"
   ```
3. **O deja que el sistema migre automáticamente:**
   - Los usuarios inician sesión con su contraseña actual (texto plano)
   - El sistema la migra a hash seguro en el primer login exitoso

## Diseño Responsivo y Funcionalidades de Usuario

### Diseño Totalmente Responsivo

El sistema ha sido optimizado para funcionar perfectamente en todos los dispositivos y tamaños de pantalla:

- **Dispositivos móviles (320px - 480px):** Diseño optimizado para smartphones pequeños con navegación vertical y formularios adaptados
- **Tablets (481px - 768px):** Interfaz equilibrada para tablets medianas y pequeñas
- **Laptops y tablets grandes (769px - 1024px):** Diseño adaptado para pantallas más grandes
- **Escritorio (1025px+):** Experiencia completa con todas las funcionalidades visibles
- **Orientación horizontal:** Manejo específico para dispositivos móviles en modo landscape
- **Modo impresión:** Estilos optimizados para imprimir informes y datos

### Características del Diseño Responsivo:

- **Header adaptativo:** Se reorganiza de horizontal (escritorio) a vertical (móvil)
- **Navegación flexible:** Los botones se ajustan y envuelven según el espacio disponible
- **Grid layouts:** Las cuadrículas se adaptan de 4 columnas (escritorio) a 1 columna (móvil)
- **Formularios optimizados:** Los campos se reorganizan en una sola columna en dispositivos móviles
- **Tablas responsivas:** Con scroll horizontal en dispositivos pequeños y texto truncado con elipsis
- **Tipografía escalable:** Los tamaños de fuente se ajustan según el dispositivo
- **KPIs y tarjetas:** Se redimensionan y reorganizan para mantener la legibilidad

### Funcionalidad "Volver al Login"

Se ha implementado un sistema de navegación mejorado con acceso rápido a la página de login:

- **Botón en el header:** Acceso conveniente en la esquina superior derecha junto al botón de cerrar sesión
- **Botón en el footer:** Acceso prominente en la parte inferior de la página ("al todo del final")
- **Estilo consistente:** Ambos botones utilizan la clase `.btn-back-login` con diseño gris y hover effects
- **Totalmente responsivo:** El botón del footer se adapta a ancho completo en dispositivos móviles
- **Accesible:** Enlaces claros y visibles desde cualquier sección de la aplicación

### Mejoras Técnicas Recientes:

- **Eliminación de CSS inline:** Todo el estilo se ha movido al archivo `styles.css` externo
- **Nuevas clases CSS:** `.header-container`, `.header-title`, `.user-info`, `.btn-back-login`, `.footer-content`
- **Optimizaciones de rendimiento:** Comentarios para propiedades CSS experimentales
- **Compatibilidad mejorada:** Soporte para navegadores modernos con fallbacks apropiados

### Notas finales

- **Desactiva `APP_DEBUG` en producción**
- **Usa contraseñas fuertes** para todos los usuarios
- **Verifica permisos de archivos:** 644 para archivos, 755 para carpetas
- **Limpia caché del navegador** después de cada cambio

Si persisten los problemas, revisa los logs de error de Hostinger y comparte el resultado de `db_check.php`.
