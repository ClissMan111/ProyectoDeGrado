<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Services\CitaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CitaController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = Cita::visible($request->user());
        $metrics = collect(Cita::ESTADOS)->map(fn ($label, $state) => (clone $query)->whereDate('fecha', today())->where('estado', $state)->count());
        $next = (clone $query)->with(['paciente', 'medico', 'especialidad'])->whereIn('estado', ['pendiente', 'confirmada'])->where(fn ($q) => $q->whereDate('fecha', '>', today())->orWhere(fn ($q) => $q->whereDate('fecha', today())->where('hora_fin', '>', now()->format('H:i:s'))))->orderBy('fecha')->orderBy('hora_inicio')->limit(6)->get();

        return view('dashboard', compact('metrics', 'next'));
    }

    public function index(Request $request)
    {
        if ($request->user()->rol === 'medico' && ! $request->has('fecha')) {
            $request->merge(['fecha' => today()->format('Y-m-d')]);
        }
        $request->validate(['fecha' => 'nullable|date_format:Y-m-d', 'estado' => ['nullable', Rule::in(array_keys(Cita::ESTADOS))], 'medico_id' => 'nullable|integer', 'q' => 'nullable|string|max:100']);
        $query = Cita::visible($request->user())->with(['paciente', 'medico', 'especialidad']);
        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('medico_id')) {
            $query->where('medico_id', $request->medico_id);
        }
        if ($request->filled('q')) {
            $query->whereHas('paciente', fn ($q) => $q->where(fn ($q) => $q->where('nombres', 'like', '%'.$request->q.'%')->orWhere('apellidos', 'like', '%'.$request->q.'%')->orWhere('ci', 'like', '%'.$request->q.'%')));
        }
        $citas = $query->orderByDesc('fecha')->orderBy('hora_inicio')->paginate(15)->withQueryString();

        return view('citas.index', ['citas' => $citas, 'medicos' => Medico::orderBy('apellidos')->get()]);
    }

    public function create(Request $request, ?Cita $cita = null)
    {
        abort_unless(in_array($request->user()->rol, ['paciente', 'administrador']), 403);
        $cita = $cita?->exists ? $cita : null;
        if ($cita) {
            abort_unless($request->user()->rol === 'paciente' && $cita->paciente?->usuario_id === $request->user()->id, 403);
            abort_unless($cita->modificable(), 422, 'Esta cita ya no admite reprogramación.');
        }

        return view('citas.create', ['cita' => $cita, 'especialidades' => Especialidad::where('estado', true)->orderBy('nombre')->get(), 'pacientes' => $request->user()->rol === 'administrador' ? Paciente::with('usuario')->whereHas('usuario', fn ($q) => $q->where('estado', true))->orderBy('apellidos')->get() : collect()]);
    }

    public function availability(Request $request, CitaService $service)
    {
        $data = $request->validate(['especialidad_id' => 'required|integer|exists:especialidades,id', 'fecha' => 'required|date_format:Y-m-d', 'cita_id' => 'nullable|integer']);
        $except = null;
        if (! empty($data['cita_id'])) {
            $cita = Cita::visible($request->user())->findOrFail($data['cita_id']);
            abort_unless($request->user()->rol === 'paciente' && $cita->modificable(), 403);
            $except = $cita->id;
        }
        $medicos = Medico::where('estado', true)->whereHas('especialidades', fn ($q) => $q->where('especialidades.id', $data['especialidad_id'])->where('estado', true))->when(isset($cita), fn ($q) => $q->whereKey($cita->medico_id))->with('usuario')->get();

        return response()->json(['medicos' => $medicos->map(fn ($m) => ['id' => $m->id, 'nombre' => $m->nombre_completo, 'horarios' => $service->slots($m, (int) $data['especialidad_id'], $data['fecha'], $except)])]);
    }

    public function store(Request $request, CitaService $service, ?Cita $cita = null)
    {
        abort_unless(in_array($request->user()->rol, ['paciente', 'administrador']), 403);
        $cita = $cita?->exists ? $cita : null;
        $data = $request->validate(['especialidad_id' => 'required|integer|exists:especialidades,id', 'medico_id' => 'required|integer|exists:medicos,id', 'paciente_id' => ($request->user()->rol === 'administrador' ? 'required' : 'nullable').'|integer|exists:pacientes,id', 'fecha' => 'required|date_format:Y-m-d', 'hora_inicio' => 'required|date_format:H:i']);
        $saved = $service->reserve($request->user(), $data, $cita);

        return redirect()->route('citas.show', $saved)->with('success', $cita ? 'Cita reprogramada. Espera la confirmación del centro.' : 'Reserva registrada. El centro confirmará tu cita.');
    }

    public function show(Request $request, Cita $cita)
    {
        abort_unless(Cita::visible($request->user())->whereKey($cita->id)->exists(), 403);
        $cita->load(['paciente', 'medico', 'especialidad', 'historial.usuario']);

        return view('citas.show', compact('cita'));
    }

    public function state(Request $request, Cita $cita, CitaService $service)
    {
        $data = $request->validate(['estado' => ['required', Rule::in(array_keys(Cita::ESTADOS))], 'motivo' => 'nullable|string|max:255']);
        $service->changeState($request->user(), $cita, $data['estado'], $data['motivo'] ?? null);

        return back()->with('success', 'Estado actualizado y registrado en el historial.');
    }
}
