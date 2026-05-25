<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\ArchivoDigital;
use Illuminate\Support\Facades\Storage;

class ArchivoDigitalController extends Controller
{
    // HU08 - Subida de archivos PDF
    public function subir(Request $request, $id)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf|max:51200',
        ]);

        $expediente = Expediente::findOrFail($id);
        $archivo = $request->file('archivo');

        // Generar nombre único
        $nombre_archivo = time() . '_' . str_replace(' ', '_', $archivo->getClientOriginalName());
        $ruta = $archivo->storeAs('expedientes/' . $expediente->id, $nombre_archivo, 'public');

        // Crear registro
        $archivoDigital = ArchivoDigital::create([
        'expediente_id' => $expediente->id,
        'usuario_id' => $request->user()->id,

        'nombre_original' => $archivo->getClientOriginalName(),
        'nombre_archivo' => $nombre_archivo,
        'ruta_archivo' => $ruta,
        'tipo_mime' => $archivo->getMimeType(),
        'tamano_bytes' => $archivo->getSize(),
        'uploaded_at' => now(),
        ]);

        // HU09 - Actualizar estado de digitalización
        $expediente->update(['digitalizado' => true]);

        return response()->json([
            'message' => 'Archivo subido correctamente',
            'archivo' => $archivoDigital,
        ], 201);
    }

    // HU10 - Listar archivos asociados
    public function listar($id)
    {
        $expediente = Expediente::findOrFail($id);
        $archivos = $expediente->archivosDigitales;

        if ($archivos->isEmpty()) {
            return response()->json([
                'message' => 'El expediente no tiene archivos asociados',
                'archivos' => [],
            ], 200);
        }

        return response()->json([
            'archivos' => $archivos,
        ], 200);
    }

    // HU10 - Descargar archivo
    public function descargar($id, $archivo_id)
    {
        $expediente = Expediente::findOrFail($id);

        $archivo = ArchivoDigital::where('id', $archivo_id)
            ->where('expediente_id', $expediente->id)
            ->firstOrFail();

        $rutaCompleta = storage_path(
            'app/public/' . $archivo->ruta_archivo
        );

        if (!file_exists($rutaCompleta)) {

            return response()->json([
                'error' => 'Archivo no encontrado'
            ], 404);
        }

        return response()->download(
            $rutaCompleta,
            $archivo->nombre_original
        );
    }
}
