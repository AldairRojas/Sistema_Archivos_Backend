<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Expediente extends Model
{
    protected $table = 'expedientes';

    protected $fillable = [
        'numero_expediente',
        'titulo',
        'descripcion',
        'tipo_documento_id',
        'area_origen_id',
        'area_actual_id',
        'numero_folios',
        'estado',
        'fecha_ingreso',
        'tiempo_conservacion',
        'fecha_revision',
        'digitalizado',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_revision' => 'date',
        'digitalizado' => 'boolean',
    ];

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    public function areaOrigen()
    {
        return $this->belongsTo(Area::class, 'area_origen_id');
    }

    public function areaActual()
    {
        return $this->belongsTo(Area::class, 'area_actual_id');
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstado::class, 'expediente_id')
                    ->orderBy('fecha_cambio', 'desc');
    }

    public function archivosDigitales()
    {
        return $this->hasMany(ArchivoDigital::class, 'expediente_id');
    }

    /**
     * Retorna la antigüedad en días desde fecha_ingreso o null si no existe.
     */
    public function calcularAntiguedad(): ?int
    {
        if (! $this->fecha_ingreso) {
            return null;
        }
        return Carbon::parse($this->fecha_ingreso)->diffInDays(Carbon::today());
    }

    /**
     * Indica si la fecha_revision está dentro del umbral (en días) desde hoy.
     */
    public function estaProximoARevision(int $umbralDias = 30): bool
    {
        if (! $this->fecha_revision) {
            return false; // permanente
        }

        $diasRestantes = Carbon::parse($this->fecha_revision)->diffInDays(Carbon::today(), false);
        return $diasRestantes >= 0 && $diasRestantes <= $umbralDias;
    }

    /**
     * Devuelve 'VIGENTE', 'PROXIMO' o 'ATRASADO' según fecha_revision.
     */
    public function obtenerEstadoAlerta(): string
    {
        if (! $this->fecha_revision) {
            return 'VIGENTE';
        }

        $diasRestantes = Carbon::parse($this->fecha_revision)->diffInDays(Carbon::today(), false);

        if ($diasRestantes < 0) {
            return 'ATRASADO';
        }

        if ($diasRestantes <= 30) {
            return 'PROXIMO';
        }

        return 'VIGENTE';
    }
}
