# Diseño y contenido del centro

## Apariencia compartida

El sitio público y los tres paneles usan azul marino `#082f55`, turquesa `#18c2ad` y fondos suaves azul grisáceo. El fondo principal `#dce6ed` distingue las tarjetas `#f3f6f8` sin recurrir al blanco puro. Los estados de citas conservan sus colores semánticos.

La navegación principal usa 16 px. El texto de lectura y los formularios se amplían según su contexto; fechas, etiquetas y datos secundarios tienen una escala menor para conservar la jerarquía. Los menús se adaptan a pantallas pequeñas y las tablas conservan desplazamiento interno cuando es necesario.

Orden de los estilos en `resources/css/app.css`: base del sitio, formularios, paneles, marca, confort visual, páginas institucionales y maqueta. Los ajustes comunes de fondos y tipografía están en `theme/comfort.css`.

## Contenido institucional

Edita `config/centro.php` para actualizar dirección, ciudad, recepción, teléfono, WhatsApp, correo, misión y visión. La reseña adicional está en `resources/views/public/about.blade.php`.

La misión, visión y presentación son textos propuestos, creados para esta versión. La bandera `institutional_copy_is_example` muestra esa aclaración. Cámbiala a `false` después de que el centro valide el contenido.

Los contactos facilitados son +591 63885613 para teléfono y WhatsApp, y villaisrael@gmail.com para correo. Los enlaces abren llamada, conversación de WhatsApp y correo respectivamente. Usa el formato internacional al modificar teléfono o WhatsApp.

La sección de horarios de Contacto se sustituyó por un mapa incrustado. Consulta `MAPA-Y-CONTACTO.md` para cambiar su ubicación. Después de editar configuración, ejecuta `php artisan config:clear` si se utilizó una caché de configuración.

## Imágenes e interacción

La fotografía de Inicio, Acerca de nosotros y Contacto es referencial; los créditos se conservan en la documentación. Su archivo local está en `public/images/clinic/hospital-reference.jpg`. Consulta origen y licencia en `RECURSOS-VISUALES.md`.

La maqueta interactiva se reconstruyó a partir de las fotografías frontal y posterior facilitadas por el usuario: dos plantas, fachada salmón, cerramiento crema, columnas azules, cubierta inclinada, anexo y depósito de agua. Las proporciones son aproximadas; no es un levantamiento arquitectónico. Está construida con HTML, CSS 3D y JavaScript local, sin librerías 3D adicionales. Los botones Fachada principal y Parte posterior permiten orientar la vista directamente. Se gira arrastrando, con el deslizador, con los botones o con las flechas del teclado; Inicio restablece la posición. Respeta la preferencia de movimiento reducido y permite el desplazamiento vertical en móviles.

Las animaciones de tarjetas, navegación y apariciones son decorativas. La actualización visual no modifica las reglas de citas, los permisos ni la información de pacientes.

## Identidad y fotografía

La marca usa un emblema vectorial propio con una figura humana protegida y una V. Aparece en el sitio público, los tres paneles y el icono del navegador. `theme/identity.css` se carga al final para definir sus tamaños y el tratamiento de las fotografías. Las fotos adicionales acompañan las vistas de especialidades, guía de reserva, ayuda, acceso, registro y bloques informativos de los paneles. Los archivos y créditos están en `RECURSOS-VISUALES.md`.
