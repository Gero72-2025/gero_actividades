<?php
// Iniciar Sesión
session_start();

// Carga el archivo de configuración
// Usamos dirname(__DIR__) para subir un nivel desde /app a la raíz del proyecto
// de forma segura y construir la ruta absoluta al archivo de configuración.
require_once dirname(__DIR__) . '/config/config.php';

// Cargar helpers
require_once __DIR__ . '/helpers.php';

// Autoloader para las clases de la carpeta 'libraries'
spl_autoload_register(function($className){
  // Usamos APPROOT para asegurar la ruta absoluta a 'libraries/'
  require_once __DIR__ . '/libraries/' . $className . '.php';
});
?>