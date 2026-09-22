# Cambiar el mapa y los datos de contacto

Abre `C:/Users/Hp/Desktop/ProyectoDeGrado/config/centro.php`.

## Ubicación del mapa

Tienes dos opciones:

1. **Dirección o coordenadas:** cambia `map_query` por la dirección completa o por `latitud,longitud`. Mantén `map_embed_url` vacío. La vista genera el mapa con esa búsqueda.
2. **Mapa exacto de Google Maps:** abre el lugar en Google Maps, selecciona Compartir → Insertar un mapa y copia únicamente la dirección que figura dentro de `src="..."`. Pégala como valor de `map_embed_url`. Este valor tiene prioridad sobre `map_query`.

Ejemplo de configuración inicial:

```php
'map_query' => 'Av. Panamericana, Av. General Bartolomé Salomón, Cochabamba, Bolivia',
'map_embed_url' => '',
```

El mapa inicial utiliza la dirección documentada como búsqueda; ajusta la ubicación exacta cuando dispongas del enlace o las coordenadas. El enlace Abrir mapa completo usa `map_query`, por lo que conviene mantenerlo actualizado aunque uses un mapa incrustado personalizado.

No pegues la etiqueta HTML `<iframe>` completa: solamente su URL. El mapa necesita conexión a Internet. La página y el enlace para abrirlo siguen disponibles si el proveedor no carga.

## Contactos configurados

```php
'phone' => '+591 63885613',
'whatsapp' => '+591 63885613',
'email' => 'villaisrael@gmail.com',
```

WhatsApp abre `https://wa.me/59163885613`. El teléfono usa `tel:+59163885613` y el correo `mailto:villaisrael@gmail.com`.

Después de cambiar configuración, ejecuta `php artisan config:clear` desde la carpeta del proyecto y recarga Contacto. No hace falta compilar CSS/JavaScript para cambiar estos datos.

La maqueta se edita en `resources/views/components/clinic-model.blade.php`, `resources/css/components/clinic-model.css` y `resources/js/modules/clinic-model.js`.
