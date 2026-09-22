# Utilidades de desarrollo

Ejecuta los comandos desde la raíz del proyecto.

| Archivo | Cómo se usa | Propósito |
| --- | --- | --- |
| `vite-dev.mjs` | `npm run dev` | Inicia Vite para trabajar en CSS y JavaScript. |
| `vite-build.mjs` | `npm run build` | Genera los recursos finales en `public/build`. |
| `prepare-local-database.php` | `php scripts/prepare-local-database.php` | Crea la base MySQL local configurada si todavía no existe; no elimina tablas. Se usa al preparar una instalación nueva. |
| `test-mysql-concurrency.php` | `php scripts/test-mysql-concurrency.php` | Comprueba conflictos de reservas en una base temporal con prefijo `proyecto_clis_qa_`; la crea y elimina al terminar. Requiere MySQL local y permisos para crear bases. |

Las pruebas habituales se ejecutan con `php artisan test`, usando SQLite en memoria según `phpunit.xml`. El script de concurrencia es una comprobación adicional para cambios en las reglas o bloqueos de citas, no un paso necesario para editar colores.

No ejecutes `migrate:fresh` sobre la base del centro: borra las tablas. Consulta `docs/IMPLEMENTACION.md` para las instrucciones de instalación y migración.
