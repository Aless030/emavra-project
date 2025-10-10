<?php

require __DIR__.'/vendor/autoload.php';

echo "=== TEST DE INTERVENTION/IMAGE CON GD ===\n\n";

// Verificar que GD está disponible
if (extension_loaded('gd')) {
    echo "✓ Extensión GD está cargada\n";
    $gdInfo = gd_info();
    echo "  Versión GD: " . $gdInfo['GD Version'] . "\n\n";
} else {
    echo "✗ Extensión GD NO está cargada\n\n";
}

// Probar Intervention/Image con GD
try {
    $manager = new \Intervention\Image\ImageManager(['driver' => 'gd']);
    echo "✓ ImageManager creado con driver GD exitosamente\n";
    
    // Crear una imagen de prueba
    $image = $manager->canvas(100, 100, '#ff0000');
    echo "✓ Imagen de prueba creada\n";
    
    // Guardar
    $testPath = __DIR__.'/test-image.jpg';
    $image->save($testPath);
    echo "✓ Imagen guardada en: $testPath\n";
    
    if (file_exists($testPath)) {
        echo "✓ Archivo existe y pesa: " . filesize($testPath) . " bytes\n";
        unlink($testPath); // Eliminar archivo de prueba
        echo "✓ Archivo de prueba eliminado\n";
    }
    
    echo "\n=== TODO FUNCIONA CORRECTAMENTE ===\n";
    
} catch (\Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}