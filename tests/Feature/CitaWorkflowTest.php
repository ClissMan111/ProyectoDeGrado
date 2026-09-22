<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use App\Services\CitaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $patient;

    private User $doctor;

    private Medico $medico;

    private Especialidad $specialty;

    private string $date;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-09-10 07:00:00'));
        $this->date = '2026-09-11';
        $this->admin = User::factory()->create(['rol' => 'administrador', 'estado' => true]);
        $this->patient = $this->patient('P-01');
        $this->doctor = User::factory()->create(['rol' => 'medico', 'estado' => true]);
        $this->specialty = Especialidad::create(['nombre' => 'General', 'estado' => true]);
        $this->medico = Medico::create(['usuario_id' => $this->doctor->id, 'ci' => 'M-01', 'nombres' => 'Ana', 'apellidos' => 'Médica', 'estado' => true]);
        $this->medico->especialidades()->attach($this->specialty);
        Horario::create(['medico_id' => $this->medico->id, 'dia_semana' => 'Viernes', 'hora_inicio' => '08:00:00', 'hora_fin' => '12:00:00', 'duracion_cita' => 30, 'estado' => true]);
    }

    private function patient(string $ci): User
    {
        $user = User::factory()->create(['rol' => 'paciente', 'estado' => true]);
        Paciente::create(['usuario_id' => $user->id, 'ci' => $ci, 'nombres' => 'Persona', 'apellidos' => $ci]);

        return $user;
    }

    private function data(string $hour = '08:00'): array
    {
        return ['medico_id' => $this->medico->id, 'especialidad_id' => $this->specialty->id, 'fecha' => $this->date, 'hora_inicio' => $hour];
    }

    private function reserve(string $hour = '08:00'): Cita
    {
        return app(CitaService::class)->reserve($this->patient, $this->data($hour));
    }

    public function test_registration_creates_only_a_patient_even_when_role_is_forged(): void
    {
        $this->post('/registro', ['nombres' => 'Nueva', 'apellidos' => 'Persona', 'ci' => 'NEW-1', 'email' => 'new@example.test', 'password' => 'Segura1234', 'password_confirmation' => 'Segura1234', 'consentimiento' => 1, 'rol' => 'administrador'])->assertRedirect('/ingresar')->assertSessionHasNoErrors();
        $user = User::where('correo', 'new@example.test')->firstOrFail();
        $this->assertSame('paciente', $user->rol);
        $this->assertNotNull($user->paciente);
        $this->post('/ingresar', ['email' => $user->email, 'password' => 'Segura1234'])->assertRedirect('/panel/paciente');
        $this->assertAuthenticatedAs($user);
        $this->post('/salir')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_duplicate_registration_is_rejected(): void
    {
        $this->post('/registro', ['nombres' => 'Persona', 'apellidos' => 'Duplicada', 'ci' => 'P-01', 'email' => $this->patient->email, 'password' => 'Segura1234', 'password_confirmation' => 'Segura1234', 'consentimiento' => 1])->assertSessionHasErrors(['ci', 'email']);
    }

    public function test_role_permissions_and_real_screens(): void
    {
        foreach ([[$this->patient, 'paciente'], [$this->doctor, 'medico'], [$this->admin, 'administracion']] as [$user,$path]) {
            $this->actingAs($user)->get('/panel/'.$path)->assertOk()->assertSee($user->name);
            $this->get('/citas')->assertOk();
            $this->get('/cuenta')->assertOk();
        }
        foreach (['pacientes', 'medicos', 'especialidades', 'horarios'] as $resource) {
            $this->actingAs($this->admin)->get('/administracion/'.$resource)->assertOk();
            if ($resource !== 'pacientes') {
                $this->get('/administracion/'.$resource.'/crear')->assertOk();
            }
        }
        $this->get('/administracion/reportes')->assertOk();
        $this->actingAs($this->patient)->get('/administracion/medicos')->assertForbidden();
        $this->get('/panel/medico')->assertForbidden();
        $this->actingAs($this->doctor)->get('/reservar')->assertForbidden();
    }

    public function test_deactivated_account_cannot_continue_session(): void
    {
        $this->patient->update(['estado' => false]);
        $this->actingAs($this->patient)->get('/panel/paciente')->assertRedirect('/ingresar');
        $this->assertGuest();
    }

    public function test_availability_and_reservation_work_for_authenticated_patient(): void
    {
        $this->actingAs($this->patient)->get('/reservar')->assertOk();
        $this->getJson('/disponibilidad?especialidad_id='.$this->specialty->id.'&fecha='.$this->date)->assertOk()->assertJsonCount(8, 'medicos.0.horarios');
        $this->post('/citas', $this->data())->assertRedirect()->assertSessionHasNoErrors();
        $cita = Cita::firstOrFail();
        $this->assertSame('pendiente', $cita->estado);
        $this->assertSame($this->patient->paciente->id, $cita->paciente_id);
        $this->get('/citas/'.$cita->id)->assertOk()->assertSee('Reserva creada');
        $this->assertDatabaseHas('historial_citas', ['cita_id' => $cita->id, 'accion' => 'reserva', 'usuario_id' => $this->patient->id]);
    }

    public function test_admin_can_reserve_for_a_patient_and_actor_is_recorded(): void
    {
        $this->actingAs($this->admin)->post('/citas', [...$this->data(), 'paciente_id' => $this->patient->paciente->id])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('historial_citas', ['usuario_id' => $this->admin->id, 'accion' => 'reserva']);
    }

    public function test_duplicate_and_forged_slots_are_rejected(): void
    {
        $this->reserve();
        $other = $this->patient('P-02');
        $this->actingAs($other)->post('/citas', $this->data())->assertSessionHasErrors('cita');
        $this->post('/citas', $this->data('08:05'))->assertSessionHasErrors('cita');
        $this->post('/citas', [...$this->data(), 'fecha' => '2026-09-09'])->assertSessionHasErrors('cita');
        $this->assertDatabaseCount('citas', 1);
    }

    public function test_patient_cannot_read_cancel_or_reschedule_another_patients_cita(): void
    {
        $cita = $this->reserve();
        $other = $this->patient('P-02');
        $this->actingAs($other)->get('/citas/'.$cita->id)->assertForbidden();
        $this->patch('/citas/'.$cita->id.'/estado', ['estado' => 'cancelada'])->assertForbidden();
        $this->put('/citas/'.$cita->id.'/reprogramar', $this->data('09:00'))->assertForbidden();
        $this->assertSame('08:00:00', $cita->fresh()->hora_inicio);
    }

    public function test_rescheduling_keeps_audit_and_failed_change_keeps_original(): void
    {
        $cita = $this->reserve();
        $this->reserve('09:00');
        $this->actingAs($this->patient)->get('/citas/'.$cita->id.'/reprogramar')->assertOk();
        $this->put('/citas/'.$cita->id.'/reprogramar', $this->data('09:00'))->assertSessionHasErrors('cita');
        $this->assertSame('08:00:00', $cita->fresh()->hora_inicio);
        $this->put('/citas/'.$cita->id.'/reprogramar', $this->data('10:00'))->assertSessionHasNoErrors();
        $this->assertSame('10:00:00', $cita->fresh()->hora_inicio);
        $this->assertDatabaseHas('historial_citas', ['cita_id' => $cita->id, 'accion' => 'reprogramacion', 'hora_inicio_anterior' => '08:00:00', 'hora_inicio_nueva' => '10:00:00']);
    }

    public function test_cancelled_slot_is_released_and_terminal_state_cannot_be_reopened(): void
    {
        $cita = $this->reserve();
        $this->actingAs($this->patient)->patch('/citas/'.$cita->id.'/estado', ['estado' => 'cancelada', 'motivo' => 'Cambio de planes'])->assertSessionHasNoErrors();
        $this->post('/citas', $this->data())->assertSessionHasNoErrors();
        $this->assertDatabaseCount('citas', 2);
        $this->actingAs($this->admin)->patch('/citas/'.$cita->id.'/estado', ['estado' => 'confirmada'])->assertSessionHasErrors('cita');
    }

    public function test_medical_attendance_requires_own_confirmed_cita_and_start_time(): void
    {
        $cita = $this->reserve();
        $this->actingAs($this->patient)->patch('/citas/'.$cita->id.'/estado', ['estado' => 'atendida'])->assertForbidden();
        $this->actingAs($this->admin)->patch('/citas/'.$cita->id.'/estado', ['estado' => 'confirmada'])->assertSessionHasNoErrors();
        $this->actingAs($this->doctor)->patch('/citas/'.$cita->id.'/estado', ['estado' => 'atendida'])->assertSessionHasErrors('cita');
        $this->travelTo(\Carbon\Carbon::parse('2026-09-11 08:30:00'));
        $this->patch('/citas/'.$cita->id.'/estado', ['estado' => 'atendida'])->assertSessionHasNoErrors();
        $this->assertSame('atendida', $cita->fresh()->estado);
    }

    public function test_doctor_supports_multiple_specialties_and_disabled_specialty_has_no_slots(): void
    {
        $second = Especialidad::create(['nombre' => 'Fisioterapia', 'estado' => true]);
        $this->medico->especialidades()->attach($second);
        $this->assertCount(8, app(CitaService::class)->slots($this->medico, $second->id, $this->date));
        $second->update(['estado' => false]);
        $this->assertSame([], app(CitaService::class)->slots($this->medico, $second->id, $this->date));
    }

    public function test_admin_schedules_validate_overlaps_and_duration(): void
    {
        $this->actingAs($this->admin)->post('/administracion/horarios', ['medico_id' => $this->medico->id, 'dia_semana' => 'Viernes', 'hora_inicio' => '09:00', 'hora_fin' => '13:00', 'duracion_cita' => 30, 'estado' => 1])->assertSessionHasErrors('hora_inicio');
        $this->post('/administracion/horarios', ['medico_id' => $this->medico->id, 'dia_semana' => 'Viernes', 'hora_inicio' => '14:00', 'hora_fin' => '15:10', 'duracion_cita' => 30, 'estado' => 1])->assertSessionHasErrors('duracion_cita');
        $this->post('/administracion/horarios', ['medico_id' => $this->medico->id, 'dia_semana' => 'Viernes', 'hora_inicio' => '14:00', 'hora_fin' => '16:00', 'duracion_cita' => 30, 'estado' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('horarios', 2);
    }

    public function test_report_filters_and_csv_are_admin_only(): void
    {
        $cita = $this->reserve();
        $query = '?desde=2026-09-11&hasta=2026-09-11';
        $this->actingAs($this->admin)->get('/administracion/reportes'.$query)->assertOk()->assertSee('1 citas en el resultado');
        $this->get('/administracion/reportes'.$query.'&csv=1')->assertOk()->assertDownload('reporte-citas.csv');
        $this->actingAs($this->patient)->get('/administracion/reportes'.$query.'&csv=1')->assertForbidden();
    }

    public function test_admin_creates_and_updates_doctor_with_multiple_specialties(): void
    {
        $second = Especialidad::create(['nombre' => 'Fisioterapia', 'estado' => true]);
        $data = ['nombres' => 'Nueva', 'apellidos' => 'Médica', 'ci' => 'M-02', 'email' => 'doctor-new@example.test', 'password' => 'Temporal1234', 'password_confirmation' => 'Temporal1234', 'especialidades' => [$this->specialty->id, $second->id], 'estado' => 1];
        $this->actingAs($this->admin)->post('/administracion/medicos', $data)->assertSessionHasNoErrors()->assertRedirect('/administracion/medicos');
        $doctor = Medico::where('ci', 'M-02')->firstOrFail();
        $this->assertSame('medico', $doctor->usuario->rol);
        $this->assertCount(2, $doctor->especialidades);
        $hash = $doctor->usuario->password;
        $this->get('/administracion/medicos/'.$doctor->id.'/editar')->assertOk()->assertSee('doctor-new@example.test');
        $this->put('/administracion/medicos/'.$doctor->id, [...$data, 'password' => '', 'password_confirmation' => '', 'estado' => 0, 'especialidades' => [$second->id]])->assertSessionHasNoErrors();
        $doctor->refresh();
        $this->assertFalse($doctor->estado);
        $this->assertFalse($doctor->usuario->estado);
        $this->assertSame($hash, $doctor->usuario->password);
        $this->assertSame([$second->id], $doctor->especialidades->pluck('id')->all());
    }

    public function test_admin_edits_registered_patient_without_changing_role(): void
    {
        $patient = $this->patient->paciente;
        $this->actingAs($this->admin)->put('/administracion/pacientes/'.$patient->id, ['nombres' => 'Nombre actualizado', 'apellidos' => $patient->apellidos, 'ci' => $patient->ci, 'email' => $this->patient->email, 'telefono' => '70000000', 'estado' => 1, 'rol' => 'administrador'])->assertSessionHasNoErrors();
        $this->assertSame('paciente', $this->patient->fresh()->rol);
        $this->assertSame('Nombre actualizado', $patient->fresh()->nombres);
    }

    public function test_password_change_requires_current_password(): void
    {
        $this->patient->update(['password' => \Illuminate\Support\Facades\Hash::make('Anterior1234')]);
        $this->actingAs($this->patient)->put('/cuenta/password', ['current_password' => 'Incorrecta1234', 'password' => 'NuevaClave1234', 'password_confirmation' => 'NuevaClave1234'])->assertSessionHasErrors('current_password');
        $this->put('/cuenta/password', ['current_password' => 'Anterior1234', 'password' => 'NuevaClave1234', 'password_confirmation' => 'NuevaClave1234'])->assertSessionHasNoErrors();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NuevaClave1234', $this->patient->fresh()->password));
    }
}
