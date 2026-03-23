<?php
  // ──────────────────────────────────────────────
  // Carga del archivo .env (clave=valor, sin Composer)
  // ──────────────────────────────────────────────
  $envFile = dirname(__DIR__) . '/.env';
  if (file_exists($envFile)) {
      $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      foreach ($lines as $line) {
          $line = trim($line);
          if ($line === '' || strpos($line, '#') === 0) continue;
          if (strpos($line, '=') === false) continue;
          [$key, $value] = array_map('trim', explode('=', $line, 2));
          if (!defined($key) && !array_key_exists($key, $_ENV)) {
              $_ENV[$key]    = $value;
              putenv("$key=$value");
          }
      }
  }
  unset($envFile, $lines, $line, $key, $value);

  // ──────────────────────────────────────────────
  // Parámetros de la Base de Datos
  // ──────────────────────────────────────────────
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root'); // Reemplaza con tu usuario de DB
  define('DB_PASS', ''); // Reemplaza con tu contraseña de DB
  define('DB_NAME', 'actividades_gero3'); // Reemplaza con el nombre de tu DB

  
  // App Root (Ruta a la carpeta /app)
  define('APPROOT', dirname(dirname(__FILE__)) . '/app');

  // URL Root (URL base de tu sitio)
  define('URLROOT', 'http://localhost/gero_activities');

  // Nombre del Sitio
  define('SITENAME', 'Gero Actividades');
?>