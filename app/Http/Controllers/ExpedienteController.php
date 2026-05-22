<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\Area;
use App\Models\TipoDocumento;
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

            // 🔥 AQUÍ LA CLAVE: mostrar PERMANENTE bonito
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
            'estado'              => 'required|in:Activo,Archivado,Prestado,Pendiente transferencia',
            'fecha_ingreso'       => 'required|date|before_or_equal:today',

            // puede ser "Permanente" o texto tipo "5 años"
            'tiempo_conservacion' => 'required|string|max:50',
        ]);

        $data = $request->all();

        $data['fecha_revision'] = null;

        // ─────────────────────────────────────────────
        // 🔥 CASO 1: PERMANENTE
        // ─────────────────────────────────────────────
        if (strtolower($data['tiempo_conservacion']) === 'permanente') {
            $data['fecha_revision'] = null;
        }

        // ─────────────────────────────────────────────
        // 🔥 CASO 2: TEMPORAL (meses o años)
        // ─────────────────────────────────────────────
        else {
            preg_match('/(\d+)\s*(año|años|mes|meses)/i', $data['tiempo_conservacion'], $matches);

            if (!empty($matches[1]) && !empty($matches[2])) {

                $cantidad = (int) $matches[1];
                $unidad   = strtolower($matches[2]);
                $fecha    = Carbon::parse($data['fecha_ingreso']);

                $data['fecha_revision'] = str_starts_with($unidad, 'mes')
                    ? $fecha->addMonths($cantidad)->toDateString()
                    : $fecha->addYears($cantidad)->toDateString();
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
