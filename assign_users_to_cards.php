<?php
// Script para asignar usuarios a tarjetas de demostración
require 'config/config.php';
require 'app/libraries/Database.php';

$db = new Database();

echo "=== ASIGNANDO USUARIOS A TARJETAS ===\n\n";

// Obtener usuarios asignados al tablero
$db->query('SELECT Id_usuario, email FROM usuario WHERE estado_usuario = 1 LIMIT 5');
$usuarios = $db->resultSet();
if(empty($usuarios)){
    echo "❌ No hay usuarios activos para asignar.\n";
    exit(1);
}

echo "✓ Usuarios disponibles: " . count($usuarios) . "\n";
foreach($usuarios as $u){
    echo "  - ID: " . $u->Id_usuario . " | Email: " . $u->email . "\n";
}

echo "\n";

// Obtener tarjetas del primer tablero
$db->query('
    SELECT t.Id_tarjeta, t.Titulo, t.Id_tablero 
    FROM tablero_tarjetas t 
    WHERE t.Estado = 1 
    LIMIT 1
');
$primerTablero = $db->single();
if(!$primerTablero){
    echo "❌ No hay tableros con tarjetas.\n";
    exit(1);
}

$idTablero = (int)$primerTablero->Id_tablero;

// Obtener usuarios asignados a este tablero
$db->query('
    SELECT DISTINCT u.Id_usuario, u.email
    FROM tablero_usuario_permiso tup
    INNER JOIN usuario u ON u.Id_usuario = tup.Id_usuario
    WHERE tup.Id_tablero = :id_tablero AND tup.Estado = 1 AND u.estado_usuario = 1
    LIMIT 3
');
$db->bind(':id_tablero', $idTablero);
$usuariosTablero = $db->resultSet();

if(empty($usuariosTablero)){
    echo "❌ No hay usuarios asignados al tablero.\n";
    exit(1);
}

echo "✓ Usuarios asignados al tablero #" . $idTablero . ": " . count($usuariosTablero) . "\n";

// Asignar usuarios a tarjetas
$db->query('
    SELECT t.Id_tarjeta, t.Titulo
    FROM tablero_tarjetas t 
    WHERE t.Id_tablero = :id_tablero AND t.Estado = 1
    LIMIT 20
');
$db->bind(':id_tablero', $idTablero);
$tarjetas = $db->resultSet();

echo "✓ Tarjetas a asignar: " . count($tarjetas) . "\n\n";

$counter = 0;
foreach($tarjetas as $i => $tarjeta){
    $usuario = $usuariosTablero[$i % count($usuariosTablero)];
    $idTarjeta = (int)$tarjeta->Id_tarjeta;
    $idUsuario = (int)$usuario->Id_usuario;
    
    $db->query('
        UPDATE tablero_tarjetas 
        SET Id_usuario_asignado = :id_usuario, Fecha_actualizacion = NOW()
        WHERE Id_tarjeta = :id_tarjeta AND Estado = 1
    ');
    $db->bind(':id_usuario', $idUsuario);
    $db->bind(':id_tarjeta', $idTarjeta);
    
    if($db->execute()){
        echo "✓ Tarjeta #" . $idTarjeta . " (" . $tarjeta->Titulo . ") → " . $usuario->email . "\n";
        $counter++;
    } else {
        echo "❌ Error asignando tarjeta #" . $idTarjeta . "\n";
    }
}

echo "\n✓ Se asignaron exitosamente " . $counter . " tarjetas.\n";

// Verificar resultado
echo "\n=== VERIFICACIÓN ===\n";
$db->query('
    SELECT 
        COUNT(CASE WHEN Id_usuario_asignado IS NULL THEN 1 END) as sin_asignar,
        COUNT(CASE WHEN Id_usuario_asignado IS NOT NULL THEN 1 END) as con_asignar
    FROM tablero_tarjetas 
    WHERE Estado = 1
');
$result = $db->single();
echo "Tarjetas sin asignar: " . $result->sin_asignar . "\n";
echo "Tarjetas con asignación: " . $result->con_asignar . "\n";
?>
