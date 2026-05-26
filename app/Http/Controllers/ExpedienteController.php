<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\Area;
use App\Models\TipoDocumento;
use App\Models\HistorialEstado;
use App\Models\HistorialEdicion;
use Carbon\Carbon;


class ExpedienteController extends Controller
{
    // ─── Helper ───────────────────────────────────────────────
     private function formatExpediente($expediente): array
    {
        return [
            'id'                  => $expediente->id,
            'numero_expediente'   => $expediente->numero_expediente,
            'titulo'              => $expediente->titulo,
            'descripcion'         => $expediente->descripcion,
            'tipo_documento_id'   => $expediente->tipo_documento_id,
            'tipo_documento'      => $expediente->tipoDocumento?->nombre ?? 'GENERAL / ADMINISTRATIVO',
            'area_origen_id'      => $expediente->area_origen_id,
            'area_origen'         => $expediente->areaOrigen?->nombre ?? 'ÁREA MUNICIPAL JLO',  
            'area_actual_id'      => $expediente->area_actual_id,
            'area_actual'         => $expediente->areaActual?->nombre ?? 'ARCHIVO CENTRAL',
            'numero_folios'       => $expediente->numero_folios,
            'estado'              => $expediente->estado,
            'fecha_ingreso'       => optional($expediente->fecha_ingreso)->format('Y-m-d'),
            'tiempo_conservacion' => $expediente->tiempo_conservacion,
            'fecha_revision'      => $expediente->fecha_revision
                ? $expediente->fecha_revision->format('Y-m-d')
                : 'PERMANENTE',
            'digitalizado'        => $expediente->digitalizado,
            'created_at'          => optional($expediente->created_at)->format('Y-m-d H:i:s'),
            'updated_at'          => optional($expediente->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    // ─── Helper: calcular estado según fecha_revision ─────────
    private function calcularEstado(?string $fechaRevision): string
    {
        if (!$fechaRevision) {
            return 'Activo'; // Permanente
        }

        $diasRestantes = Carbon::today()->diffInDays(Carbon::parse($fechaRevision), false);

        if ($diasRestantes <= 30) {
            return 'Para revision'; // PROXIMO o ATRASADO
        }

        return 'Activo'; // VIGENTE
    }

    // ─── Store ────────────────────────────────────────────────
 public function store(Request $request)
    {
        $request->validate([
            'numero_expediente'   => 'required|string|max:50|unique:expedientes,numero_expediente',
            'titulo'              => 'required|string|max:255',
            'descripcion'         => 'required|string',
            'tipo_documento_id'   => 'required|exists:tipos_documento,id',
            'area_origen_id'      => 'required|exists:areas,id',
            'area_actual_id'      => 'required|exists:areas,id',
            'numero_folios'       => 'required|integer|min:1',
            'fecha_ingreso'       => 'required|date|before_or_equal:today',
            'tiempo_conservacion' => 'required|string|max:50',
        ]);

        $data = $request->all();
        $data['fecha_revision'] = null;

        $tiempo = strtolower(trim($data['tiempo_conservacion']));

        if ($tiempo === 'permanente') {
            $data['fecha_revision'] = null;
        } else {
            $fecha = Carbon::parse($data['fecha_ingreso']);

            if ($tiempo === '0.5') {
                $data['fecha_revision'] = $fecha->addMonths(6)->toDateString();
            } else {
                $años = (int) $tiempo;
                if ($años > 0) {
                    $data['fecha_revision'] = $fecha->addYears($años)->toDateString();
                }
            }
        }

        $data['estado'] = $this->calcularEstado($data['fecha_revision']);

        $expediente = Expediente::create($data);
        
        // Forzamos la carga de relaciones para que formatExpediente no retorne nulos
        $expediente->load(['tipoDocumento', 'areaOrigen', 'areaActual']);

        return response()->json([
            'message'    => 'Expediente registrado correctamente',
            'expediente' => $this->formatExpediente($expediente),
        ], 201);
    }

    // ─── Index ────────────────────────────────────────────────
    public function index()
    {
        $expedientes = Expediente::with(['areaActual', 'tipoDocumento', 'areaOrigen'])
                                ->orderBy('updated_at', 'desc')
                                ->get();
        return response()->json($expedientes);
    }

    // ─── Show ─────────────────────────────────────────────────
    public function show($id)
    {
        $expediente = Expediente::with(['tipoDocumento', 'areaOrigen', 'areaActual'])->find($id);

        if (!$expediente) {
            return response()->json(['message' => 'Expediente no encontrado'], 404);
        }

        return response()->json($this->formatExpediente($expediente), 200);
    }

    // ─── Update ───────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $expediente = Expediente::find($id);

        if (!$expediente) {
            return response()->json(['message' => 'Expediente no encontrado'], 404);
        }

        $request->validate([
            'numero_expediente'   => 'required|string|max:50|unique:expedientes,numero_expediente,' . $id,
            'titulo'              => 'required|string|max:255',
            'descripcion'         => 'required|string',
            'tipo_documento_id'   => 'required|exists:tipos_documento,id',
            'area_origen_id'      => 'required|exists:areas,id',
            'area_actual_id'      => 'required|exists:areas,id',
            'numero_folios'       => 'required|integer|min:1',
            'fecha_ingreso'       => 'required|date|before_or_equal:today',
            'tiempo_conservacion' => 'required|string|max:50',
        ]);

        $data = $request->except('estado');

        // Recalcular fecha_revision si cambió fecha_ingreso o tiempo_conservacion
        $fechaIngresoAnterior = $expediente->fecha_ingreso ? $expediente->fecha_ingreso->format('Y-m-d') : null;
        $cambioFechaIngreso = $data['fecha_ingreso'] !== $fechaIngresoAnterior;
        $cambioTiempoConservacion = $data['tiempo_conservacion'] !== $expediente->tiempo_conservacion;

        if ($cambioFechaIngreso || $cambioTiempoConservacion) {
            $tiempo = strtolower(trim($data['tiempo_conservacion']));

            if ($tiempo === 'permanente') {
                $data['fecha_revision'] = null;
            } else {
                $fecha = Carbon::parse($data['fecha_ingreso']);

                if ($tiempo === '0.5') {
                    $data['fecha_revision'] = $fecha->addMonths(6)->toDateString();
                } else {
                    $años = (int) $tiempo;
                    if ($años > 0) {
                        $data['fecha_revision'] = $fecha->addYears($años)->toDateString();
                    }
                }
            }
        }

        $fechaRevisionFinal = $data['fecha_revision'] ?? ($expediente->fecha_revision?->format('Y-m-d'));
        $data['estado'] = $this->calcularEstado($fechaRevisionFinal);

        // Registrar cambios en historial_ediciones con formateo estricto
        foreach ($data as $campo => $valor_nuevo) {
            if (in_array($campo, ['estado', 'fecha_revision'])) {
                continue;
            }

            $valor_anterior = $expediente->{$campo} ?? null;

            if ($valor_anterior instanceof \Carbon\Carbon) {
                $valor_anterior = $valor_anterior->format('Y-m-d');
            }

            $valor_anterior_str = (string) $valor_anterior;
            $valor_nuevo_str = (string) $valor_nuevo;

            if ($valor_anterior_str !== $valor_nuevo_str) {
                HistorialEdicion::create([
                    'expediente_id'    => $expediente->id,
                    'campo_modificado' => $campo,
                    'valor_anterior'   => $valor_anterior_str,
                    'valor_nuevo'      => $valor_nuevo_str,
                    'usuario_id'       => $request->user()->id ?? null,
                    'fecha_cambio'     => Carbon::now('America/Lima'),
                ]);
            }
        }

        $expediente->update($data);
        $expediente->refresh();
        $expediente->load(['tipoDocumento', 'areaOrigen', 'areaActual']);

        return response()->json([
            'message'    => 'Expediente actualizado correctamente',
            'expediente' => $this->formatExpediente($expediente),
        ], 200);
    }

    // ─── Search ───────────────────────────────────────────────
    public function search(Request $request)
    {
        $query = Expediente::with(['tipoDocumento', 'areaOrigen', 'areaActual']);

        if ($request->numero_expediente) {
            $query->where('numero_expediente', 'like', '%' . $request->numero_expediente . '%');
        }
        if ($request->titulo) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }
        if ($request->area_actual_id) {
            $query->where('area_actual_id', $request->area_actual_id);
        }
        if ($request->estado) {
            $query->where('estado', $request->estado);
        }
        if ($request->tipo_documento_id) {
            $query->where('tipo_documento_id', $request->tipo_documento_id);
        }
        if ($request->fecha_inicio && $request->fecha_fin) {
            $query->whereBetween('fecha_ingreso', [$request->fecha_inicio, $request->fecha_fin]);
        }

        $expedientes = $query->orderBy('updated_at', 'desc')->paginate(10);

        if ($expedientes->isEmpty()) {
            return response()->json(['message' => 'No se encontraron expedientes', 'expedientes' => []], 200);
        }

        return response()->json([
            'data'         => $expedientes->map(fn($e) => $this->formatExpediente($e)),
            'current_page' => $expedientes->currentPage(),
            'last_page'    => $expedientes->lastPage(),
            'per_page'     => $expedientes->perPage(),
            'total'        => $expedientes->total(),
        ], 200);
    }

    // ─── Listas para formulario ───────────────────────────────
     public function areas()
    {
        return response()->json(Area::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre']), 200);
    }

    public function tiposDocumento()
    {
        return response()->json(TipoDocumento::orderBy('nombre')->get(['id', 'nombre']), 200);
    }

    // ─── Historial de ediciones ───────────────────────────────
    public function historial($id)
    {
        $expediente = Expediente::find($id);

        if (!$expediente) {
            return response()->json(['message' => 'Expediente no encontrado'], 404);
        }

        $historialesEdiciones = HistorialEdicion::where('expediente_id', $id)
            ->with(['usuario'])
            ->orderBy('fecha_cambio', 'desc')
            ->get();
            
        return response()->json([
            'historialesEdiciones' => $historialesEdiciones,
        ], 200);
    }
}

