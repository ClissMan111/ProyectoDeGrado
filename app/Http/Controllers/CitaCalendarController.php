<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CitaCalendarController extends Controller
{
    public function __invoke(Request $request, Cita $cita)
    {
        abort_unless(Cita::visible($request->user())->whereKey($cita->id)->exists(), 403);
        abort_unless(in_array($cita->estado, ['pendiente', 'confirmada']), 422, 'Solo puedes añadir al calendario una cita vigente.');
        $escape = fn ($text) => str_replace(['\\', "\r\n", "\r", "\n", ';', ','], ['\\\\', '\\n', '\\n', '\\n', '\\;', '\\,'], $text);
        $start = $cita->inicio()->utc()->format('Ymd\THis\Z');
        $end = Carbon::parse($cita->fecha->toDateString().' '.$cita->hora_fin)->utc()->format('Ymd\THis\Z');
        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Villa Israel//Citas//ES', 'CALSCALE:GREGORIAN', 'BEGIN:VEVENT',
            'UID:cita-'.$cita->id.'@villaisrael.local', 'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$start, 'DTEND:'.$end, 'SUMMARY:'.$escape('Cita de '.$cita->especialidad->nombre),
            'DESCRIPTION:'.$escape('Médico: '.$cita->medico->nombre_completo.'. Estado: '.Cita::ESTADOS[$cita->estado].'. Consulta tu cuenta para verificar cambios.'),
            'LOCATION:'.$escape('Centro de Salud Villa Israel, Cochabamba'),
            'STATUS:'.($cita->estado === 'confirmada' ? 'CONFIRMED' : 'TENTATIVE'), 'END:VEVENT', 'END:VCALENDAR'];
        $folded = [];
        foreach ($lines as $line) {
            while (strlen($line) > 75) {
                $part = mb_strcut($line, 0, 75, 'UTF-8');
                $folded[] = $part;
                $line = ' '.substr($line, strlen($part));
            }
            $folded[] = $line;
        }

        return response(implode("\r\n", $folded)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="cita-'.$cita->id.'.ics"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
