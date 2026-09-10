<?php

return [
    // Valores iniciales ajustables al validar la operación con el centro.
    'anticipacion_minutos' => (int) env('CITAS_ANTICIPACION_MINUTOS', 0),
    'cambios_minutos' => (int) env('CITAS_CAMBIOS_MINUTOS', 0),
    'horizonte_dias' => (int) env('CITAS_HORIZONTE_DIAS', 60),
];
