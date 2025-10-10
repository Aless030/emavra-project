<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

try {
    $url = config('app.frontend_url') . '/?tree_id=999#map';
    echo "Frontend URL configurada: " . config('app.frontend_url') . "\n";
    echo "URL del QR: $url\n";
    
    $path = storage_path('app/public/qr_codes/test_qr.png');
    
    // Crear directorio
    $dir = dirname($path);
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "Directorio creado: $dir\n";
    }
    
    // Generar QR (sintaxis v6)
    $qrCode = new QrCode($url);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    file_put_contents($path, $result->getString());
    
    if (file_exists($path)) {
        echo "✓ QR generado exitosamente: $path\n";
        echo "Tamaño: " . filesize($path) . " bytes\n";
    } else {
        echo "✗ El archivo no se creó\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}