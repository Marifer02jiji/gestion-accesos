<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\QR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::where('id_organizador', Auth::id())
            ->orderBy('fecha_evento', 'asc')
            ->paginate(10);

        return view('eventos.index', compact('eventos'));
    }

    public function create()
    {
        return view('eventos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_evento'        => 'required|string',
            'descripcion'        => 'nullable|string',
            'lugar'              => 'required|string|max:100',
            'tolerancia_antes'   => 'required|in:15,30',
            'tolerancia_despues' => 'required|in:15,30',
            'fecha_evento'       => ['required', 'date', 'after:now', function($attribute, $value, $fail) {
                $fecha = \Carbon\Carbon::parse($value);
                $dia   = (int) $fecha->dayOfWeek;
                if ($dia === 0) {
                    $fail('No se pueden agendar eventos los domingos.');
                    return;
                }
                if ($dia === 6) {
                    $fail('No se pueden agendar eventos los sabados.');
                    return;
                }
            }],
            'numero_personas'    => 'required|integer|min:1',
            'correo_responsable' => 'required|email|max:150',
            'nombre_responsable' => 'required|string|max:150',
        ]);

        $evento = Evento::create([
            'folio'               => Evento::generarFolio(),
            'tipo_evento'         => $request->tipo_evento,
            'descripcion'         => $request->descripcion,
            'lugar'               => $request->lugar,
            'fecha_evento'        => $request->fecha_evento,
            'numero_personas'     => $request->numero_personas,
            'correo_responsable'  => $request->correo_responsable,
            'nombre_responsable'  => $request->nombre_responsable,
            'tolerancia_antes'    => $request->tolerancia_antes,
            'tolerancia_despues'  => $request->tolerancia_despues,
            'id_organizador'      => Auth::id(),
            'id_estado_solicitud' => 2,
        ]);

        // Generar QR con tolerancia
        $qr = QR::create([
            'codigo_numerico'        => $evento->folio,
            'vigencia_inicio'        => \Carbon\Carbon::parse($evento->fecha_evento)->subMinutes((int) $request->tolerancia_antes),
            'vigencia_final'         => \Carbon\Carbon::parse($evento->fecha_evento)->addMinutes((int) $request->tolerancia_despues),           
            'prorroga_tolerancia'    => false,
            'id_estadoQr'            => 1,
            'id_solicitud_visitante' => null,
        ]);

        $evento->update(['id_qr' => $qr->id_qr]);

        // Enviar QR automáticamente
        try {
            Mail::to($evento->correo_responsable)
                ->send(new \App\Mail\EnviarQRMail($qr));
        } catch (\Throwable $e) {
            Log::error('Error enviando QR evento: ' . $e->getMessage());
        }

        return redirect()->route('eventos.show', $evento->id_evento)
            ->with('success', "Evento creado y QR enviado a {$evento->correo_responsable}. Folio: {$evento->folio}");
    }

    public function show($id)
    {
        $evento = Evento::with('qr')->findOrFail($id);
        return view('eventos.show', compact('evento'));
    }

    public function reenviarQR($id)
    {
        $evento = Evento::with('qr')->findOrFail($id);

        if (!$evento->qr) {
            return redirect()->route('eventos.show', $id)
                ->with('error', 'Este evento no tiene QR generado.');
        }

        try {
            Mail::to($evento->correo_responsable)
                ->send(new \App\Mail\EnviarQRMail($evento->qr));

            return redirect()->route('eventos.show', $id)
                ->with('success', "QR reenviado correctamente a {$evento->correo_responsable}.");
        } catch (\Throwable $e) {
            Log::error('Error reenviando QR evento: ' . $e->getMessage());
            return redirect()->route('eventos.show', $id)
                ->with('error', 'No se pudo reenviar el QR.');
        }
    }

    public function destroy($id)
    {
        $evento = Evento::findOrFail($id);

        if ($evento->id_qr) {
            \App\Models\RegistroAcceso::where('id_qr', $evento->id_qr)->delete();
            QR::where('id_qr', $evento->id_qr)->delete();
        }

        $evento->delete();

        return redirect()->route('eventos.index')
            ->with('success', 'Evento eliminado correctamente.');
    }
}