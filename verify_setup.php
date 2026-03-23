<?php
/**
 * ============================================================
 * SETUP VERIFICATION - Verificar instalación
 * ============================================================
 * Este script verifica que todo esté configurado correctamente
 * después de ejecutar auto_setup.php
 * ============================================================
 */

// Configuración
$configPath = __DIR__ . '/config/config.php';
$configLoaded = false;

if (file_exists($configPath)) {
    require_once $configPath;
    $configLoaded = true;
}

$db_host = defined('DB_HOST') ? DB_HOST : 'localhost';
$db_user = defined('DB_USER') ? DB_USER : 'root';
$db_pass = defined('DB_PASS') ? DB_PASS : '';
$db_name = defined('DB_NAME') ? DB_NAME : 'gestor_actividades';

$issues = [];
$success_count = 0;
$checks = [];

// 1. Verificar conexión MySQL
$check1 = [
    'number' => 1,
    'title' => 'Conexión a MySQL',
    'status' => 'pending',
    'message' => '',
    'details' => []
];

try {
    $conn = new mysqli($db_host, $db_user, $db_pass);
    if ($conn->connect_error) {
        $check1['status'] = 'error';
        $check1['message'] = "No se puede conectar a MySQL: " . $conn->connect_error;
        $issues[] = $check1['message'];
    } else {
        $check1['status'] = 'success';
        $check1['message'] = "Conexión exitosa a MySQL";
        $success_count++;
    }
} catch (Exception $e) {
    $check1['status'] = 'error';
    $check1['message'] = "Excepción: " . $e->getMessage();
    $issues[] = $check1['message'];
}
$checks[] = $check1;

// 2. Verificar base de datos
$check2 = [
    'number' => 2,
    'title' => 'Base de Datos',
    'status' => 'pending',
    'message' => '',
    'details' => []
];

if (isset($conn) && !$conn->connect_error) {
    $db_check = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
    if ($db_check && $db_check->num_rows > 0) {
        $check2['status'] = 'success';
        $check2['message'] = "Base de datos '$db_name' existe";
        $success_count++;
        $conn->select_db($db_name);
    } else {
        $check2['status'] = 'error';
        $check2['message'] = "Base de datos '$db_name' no existe";
        $issues[] = "Ejecuta auto_setup.php primero.";
    }
} else {
    $check2['status'] = 'error';
    $check2['message'] = "No se pudo verificar la base de datos";
}
$checks[] = $check2;

// 3. Verificar tablas
$check3 = [
    'number' => 3,
    'title' => 'Tablas Requeridas',
    'status' => 'pending',
    'message' => '',
    'details' => [],
    'table_details' => []
];

// Tabla a verificar con sus columnas requeridas
$tables = [
    'usuario' => ['Id_usuario', 'email', 'pass', 'estado_usuario'],
    'roles' => ['Id_role', 'Nombre', 'Estado'],
    'permisos' => ['Id_permiso', 'Nombre', 'Modulo', 'Accion', 'Estado'],
    'role_permiso' => ['Id_role', 'Id_permiso', 'Estado'],
    'usuario_role' => ['Id_usuario', 'Id_role', 'Estado'],
    'division' => ['Id_Division', 'Nombre', 'Siglas', 'Estado_division'],
    'personal' => ['Id_personal', 'Nombre_Completo', 'Apellido_Completo', 'Id_usuario', 'Estado'],
    'contratos' => ['Id_contrato', 'Descripcion', 'Inicio_contrato', 'Fin_contrato', 'Contrato_activo', 'Estado'],
    'alcances' => ['Id_alcance', 'Id_contrato', 'Descripcion', 'es_recurrente', 'Estado'],
    'actividades' => ['Id_actividad', 'Id_personal', 'Id_alcance', 'Fecha_ingreso', 'Estado_actividad', 'Estado']
];

