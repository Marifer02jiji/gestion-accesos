<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistroAcceso extends Model
{
    protected $table      = 'registroacceso';
    protected $primaryKey = 'id_registro';
    public    $timestamps = false;

    protected $guarded = [];

    protected $fillable = [
        'hora_llegada_institucion',
        'hora_llegada_encuentro',
        'hora_salida_encuentro',
        'hora_salida_institucion',
        'observaciones',
        'telefono_vigilante_entrada',
        'caseta_entrada',
        'telefono_vigilante_salida',
        'caseta_salida',
        'id_qr',
    ];

    public function qr()
    {
        return $this->belongsTo(QR::class, 'id_qr', 'id_qr');
    }

    public static function normalizarTelefono(string $valor): string
    {
        return preg_replace('/\D+/', '', trim($valor)) ?? '';
    }

    /**
     * INSERT directo: evita que mass-assignment omita telefono_vigilante_entrada.
     */
    public static function registrarEntradaInstitucional(
        int $idQr,
        string $telefonoVigilanteEntrada,
        string $casetaEntrada
    ): self {
        $telefono = self::normalizarTelefono($telefonoVigilanteEntrada);
        $caseta   = trim($casetaEntrada);

        if (strlen($telefono) !== 10) {
            throw new \InvalidArgumentException(
                'telefono_vigilante_entrada debe tener 10 dígitos.'
            );
        }

        if ($caseta === '') {
            throw new \InvalidArgumentException(
                'caseta_entrada es obligatoria.'
            );
        }

        $ahora = now();

        Log::info('RegistroAcceso::registrarEntradaInstitucional', [
            'id_qr'                      => $idQr,
            'telefono_vigilante_entrada' => $telefono,
            'caseta_entrada'             => $caseta,
        ]);

        $idRegistro = DB::table('registroacceso')->insertGetId([
            'id_qr'                      => $idQr,
            'hora_llegada_institucion'   => $ahora,
            'telefono_vigilante_entrada' => $telefono,
            'caseta_entrada'             => $caseta,
        ]);

        return self::query()->findOrFail($idRegistro);
    }

    public static function registrarSalidaInstitucional(
        RegistroAcceso $registro,
        string $telefonoVigilanteSalida,
        string $casetaSalida
    ): void {
        $telefono = self::normalizarTelefono($telefonoVigilanteSalida);
        $caseta   = trim($casetaSalida);

        if (strlen($telefono) !== 10) {
            throw new \InvalidArgumentException(
                'telefono_vigilante_salida debe tener 10 dígitos.'
            );
        }

        if ($caseta === '') {
            throw new \InvalidArgumentException(
                'caseta_salida es obligatoria.'
            );
        }

        DB::table('registroacceso')
            ->where('id_registro', $registro->id_registro)
            ->update([
                'hora_salida_institucion'   => now(),
                'telefono_vigilante_salida' => $telefono,
                'caseta_salida'             => $caseta,
            ]);

        $registro->refresh();
    }
}
