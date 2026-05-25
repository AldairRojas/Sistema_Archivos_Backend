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
            'tipo_documento'      => $expediente->tipoDocumento?->nombre,
            'area_origen'         => $expediente->areaOrigen?->nombre,
            'area_actual'         => $expediente->areaActual?->nombre,
            'numero_folios'       => $expediente->numero_folios,
            'estado'              => $expediente->estado,
            'fecha_ingreso'       => optional($expediente->fecha_ingreso)->format('Y-m-d'),
            'tiempo_conservacion' => $expediente->tiempo_conservacion,
            'fecha_revision' => $expediente->fecha_revision
                ? $expediente->fecha_revision->format('Y-m-d')
                : 'PERMANENTE',

            'digitalizado'        => $expediente->digitalizado,
            'created_at'          => $expediente->created_at,
        ];
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
            'estado'              => 'required|in:Activo,Para revision',
            'fecha_ingreso'       => 'required|date|before_or_equal:today',

            // puede ser "Permanente" o texto tipo "5 años"
            'tiempo_conservacion' => 'required|string|max:50',
        ]);

        $data = $request->all();
        $data['fecha_revision'] = null;

        $tiempo = strtolower(trim($data['tiempo_conservacion']));

        // ─────────────────────────────────────────────
        // 🔥 CASO 1: PERMANENTE
        // ─────────────────────────────────────────────
        if ($tiempo === 'permanente') {
            $data['fecha_revision'] = null;
        }
        // ─────────────────────────────────────────────
        // 🔥 CASO 2: TEMPORAL (Parchado para React)
        // ─────────────────────────────────────────────
        else {
            $fecha = Carbon::parse($data['fecha_ingreso']);

            // Si es "0.5", sabemos que representa los 6 meses de React
            if ($tiempo === '0.5') {
                $data['fecha_revision'] = $fecha->addMonths(6)->toDateString();
            } else {
                // Si viene un número entero (ej: "1", "5", "10") de años
                $años = (int) $tiempo;
                if ($años > 0) {
                    $data['fecha_revision'] = $fecha->addYears($años)->toDateString();
                }
            }
        }

        $expediente = Expediente::create($data);
        $expediente->load(['tipoDocumento', 'areaOrigen', 'areaActual']);

        return response()->json([
            'message'    => 'Expediente registrado correctamente',
            'expediente' => $this->formatExpediente($expediente),
        ], 201);
    }

    // ─── Index ────────────────────────────────────────────────
    public function index()
    {
        $expedientes = Expediente::with(['areaActual', 'tipoDocumento'])->get();
        return response()->json($expedientes);
    }

    // ─── Show ─────────────────────────────────────────────────
    public function show($id)
    {
        $expediente = Expediente::with(['tipoDocumento', 'areaOrigen', 'areaActual'])
            ->find($id);

        if (!$expediente) {
            return response()->json([
                'message' => 'Expediente no encontrado'
            ], 404);
        }

        return response()->json($this->formatExpediente($expediente), 200);
    }

     // ─── Update (HU06) ────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $expediente = Expediente::find($id);

        if (!$expediente) {
            return response()->json([
                'message' => 'Expediente no encontrado'
            ], 404);
        }

        $request->validate([
            // numero_expediente es fijo, no se puede cambiar
            // si en el futuro se necesita cambiar, descomentar:
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

        $data = $request->except( 'estado');

        // ── Recalcular fecha_revision si cambia tiempo_conservacion ──
        preg_match('/(\d+)\s*(año|años|mes|meses)/i', $data['tiempo_conservacion'], $matches);

        if (!empty($matches[1]) && !empty($matches[2])) {
            $cantidad = (int) $matches[1];
            $unidad   = strtolower($matches[2]);
            $fecha    = Carbon::parse($data['fecha_ingreso']);

            $data['fecha_revision'] = str_starts_with($unidad, 'mes')
                ? $fecha->addMonths($cantidad)->toDateString()
                : $fecha->addYears($cantidad)->toDateString();
        }

        // Registrar cambios en historial_ediciones
        foreach ($data as $campo => $valor_nuevo) {
            $valor_anterior = $expediente->{$campo} ?? null;
            
            if ($valor_anterior != $valor_nuevo) {
                HistorialEdicion::create([
                    'expediente_id'   => $expediente->id,
                    'campo_modificado' => $campo,
                    'valor_anterior'  => $valor_anterior,
                    'valor_nuevo'     => $valor_nuevo,
                    'usuario_id'      => $request->user()->id ?? null,
                    'fecha_cambio'    => now(),
                ]);
            }
        }

        $expediente->update($data);
        $expediente->load(['tipoDocumento', 'areaOrigen', 'areaActual']);

        return response()->json([
            'message'    => 'Expediente actualizado correctamente',
            'expediente' => $this->formatExpediente($expediente),
        ], 200);
    }

    // ─── Cambiar estado (HU07) ────────────────────────────────
    public function cambiarEstado(Request $request, $id)
    {
        $expediente = Expediente::find($id);

        if (!$expediente) {
            return response()->json([
                'message' => 'Expediente no encontrado'
            ], 404);
        }

        $request->validate([
            'estado' => 'required|in:Activo,Para revision',
            'observaciones' => 'nullable|string|max:500',
        ]);

        if ($expediente->estado === $request->estado) {
            return response()->json([
                'message' => 'El expediente ya tiene ese estado'
            ], 422);
        }

        $estadoAnterior = $expediente->estado;

        $expediente->update(['estado' => $request->estado]);

        HistorialEstado::create([
            'expediente_id'   => $expediente->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $request->estado,
            'usuario_id'      => $request->user()->id,
            'fecha_cambio'    => now(),
            'observaciones'   => $request->observaciones,
        ]);

        return response()->json([
            'message'   => 'Estado actualizado correctamente',
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $request->estado,
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
            $query->whereBetween('fecha_ingreso', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }

        $expedientes = $query->orderBy('created_at', 'desc')->paginate(10);

        if ($expedientes->isEmpty()) {
            return response()->json([
                'message'     => 'No se encontraron expedientes con los filtros aplicados',
                'expedientes' => []
            ], 200);
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
        return response()->json(
            Area::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre']),
            200
        );
    }

    public function tiposDocumento()
    {
        return response()->json(
            TipoDocumento::orderBy('nombre')->get(['id', 'nombre']),
            200
        );
    }

    // ─── Historial de ediciones ───────────────────────
    public function historial($id)
    {
        $expediente = Expediente::find($id);

        if (!$expediente) {
            return response()->json([
                'message' => 'Expediente no encontrado'
            ], 404);
        }

        $historialesEdiciones = HistorialEdicion::where('expediente_id', $id)
            ->with(['usuario'])
            ->orderBy('fecha_cambio', 'desc')
            ->get();

        if ($historialesEdiciones->isEmpty()) {
            return response()->json([
                'message' => 'No hay cambios registrados para este expediente',
                'historialesEdiciones' => [],
            ], 200);
        }

        return response()->json([
            'historialesEdiciones' => $historialesEdiciones,
        ], 200);
    }

    /**
     * Devuelve expedientes agrupados por estado de alerta: VIGENTE, PROXIMO, ATRASADO
     */
    public function alertas()
    {
        $expedientes = Expediente::with(['areaActual', 'tipoDocumento'])->get();

        $grupos = [
            'VIGENTE' => [],
            'PROXIMO' => [],
            'ATRASADO' => [],
        ];

        foreach ($expedientes as $e) {
            $estado = $e->obtenerEstadoAlerta();
            $grupos[$estado][] = $this->formatExpediente($e);
        }

        return response()->json($grupos, 200);
    }
}
