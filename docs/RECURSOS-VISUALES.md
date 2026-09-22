# Recursos visuales

## Fotografía hospitalaria de referencia

- Archivo local: `public/images/clinic/hospital-reference.jpg`.
- Proveedor: Unsplash.
- Recurso descargado: https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1600&q=82
- Licencia consultada: https://unsplash.com/license
- Fecha de incorporación: 22 de septiembre de 2026.
- Uso: fondos de Inicio, Acerca de nosotros y Contacto. Los paneles ilustrativos usan ahora las fotografías de consulta listadas más abajo.

La licencia de Unsplash permite descargar y utilizar imágenes gratuitamente, incluso en proyectos comerciales, sin atribución obligatoria. Los créditos se conservan en esta documentación; los rótulos superpuestos fueron retirados a petición del usuario. No se afirma que muestre el Centro de Salud Villa Israel ni que el establecimiento fotografiado respalde este proyecto.

El archivo se sirve desde el propio proyecto, por lo que la fotografía no necesita una conexión a Unsplash durante la navegación. No se le asigna un fotógrafo sin una atribución verificada.

## Recursos propios del proyecto

La maqueta 3D se creó con elementos HTML/CSS a partir de las dos fotografías facilitadas por el usuario el 22 de septiembre de 2026, correspondientes a la fachada principal y posterior. Sus detalles y colores se aproximan a esas referencias. Los iconos se mantienen como SVG en el componente `icon.blade.php`. El fondo `public/images/care-landscape.svg` permanece como recurso local del diseño existente.

## Fotografías incorporadas a la identidad visual

Archivos locales en `public/images/care`. Descargados el 22 de septiembre de 2026 en versiones de 1200 px. Se usan como apoyo visual, sin identificar a las personas como empleados o pacientes reales de Villa Israel. Los créditos se mantienen aquí, sin rótulos sobre las fotos.

| Archivo | Autor | Fuente |
| --- | --- | --- |
| `consulta-digital.jpg` | Cedric Fauntleroy | https://www.pexels.com/photo/a-doctor-and-a-patient-looking-at-a-tablet-4266938/ |
| `orientacion-medica.jpg` | Cedric Fauntleroy | https://www.pexels.com/photo/a-doctor-talking-to-a-patient-4266940/ |
| `agenda-medica.jpg` | Thirdman | https://www.pexels.com/photo/a-medical-person-using-a-laptop-for-research-5327916/ |
| `instrumental-medico.jpg` | Fernando Lacerda Branco | https://www.pexels.com/photo/stethoscope-on-wooden-table-12344248/ |

Licencia consultada: https://www.pexels.com/license/. Permite uso gratuito personal y comercial; no exige atribución. No se presenta respaldo de las personas fotografiadas al centro.

## Logo Villa Israel

Emblema vectorial creado para esta interfaz: figura humana rodeada por dos formas de protección que dibujan una V. Colores principales `#082f55` y `#18c2ad`, con variante clara para fondos oscuros.

- `public/images/brand/villa-israel.svg`: emblema sobre fondo claro.
- `public/images/brand/villa-israel-light.svg`: emblema sobre fondo oscuro.
- `public/images/brand/villa-israel-horizontal.svg`: versión horizontal con el nombre.
- `public/favicon.svg`: icono de la pestaña del navegador.
- `resources/views/components/brand-mark.blade.php`: uso compartido en los menús y pie de página.
- `resources/css/theme/identity.css`: tamaños, tipografía y estilo de las fotografías.

El componente `care-photo.blade.php` centraliza tamaño, carga diferida y accesibilidad de las fotografías. Los cambios en estilos se compilan con `npm run build`.
