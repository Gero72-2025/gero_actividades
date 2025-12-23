<?php
/**
 * ============================================================
 * SETUP VERIFICATION - Verificar instalación
 * ============================================================
 * Este script verifica que todo esté configurado correctamente
 * después de ejecutar auto_setup.php
 * ============================================================
 */

echo "🔍 Verificando instalación de Gero Actividades...\n\n";

// Configuración
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'gestor_actividades';

$issues = [];
$success_count = 0;

// 1. Verificar conexión MySQL
echo "1️⃣  Verificando conexión a MySQL...\n";
try {
    $conn = new mysqli($db_host, $db_user, $db_pass);
    if ($conn->connect_error) {
        $issues[] = "❌ No se puede conectar a MySQL: " . $conn->connect_error;
        echo "   ❌ FALLO\n\n";
    } else {
        echo "   ✅ Conexión OK\n\n";
        $success_count++;
    }
} catch (Exception $e) {
    $issues[] = "❌ Excepción: " . $e->getMessage();
    echo "   ❌ FALLO\n\n";
}

// 2. Verificar base de datos
echo "2️⃣  Verificando base de datos '$db_name'...\n";
$db_check = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
if ($db_check && $db_check->num_rows > 0) {
    echo "   ✅ Base de datos existe\n\n";
    $success_count++;
    $conn->select_db($db_name);
} else {
    $issues[] = "❌ Base de datos '$db_name' no existe. Ejecuta auto_setup.php primero.";
    echo "   ❌ FALLO\n\n";
}

// 3. Verificar tablas
echo "3️⃣  Verificando tablas requeridas...\n";
$tables = [
    'usuario',
    'roles',
    'permisos',
    'role_permiso',
    'usuario_role',
    'division',
    'personal',
    'contratos',
    'alcances',
    'actividades'
];

$tables_ok = 0;
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "   ✅ Tabla '$table' existe\n";
        $tables_ok++;
    } else {
        echo "   ❌ Tabla '$table' NO existe\n";
        $issues[] = "❌ Tabla '$table' no existe";
    }
}
echo "\n";

if ($tables_ok == count($tables)) {
    $success_count++;
}

// 4. Verificar usuario administrador
echo "4️⃣  Verificando usuario administrador...\n";
$admin_check = $conn->query("SELECT * FROM usuario WHERE email = 'admin@admin.com'");
if ($admin_check && $admin_check->num_rows > 0) {
    $admin = $admin_check->fetch_assoc();
    echo "   ✅ Usuario admin existe\n";
    echo "      Email: {$admin['email']}\n";
    echo "      Estado: " . ($admin['estado_usuario'] == 1 ? 'Activo' : 'Inactivo') . "\n";
    echo "      Creado: {$admin['Fecha_creacion']}\n\n";
    $success_count++;
} else {
    $issues[] = "❌ Usuario administrador no existe";
    echo "   ❌ Usuario admin NO existe\n\n";
}

// 5. Verificar roles
echo "5️⃣  Verificando roles...\n";
$roles_check = $conn->query("SELECT COUNT(*) as count FROM roles WHERE Estado = 1");
if ($roles_check) {
    $roles_count = $roles_check->fetch_assoc()['count'];
    if ($roles_count >= 5) {
        echo "   ✅ Roles creados ($roles_count)\n\n";
        $success_count++;
    } else {
        $issues[] = "⚠️  Solo hay $roles_count roles (se esperaban 5 o más)";
        echo "   ⚠️  ADVERTENCIA: Solo hay $roles_count roles\n\n";
    }
}

// 6. Verificar permisos
echo "6️⃣  Verificando permisos...\n";
$permisos_check = $conn->query("SELECT COUNT(*) as count FROM permisos WHERE Estado = 1");
if ($permisos_check) {
    $permisos_count = $permisos_check->fetch_assoc()['count'];
    if ($permisos_count >= 30) {
        echo "   ✅ Permisos creados ($permisos_count)\n\n";
        $success_count++;
    } else {
        $issues[] = "⚠️  Solo hay $permisos_count permisos (se esperaban 30 o más)";
        echo "   ⚠️  ADVERTENCIA: Solo hay $permisos_count permisos\n\n";
    }
}

// 7. Verificar asignación de permisos al admin
echo "7️⃣  Verificando asignación de permisos al admin...\n";
$admin_permisos = $conn->query("
    SELECT COUNT(DISTINCT rp.Id_permiso) as count
    FROM usuario u
    JOIN usuario_role ur ON u.Id_usuario = ur.Id_usuario
    JOIN role_permiso rp ON ur.Id_role = rp.Id_role
    WHERE u.email = 'admin@admin.com' AND ur.Estado = 1 AND rp.Estado = 1
");

if ($admin_permisos) {
    $permisos_asignados = $admin_permisos->fetch_assoc()['count'];
    if ($permisos_asignados > 0) {
        echo "   ✅ Admin tiene $permisos_asignados permisos asignados\n\n";
        $success_count++;
    } else {
        $issues[] = "❌ El admin no tiene permisos asignados";
        echo "   ❌ Admin no tiene permisos asignados\n\n";
    }
}

// 8. Verificar archivo config.php
echo "8️⃣  Verificando archivo config/config.php...\n";
if (file_exists('../config/config.php')) {
    echo "   ✅ Archivo config.php existe\n\n";
    $success_count++;
} else {
    $issues[] = "❌ Archivo config/config.php no encontrado";
    echo "   ❌ Archivo config/config.php no encontrado\n\n";
}

// Resumen
echo "\n════════════════════════════════════════════════════════════\n";
echo "📊 RESUMEN DE VERIFICACIÓN\n";
echo "════════════════════════════════════════════════════════════\n\n";

$total_checks = 8;
$percentage = ($success_count / $total_checks) * 100;

echo "Verificaciones exitosas: $success_count / $total_checks (" . round($percentage) . "%)\n\n";

if (count($issues) > 0) {
    echo "⚠️  PROBLEMAS ENCONTRADOS:\n";
    foreach ($issues as $issue) {
        echo "   $issue\n";
    }
    echo "\n";
}

if ($success_count == $total_checks) {
    echo "🎉 ¡INSTALACIÓN VERIFICADA CORRECTAMENTE!\n";
    echo "\n✅ El sistema está listo para usar.\n";
    echo "\nPróximos pasos:\n";
    echo "1. Ve a: http://localhost/gero_activities/\n";
    echo "2. Inicia sesión con:\n";
    echo "   - Email: admin@admin.com\n";
    echo "   - Password: Admin.62\n";
    echo "3. Cambia la contraseña del admin\n";
} else if ($success_count >= 6) {
    echo "⚠️  INSTALACIÓN PARCIALMENTE COMPLETADA\n";
    echo "\nAlgunos elementos faltan. Revisa los problemas arriba.\n";
} else {
    echo "❌ INSTALACIÓN INCOMPLETA\n";
    echo "\nDebes ejecutar auto_setup.php primero.\n";
}

echo "\n════════════════════════════════════════════════════════════\n";

if ($conn) {
    $conn->close();
}

?>