$tables_ok = 0;
if (isset($conn) && !$conn->connect_error) {
    foreach ($tables as $table => $required_columns) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            // Tabla existe, ahora verificar columnas
            $columns_result = $conn->query("SHOW COLUMNS FROM `$table`");
            $existing_columns = [];
            
            if ($columns_result) {
                while ($row = $columns_result->fetch_assoc()) {
                    $existing_columns[] = $row['Field'];
                }
            }
            
            // Verificar columnas requeridas
            $missing_columns = [];
            foreach ($required_columns as $required) {
                if (!in_array($required, $existing_columns)) {
                    $missing_columns[] = $required;
                }
            }
            
            if (empty($missing_columns)) {
                $check3['table_details'][] = [
                    'name' => $table, 
                    'status' => 'success',
                    'columns' => count($existing_columns),
                    'details' => 'Todas las columnas correctas'
                ];
                $tables_ok++;
            } else {
                $check3['table_details'][] = [
                    'name' => $table, 
                    'status' => 'warning',
                    'columns' => count($existing_columns),
                    'details' => 'Faltan: ' . implode(', ', $missing_columns)
                ];
                $issues[] = "Tabla '$table' tiene columnas faltantes: " . implode(', ', $missing_columns);
            }
        } else {
            $check3['table_details'][] = [
                'name' => $table, 
                'status' => 'error',
                'details' => 'Tabla no existe'
            ];
            $issues[] = "Tabla '$table' no existe";
        }
    }
}

if ($tables_ok == count($tables)) {
    $check3['status'] = 'success';
    $check3['message'] = "Todas las tablas y estructuras son correctas (" . count($tables) . ")";
    $success_count++;
} else {
    $warnings = count($check3['table_details']) - $tables_ok;
    if ($warnings > 0 && $tables_ok > 0) {
        $check3['status'] = 'warning';
        $check3['message'] = "$tables_ok de " . count($tables) . " tablas correctas ($warnings con problemas)";
    } else {
        $check3['status'] = 'error';
        $check3['message'] = "$tables_ok de " . count($tables) . " tablas encontradas";
    }
}
$checks[] = $check3;

// 4. Verificar usuario administrador
$check4 = [
    'number' => 4,
    'title' => 'Usuario Administrador',
    'status' => 'pending',
    'message' => '',
    'details' => []
];

if (isset($conn) && !$conn->connect_error) {
    $admin_check = $conn->query("SELECT * FROM usuario WHERE email = 'admin@admin.com'");
    if ($admin_check && $admin_check->num_rows > 0) {
        $admin = $admin_check->fetch_assoc();
        $check4['status'] = 'success';
        $check4['message'] = "Usuario admin existe";
        $check4['details'][] = "Email: " . $admin['email'];
        $check4['details'][] = "Estado: " . ($admin['estado_usuario'] == 1 ? 'Activo' : 'Inactivo');
        $check4['details'][] = "Creado: " . $admin['Fecha_creacion'];
        $success_count++;
    } else {
        $check4['status'] = 'error';
        $check4['message'] = "Usuario administrador no existe";
        $issues[] = "Usuario admin no encontrado";
    }
} else {
    $check4['status'] = 'error';
    $check4['message'] = "No se pudo verificar el usuario";
}
$checks[] = $check4;

// 5. Verificar roles
$check5 = [
    'number' => 5,
    'title' => 'Roles del Sistema',
    'status' => 'pending',
    'message' => '',
    'details' => []
];

if (isset($conn) && !$conn->connect_error) {
    $roles_check = $conn->query("SELECT COUNT(*) as count FROM roles WHERE Estado = 1");
    if ($roles_check) {
        $roles_count = $roles_check->fetch_assoc()['count'];
        $expected_roles = 6; // Administrador, Gerente, Jefe, Supervisor, Personal, Visualizador
        
        if ($roles_count >= $expected_roles) {
            $check5['status'] = 'success';
            $check5['message'] = "Roles configurados correctamente";
            $check5['details'][] = "Total de roles activos: " . $roles_count . " (se esperaban mínimo $expected_roles)";
            
            // Listar roles existentes
            $roles_list = $conn->query("SELECT Nombre FROM roles WHERE Estado = 1 ORDER BY Nombre");
            if ($roles_list && $roles_list->num_rows > 0) {
                $check5['details'][] = "Roles: ";
                while ($role = $roles_list->fetch_assoc()) {
                    $check5['details'][] = "  • " . $role['Nombre'];
                }
            }
            $success_count++;
        } else {
            $check5['status'] = 'warning';
            $check5['message'] = "Roles insuficientes: $roles_count encontrados (se esperaban $expected_roles)";
            $check5['details'][] = "Solo hay $roles_count roles (se esperaban $expected_roles o más)";
            $issues[] = "Roles insuficientes: $roles_count de $expected_roles";
        }
    }
} else {
    $check5['status'] = 'error';
    $check5['message'] = "No se pudo verificar los roles";
}
$checks[] = $check5;

// 6. Verificar permisos
$check6 = [
    'number' => 6,
    'title' => 'Permisos del Sistema',
    'status' => 'pending',
    'message' => '',
    'details' => []
];

