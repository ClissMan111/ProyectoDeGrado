<?php

namespace Tests\Feature;

use Tests\TestCase;

class ScreensTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;
    public function test_public_screens_are_available(): void
    {
        $screens = [
            '/' => 'Tu cita empieza',
            '/especialidades' => 'Encuentra la atención',
            '/como-reservar' => 'Tu cita, paso a paso',
            '/nosotros' => 'Cuidado cercano',
            '/contacto' => 'Estamos para orientarte',
            '/preguntas-frecuentes' => 'Respuestas antes de reservar',
            '/ingresar' => 'Iniciar sesión',
            '/registro' => 'Crea tu cuenta de paciente',
        ];

        foreach ($screens as $path => $text) {
            $this->get($path)->assertOk()->assertSee($text);
        }
    }

    public function test_role_dashboards_require_authentication(): void
    {
        $screens = [
            '/panel/paciente' => 'Buenos días, María',
            '/panel/medico' => 'Su jornada de hoy',
            '/panel/administracion' => 'Todo el centro, de un vistazo',
        ];

        foreach ($screens as $path => $text) {
            $this->get($path)->assertRedirect('/ingresar');
        }
    }
}
