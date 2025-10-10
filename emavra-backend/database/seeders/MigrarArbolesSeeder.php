<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrarArbolesSeeder extends Seeder
{
    public function run()
    {
        // Configuración de la base de datos antigua
        $oldDb = [
            'host' => '127.0.0.1',
            'database' => 'reforest', // Nombre de tu BD antigua
            'username' => 'root',
            'password' => '',
            'port' => 3306
        ];

        try {
            // Conectar a la base de datos antigua
            $oldConnection = new \mysqli(
                $oldDb['host'],
                $oldDb['username'],
                $oldDb['password'],
                $oldDb['database'],
                $oldDb['port']
            );

            if ($oldConnection->connect_error) {
                throw new \Exception("Error conectando a BD antigua: " . $oldConnection->connect_error);
            }

            echo "✓ Conectado a base de datos antigua\n";

            // Obtener todos los árboles de la tabla antigua
            $query = "SELECT * FROM arboles ORDER BY id";
            $result = $oldConnection->query($query);

            if (!$result) {
                throw new \Exception("Error en query: " . $oldConnection->error);
            }

            echo "✓ Se encontraron {$result->num_rows} árboles para migrar\n";

            $migrados = 0;
            $errores = 0;

            while ($row = $result->fetch_assoc()) {
                try {
                    // Extraer coordenadas si están en formato POINT
                    $lat = $row['latitud'];
                    $lng = $row['longitud'];

                    if (isset($row['coordenadas']) && !empty($row['coordenadas'])) {
                        // Si coordenadas está en formato "POINT(lng lat)"
                        if (preg_match('/POINT\(([-\d.]+)\s+([-\d.]+)\)/', $row['coordenadas'], $matches)) {
                            $lng = $matches[1];
                            $lat = $matches[2];
                        }
                    }

                    // Copiar archivos (imagen, PDF, QR) si existen
                    $fotoUrl = $this->copyFile($row['fotoUrl'] ?? null, 'trees');
                    $pdfUrl = $this->copyFile($row['pdfUrl'] ?? null, 'pdfs');
                    $qrUrl = $this->copyFile($row['qrUrl'] ?? null, 'qr_codes');

                    // Insertar en la nueva tabla
                    $newId = DB::table('arboles')->insertGetId([
                        'especie' => $row['especie'] ?? '',
                        'nombre_comun' => $row['nombre_comun'] ?? '',
                        'edad' => $row['edad'] ?? 0,
                        'estado' => $row['estado'] ?? 'nativo',
                        'fotoUrl' => $fotoUrl,
                        'altura' => $row['altura'] ?? 0,
                        'diametroTronco' => $row['diametroTronco'] ?? 0,
                        'diametro_copa' => $row['diametro_copa'] ?? 0,
                        'codigo_arbol' => $row['codigo_arbol'] ?? 'MIGRADO_' . $row['id'],
                        'latitud' => $lat,
                        'longitud' => $lng,
                        'propiedad' => $row['propiedad'] ?? '',
                        'otb' => $row['otb'] ?? '',
                        'nombre_area_verde' => $row['nombre_area_verde'] ?? '',
                        'inspector' => $row['inspector'] ?? '',
                        'estado_fitosanitario' => $row['estado_fitosanitario'] ?? null,
                        'pdfUrl' => $pdfUrl,
                        'qrUrl' => $qrUrl,
                        'fecha_registro' => $row['fecha_registro'] ?? now()->toDateString(),
                        'hora_registro' => $row['hora_registro'] ?? now()->toTimeString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Actualizar coordenadas espaciales
                    DB::statement(
                        "UPDATE arboles SET coordenadas = ST_GeomFromText('POINT($lng $lat)') WHERE id = ?",
                        [$newId]
                    );

                    $migrados++;
                    echo "✓ Migrado árbol #{$row['id']}: {$row['especie']}\n";

                } catch (\Exception $e) {
                    $errores++;
                    echo "✗ Error migrando árbol #{$row['id']}: {$e->getMessage()}\n";
                }
            }

            $oldConnection->close();

            echo "\n========================================\n";
            echo "RESUMEN DE MIGRACIÓN\n";
            echo "========================================\n";
            echo "Total de árboles procesados: " . ($migrados + $errores) . "\n";
            echo "Migrados exitosamente: $migrados\n";
            echo "Errores: $errores\n";
            echo "========================================\n";

        } catch (\Exception $e) {
            echo "ERROR FATAL: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Copiar archivo del sistema antiguo al nuevo
     */
    private function copyFile($oldPath, $folder)
    {
        if (empty($oldPath)) {
            return null;
        }

        // Rutas posibles donde pueden estar los archivos antiguos
        $possiblePaths = [
            public_path($oldPath),
            public_path('../' . $oldPath),
            base_path('../' . $oldPath),
            'C:/wamp64/www/' . $oldPath,
        ];

        foreach ($possiblePaths as $sourcePath) {
            if (File::exists($sourcePath)) {
                try {
                    // Generar nuevo nombre
                    $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                    $newFilename = $folder . '/' . uniqid('migrated_') . '.' . $extension;
                    
                    // Crear directorio si no existe
                    $targetDir = storage_path('app/public/' . $folder);
                    if (!File::exists($targetDir)) {
                        File::makeDirectory($targetDir, 0755, true);
                    }

                    // Copiar archivo
                    $targetPath = storage_path('app/public/' . $newFilename);
                    File::copy($sourcePath, $targetPath);

                    return $newFilename;

                } catch (\Exception $e) {
                    echo "  ⚠ No se pudo copiar archivo $oldPath: {$e->getMessage()}\n";
                    return null;
                }
            }
        }

        echo "  ⚠ Archivo no encontrado: $oldPath\n";
        return null;
    }
}