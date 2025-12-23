<?php
/**
 * Script para descargar librerías CDN localmente
 * Ejecutar: php download_libraries.php
 */

$libraries = [
    [
        'name' => 'Bootstrap CSS',
        'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css',
        'path' => __DIR__ . '/public/lib/bootstrap/bootstrap.min.css'
    ],
    [
        'name' => 'Bootstrap JS',
        'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js',
        'path' => __DIR__ . '/public/lib/bootstrap/bootstrap.bundle.min.js'
    ],
    [
        'name' => 'jQuery',
        'url' => 'https://code.jquery.com/jquery-3.5.1.min.js',
        'path' => __DIR__ . '/public/lib/jquery/jquery-3.5.1.min.js'
    ],
    [
        'name' => 'Font Awesome CSS',
        'url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        'path' => __DIR__ . '/public/lib/font-awesome/all.min.css'
    ],
    [
        'name' => 'Font Awesome Fonts (woff2)',
        'url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/webfonts/fa-solid-900.woff2',
        'path' => __DIR__ . '/public/lib/font-awesome/fonts/fa-solid-900.woff2'
    ],
    [
        'name' => 'Font Awesome Fonts (ttf)',
        'url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/webfonts/fa-solid-900.ttf',
        'path' => __DIR__ . '/public/lib/font-awesome/fonts/fa-solid-900.ttf'
    ],
    [
        'name' => 'Chart.js',
        'url' => 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
        'path' => __DIR__ . '/public/lib/chartjs/chart.min.js'
    ]
];

echo "Iniciando descarga de librerías...\n\n";

foreach ($libraries as $lib) {
    echo "Descargando {$lib['name']}...\n";
    
    // Crear directorio si no existe
    $dir = dirname($lib['path']);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "  ✓ Directorio creado: {$dir}\n";
    }
    
    // Descargar archivo
    $content = @file_get_contents($lib['url']);
    
    if ($content === false) {
        echo "  ✗ Error al descargar desde: {$lib['url']}\n";
        continue;
    }
    
    // Guardar archivo
    if (file_put_contents($lib['path'], $content)) {
        $size = filesize($lib['path']);
        echo "  ✓ Guardado: {$lib['path']} ({$size} bytes)\n";
    } else {
        echo "  ✗ Error al guardar: {$lib['path']}\n";
    }
}

echo "\n✓ Descarga completada.\n";
echo "Ahora debes actualizar los archivos HTML para usar las librerías locales.\n";
