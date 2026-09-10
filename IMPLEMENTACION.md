# Sistema de citas de Villa Israel

El proyecto funciona con Laravel 10, PHP 8.1 o posterior y MySQL. Mantiene la paleta original y añade fondos SVG locales, formularios funcionales y navegación por roles.

## Ejecutar localmente

1. Iniciar MySQL desde XAMPP.
2. Desde la carpeta del proyecto, ejecutar `php artisan serve --host=127.0.0.1 --port=8000`.
3. Abrir http://127.0.0.1:8000 e ingresar por `/ingresar`.

La base configurada es `proyecto_clis`. Las migraciones ya se aplicaron durante esta implementación. Para una instalación nueva, ajustar las credenciales de `.env`, ejecutar `php scripts/prepare-local-database.php` y después `php artisan migrate`. No usar `migrate:fresh` sobre datos del centro.

Para compilar cambios visuales, ejecutar `npm run build`. El resultado compilado está en `public/build`; no hace falta mantener Vite abierto para usar el sistema.

## Cuentas de demostración

Se crearon `administrador@villaisrael.test`, `medico@villaisrael.test` y `paciente@villaisrael.test`. La contraseña generada se entregó en la conversación y puede cambiarse en Cuenta y seguridad. `php artisan villa:demo` prepara datos solamente en entorno local y conserva las contraseñas de cuentas existentes.

El médico Daniel Rojas Demo está asociado a Medicina general y Fisioterapia, con horarios de lunes a viernes de 08:00 a 12:00 en bloques de 30 minutos. Son datos de prueba, no disponibilidad real del centro. Los registros de la prueba de navegador se identifican como Demo y su cita quedó cancelada con el motivo de prueba.

## Alcance implementado

| Requisito | Implementación |
| --- | --- |
| RF-01 y RF-02 | Registro de paciente, inicio/cierre de sesión, cuenta activa, permisos y cambio de contraseña |
| RF-03 | Consulta y edición administrativa de pacientes registrados |
| RF-04 y RF-05 | Médicos con múltiples especialidades, edición y desactivación de cuentas y catálogo |
| RF-06 y RF-07 | Horarios semanales, duración de citas, detección de solapamientos y consulta de turnos disponibles |
| RF-08 | Reserva por paciente o por administrador en nombre de un paciente existente |
| RF-09 y RF-10 | Cancelación y reprogramación de citas propias con historial y transacciones |
| RF-11 y RF-12 | Agenda del médico, confirmación administrativa, atención, cancelación y no asistencia |
| RF-13 | Listado de citas y cronología de cambios, filtrados según permisos |
| RF-14 | Reportes por fechas, médico, especialidad y estado, con descarga CSV |

## Reglas iniciales configurables

En `config/citas.php` se encuentran los valores iniciales. Pueden establecerse en `.env` con `CITAS_ANTICIPACION_MINUTOS`, `CITAS_CAMBIOS_MINUTOS` y `CITAS_HORIZONTE_DIAS`.

- Anticipación mínima: 0 minutos; siempre se exige que el inicio sea futuro.
- Cancelación/reprogramación del paciente: antes del inicio, solo en pendiente o confirmada.
- Horizonte de reservas: 60 días.
- Reprogramar conserva médico y especialidad, cambia fecha/hora y vuelve a pendiente.
- Pendiente puede pasar a confirmada, cancelada o no_asistio. Confirmada puede pasar a atendida, cancelada o no_asistio. Los estados finales no se reabren.
- El administrador confirma y cancela; médico y administrador pueden registrar atención o no asistencia cuando la cita ya comenzó. El médico solo actúa sobre su agenda.
- Una cita cancelada libera el horario. El sistema rechaza también citas simultáneas de un paciente con médicos distintos.
- Los horarios son comunes a las especialidades del médico: una reserva ocupa su tiempo en todas ellas.
- Cambiar o desactivar horarios, médicos o especialidades impide nuevas reservas afectadas, pero no cancela automáticamente citas existentes. Administración debe revisarlas.
- El reporte filtra por fecha programada actual de la cita. El indicador de reprogramaciones cuenta citas del resultado que se reprogramaron al menos una vez, no la cantidad de cambios.

Para alinear el documento con Laravel se conserva `users.name/email` en lugar de `usuarios.nombre/correo`; `usuario_id` referencia `users`. `dia_semana` usa ISO numérico: lunes 1 a domingo 7. `historial_citas` no tiene `updated_at`, porque cada cambio crea un evento nuevo.

## Pruebas

Verificación local realizada el 9 de septiembre de 2026: **21 pruebas y 139 aserciones aprobadas**, compilación Vite correcta y dos escenarios concurrentes aprobados sobre MySQL. En navegador se probó inicio de sesión de los tres roles, reserva, reprogramación, cancelación con historial, filtros de reportes y navegación móvil. Se generó un respaldo SQL; su restauración todavía debe comprobarse antes de operar con datos reales.

Con XAMPP en este equipo: `php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit`. La configuración de PHPUnit fuerza SQLite en memoria para no tocar MySQL del proyecto. Si la extensión ya está activa, ejecutar sin `-d extension=pdo_sqlite`.

`php scripts/test-mysql-concurrency.php` valida reservas simultáneas para el mismo médico y el mismo paciente. Crea una base temporal `proyecto_clis_qa_...`, usa dos procesos concurrentes y elimina únicamente esa base al finalizar. Requiere MySQL local y permisos de crear/eliminar esa base temporal.

## Respaldo y operación

Ejecutar `php artisan villa:backup` para generar SQL en `storage/app/backups`, fuera de `public`. Puede configurarse la ruta de mysqldump con `MYSQLDUMP_BINARY`. Acordar con el centro frecuencia, retención y copia a una ubicación separada. No se instaló una tarea programada en Windows.

Antes de usar datos reales, reemplazar los datos Demo, validar horarios y reglas con el centro, definir respaldo periódico y comprobar una restauración en una base separada. La puesta en producción requiere HTTPS, `APP_DEBUG=false`, credenciales de base con permisos acotados y configuración del servidor apuntando a `public`.

No incluye historia clínica, recetas, pagos, notificaciones por WhatsApp/SMS, excepciones por feriados/vacaciones ni despliegue público. Los RNF de carga, disponibilidad y aceptación necesitan validación en el entorno final; las pruebas locales no certifican una carga real del centro.
