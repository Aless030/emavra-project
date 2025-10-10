<?php

namespace App\Http\Controllers;

use App\Models\Arbol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;


class ArbolController extends Controller
{
    /**
     * Obtener todos los árboles
     */
    public function index()
    {
        $arboles = Arbol::select([
            'id', 'especie', 'nombre_comun', 'edad', 'estado', 'fotoUrl', 
            'altura', 'diametroTronco', 'diametro_copa', 'codigo_arbol',
            'latitud', 'longitud', 'propiedad', 'otb', 'nombre_area_verde',
            'inspector', 'estado_fitosanitario', 'pdfUrl', 'qrUrl',
            DB::raw("DATE_FORMAT(fecha_registro, '%d/%m/%Y') as fecha_formato"),
            'hora_registro',
            // CORREGIDO: Manejar NULL en coordenadas
            DB::raw("IFNULL(ST_AsText(coordenadas), NULL) as coordenadas")
        ])->orderBy('fecha_registro', 'desc')
          ->orderBy('hora_registro', 'desc')
          ->get();

        return response()->json($arboles);
    }
    
    /**
     * Obtener un árbol específico
     */
    public function show($id)
    {
        $arbol = Arbol::select([
            'id', 'especie', 'nombre_comun', 'edad', 'estado', 'fotoUrl',
            'altura', 'diametroTronco', 'diametro_copa', 'codigo_arbol',
            'latitud', 'longitud', 'propiedad', 'otb', 'nombre_area_verde',
            'inspector', 'estado_fitosanitario', 'pdfUrl', 'qrUrl',
            DB::raw("DATE_FORMAT(fecha_registro, '%d/%m/%Y') as fecha_formato"),
            'hora_registro',
            // CORREGIDO: Manejar NULL en coordenadas
            DB::raw("IFNULL(ST_AsText(coordenadas), NULL) as coordenadas")
        ])->findOrFail($id);

        return response()->json($arbol);
    }

    /**
     * Crear nuevo árbol
     */
    public function store(Request $request)
    {
        // Validación
        $validator = Validator::make($request->all(), [
            'especie' => 'required|string|max:255',
            'nombre_comun' => 'required|string|max:255',
            'codigo_arbol' => 'required|string|unique:arboles,codigo_arbol|max:50',
            'edad' => 'required|integer|min:1',
            'estado' => 'required|in:exótico,nativo',
            'altura' => 'required|numeric|min:0.1',
            'diametroTronco' => 'required|numeric|min:0.1',
            'diametro_copa' => 'required|numeric|min:0.1',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'propiedad' => 'required|string|max:255',
            'otb' => 'required|string|max:255',
            'nombre_area_verde' => 'required|string|max:255',
            'inspector' => 'required|string|max:255',
            'estado_fitosanitario' => 'nullable|string',
            'foto' => 'required|file|max:8192',
            'pdf' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Validar manualmente que sea imagen
            $file = $request->file('foto');
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo debe ser una imagen válida',
                ], 422);
            }

            // Procesar imagen
            $fotoUrl = $this->processImage($file);

            // Procesar PDF si existe
            $pdfUrl = null;
            if ($request->hasFile('pdf')) {
                $pdfUrl = $this->processPdf($request->file('pdf'));
            }

            // Crear árbol
            $arbol = new Arbol([
                'especie' => $request->especie,
                'nombre_comun' => $request->nombre_comun,
                'edad' => $request->edad,
                'estado' => $request->estado,
                'fotoUrl' => $fotoUrl,
                'altura' => $request->altura,
                'diametroTronco' => $request->diametroTronco,
                'diametro_copa' => $request->diametro_copa,
                'codigo_arbol' => $request->codigo_arbol,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'propiedad' => $request->propiedad,
                'otb' => $request->otb,
                'nombre_area_verde' => $request->nombre_area_verde,
                'inspector' => $request->inspector,
                'estado_fitosanitario' => $request->estado_fitosanitario,
                'pdfUrl' => $pdfUrl,
                'fecha_registro' => now()->toDateString(),
                'hora_registro' => now()->toTimeString(),
            ]);

            $arbol->save();

            // Actualizar coordenadas espaciales
            if ($arbol->latitud && $arbol->longitud) {
                DB::statement(
                    "UPDATE arboles SET coordenadas = POINT(?, ?) WHERE id = ?",
                    [$arbol->longitud, $arbol->latitud, $arbol->id]
                );
            }

            // Generar QR (después de que el ID existe)
            try {
                $qrUrl = $this->generateQR($arbol->id);
                $arbol->qrUrl = $qrUrl;
                $arbol->save();
            } catch (\Exception $e) {
                \Log::error('Error generando QR: ' . $e->getMessage());
                // No fallar todo el registro si el QR falla
            }