if (isset($conn) && !$conn->connect_error) {
    $permisos_check = $conn->query("SELECT COUNT(*) as count FROM permisos WHERE Estado = 1");
    if ($permisos_check) {
        $permisos_count = $permisos_check->fetch_assoc()['count'];
        $expected_permisos = 30; // Aproximadamente 30 permisos iniciales
        
        if ($permisos_count >= $expected_permisos) {
            $check6['status'] = 'success';
            $check6['message'] = "Permisos configurados correctamente";
            $check6['details'][] = "Total de permisos activos: " . $permisos_count . " (se esperaban mínimo $expected_permisos)";
            
            // Agrupar permisos por módulo
            $modulos_check = $conn->query("SELECT DISTINCT Modulo, COUNT(*) as total FROM permisos WHERE Estado = 1 GROUP BY Modulo ORDER BY Modulo");
            if ($modulos_check && $modulos_check->num_rows > 0) {
                $check6['details'][] = "Permisos por módulo: ";
                while ($modulo = $modulos_check->fetch_assoc()) {
                    $check6['details'][] = "  • " . $modulo['Modulo'] . ": " . $modulo['total'] . " permisos";
                }
            }
            $success_count++;
        } else {
            $check6['status'] = 'warning';
            $check6['message'] = "Permisos insuficientes: $permisos_count encontrados (se esperaban $expected_permisos)";
            $check6['details'][] = "Solo hay $permisos_count permisos (se esperaban $expected_permisos o más)";
            $issues[] = "Permisos insuficientes: $permisos_count de $expected_permisos";
        }
    }
} else {
    $check6['status'] = 'error';
    $check6['message'] = "No se pudo verificar los permisos";
}
$checks[] = $check6;

// 7. Verificar asignación de permisos al admin
$check7 = [
    'number' => 7,
    'title' => 'Permisos del Admin',
    'status' => 'pending',
    'message' => '',
    'details' => []
];

if (isset($conn) && !$conn->connect_error) {
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
            $check7['status'] = 'success';
            $check7['message'] = "Admin tiene permisos asignados";
            $check7['details'][] = "Permisos asignados: " . $permisos_asignados;
            $success_count++;
        } else {
            $check7['status'] = 'error';
            $check7['message'] = "El admin no tiene permisos asignados";
            $issues[] = "Admin sin permisos";
        }
    }
} else {
    $check7['status'] = 'error';
    $check7['message'] = "No se pudo verificar los permisos del admin";
}
$checks[] = $check7;

// 8. Verificar integridad de relaciones (Foreign Keys)
$check8 = [
    'number' => 8,
    'title' => 'Relaciones entre Tablas',
    'status' => 'pending',
    'message' => '',
    'details' => []
];

