<?php
// Iniciar Sesión
session_start();

// Prevenir caché en el navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Validación de sesión: si la sesión existe pero el usuario_id está vacío/eliminado,
// limpiar la sesión (protección contra logout incompletos)
if(isset($_SESSION) && empty($_SESSION['user_id']) && !empty($_SESSION)){
    // Hay datos en sesión pero no hay user_id, limpiar todo
    session_unset();
}

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