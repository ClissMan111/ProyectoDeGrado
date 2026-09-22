# Guía del proyecto Villa Israel

La carpeta del sistema es `C:/Users/Hp/Desktop/ProyectoDeGrado`. Abre primero su `README.md`: funciona como índice de toda la documentación.

## Mapa de carpetas

| Carpeta | Qué contiene |
| --- | --- |
| `app/Http/Controllers` | Acciones de citas, usuarios, administración y reportes. |
| `app/Http/Middleware` | Acceso, sesión, roles y protección de solicitudes. |
| `app/Models` | Usuarios, pacientes, médicos, especialidades, horarios, citas e historial. |
| `app/Services` | Reglas de reserva, reprogramación, cancelación e indisponibilidades. |
| `config` | Configuración del sistema. `centro.php` reúne el contenido institucional. |
| `database/migrations` | Evolución de la estructura de la base de datos. |
| `database/factories` y `database/seeders` | Preparación de datos de desarrollo y pruebas. |
| `resources/views` | Pantallas Blade y piezas de interfaz reutilizables. |
| `resources/css` | Estilos organizados por base, distribución, tema, página y componente. |
| `resources/js/modules` | Interacciones organizadas por responsabilidad. |
| `public/images` | Imágenes y fondos que usa el navegador. |
| `public/build` | CSS y JavaScript compilados por Vite. |
| `routes` | Direcciones y permisos de acceso a cada pantalla. |
| `tests` | Comprobaciones automáticas del sistema. |
| `scripts` | Utilidades de desarrollo, con su propio README. |
| `docs` | Guías del proyecto e implementación. |
| `docs/archivo/vistas-originales` | Plantillas antiguas sin rutas activas, conservadas como referencia. |
| `storage` | Registros, caché, sesiones y respaldos de código. |
| `bootstrap` | Inicio de Laravel y cachés del framework. |
| `vendor` y `node_modules` | Dependencias instaladas de PHP y JavaScript. |

## Qué archivo editar

Todas las rutas siguientes son relativas a la carpeta del proyecto.

| Quiero cambiar… | Archivo o carpeta |
| --- | --- |
| Inicio público | `resources/views/public/home.blade.php` |
| Acerca de nosotros | `resources/views/public/about.blade.php` |
| Contacto | `resources/views/public/contact.blade.php` |
| Dirección, teléfono, WhatsApp, correo, misión y visión | `config/centro.php` |
| Ubicación del mapa de Contacto | `config/centro.php`: `map_query` y `map_embed_url`. Consulta `docs/MAPA-Y-CONTACTO.md`. |
| Menú y pie del sitio público | `resources/views/layouts/public.blade.php` |
| Barra lateral y cabecera de los tres paneles | `resources/views/layouts/dashboard.blade.php` |
| Panel administrador | `resources/views/dashboards/admin.blade.php` |
| Panel paciente | `resources/views/dashboards/patient.blade.php` |
| Panel médico | `resources/views/dashboards/doctor.blade.php` |
| Ingreso y registro | `resources/views/auth` |
| Formularios y listado de citas | `resources/views/citas` |
| Gestión y reportes administrativos | `resources/views/admin` |
| Agenda compartida | `resources/views/partials/dashboard-agenda.blade.php` |
| Logo del sistema | `resources/views/components/brand-mark.blade.php` y `public/images/brand` |
| Fotografías y su presentación | `public/images/care`, `resources/views/components/care-photo.blade.php` y `resources/css/theme/identity.css` |
| Iconos | `resources/views/components/icon.blade.php` |
| Fondos suaves, tamaño de letra y contraste de tarjetas | `resources/css/theme/comfort.css` |
| Paleta y detalles compartidos de la marca | `resources/css/theme/brand.css` |
| Distribución de paneles | `resources/css/layouts/workspace.css` |
| Acerca de nosotros y Contacto: estilos | `resources/css/pages/institutional.css` |
| Maqueta 3D | `resources/views/components/clinic-model.blade.php`, `resources/css/components/clinic-model.css` y `resources/js/modules/clinic-model.js` |
| Copiar dirección | `resources/js/modules/contact.js` |
| Menú público y preguntas desplegables | `resources/js/modules/navigation.js` |
| Búsqueda de secciones, paneles y agenda | `resources/js/modules/workspace.js` |
| Efectos visuales y botón de volver arriba | `resources/js/modules/appearance.js` |
| Disponibilidad del formulario de reserva | `resources/js/modules/reservations.js` |
| Direcciones del sitio | `routes/web.php` |
| Reglas de citas | `app/Services/CitaService.php` y `config/citas.php` |

## Archivos que deben permanecer en la raíz

Laravel y sus herramientas esperan encontrar `artisan`, `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `vite.config.js`, `phpunit.xml` y `.env` en la raíz. La organización mantiene esa estructura para que los comandos sigan funcionando.

`resources/css/app.css` y `resources/js/app.js` son los puntos de entrada: importan los archivos organizados en subcarpetas. Si añades un módulo, agrégalo a su entrada y ejecuta `npm run build`.

## Archivos generados y respaldos

No edites manualmente `vendor`, `node_modules`, `public/build` ni las cachés de `storage` y `bootstrap/cache`. `.npm-cache` y `.phpunit.result.cache` son archivos de herramientas, no pantallas del sistema; están excluidos de Git.

Los respaldos de las intervenciones están en `storage/app/backups`. El respaldo `experiencia-20260922` contiene los archivos de código anteriores y el manifiesto de esta actualización. No es un respaldo de la base de datos.

La reorganización conserva las carpetas estándar de Laravel. Agrupa estilos y JavaScript, traslada `IMPLEMENTACION.md` a `docs` y archiva las plantillas iniciales `welcome.blade.php` y `public/booking.blade.php`, que no tenían rutas activas. El formulario real de reservas sigue en `resources/views/citas/create.blade.php`.
