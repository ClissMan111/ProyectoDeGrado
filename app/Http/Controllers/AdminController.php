<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\Horario;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    private const MODELS = ['pacientes' => Paciente::class, 'medicos' => Medico::class, 'especialidades' => Especialidad::class, 'horarios' => Horario::class];

    public function index(Request $request, string $resource)
    {
        $model = self::MODELS[$resource];
        $query = $model::query();
        if (in_array($resource, ['medicos', 'pacientes'])) {
            $query->with('usuario');
        }
        if ($resource === 'medicos') {
            $query->with('especialidades');
        }
        if ($resource === 'horarios') {
            $query->with('medico');
        }
        if ($request->filled('q')) {
            $term = mb_substr($request->string('q')->toString(), 0, 100);
            if ($resource === 'especialidades') {
                $query->where('nombre', 'like', '%'.$term.'%');
            } elseif ($resource !== 'horarios') {
                $query->where(fn ($q) => $q->where('nombres', 'like', '%'.$term.'%')->orWhere('apellidos', 'like', '%'.$term.'%')->orWhere('ci', 'like', '%'.$term.'%'));
            }
        }

        return view('admin.index', ['resource' => $resource, 'items' => $query->latest('id')->paginate(15)->withQueryString()]);
    }

    public function edit(string $resource, ?int $id = null)
    {
        $model = self::MODELS[$resource];
        abort_if($resource === 'pacientes' && ! $id, 404);

        return view('admin.form', ['resource' => $resource, 'item' => $id ? $model::findOrFail($id) : new $model, 'especialidades' => Especialidad::where('estado', true)->orderBy('nombre')->get(), 'medicos' => Medico::where('estado', true)->orderBy('apellidos')->get()]);
    }

    public function save(Request $request, string $resource, ?int $id = null)
    {
        $model = self::MODELS[$resource];
        $item = $id ? $model::findOrFail($id) : new $model;
        abort_if($resource === 'pacientes' && ! $id, 404);
        if ($resource === 'especialidades') {
            $data = $request->validate(['nombre' => ['required', 'string', 'max:100', Rule::unique('especialidades')->ignore($id)], 'descripcion' => 'nullable|string|max:2000', 'estado' => 'required|boolean']);
            $item->fill($data)->save();
        } elseif ($resource === 'horarios') {
            $data = $request->validate(['medico_id' => 'required|integer|exists:medicos,id', 'dia_semana' => 'required|integer|between:1,7', 'hora_inicio' => 'required|date_format:H:i', 'hora_fin' => 'required|date_format:H:i|after:hora_inicio', 'duracion_cita' => 'required|integer|between:5,240', 'estado' => 'required|boolean']);
            DB::transaction(function () use ($item, $data, $id) {
                $medico = Medico::lockForUpdate()->findOrFail($data['medico_id']);
                if ($id && $item->medico_id != $medico->id) {
                    throw ValidationException::withMessages(['medico_id' => 'Crea otro bloque para cambiar de médico.']);
                }
                $overlap = Horario::where('medico_id', $medico->id)->where('dia_semana', $data['dia_semana'])->where('estado', true)->when($id, fn ($q) => $q->where('id', '!=', $id))->where('hora_inicio', '<', $data['hora_fin'].':00')->where('hora_fin', '>', $data['hora_inicio'].':00')->exists();
                if ($data['estado'] && $overlap) {
                    throw ValidationException::withMessages(['hora_inicio' => 'Este bloque se superpone con otro horario activo.']);
                }
                $minutes = \Carbon\Carbon::parse($data['hora_inicio'])->diffInMinutes(\Carbon\Carbon::parse($data['hora_fin']));
                if ($minutes < $data['duracion_cita'] || $minutes % $data['duracion_cita'] !== 0) {
                    throw ValidationException::withMessages(['duracion_cita' => 'La duración debe dividir el bloque en citas completas.']);
                }
                $item->fill($data)->save();
            }, 3);
        } else {
            $data = $request->validate(['nombres' => 'required|string|max:100', 'apellidos' => 'required|string|max:120', 'ci' => ['required', 'string', 'max:20', Rule::unique($resource, 'ci')->ignore($id)], 'telefono' => 'nullable|string|max:20', 'fecha_nacimiento' => 'nullable|date|before_or_equal:today', 'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($item->usuario_id)], 'password' => [$id ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'], 'estado' => 'required|boolean', ...($resource === 'medicos' ? ['especialidades' => 'required|array|min:1', 'especialidades.*' => ['integer', 'distinct', Rule::exists('especialidades', 'id')->where('estado', true)]] : [])]);
            DB::transaction(function () use ($item, $data, $resource, $id) {
                if ($id) {
                    $item = $item->newQuery()->lockForUpdate()->findOrFail($id);
                }
                $user = $item->usuario ?? new User;
                $user->fill(['name' => $data['nombres'].' '.$data['apellidos'], 'email' => $data['email'], 'rol' => $resource === 'medicos' ? 'medico' : 'paciente', 'estado' => $data['estado']]);
                if (! empty($data['password'])) {
                    $user->password = Hash::make($data['password']);
                }
                $user->save();
                $fields = collect($data)->only(['nombres', 'apellidos', 'ci', 'telefono'])->all();
                if ($resource === 'pacientes') {
                    $fields['fecha_nacimiento'] = $data['fecha_nacimiento'] ?? null;
                } else {
                    $fields['estado'] = $data['estado'];
                }
                $item->fill([...$fields, 'usuario_id' => $user->id])->save();
                if ($resource === 'medicos') {
                    $item->especialidades()->sync($data['especialidades']);
                }
            }, 3);
        }

        return redirect()->route('admin.index', $resource)->with('success', 'Información guardada. Las citas existentes conservan sus datos.');
    }
}
