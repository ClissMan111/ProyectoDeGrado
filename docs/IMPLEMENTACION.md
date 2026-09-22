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
| RF-06 y RF-07 | Horarios semanales e indisponibilidades por fecha/hora; exclusión de feriados, vacaciones, ausencias o bloqueos; validación de citas afectadas y turnos disponibles |
| RF-08 | Reserva por paciente o por administrador en nombre de un paciente existente |
| RF-09 y RF-10 | Cancelación y reprogramación de citas propias con historial y transacciones |
| RF-11 y RF-12 | Agenda del médico, confirmación administrativa, atención, cancelación y no asistencia |
| RF-13 | Listado de citas y cronología de cambios, filtrados según permisos |
| RF-14 | Reportes por fechas, médico, especialidad y estado, con descarga CSV |

## Reglas de la documentación oficial

Las reglas se contrastaron con `ProyectodeGradoClisman.docx`, especialmente RF-06 a RF-12, CU06 a CU10, la matriz de transición de estados y el diccionario de datos. El Word no se modifica.

- Reserva y nuevo horario de reprogramación: al menos 1 hora de anticipación, incluida la frontera exacta de 60 minutos.
- Paciente y administrador pueden cancelar citas pendientes o confirmadas únicamente antes del inicio.
- El paciente puede reprogramar sus propias citas pendientes o confirmadas antes de su inicio. La reprogramación conserva médico, especialidad y estado y registra los valores anteriores y nuevos en el historial.
- Pendiente pasa a confirmada o cancelada. Confirmada pasa a atendida, cancelada o no_asistio. Los estados finales no se reabren.
- Solo el administrador confirma; médico y administrador registran atención o no asistencia de citas confirmadas cuando han comenzado. El médico solo actúa sobre su agenda.
- Solo pendiente y confirmada ocupan disponibilidad. Los bloqueos se aplican al médico en todas sus especialidades, incluso cuando coinciden parcialmente con un turno.
- Un bloqueo activo no puede guardarse si afecta citas pendientes o confirmadas. El mensaje identifica las citas para gestionarlas antes de completar el bloqueo; ninguna se elimina o cancela automáticamente.
- Administración dispone de filtros por médico en Horarios e Indisponibilidades, filtros por especialidad en Citas y acceso al historial de cada paciente desde Pacientes.
- Se retira el límite de 60 días de reserva que no estaba definido en el documento.
- Los reportes filtran por fecha programada actual; el indicador de reprogramaciones cuenta citas que se reprogramaron al menos una vez.

La base usa `usuarios.nombre/correo`, `rol` enum en MySQL y `horarios.dia_semana` como varchar(15), conforme al diccionario. El modelo `User` conserva atributos compatibles `name/email` para Laravel, pero las consultas usan los nombres SQL documentados. Se conservan los campos técnicos de sesión y los motivos ya existentes para no perder datos.

## Actualizar instalaciones anteriores

1. Detener escrituras durante la actualización y generar un respaldo con `php artisan villa:backup`.
2. Instalar los archivos corregidos y ejecutar `php artisan migrate --force`. Las migraciones nuevas renombran las columnas conservando datos, convierten los días de atención y crean `indisponibilidades_medico`.
3. Ejecutar `php artisan view:clear` y comprobar acceso y disponibilidad. Los archivos de Vite compilados se incluyen en `public/build`.
4. Nunca ejecutar `migrate:fresh` sobre la base del centro.

## Pruebas

Verificación del 22 de septiembre de 2026: **32 pruebas y 243 aserciones** en SQLite en memoria. Ejecución: `php vendor/phpunit/phpunit/phpunit` (requiere pdo_sqlite).

`php scripts/test-mysql-concurrency.php` comprueba tres carreras en MySQL: mismo médico, mismo paciente y reserva contra bloqueo. Crea y elimina exclusivamente una base temporal con prefijo `proyecto_clis_qa_`.

También se comprobó restaurar el respaldo real en una base separada, aplicar y revertir las migraciones sin perder cuentas, hashes de contraseñas, perfiles, especialidades, horarios, citas ni historial. Para verificar citas e historial se añadieron registros de prueba únicamente en esa copia cuando las tablas estaban vacías.

## Respaldo y operación

Ejecutar `php artisan villa:backup` para generar SQL en `storage/app/backups`, fuera de `public`. Puede configurarse la ruta de mysqldump con `MYSQLDUMP_BINARY`. El programador de Laravel ejecuta el respaldo a las 23:00, hora de Bolivia, con protección contra ejecuciones superpuestas. Mantener `php artisan schedule:work` en ejecución o configurar el servidor para ejecutar `php artisan schedule:run` cada minuto. No se instaló una tarea programada en Windows. Acordar con el centro la retención y copia en una ubicación separada.

Antes de usar datos reales, reemplazar los datos Demo, validar horarios y reglas con el centro, definir respaldo periódico y repetir periódicamente la comprobación de restauración en una base separada. La puesta en producción requiere HTTPS, `APP_DEBUG=false`, credenciales de base con permisos acotados y configuración del servidor apuntando a `public`.

No incluye historia clínica, recetas, pagos, notificaciones automáticas por correo, WhatsApp o SMS ni despliegue público. Los RNF de carga, disponibilidad y aceptación necesitan validación en el entorno final; las pruebas locales no certifican una carga real del centro.