            return response()->json([
                'success' => true,
                'message' => 'Árbol registrado exitosamente',
                'id' => $arbol->id,
                'arbol' => $arbol
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error en store(): ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar árbol: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar Arbol
     */
    public function update(Request $request, $id)
    {
        $arbol = Arbol::findOrFail($id);

        // Log para debug
        \Log::info('Update request', [
            'id' => $id,
            'has_foto' => $request->hasFile('foto'),
            'has_pdf' => $request->hasFile('pdf'),
            'all_data' => $request->except(['foto', 'pdf', '_method'])
        ]);

        // Validación más completa
        $validator = Validator::make($request->all(), [
            'especie' => 'nullable|string|max:255',
            'nombre_comun' => 'nullable|string|max:255',
            'edad' => 'nullable|integer|min:1',
            'estado' => 'nullable|in:exótico,nativo',
            'altura' => 'nullable|numeric|min:0.1',
            'diametroTronco' => 'nullable|numeric|min:0.1',
            'diametro_copa' => 'nullable|numeric|min:0.1',
            'codigo_arbol' => 'nullable|string|max:50',
            'inspector' => 'nullable|string|max:255',
            'propiedad' => 'nullable|string|max:255',
            'otb' => 'nullable|string|max:255',
            'nombre_area_verde' => 'nullable|string|max:255',
            'estado_fitosanitario' => 'nullable|string',
            'foto' => 'nullable|file|max:8192',
            'pdf' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Actualizar imagen
            if ($request->hasFile('foto')) {
                // Validar manualmente que sea imagen
                $file = $request->file('foto');
                $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                
                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El archivo debe ser una imagen válida',
                    ], 422);
                }

                // Eliminar imagen anterior
                if ($arbol->fotoUrl) {
                    Storage::disk('public')->delete($arbol->fotoUrl);
                }
                $arbol->fotoUrl = $this->processImage($request->file('foto'));
                \Log::info('Imagen actualizada: ' . $arbol->fotoUrl);
            }

            // Actualizar PDF
            if ($request->hasFile('pdf')) {
                // Eliminar PDF anterior
                if ($arbol->pdfUrl) {
                    Storage::disk('public')->delete($arbol->pdfUrl);
                }
                $arbol->pdfUrl = $this->processPdf($request->file('pdf'));
                \Log::info('PDF actualizado: ' . $arbol->pdfUrl);
            }

            // Actualizar todos los campos de texto
            $fieldsToUpdate = [
                'especie', 'nombre_comun', 'edad', 'estado', 'altura',
                'diametroTronco', 'diametro_copa', 'codigo_arbol',
                'inspector', 'propiedad', 'otb', 'nombre_area_verde',
                'estado_fitosanitario'
            ];

            foreach ($fieldsToUpdate as $field) {
                if ($request->has($field)) {
                    $arbol->$field = $request->input($field);
                    \Log::info("Campo actualizado: $field = " . $request->input($field));
                }
            }

            $arbol->save();

            \Log::info('Árbol actualizado exitosamente', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Actualizado correctamente',
                'arbol' => $arbol
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en update: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar árbol
     */
    public function destroy($id)
    {
        try {
            $arbol = Arbol::findOrFail($id);

            // Eliminar archivos
            if ($arbol->fotoUrl && Storage::disk('public')->exists($arbol->fotoUrl)) {
                Storage::disk('public')->delete($arbol->fotoUrl);
            }
            if ($arbol->pdfUrl && Storage::disk('public')->exists($arbol->pdfUrl)) {
                Storage::disk('public')->delete($arbol->pdfUrl);
            }
            if ($arbol->qrUrl && Storage::disk('public')->exists($arbol->qrUrl)) {
                Storage::disk('public')->delete($arbol->qrUrl);
            }

            $arbol->delete();

            return response()->json([
                'success' => true,
                'message' => 'Árbol eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesar imagen con compresión
     */
    private function processImage($file)
    {
        $filename = 'trees/' . uniqid('tree_') . '.jpg';
        $path = storage_path('app/public/' . $filename);

        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        // USAR GD DIRECTAMENTE
        $manager = new \Intervention\Image\ImageManager(['driver' => 'gd']);
        $image = $manager->make($file->getRealPath());
        
        if ($image->width() > 1920 || $image->height() > 1920) {
            $image->resize(1920, 1920, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $image->save($path, 80, 'jpg');
        return $filename;
    }

    /**
     * Procesar PDF - CORREGIDO
     */
    private function processPdf($file)
    {
        $filename = uniqid('pdf_') . '.pdf';
        
        // Usar el método de Laravel directamente
        $path = $file->storeAs('pdfs', $filename, 'public');
        
        return $path;
    }

    /**
     * Generar código QR
     */
    private function generateQR($treeId)
    {
        $url = config('app.frontend_url', 'http://localhost:3000') . '/?tree_id=' . $treeId . '#map';
        $filename = 'qr_codes/qr_' . $treeId . '.png';
        $path = storage_path('app/public/' . $filename);

        // Crear directorio si no existe
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        // Generar QR
        $qrCode = new QrCode($url);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        file_put_contents($path, $result->getString());

        return $filename;
    }
}