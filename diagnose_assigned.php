<?php
require 'config/config.php';
require 'app/libraries/Database.php';

$db = new Database();

echo "=== DIAGNÓSTICO DE ASIGNACIONES DE USUARIOS ===\n\n";

// Revisar usuarios con NULL email
$db->query('SELECT Id_usuario, email, estado_usuario FROM usuario WHERE email IS NULL OR email = "" ORDER BY Id_usuario');
$nullEmails = $db->resultSet();
echo "1. Usuarios con email NULL o vacío: " . count($nullEmails) . "\n";
if(!empty($nullEmails)){
    foreach($nullEmails as $user){
        echo "   ID: " . $user->Id_usuario . " - Estado: " . $user->estado_usuario . "\n";
    }
}

echo "\n";

// Revisar usuarios inactivos
$db->query('SELECT COUNT(*) as total FROM usuario WHERE estado_usuario = 0');
$inactivos = $db->single();
echo "2. Usuarios inactivos (estado_usuario=0): " . $inactivos->total . "\n";

echo "\n";

// Revisar tarjetas sin asignar vs asignadas
$db->query('
    SELECT 
        COUNT(CASE WHEN Id_usuario_asignado IS NULL THEN 1 END) as sin_asignar,
        COUNT(CASE WHEN Id_usuario_asignado IS NOT NULL THEN 1 END) as con_asignar
    FROM tablero_tarjetas 
    WHERE Estado = 1
');
$tarjetas = $db->single();
echo "3. Tarjetas sin asignar: " . $tarjetas->sin_asignar . " | Con asignación: " . $tarjetas->con_asignar . "\n";

echo "\n";

// Revisar asignaciones a usuarios inactivos o sin email
$db->query('
    SELECT 
        COUNT(*) as total
    FROM tablero_tarjetas t
    LEFT JOIN usuario u ON u.Id_usuario = t.Id_usuario_asignado
    WHERE t.Estado = 1
      AND t.Id_usuario_asignado IS NOT NULL
      AND (u.email IS NULL OR u.email = "" OR u.estado_usuario = 0)
');
$problemas = $db->single();
echo "4. Tarjetas asignadas a usuarios inactivos o sin email: " . $problemas->total . "\n";

echo "\n";

// Ver algunos ejemplos de tarjetas sin email de asignado
$db->query('
    SELECT 
        t.Id_tarjeta,
        t.Titulo,
        t.Id_usuario_asignado,
        u.email,
        u.estado_usuario
    FROM tablero_tarjetas t
    LEFT JOIN usuario u ON u.Id_usuario = t.Id_usuario_asignado
    WHERE t.Estado = 1
      AND t.Id_usuario_asignado IS NOT NULL
      AND (u.email IS NULL OR u.email = "" OR u.estado_usuario = 0)
    LIMIT 5
');
$ejemplos = $db->resultSet();
if(!empty($ejemplos)){
    echo "5. Ejemplos de tarjetas con problema:\n";
    foreach($ejemplos as $tarjeta){
        echo "   Tarjeta #" . $tarjeta->Id_tarjeta . " (" . $tarjeta->Titulo . ")\n";
        echo "     - Asignado a Usuario ID: " . $tarjeta->Id_usuario_asignado . "\n";
        echo "     - Email en BD: " . ($tarjeta->email ?? "NULL") . "\n";
        echo "     - Estado usuario: " . ($tarjeta->estado_usuario ?? "N/A") . "\n";
    }
}

echo "\n";

// Ver usuarios de la tabla personal que pueden no estar en usuario
$db->query('
    SELECT 
        p.Id_personal,
        p.Nombre_completo,
        p.Id_usuario,
        u.email,
        u.estado_usuario
    FROM personal p
    LEFT JOIN usuario u ON u.Id_usuario = p.Id_usuario
    WHERE p.Estado = 1
      AND (u.Id_usuario IS NULL OR u.email IS NULL OR u.email = "" OR u.estado_usuario = 0)
    LIMIT 5
');
$personalProblemas = $db->resultSet();
if(!empty($personalProblemas)){
    echo "6. Personal sin usuario válido o activo:\n";
    foreach($personalProblemas as $pers){
        echo "   " . $pers->Nombre_completo . " (ID Personal: " . $pers->Id_personal . ")\n";
        echo "     - Usuario ID: " . ($pers->Id_usuario ?? "NULL") . "\n";
        echo "     - Email: " . ($pers->email ?? "NULL") . "\n";
    }
}
?>
