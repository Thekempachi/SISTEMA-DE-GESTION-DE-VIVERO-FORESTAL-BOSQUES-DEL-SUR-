# Sistema de Gestión de Vivero – Despliegue en Hostinger (Compartido)

Esta guía describe únicamente cómo desplegar y ejecutar el proyecto en un hosting compartido (Hostinger) sin dependencias adicionales (sin Composer, sin `.env`). El backend es PHP puro y el frontend son archivos estáticos HTML/JS.

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
- `.../Sistema_Gestion_Vivero/backend/php/Domain/`
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

El archivo ya maneja CORS básico y sesiones PHP. No es necesario configurar Composer ni `.env`.

## URLs principales (mismo dominio)

- Frontend
  - `https://TU-DOMINIO/.../Sistema_Gestion_Vivero/frontend/html/login.html`
  - `https://TU-DOMINIO/.../Sistema_Gestion_Vivero/frontend/html/index.html`
- Backend API (referenciada de forma relativa por `frontend/js/api.js`)
  - `https://TU-DOMINIO/.../Sistema_Gestion_Vivero/backend/php/api/`

El archivo `frontend/js/api.js` calcula la ruta de la API en relativo partiendo de `login.html`/`index.html`, por lo que no necesitas modificar URLs al cambiar de dominio.

## Inicialización (catálogos y usuario admin)

1. Visita `index.html` (Dashboard) y pulsa “Inicializar catálogos” o abre:
   - `https://TU-DOMINIO/.../Sistema_Gestion_Vivero/backend/php/api/catalogs.php?seed=1`
2. Se crearán catálogos base y el usuario admin si no existe:
   - Usuario: `admin`
   - Contraseña: `admin123`

## Autenticación y Seguridad

- Inicia sesión en `login.html`. Si no hay sesión activa, `index.html` redirige automáticamente a `login.html`.
- Sesiones PHP con cookies (seguras por defecto). Las llamadas `fetch` del frontend incluyen credenciales.
- Operaciones protegidas por sesión y rol (`require_role()` en controladores):
  - POST/PUT: Admin (1) y Técnico (2)
  - DELETE: solo Admin (1)
  - Módulos: Especies, Lotes, Fases, Plantas, Inventario, Tratamientos, Condiciones, Despachos
- Lecturas (GET) quedan públicas por compatibilidad. Si deseas forzar autenticación en GET, podemos activarlo.

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

- No carga la API: revisa `backend/php/conection.php` (credenciales). Verifica que `api/*.php` sean accesibles por URL.
- No mantiene sesión: confirma que el frontend y backend estén en el mismo dominio/ruta y que `credentials: 'include'` esté activo (ya lo está en `api.js`).
- 403 al crear/editar: el usuario no tiene rol adecuado. Revisa `rol_id` del usuario en DB (1=Admin, 2=Técnico).
- Dashboard vacío: pulsa “Inicializar catálogos” en `index.html` y recarga. Asegura datos en tablas.
