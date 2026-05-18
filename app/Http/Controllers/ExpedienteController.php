<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;

class ExpedienteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'numero_expediente' => 'required|string|max:50|unique:expedientes,numero_expediente',
            'titulo'            => 'required|string|max:255',
            'descripcion'       => 'nullable|string',
            'tipo_documento_id' => 'required|exists:tipos_documento,id',
            'area_origen_id'    => 'required|exists:areas,id',
            'area_actual_id'    => 'required|exists:areas,id',
            'numero_folios'     => 'nullable|integer|min:1',
            'estado'            => 'required|in:Activo,Archivado,Prestado,Pendiente transferencia',
            'fecha_ingreso'     => 'required|date',
            'tiempo_conservacion' => 'nullable|string|max:50',
            'fecha_revision'    => 'nullable|date|after:fecha_ingreso',
        ]);

        $expediente = Expediente::create($request->all());

        return response()->json([
            'message'     => 'Expediente registrado correctamente',
            'expediente'  => $expediente->load(['tipoDocumento', 'areaOrigen', 'areaActual'])
        ], 201);
    }

    public function index()
    {
        $expedientes = Expediente::with(['tipoDocumento', 'areaOrigen', 'areaActual'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        if ($expedientes->isEmpty()) {
            return response()->json([
                'message'     => 'No hay expedientes registrados',
                'expedientes' => []
            ], 200);
        }

        return response()->json($expedientes, 200);
    }

    public function show($id)
    {
        $expediente = Expediente::with(['tipoDocumento', 'areaOrigen', 'areaActual'])
            ->find($id);

        if (!$expediente) {
            return response()->json([
                'message' => 'Expediente no encontrado'
            ], 404);
        }

        return response()->json($expediente, 200);
    }

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

        return response()->json($expedientes, 200);
    }
}