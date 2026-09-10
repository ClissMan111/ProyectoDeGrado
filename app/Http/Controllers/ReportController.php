<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\HistorialCita;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['desde' => 'nullable|date_format:Y-m-d', 'hasta' => 'nullable|date_format:Y-m-d|after_or_equal:desde', 'medico_id' => 'nullable|integer', 'especialidad_id' => 'nullable|integer', 'estado' => ['nullable', Rule::in(array_keys(Cita::ESTADOS))]]);
        $from = $data['desde'] ?? today()->startOfMonth()->format('Y-m-d');
        $to = $data['hasta'] ?? today()->format('Y-m-d');
        $query = Cita::with(['paciente', 'medico', 'especialidad'])->whereDate('fecha', '>=', $from)->whereDate('fecha', '<=', $to);
        foreach (['medico_id', 'especialidad_id', 'estado'] as $field) {
            if (! empty($data[$field])) {
                $query->where($field, $data[$field]);
            }
        }
        $counts = (clone $query)->selectRaw('estado, COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $reprogramadas = HistorialCita::where('accion', 'reprogramacion')->whereIn('cita_id', (clone $query)->select('id'))->distinct()->count('cita_id');
        if ($request->boolean('csv')) {
            return response()->streamDownload(function () use ($query) {
                $out = fopen('php://output', 'w');
                fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, ['Código', 'Fecha', 'Inicio', 'Fin', 'Paciente', 'Médico', 'Especialidad', 'Estado']);
                foreach ($query->orderBy('id')->lazy(200) as $c) {
                    $cells = [$c->id, $c->fecha->format('Y-m-d'), substr($c->hora_inicio, 0, 5), substr($c->hora_fin, 0, 5), $c->paciente->nombre_completo, $c->medico->nombre_completo, $c->especialidad->nombre, Cita::ESTADOS[$c->estado]];
                    fputcsv($out, array_map(fn ($v) => preg_match('/^[=+@\-\t\r]/', (string) $v) ? "'".$v : $v, $cells));
                }
                fclose($out);
            }, 'reporte-citas.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('admin.reports', ['counts' => $counts, 'reprogramadas' => $reprogramadas, 'citas' => $query->orderByDesc('fecha')->paginate(20)->withQueryString(), 'medicos' => Medico::all(), 'especialidades' => Especialidad::all(), 'from' => $from, 'to' => $to]);
    }
}
