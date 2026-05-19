<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'fecha_ingreso' => 'date:Y-m-d',
        'fecha_revision' => 'date:Y-m-d',
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
}