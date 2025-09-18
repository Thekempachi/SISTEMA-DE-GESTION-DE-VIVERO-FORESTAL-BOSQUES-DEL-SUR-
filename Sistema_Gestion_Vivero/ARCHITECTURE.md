# Arquitectura del Proyecto (Optimizada para Hosting Compartido)

Este documento describe la arquitectura en capas del proyecto y su organización pensada para funcionar en hosting compartido (sin Composer, sin autoload externo y sin `.env`).

## Capas del Backend (PHP)

- **API (puerta de entrada)**: `backend/php/api/*.php`
  - Ficheros muy delgados. Solo incluyen al controlador correspondiente y delegan toda la lógica.
  - Ejemplo: `api/especies.php` llama a `Controllers/EspeciesController.php`.

- **Controladores**: `backend/php/Controllers/*.php`
  - Reciben la petición (método HTTP, query string), validan parámetros mínimos, llaman al Servicio y regresan JSON con `send_json()`.
  - Aplican seguridad de sesión/rol donde corresponda (`require_auth()` / `require_role()`).

- **Servicios (lógica de negocio)**: `backend/php/service/*Service.php`
  - Encapsulan reglas de negocio, validaciones y, si hace falta, transacciones simples.
  - No conocen el protocolo HTTP; retornan datos o lanzan excepciones.

- **Repositorios (acceso a datos)**: `backend/php/repository/*Repository.php`
  - Operaciones SQL mediante PDO. No hay ORMs ni dependencias externas.

- **Utilidades y Conexión**: `backend/php/conection.php`
  - Crea la conexión PDO (`db()`), utilidades HTTP (`send_json`, `json_input`), helpers de sesión (`ensure_session_started`, `current_user`, `require_auth`, `require_role`) y CORS básico.
  - Contiene constantes `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` editables para el entorno del hosting.

## Frontend (HTML/JS)

- **Páginas**: `frontend/html/`
  - `login.html`: ingreso con usuario/contraseña.
  - `index.html`: interfaz principal con Dashboard (KPIs, resumen de inventario, últimos lotes) y secciones (Especies, Lotes, Fases, Plantas, Inventario, Tratamientos, Condiciones, Despachos).

- **JS (ES Modules)**: `frontend/js/`
  - `api.js`: cliente `fetch` con `credentials: 'include'` (necesario para cookies de sesión). Calcula la URL de la API de forma relativa desde `login.html`/`index.html` hasta `backend/php/api/`.
  - `auth.js`: lógica de login (`auth.php?action=login|me|logout`).
  - `main.js`: verificación de sesión al iniciar, Dashboard (render de KPIs y resúmenes) y lógica de UI por sección.

- **CSS**: `frontend/css/styles.css`
  - Estilos unificados sin inline, con utilidades (`.center-screen`, `.w-420`, `.p-20`, `.rounded-12`, `.mt-8`, `.mt-12`, `.w-full`, `.kpi-number`, etc.).
  - Estructura por secciones (Variables/Reset, Base, Componentes, Formularios, Tablas, Utilidades) y mejoras de a11y (`:focus-visible`).
  - Tema oscuro opcional: `body.theme-dark` redefine variables.
  - **Diseño totalmente responsivo** con 6 breakpoints: móviles (320px-480px), tablets (481px-768px), laptops (769px-1024px), escritorio (1025px+), landscape y modo impresión.
  - **Nuevas clases CSS**: `.header-container`, `.header-title`, `.user-info`, `.btn-back-login`, `.footer-content` para mejor organización y mantenibilidad.
  - **Componentes adaptativos**: Header (horizontal↔vertical), navegación flexible, grid layouts (4↔1 columnas), formularios optimizados y tablas responsivas.
  - **Funcionalidad "Volver al Login"**: Implementada con botones en header y footer para mejor navegación y accesibilidad.

## Flujo de Peticiones

1. El navegador carga `login.html` o `index.html` (estático).
2. El front llama a la API relativa: `../../backend/php/api/*.php`.
3. El `*.php` en `api/` incluye el Controlador y ejecuta `Controller::handle()`.
4. El Controlador usa Servicios y Repositorios para atender la solicitud y responde JSON.

## Autenticación y Seguridad

- Basada en **sesiones PHP** y **contraseñas hasheadas** (`password_hash`/`password_verify`).
- `frontend/js/api.js` envía cookies con `credentials: 'include'`.
- Helpers en `conection.php`:
  - `require_auth()` exige sesión.
  - `require_role($ids)` restringe por rol (opcional).
- Políticas aplicadas actualmente:
  - POST/PUT protegidos para roles Admin (1) y Técnico (2) donde corresponda.
  - DELETE restringido a Admin (1).
  - Módulos afectados: Especies, Lotes, Fases, Plantas, Inventario, Tratamientos, Condiciones y Despachos.
  - GET públicos por compatibilidad (se pueden cerrar con `require_auth()` si se desea privacidad total).

