# Villa Israel · Sistema de reserva de citas

Sistema del Centro de Salud Villa Israel, desarrollado con Laravel 10, PHP 8.1 o posterior, MySQL y Vite. Incluye el sitio público y los paneles de administración, paciente y médico.

## Empieza aquí

- [Guía de carpetas y archivos](docs/GUIA-DEL-PROYECTO.md): dónde encontrar cada pantalla, estilo y función.
- [Diseño y contenido del centro](docs/DISENO-Y-CONTENIDO.md): colores, tipografía, imágenes, maqueta 3D y contactos.
- [Implementación y reglas del sistema](docs/IMPLEMENTACION.md): funcionalidades y relación con la documentación oficial.
- [Mapa y datos de contacto](docs/MAPA-Y-CONTACTO.md): cómo cambiar la ubicación y los canales de atención.
- [Recursos visuales](docs/RECURSOS-VISUALES.md): origen de la fotografía y licencia.
- [Utilidades de desarrollo](scripts/README.md): para qué sirve cada script.

## Abrir la instalación existente

1. Inicia MySQL desde XAMPP.
2. Abre una terminal en esta carpeta y ejecuta `php artisan serve --host=127.0.0.1 --port=8000`.
3. Visita http://127.0.0.1:8000. El acceso está en `/ingresar`.

Si PHP no está en el PATH de Windows, usa `C:\xampp\php\php.exe` en lugar de `php`. Si el servidor ya está abierto, basta con visitar la dirección.

## Editar la apariencia

Las pantallas están en `resources/views`, los estilos en `resources/css` y las interacciones en `resources/js/modules`. Después de cambiar CSS o JavaScript, ejecuta `npm run build` desde la raíz. `public/build` contiene el resultado generado y no se edita a mano.

Para desarrollo con recarga automática: `npm run dev`. Los datos institucionales de Acerca de nosotros y Contacto se editan en `config/centro.php`.

## Validar cambios

`php artisan test` ejecuta las pruebas configuradas con SQLite en memoria; requiere la extensión PDO SQLite. `npm run build` comprueba y compila los recursos del navegador.

Las instrucciones de una instalación nueva están en [IMPLEMENTACION.md](docs/IMPLEMENTACION.md). La documentación oficial `ProyectodeGradoClisman.docx` se conserva fuera del proyecto y no se modifica con los ajustes visuales.
