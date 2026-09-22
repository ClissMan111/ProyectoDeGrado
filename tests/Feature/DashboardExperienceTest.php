<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardExperienceTest extends TestCase
{
    use RefreshDatabase;

    private User $patient;

    private User $doctor;

    private User $admin;

    private Cita $appointment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Carbon\Carbon::parse('2026-09-22 07:00:00'));
        $this->patient = User::factory()->create(['rol' => 'paciente', 'estado' => true]);
        $this->doctor = User::factory()->create(['rol' => 'medico', 'estado' => true]);
        $this->admin = User::factory()->create(['rol' => 'administrador', 'estado' => true]);
        $patient = Paciente::create(['usuario_id' => $this->patient->id, 'ci' => 'P-1', 'nombres' => 'Lucía', 'apellidos' => 'Vargas']);
        $doctor = Medico::create(['usuario_id' => $this->doctor->id, 'ci' => 'M-1', 'nombres' => 'Ana', 'apellidos' => 'Rojas', 'estado' => true]);
        $specialty = Especialidad::create(['nombre' => 'Medicina general', 'estado' => true]);
        $doctor->especialidades()->attach($specialty);
        $this->appointment = Cita::create(['paciente_id' => $patient->id, 'medico_id' => $doctor->id, 'especialidad_id' => $specialty->id, 'fecha' => '2026-09-22', 'hora_inicio' => '10:00:00', 'hora_fin' => '10:30:00', 'estado' => 'confirmada']);
    }

    public function test_dashboards_scope_every_collection_and_metric_to_the_current_role(): void
    {
        $otherPatient = User::factory()->create(['rol' => 'paciente', 'estado' => true]);
        $profile = Paciente::create(['usuario_id' => $otherPatient->id, 'ci' => 'P-2', 'nombres' => 'Privado', 'apellidos' => 'Paciente']);
        $otherDoctor = User::factory()->create(['rol' => 'medico', 'estado' => true]);
        $doctor = Medico::create(['usuario_id' => $otherDoctor->id, 'ci' => 'M-2', 'nombres' => 'Privado', 'apellidos' => 'Médico', 'estado' => true]);
        $copy = $this->appointment->replicate();
        $copy->fill(['paciente_id' => $profile->id, 'medico_id' => $doctor->id, 'hora_inicio' => '11:00:00', 'hora_fin' => '11:30:00']);
        $copy->save();
        foreach ([[$this->patient, 'paciente'], [$this->doctor, 'medico']] as [$user,$path]) {
            $this->actingAs($user)->get('/panel/'.$path)->assertOk()
                ->assertViewHas('agenda', fn ($rows) => $rows->modelKeys() === [$this->appointment->id])
                ->assertViewHas('next', fn ($rows) => $rows->modelKeys() === [$this->appointment->id])
                ->assertViewHas('metrics', fn ($counts) => $counts['confirmada'] === 1 && $counts->sum() === 1)
                ->assertViewHas('week', fn ($days) => $days->sum('count') === 1)
                ->assertViewHas('summary', fn ($totals) => $totals['upcoming'] === 1)
                ->assertDontSee('Privado');
        }
        $this->actingAs($this->admin)->get('/panel/administracion')->assertOk()
            ->assertViewHas('metrics', fn ($counts) => $counts->sum() === 2)
            ->assertViewHas('agenda', fn ($rows) => $rows->count() === 2);
    }

    public function test_date_and_status_filter_agenda_without_changing_whole_day_totals(): void
    {
        $pending = $this->appointment->replicate();
        $pending->fill(['fecha' => '2026-09-23', 'estado' => 'pendiente']);
        $pending->save();
        $confirmed = $this->appointment->replicate();
        $confirmed->fill(['fecha' => '2026-09-23', 'hora_inicio' => '11:00:00', 'hora_fin' => '11:30:00']);
        $confirmed->save();
        $this->actingAs($this->patient)->get('/panel/paciente?fecha=2026-09-23&estado=pendiente')->assertOk()
            ->assertViewHas('agenda', fn ($rows) => $rows->modelKeys() === [$pending->id])
            ->assertViewHas('metrics', fn ($counts) => $counts['pendiente'] === 1 && $counts['confirmada'] === 1)
            ->assertViewHas('date', fn ($date) => $date->toDateString() === '2026-09-23');
        $this->get('/panel/paciente?fecha=2026-02-30')->assertSessionHasErrors('fecha');
        $this->get('/panel/paciente?estado=inventado')->assertSessionHasErrors('estado');
    }

    public function test_calendar_download_uses_bolivia_time_and_requires_access_to_the_appointment(): void
    {
        $url = '/citas/'.$this->appointment->id.'/calendario';
        $this->get($url)->assertRedirect('/ingresar');
        foreach ([$this->patient, $this->doctor, $this->admin] as $user) {
            $response = $this->actingAs($user)->get($url)->assertOk()
                ->assertHeader('Content-Type', 'text/calendar; charset=UTF-8')
                ->assertHeader('Content-Disposition', 'attachment; filename="cita-'.$this->appointment->id.'.ics"');
            $body = $response->getContent();
            $this->assertStringContainsString('DTSTART:20260922T140000Z', $body);
            $this->assertStringContainsString('DTEND:20260922T143000Z', $body);
            $this->assertStringContainsString('STATUS:CONFIRMED', $body);
            $this->assertStringNotContainsString('Lucía', $body);
        }
        foreach (['paciente', 'medico'] as $role) {
            $stranger = User::factory()->create(['rol' => $role, 'estado' => true]);
            $this->actingAs($stranger)->get($url)->assertForbidden();
        }
        $this->appointment->update(['estado' => 'cancelada']);
        $this->actingAs($this->patient)->get($url)->assertStatus(422);
    }

    public function test_calendar_escapes_and_folds_utf8_text_without_creating_extra_fields(): void
    {
        $name = str_repeat('Atención médica, ', 8)."especial;\nfin";
        $this->appointment->especialidad->update(['nombre' => $name]);
        $this->appointment->update(['estado' => 'pendiente']);
        $response = $this->actingAs($this->patient)->get('/citas/'.$this->appointment->id.'/calendario')->assertOk();
        $body = $response->getContent();
        $this->assertStringContainsString('STATUS:TENTATIVE', $body);
        $this->assertStringContainsString('especial\\;\\nfin', str_replace("\r\n ", '', $body));
        $this->assertStringContainsString('médica\\,', str_replace("\r\n ", '', $body));
        foreach (explode("\r\n", $body) as $line) {
            $this->assertLessThanOrEqual(75, strlen($line));
            $this->assertTrue(mb_check_encoding($line, 'UTF-8'));
        }
    }
}