if (isset($conn) && !$conn->connect_error) {
    $fk_checks = [
        ['usuario_role', 'usuario'],
        ['usuario_role', 'roles'],
        ['role_permiso', 'roles'],
        ['role_permiso', 'permisos'],
        ['personal', 'usuario'],
        ['personal', 'division'],
        ['contratos', 'division'],
        ['alcances', 'contratos'],
        ['actividades', 'personal'],
        ['actividades', 'alcances']
    ];
    
    $fk_ok = 0;
    $fk_issues = [];
    
    foreach ($fk_checks as $fk_check) {
        $table = $fk_check[0];
        $referenced = $fk_check[1];
        
        $constraint_check = $conn->query("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = '$db_name'
            AND TABLE_NAME = '$table'
            AND REFERENCED_TABLE_NAME = '$referenced'
            LIMIT 1
        ");
        
        if ($constraint_check && $constraint_check->num_rows > 0) {
            $fk_ok++;
            $check8['details'][] = "✓ $table → $referenced";
        } else {
            $fk_issues[] = "$table → $referenced";
            $check8['details'][] = "✗ FALTA: $table → $referenced";
        }
    }
    
    if (empty($fk_issues)) {
        $check8['status'] = 'success';
        $check8['message'] = "Todas las relaciones están correctas";
        $success_count++;
    } else {
        $check8['status'] = 'warning';
        $check8['message'] = "$fk_ok/" . count($fk_checks) . " relaciones correctas";
        $issues[] = "Algunas relaciones (Foreign Keys) pueden estar incompletas";
    }
} else {
    $check8['status'] = 'error';
    $check8['message'] = "No se pudo verificar las relaciones";
}
$checks[] = $check8;

// 9. Verificar archivo config.php
$check9 = [
    'number' => 9,
    'title' => 'Archivo de Configuración',
    'status' => 'pending',
    'message' => '',
    'details' => []
];

if ($configLoaded) {
    $check9['status'] = 'success';
    $check9['message'] = "Archivo config/config.php existe";
    $check9['details'][] = $configPath;
    $check9['details'][] = "Host configurado: " . $db_host;
    $check9['details'][] = "Usuario configurado: " . $db_user;
    $check9['details'][] = "Base de datos configurada: " . $db_name;
    $success_count++;
} else {
    $check9['status'] = 'error';
    $check9['message'] = "Archivo config/config.php no encontrado";
    $issues[] = "Archivo de configuración faltante";
}
$checks[] = $check9;

$total_checks = count($checks);
$percentage = ($success_count / $total_checks) * 100;

if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Instalación - Gero Actividades</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
            animation: slideDown 0.6s ease-out;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .progress-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.6s ease-out;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .progress-text {
            font-size: 1.2em;
            font-weight: 600;
            color: #333;
        }

        .progress-percentage {
            font-size: 1.3em;
            font-weight: bold;
            color: #667eea;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .checks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .check-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 5px solid #ddd;
            animation: fadeIn 0.6s ease-out backwards;
        }

        .check-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .check-card.success {
            border-left-color: #4caf50;
            background: linear-gradient(135deg, #f5fff8 0%, #ffffff 100%);
        }

        .check-card.error {
            border-left-color: #f44336;
            background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        }

        .check-card.warning {
            border-left-color: #ff9800;
            background: linear-gradient(135deg, #fffef5 0%, #ffffff 100%);
        }

        .check-card.pending {
            border-left-color: #9e9e9e;
            background: #fafafa;
        }

        .check-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .check-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            margin-right: 15px;
            font-size: 1.2em;
        }

        .check-card.success .check-number {
            background: #4caf50;
        }

        .check-card.error .check-number {
            background: #f44336;
        }

        .check-card.warning .check-number {
            background: #ff9800;
        }

        .check-card.pending .check-number {
            background: #9e9e9e;
        }

        .check-title {
            font-size: 1.1em;
            font-weight: 600;
            color: #333;
        }

        .check-status {
            margin-top: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.95em;
            font-weight: 500;
        }

        .check-card.success .check-status {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .check-card.error .check-status {
            background: #ffebee;
            color: #c62828;
        }

        .check-card.warning .check-status {
            background: #fff3e0;
            color: #e65100;
        }

        .check-details {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e0e0e0;
            font-size: 0.9em;
            color: #666;
        }

        .check-detail-item {
            margin: 6px 0;
            padding: 4px 0;
        }

        .table-details {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e0e0e0;
        }

        .table-item {
            display: flex;
            align-items: center;
            margin: 6px 0;
            font-size: 0.9em;
        }

        .table-item .status-icon {
            margin-right: 8px;
            font-size: 1.1em;
        }

        .summary-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.8s ease-out;
        }

        .summary-title {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .result-status {
            text-align: center;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .result-status.success {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border: 2px solid #4caf50;
        }

        .result-status.warning {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border: 2px solid #ff9800;
        }

        .result-status.error {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            border: 2px solid #f44336;
        }

        .result-status h2 {
            font-size: 1.8em;
            margin-bottom: 10px;
        }

        .result-status.success h2 {
            color: #2e7d32;
        }

        .result-status.warning h2 {
            color: #e65100;
        }

        .result-status.error h2 {
            color: #c62828;
        }

        .result-status p {
            font-size: 1.1em;
            color: #333;
        }

        .issues-section {
            margin-top: 20px;
            padding: 20px;
            background: #fff3e0;
            border: 1px solid #ffb74d;
            border-radius: 8px;
            display: none;
        }

        .issues-section.show {
            display: block;
        }

        .issues-section h3 {
            color: #e65100;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .issue-item {
            padding: 10px 0;
            color: #d84315;
            border-bottom: 1px solid #ffe0b2;
        }

        .issue-item:last-child {
            border-bottom: none;
        }

        .next-steps {
            margin-top: 30px;
            padding: 20px;
            background: #e3f2fd;
            border: 1px solid #64b5f6;
            border-radius: 8px;
            display: none;
        }

        .next-steps.show {
            display: block;
        }

        .next-steps h3 {
            color: #1565c0;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .step-item {
            padding: 10px 0;
            padding-left: 25px;
            position: relative;
            color: #0d47a1;
        }

        .step-item:before {
            content: "✓";
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .credential-box {
            background: #f5f5f5;
            padding: 12px;
            border-radius: 6px;
            margin: 10px 0;
            font-family: monospace;
            border: 1px solid #ddd;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8em;
            }

            .checks-grid {
                grid-template-columns: 1fr;
            }

            .progress-info {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Verificación de Instalación</h1>
            <p>Gero Actividades - Gestor de Actividades</p>
        </div>

        <div class="progress-section">
            <div class="progress-info">
                <span class="progress-text">Progreso General</span>
                <span class="progress-percentage"><?php echo round($percentage); ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
            </div>
        </div>

        <div class="checks-grid">
            <?php foreach ($checks as $check): ?>
                <?php
                    $delay = ($check['number'] - 1) * 0.1;
                    $style = "animation-delay: {$delay}s";
                ?>
                <div class="check-card <?php echo $check['status']; ?>" style="<?php echo $style; ?>">
                    <div class="check-header">
                        <div class="check-number"><?php echo $check['number']; ?></div>
                        <div class="check-title"><?php echo $check['title']; ?></div>
                    </div>
                    <div class="check-status">
                        <?php
                            $icons = [
                                'success' => '✓ ',
                                'error' => '✗ ',
                                'warning' => '⚠ ',
                                'pending' => '○ '
                            ];
                            echo $icons[$check['status']] . $check['message'];
                        ?>
                    </div>
                    <?php if (!empty($check['details'])): ?>
                        <div class="check-details">
                            <?php foreach ($check['details'] as $detail): ?>
                                <div class="check-detail-item">→ <?php echo htmlspecialchars($detail); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($check['table_details'])): ?>
                        <div class="table-details">
                            <?php foreach ($check['table_details'] as $table): ?>
                                <div class="table-item">
                                    <span class="status-icon"><?php echo $table['status'] === 'success' ? '✓' : ($table['status'] === 'warning' ? '⚠' : '✗'); ?></span>
                                    <span style="flex: 1;"><strong><?php echo htmlspecialchars($table['name']); ?></strong></span>
                                    <?php if (isset($table['columns'])): ?>
                                        <span style="color: #666; font-size: 0.85em;">cols: <?php echo $table['columns']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($table['details']) && $table['details'] !== 'Todas las columnas correctas'): ?>
                                    <div style="margin-left: 45px; padding: 6px 0; font-size: 0.85em; color: #e65100;">
                                        → <?php echo htmlspecialchars($table['details']); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="summary-section" style="margin-top: 30px;">
            <div class="summary-title">📊 Resumen Final</div>
            
            <?php
                if ($success_count == $total_checks) {
                    $status_class = 'success';
                    $title = '🎉 ¡INSTALACIÓN VERIFICADA CORRECTAMENTE!';
                    $message = 'El sistema está listo para usar';
                } else if ($success_count >= 6) {
                    $status_class = 'warning';
                    $title = '⚠️ INSTALACIÓN PARCIALMENTE COMPLETADA';
                    $message = 'Algunos elementos faltan. Revisa los problemas abajo.';
                } else {
                    $status_class = 'error';
                    $title = '❌ INSTALACIÓN INCOMPLETA';
                    $message = 'Debes ejecutar auto_setup.php primero.';
                }
            ?>

            <div class="result-status <?php echo $status_class; ?>">
                <h2><?php echo $title; ?></h2>
                <p><?php echo $message; ?></p>
                <p style="margin-top: 10px; font-size: 0.95em;">
                    Verificaciones exitosas: <strong><?php echo $success_count; ?> / <?php echo $total_checks; ?></strong>
                </p>
            </div>

            <?php if (!empty($issues)): ?>
                <div class="issues-section show">
                    <h3>Problemas Encontrados:</h3>
                    <?php foreach ($issues as $issue): ?>
                        <div class="issue-item">→ <?php echo htmlspecialchars($issue); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_count == $total_checks): ?>
                <div class="next-steps show">
                    <h3>✅ Próximos Pasos:</h3>
                    <div class="step-item">Accede a: <strong>http://localhost/gero_activities/</strong></div>
                    <div class="step-item">Inicia sesión con las siguientes credenciales:
                        <div class="credential-box">
                            Email: <strong>admin@admin.com</strong><br>
                            Contraseña: <strong>Admin.62</strong>
                        </div>
                    </div>
                    <div class="step-item">Cambia la contraseña del administrador inmediatamente</div>
                    <div class="step-item">Comienza a configurar tu sistema</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