## Estructura de Carpetas (resumen)

```
Sistema_Gestion_Vivero/
├─ backend/
│  └─ php/
│     ├─ api/                # Entradas HTTP muy delgadas (delegan en controladores)
│     ├─ Controllers/        # Controladores (HTTP -> Servicios)
│     ├─ service/            # Lógica de negocio
│     ├─ repository/         # Acceso a datos
│     └─ conection.php       # PDO, utilidades HTTP, sesiones y CORS
└─ frontend/
   ├─ html/
   │  ├─ index.html          # Dashboard + secciones
   │  └─ login.html          # Inicio de sesión
   ├─ js/
   │  ├─ api.js              # Cliente API y helpers
   │  ├─ auth.js             # Flujo de autenticación
   │  └─ main.js             # Lógica de Dashboard y secciones
   └─ css/
      └─ styles.css          # Estilos unificados y utilidades
```

## Capas
- Controladores: reciben la solicitud (`GET/POST/PUT/DELETE`), invocan servicios y devuelven JSON estandarizado.
- Servicios: encapsulan la lógica de negocio y validaciones. Orquestan uno o varios repositorios.
- Repositorios: encapsulan SQL/consultas y devuelven arrays PHP (DTO simples).

## Cambios ya aplicados
- Unificación de CSS y eliminación de estilos inline.
- Dashboard real (KPIs, resumen de inventario, últimos lotes).
- Reglas por rol aplicadas en controladores (POST/PUT roles 1-2; DELETE solo rol 1).
- Endpoints existentes mantienen rutas y formatos de respuesta.
- **Diseño totalmente responsivo** implementado con 6 breakpoints para todos los dispositivos.
- **Nueva arquitectura CSS** con clases semánticas para mejor mantenibilidad.
- **Funcionalidad "Volver al Login"** con botones en header y footer para mejor UX.
- **Optimizaciones de rendimiento** y compatibilidad con comentarios para propiedades experimentales.

## Recomendaciones de seguridad
- `backend/php/conection.php` soporta variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`. Configure el hosting para no hardcodear credenciales en el repo.
- Para desarrollo local, se recomienda usar un archivo `.env` (no versionado) para las credenciales y `APP_DEBUG`. Aunque este proyecto no usa Composer, puedes cargar el `.env` manualmente en tus scripts de desarrollo.

## Plan para migrar el resto de endpoints
Para cada archivo en `backend/php/api/`:
1. Crear `Controllers/<Recurso>Controller.php`.
2. Crear `Domain/<Recurso>/<Recurso>Service.php` y `<Recurso>Repository.php`.
3. Reescribir el endpoint `api/<recurso>.php` para delegar en el controlador.

Siguientes candidatos:
- `lotes.php`, `fases.php`, `plantas.php`, `tratamientos.php`, `condiciones.php`, `despachos.php`, `catalogs.php`.

## Estado de modularización del frontend
- Uso de ES Modules activo: `api.js`, `auth.js`, `main.js`.
- Opcional: separar por feature en el futuro (`especies.js`, `lotes.js`, etc.) si crece la complejidad.

## Estándar de respuesta JSON
- Éxito: `{ "ok": true, ... }`
- Error: `{ "error": "mensaje" }` y `http_response_code` apropiado (400/404/405/500).

## Cómo extender
- Para un nuevo recurso `X`:
  - Crear `Domain/X/XRepository.php` y `XService.php` con operaciones.
  - Crear `Controllers/XController.php` que use el servicio.
  - Actualizar `api/x.php` para delegar en el controlador.

## Pruebas manuales rápidas
- Especies: listar y crear desde `index.html` (sección Especies).
- Inventario: listar y actualizar (sección Inventario).

## Próximos pasos sugeridos (backend)
- **Completado:** Migrar todos los endpoints de la API a la nueva estructura de controladores, servicios y repositorios.
- Añadir logs/try-catch más específicos por operación.
- Añadir validaciones de dominio en servicios (por ejemplo, rangos y combinaciones válidas).

## Próximos pasos sugeridos (frontend)
- Separar `api.js` y utilidades.
- Dividir `script.js` por páginas/secciones.
- Añadir manejo de errores y spinners reutilizables.
- **Completado**: Implementación de diseño totalmente responsivo con 6 breakpoints.
- **Completado**: Optimización de arquitectura CSS con clases semánticas.
- **Completado**: Mejora de UX con funcionalidad "Volver al Login" dual (header + footer).

---
Esta arquitectura facilita mantener y escalar el sistema sin cambiar URLs existentes, minimizando riesgos de regresión mientras se mejora la organización del código.
