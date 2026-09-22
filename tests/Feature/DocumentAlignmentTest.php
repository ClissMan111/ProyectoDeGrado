<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\IndisponibilidadMedico;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use App\Services\CitaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DocumentAlignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $patient;

    private User $doctor;

    private Medico $medico;

    private Especialidad $specialty;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-09-11 07:00:00'));
        $this->admin = User::factory()->create(['rol' => 'administrador', 'estado' => true]);
        $this->patient = User::factory()->create(['rol' => 'paciente', 'estado' => true]);
        Paciente::create(['usuario_id' => $this->patient->id, 'ci' => 'P-ALIGN', 'nombres' => 'Paciente', 'apellidos' => 'Prueba']);
        $this->doctor = User::factory()->create(['rol' => 'medico', 'estado' => true]);
        $this->medico = Medico::create(['usuario_id' => $this->doctor->id, 'ci' => 'M-ALIGN', 'nombres' => 'Médico', 'apellidos' => 'Prueba', 'estado' => true]);
        $this->specialty = Especialidad::create(['nombre' => 'General', 'estado' => true]);
        $this->medico->especialidades()->attach($this->specialty);
        Horario::create(['medico_id' => $this->medico->id, 'dia_semana' => 'Viernes', 'hora_inicio' => '08:00:00', 'hora_fin' => '12:00:00', 'duracion_cita' => 30, 'estado' => true]);
    }

    private function data(string $hour = '08:00'): array
    {
        return ['medico_id' => $this->medico->id, 'especialidad_id' => $this->specialty->id, 'fecha' => '2026-09-11', 'hora_inicio' => $hour];
    }

    private function period(string $start = '2026-09-11T08:15', string $end = '2026-09-11T09:15'): array
    {
        return ['medico_id' => $this->medico->id, 'inicio' => $start, 'fin' => $end, 'motivo' => 'Ausencia médica', 'estado' => 1];
    }

    private function reserve(string $hour = '08:00'): Cita
    {
        return app(CitaService::class)->reserve($this->patient, $this->data($hour));
    }

    public function test_one_hour_boundary_for_patient_admin_and_availability(): void
    {
        $this->actingAs($this->patient)->getJson('/disponibilidad?especialidad_id='.$this->specialty->id.'&fecha=2026-09-11')->assertJsonPath('medicos.0.horarios.0.inicio', '08:00');
        $this->travelTo(Carbon::parse('2026-09-11 07:00:01'));
        $this->getJson('/disponibilidad?especialidad_id='.$this->specialty->id.'&fecha=2026-09-11')->assertJsonPath('medicos.0.horarios.0.inicio', '08:30');
        $this->post('/citas', $this->data())->assertSessionHasErrors('cita');
        $this->actingAs($this->admin)->post('/citas', [...$this->data(), 'paciente_id' => $this->patient->paciente->id])->assertSessionHasErrors('cita');
        $this->assertDatabaseCount('citas', 0);
        $this->travelTo(Carbon::parse('2026-09-11 07:00:00'));
        $this->post('/citas', [...$this->data(), 'paciente_id' => $this->patient->paciente->id])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('citas', 1);
    }

    public function test_confirmed_reschedule_keeps_state_and_history_and_checks_one_hour(): void
    {
        $cita = $this->reserve('09:00');
        app(CitaService::class)->changeState($this->admin, $cita, 'confirmada');
        $this->travelTo(Carbon::parse('2026-09-11 07:30:01'));
        $this->actingAs($this->patient)->put('/citas/'.$cita->id.'/reprogramar', $this->data('08:30'))->assertSessionHasErrors('cita');
        $this->assertSame('09:00:00', $cita->fresh()->hora_inicio);
        $this->put('/citas/'.$cita->id.'/reprogramar', $this->data('10:00'))->assertSessionHasNoErrors();
        $this->assertSame('confirmada', $cita->fresh()->estado);
        $this->assertDatabaseHas('historial_citas', ['cita_id' => $cita->id, 'accion' => 'reprogramacion', 'estado_anterior' => 'confirmada', 'estado_nuevo' => 'confirmada', 'hora_inicio_anterior' => '09:00:00', 'hora_inicio_nueva' => '10:00:00']);
        $this->get('/citas/'.$cita->id.'/reprogramar')->assertSee('conserva el estado actual');
    }

    public function test_admin_cannot_cancel_at_or_after_start_and_doctor_cannot_cancel(): void
    {
        $cita = $this->reserve();
        $this->actingAs($this->doctor)->patch('/citas/'.$cita->id.'/estado', ['estado' => 'cancelada'])->assertForbidden();
        $this->travelTo(Carbon::parse('2026-09-11 08:00:00'));
        foreach ([$this->admin, $this->patient] as $actor) {
            $this->actingAs($actor)->patch('/citas/'.$cita->id.'/estado', ['estado' => 'cancelada'])->assertSessionHasErrors('cita');
        }
        $this->actingAs($this->admin)->get('/citas/'.$cita->id)->assertDontSee('value="cancelada"', false);
        $this->assertSame('pendiente', $cita->fresh()->estado);
    }

    public function test_no_show_requires_confirmation_and_start_and_is_final(): void
    {
        $pending = $this->reserve();
        $confirmed = $this->reserve('09:00');
        app(CitaService::class)->changeState($this->admin, $confirmed, 'confirmada');
        $this->actingAs($this->doctor)->patch('/citas/'.$confirmed->id.'/estado', ['estado' => 'no_asistio'])->assertSessionHasErrors('cita');
        $this->travelTo(Carbon::parse('2026-09-11 09:00:00'));
        foreach ([$this->doctor, $this->admin] as $actor) {
            $this->actingAs($actor)->patch('/citas/'.$pending->id.'/estado', ['estado' => 'no_asistio'])->assertSessionHasErrors('cita');
            $this->get('/citas/'.$pending->id)->assertDontSee('value="no_asistio"', false);
        }
        $this->actingAs($this->doctor)->patch('/citas/'.$confirmed->id.'/estado', ['estado' => 'no_asistio'])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->patch('/citas/'.$confirmed->id.'/estado', ['estado' => 'atendida'])->assertSessionHasErrors('cita');
        $this->assertSame('no_asistio', $confirmed->fresh()->estado);
    }

    public function test_partial_and_multiday_blocks_and_inactive_periods(): void
    {
        $this->actingAs($this->admin)->post('/administracion/indisponibilidades', $this->period())->assertSessionHasNoErrors();
        $slots = app(CitaService::class)->slots($this->medico, $this->specialty->id, '2026-09-11');
        $this->assertSame(['09:30', '10:00', '10:30', '11:00', '11:30'], array_column($slots, 'inicio'));
        $period = IndisponibilidadMedico::firstOrFail();
        $this->put('/administracion/indisponibilidades/'.$period->id, [...$this->period(), 'estado' => 0])->assertSessionHasNoErrors();
        $this->assertCount(8, app(CitaService::class)->slots($this->medico, $this->specialty->id, '2026-09-11'));
        $this->put('/administracion/indisponibilidades/'.$period->id, $this->period('2026-09-10T12:00', '2026-09-12T12:00'))->assertSessionHasNoErrors();
        $this->actingAs($this->patient)->getJson('/disponibilidad?especialidad_id='.$this->specialty->id.'&fecha=2026-09-11')->assertJsonCount(0, 'medicos');
    }

    public function test_adjacent_blocks_do_not_remove_nonoverlapping_slots(): void
    {
        $this->actingAs($this->admin)->post('/administracion/indisponibilidades', $this->period('2026-09-11T08:30', '2026-09-11T09:00'))->assertSessionHasNoErrors();
        $this->assertSame(['08:00', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30'], array_column(app(CitaService::class)->slots($this->medico, $this->specialty->id, '2026-09-11'), 'inicio'));
    }

    public function test_blocked_slot_cannot_be_booked_or_rescheduled_even_with_forged_request(): void
    {
        $cita = $this->reserve('10:00');
        $second = Especialidad::create(['nombre' => 'Odontología', 'estado' => true]);
        $this->medico->especialidades()->attach($second);
        $this->actingAs($this->admin)->post('/administracion/indisponibilidades', $this->period())->assertSessionHasNoErrors();
        $this->actingAs($this->patient)->post('/citas', [...$this->data(), 'especialidad_id' => $second->id])->assertSessionHasErrors('cita');
        $this->put('/citas/'.$cita->id.'/reprogramar', $this->data())->assertSessionHasErrors('cita');
        $this->assertSame('10:00:00', $cita->fresh()->hora_inicio);
        $this->assertDatabaseCount('citas', 1);
        $this->assertDatabaseCount('historial_citas', 1);
    }

    public function test_affected_pending_and_confirmed_citas_prevent_activation_without_data_loss(): void
    {
        $pending = $this->reserve();
        $confirmed = $this->reserve('08:30');
        app(CitaService::class)->changeState($this->admin, $confirmed, 'confirmada');
        $this->actingAs($this->admin)->post('/administracion/indisponibilidades', $this->period())->assertSessionHasErrors('inicio');
        $this->assertDatabaseCount('indisponibilidades_medico', 0);
        $this->post('/administracion/indisponibilidades', [...$this->period(), 'estado' => 0])->assertSessionHasNoErrors();
        $period = IndisponibilidadMedico::firstOrFail();
        $this->put('/administracion/indisponibilidades/'.$period->id, $this->period())->assertSessionHasErrors('inicio');
        $this->assertFalse($period->fresh()->estado);
        $this->assertSame('pendiente', $pending->fresh()->estado);
        $this->assertSame('confirmada', $confirmed->fresh()->estado);
        foreach ([$pending, $confirmed] as $cita) {
            $this->patch('/citas/'.$cita->id.'/estado', ['estado' => 'cancelada'])->assertSessionHasNoErrors();
        }
        $this->put('/administracion/indisponibilidades/'.$period->id, $this->period())->assertSessionHasNoErrors();
        $this->assertTrue($period->fresh()->estado);
        $this->assertDatabaseCount('citas', 2);
    }

    public function test_period_validation_and_role_permissions(): void
    {
        foreach ([$this->patient, $this->doctor] as $actor) {
            $this->actingAs($actor)->get('/administracion/indisponibilidades')->assertForbidden();
            $this->post('/administracion/indisponibilidades', $this->period())->assertForbidden();
        }
        $this->actingAs($this->admin)->get('/administracion/indisponibilidades/crear')->assertOk()->assertSee('datetime-local', false);
        $this->post('/administracion/indisponibilidades', $this->period('2026-09-11T09:00', '2026-09-11T08:00'))->assertSessionHasErrors('fin');
        $this->post('/administracion/indisponibilidades', $this->period('2026-09-11T09:00', '2026-09-11T09:00'))->assertSessionHasErrors('fin');
        $this->post('/administracion/indisponibilidades', $this->period())->assertSessionHasNoErrors();
        $period = IndisponibilidadMedico::firstOrFail();
        $this->get('/administracion/indisponibilidades/'.$period->id.'/editar')->assertOk()->assertSee('2026-09-11T08:15');
        $this->get('/administracion/indisponibilidades?medico_id='.$this->medico->id)->assertOk()->assertSee('Ausencia médica');
        $this->get('/administracion/horarios?medico_id='.$this->medico->id)->assertOk()->assertSee('Ver indisponibilidades');
        $this->medico->update(['estado' => false]);
        $this->post('/administracion/indisponibilidades', $this->period())->assertSessionHasErrors('medico_id');
        $this->post('/administracion/horarios', ['medico_id' => $this->medico->id, 'dia_semana' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '10:00', 'duracion_cita' => 30, 'estado' => 1])->assertSessionHasErrors('medico_id');
    }

    public function test_admin_specialty_and_patient_history_filters(): void
    {
        $cita = $this->reserve();
        $second = Especialidad::create(['nombre' => 'Segunda', 'estado' => true]);
        $this->actingAs($this->admin)->get('/citas?especialidad_id='.$second->id)->assertOk()->assertDontSee('Paciente Prueba');
        $this->get('/citas?especialidad_id='.$this->specialty->id.'&paciente_id='.$cita->paciente_id)->assertOk()->assertSee('Paciente Prueba');
        $this->get('/administracion/pacientes')->assertOk()->assertSee('Historial de citas');
        $this->actingAs($this->doctor)->get('/administracion/indisponibilidades/'.$this->medico->id.'/editar')->assertForbidden();
    }

    public function test_documented_schema_preserves_existing_accounts_and_schedule_on_upgrade(): void
    {
        $password = $this->patient->password;
        $cita = $this->reserve();
        Artisan::call('migrate:rollback', ['--step' => 2, '--force' => true]);
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertSame($password, DB::table('users')->where('id', $this->patient->id)->value('password'));
        $this->assertSame('5', (string) DB::table('horarios')->value('dia_semana'));
        Artisan::call('migrate', ['--force' => true]);
        $this->assertFalse(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumns('usuarios', ['nombre', 'correo', 'password', 'rol', 'estado']));
        $this->assertTrue(Schema::hasColumns('indisponibilidades_medico', ['medico_id', 'inicio', 'fin', 'motivo', 'estado']));
        $this->assertSame($password, $this->patient->fresh()->password);
        $this->assertSame('Viernes', Horario::first()->dia_semana);
        $this->assertSame($this->patient->id, $cita->fresh()->paciente->usuario->id);
        $this->assertSame($this->patient->id, $cita->historial->first()->usuario->id);
        $this->assertCount(7, app(CitaService::class)->slots($this->medico, $this->specialty->id, '2026-09-11'));
    }
}
