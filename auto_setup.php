<?php
/**
 * ============================================================
 * AUTO SETUP - Instalación Automática de Gero Actividades
 * ============================================================
 * Este script configura automáticamente:
 * - Base de datos
 * - Todas las tablas necesarias
 * - Usuario administrador inicial
 * - Roles y permisos predeterminados
 * 
 * Uso: Ejecutar una sola vez al instalar el proyecto
 * ============================================================
 */

// Detectar si se ejecuta desde navegador o CLI
$isWeb = php_sapi_name() !== 'cli';
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = null;

// Si viene de POST, usa ese nombre de BD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['db_name'])) {
    $db_name = trim(preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['db_name']));
}

// Si se ejecuta desde CLI, solicita el nombre de la BD
if (php_sapi_name() === 'cli' && empty($db_name)) {
    echo "════════════════════════════════════════════════════════════\n";
    echo "🚀 AUTO SETUP - Gero Actividades\n";
    echo "════════════════════════════════════════════════════════════\n\n";
    echo "Por favor, ingresa el nombre de la base de datos:\n";
    echo "Nombre por defecto: gestor_actividades\n";
    echo "Ingresa el nombre (o presiona Enter para usar el por defecto): ";
    
    $input = trim(fgets(STDIN));
    $db_name = !empty($input) ? preg_replace('/[^a-zA-Z0-9_]/', '', $input) : 'gestor_actividades';
    echo "\n";
}

// Si es web y no tiene nombre de BD, muestra formulario
if ($isWeb && empty($db_name)) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Auto Setup - Gero Actividades</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container {
                background: white;
                border-radius: 10px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                max-width: 500px;
                width: 100%;
                padding: 40px;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 3px solid #667eea;
                padding-bottom: 20px;
            }
            .header h1 {
                color: #667eea;
                font-size: 2em;
                margin-bottom: 10px;
            }
            .header p {
                color: #666;
                font-size: 1em;
            }
            .form-group {
                margin-bottom: 25px;
            }
            label {
                display: block;
                margin-bottom: 8px;
                color: #333;
                font-weight: 600;
                font-size: 1em;
            }
            input {
                width: 100%;
                padding: 12px;
                border: 2px solid #ddd;
                border-radius: 5px;
                font-size: 1em;
                transition: border-color 0.3s;
            }
            input:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            .info {
                background: #e7f3ff;
                border-left: 4px solid #2196F3;
                padding: 12px;
                margin-bottom: 20px;
                border-radius: 3px;
                color: #1565c0;
                font-size: 0.95em;
            }
            .button-group {
                display: flex;
                gap: 10px;
                margin-top: 30px;
            }
            button {
                flex: 1;
                padding: 12px;
                border: none;
                border-radius: 5px;
                font-size: 1em;
                font-weight: bold;
                cursor: pointer;
                transition: all 0.3s;
            }
            .btn-submit {
                background: #667eea;
                color: white;
            }
            .btn-submit:hover {
                background: #5568d3;
                box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            }
            .btn-reset {
                background: #f0f0f0;
                color: #333;
            }
            .btn-reset:hover {
                background: #ddd;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🚀 Auto Setup</h1>
                <p>Gero Actividades - Instalación</p>
            </div>
            
            <form method="POST" action="">
                <div class="info">
                    ℹ️ Ingresa el nombre de la base de datos a crear
                </div>
                
                <div class="form-group">
                    <label for="db_name">Nombre de la Base de Datos:</label>
                    <input 
                        type="text" 
                        id="db_name" 
                        name="db_name" 
                        placeholder="ej: gestor_actividades"
                        value="gestor_actividades"
                        pattern="[a-zA-Z0-9_]+"
                        maxlength="64"
                        required
                        autofocus
                    >
                    <small style="color: #666; margin-top: 5px; display: block;">
                        Solo caracteres alfanuméricos y guiones bajos (_)
                    </small>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-submit">▶️ Continuar</button>
                    <button type="reset" class="btn-reset">🔄 Limpiar</button>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Datos de administrador
$admin_email = 'admin@admin.com';
$admin_pass = 'Admin.62';

// Función para output compatible con web y CLI
function output($message) {
    global $isWeb;
    if ($isWeb) {
        echo "<div class='step'>$message</div>\n";
    } else {
        echo $message . "\n";
    }
}

// Función para error
function outputError($message) {
    global $isWeb;
    if ($isWeb) {
        echo "<div class='step' style='color: #dc3545; border-left-color: #dc3545;'><strong>❌ $message</strong></div>\n";
    } else {
        echo "❌ $message\n";
    }
}

// Función para éxito
function outputSuccess($message) {
    global $isWeb;
    if ($isWeb) {
        echo "<div class='step' style='color: #28a745; border-left-color: #28a745;'><strong>✅ $message</strong></div>\n";
    } else {
        echo "✅ $message\n";
    }
}

// Mostrar inicio
if ($isWeb) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Auto Setup - Instalación</title>";
    echo "<style>body{font-family: Arial; background: #f5f5f5; padding: 20px;} ";
    echo ".content{max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px;} ";
    echo ".success{color: #28a745;} .error{color: #dc3545;} .info{color: #17a2b8;} ";
    echo ".step{margin: 15px 0; padding: 10px; border-left: 4px solid #667eea;} ";
    echo ".check{color: #28a745;} h1{color: #667eea;} .final{padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724; margin-top: 20px;}</style></head><body><div class='content'>";
    echo "<h1>🚀 Auto Setup - Instalación en progreso...</h1>";
    echo "<p class='info'>Base de datos: <strong>$db_name</strong></p>";
    echo "<hr style='margin: 20px 0;'>";
} else {
    echo "════════════════════════════════════════════════════════════\n";
    echo "🚀 AUTO SETUP - Gero Actividades\n";
    echo "════════════════════════════════════════════════════════════\n";
    echo "Base de datos: $db_name\n";
    echo "════════════════════════════════════════════════════════════\n\n";
}

// Conexión sin seleccionar BD (para crearla)
    
try {
    $conn = new mysqli($db_host, $db_user, $db_pass);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    output("✅ Conectado a MySQL");
    
    // Crear base de datos
    $sql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        output("✅ Base de datos '$db_name' creada/verificada");
    } else {
        throw new Exception("Error al crear BD: " . $conn->error);
    }
    
    // Seleccionar la BD
    $conn->select_db($db_name);
    output("✅ BD '$db_name' seleccionada");
    output("");
    
    // ============================================================
    // CREAR TABLAS
    // ============================================================
    
    // ============================================================
    // 1. CREAR TABLA: usuario
    // ============================================================
    output("📋 Creando tabla 'usuario'...");
    $sql_usuario = "
        CREATE TABLE IF NOT EXISTS `usuario` (
            `Id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `pass` VARCHAR(255) NOT NULL,
            `estado_usuario` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `conectado` TINYINT(1) DEFAULT 0,
            `fecha_ultimo_login` TIMESTAMP NULL,
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_estado (estado_usuario)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($sql_usuario) === TRUE) {
        outputSuccess("Tabla 'usuario' creada");
    } else {
        throw new Exception("Error: " . $conn->error);
    }
    
    // ============================================================
    // 2. CREAR TABLA: roles
    // ============================================================
    echo "📋 Creando tabla 'roles'...\n";
    $sql_roles = "
        CREATE TABLE IF NOT EXISTS `roles` (
            `Id_role` INT AUTO_INCREMENT PRIMARY KEY,
            `Nombre` VARCHAR(100) NOT NULL UNIQUE,
            `Descripcion` TEXT,
            `Estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_estado (Estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($sql_roles) === TRUE) {
        echo "✅ Tabla 'roles' creada\n";
    } else {
        die("❌ Error: " . $conn->error);
    }
    
    // ============================================================
    // 3. CREAR TABLA: permisos
    // ============================================================
    echo "📋 Creando tabla 'permisos'...\n";
    $sql_permisos = "
        CREATE TABLE IF NOT EXISTS `permisos` (
            `Id_permiso` INT AUTO_INCREMENT PRIMARY KEY,
            `Nombre` VARCHAR(100) NOT NULL UNIQUE,
            `Descripcion` TEXT,
            `Modulo` VARCHAR(50) NOT NULL COMMENT 'Ej: actividades, personal, contratos, etc.',
            `Accion` VARCHAR(50) NOT NULL COMMENT 'Ej: ver, crear, editar, eliminar',
            `Estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_estado (Estado),
            INDEX idx_modulo (Modulo),
            INDEX idx_accion (Accion)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($sql_permisos) === TRUE) {
        echo "✅ Tabla 'permisos' creada\n";
    } else {
        die("❌ Error: " . $conn->error);
    }
    
    // ============================================================
    // 4. CREAR TABLA: role_permiso
    // ============================================================
    echo "📋 Creando tabla 'role_permiso'...\n";
    $sql_role_permiso = "
        CREATE TABLE IF NOT EXISTS `role_permiso` (
            `Id_role` INT NOT NULL,
            `Id_permiso` INT NOT NULL,
            `Estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `Fecha_asignacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`Id_role`, `Id_permiso`),
            FOREIGN KEY (`Id_role`) REFERENCES `roles`(`Id_role`) ON DELETE CASCADE,
            FOREIGN KEY (`Id_permiso`) REFERENCES `permisos`(`Id_permiso`) ON DELETE CASCADE,
            INDEX idx_estado (Estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($sql_role_permiso) === TRUE) {
        echo "✅ Tabla 'role_permiso' creada\n";
    } else {
        die("❌ Error: " . $conn->error);
    }
    
    // ============================================================
    // 5. CREAR TABLA: usuario_role
    // ============================================================
    echo "📋 Creando tabla 'usuario_role'...\n";
    $sql_usuario_role = "
        CREATE TABLE IF NOT EXISTS `usuario_role` (
            `Id_usuario` INT NOT NULL,
            `Id_role` INT NOT NULL,
            `Estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `Fecha_asignacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`Id_usuario`, `Id_role`),
            FOREIGN KEY (`Id_usuario`) REFERENCES `usuario`(`Id_usuario`) ON DELETE CASCADE,
            FOREIGN KEY (`Id_role`) REFERENCES `roles`(`Id_role`) ON DELETE CASCADE,
            INDEX idx_estado (Estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($sql_usuario_role) === TRUE) {
        echo "✅ Tabla 'usuario_role' creada\n";
    } else {
        die("❌ Error: " . $conn->error);
    }
    
    // ============================================================
    // 6. CREAR TABLA: division
    // ============================================================
    echo "📋 Creando tabla 'division'...\n";
    $sql_division = "
        CREATE TABLE IF NOT EXISTS `division` (
            `Id_Division` INT AUTO_INCREMENT PRIMARY KEY,
            `Nombre` VARCHAR(100) NOT NULL UNIQUE,
            `Siglas` VARCHAR(10),
            `Id_personal_jefe` INT NULL,
            `Estado_division` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_estado (Estado_division)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($sql_division) === TRUE) {
        echo "✅ Tabla 'division' creada\n";
    } else {
        die("❌ Error: " . $conn->error);
    }
    
    // ============================================================
    // 7. CREAR TABLA: personal
    // ============================================================
    echo "📋 Creando tabla 'personal'...\n";
    $sql_personal = "
        CREATE TABLE IF NOT EXISTS `personal` (
            `Id_personal` INT AUTO_INCREMENT PRIMARY KEY,
            `Nombre_Completo` VARCHAR(100) NOT NULL,
            `Apellido_Completo` VARCHAR(100) NOT NULL,
            `Puesto` VARCHAR(100),
            `Id_division` INT NULL,
            `Id_usuario` INT NOT NULL,
            `Id_contrato` INT NULL,
            `Estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_usuario (Id_usuario),
            FOREIGN KEY (`Id_usuario`) REFERENCES `usuario`(`Id_usuario`) ON DELETE CASCADE,
            FOREIGN KEY (`Id_division`) REFERENCES `division`(`Id_Division`) ON DELETE SET NULL,
            INDEX idx_estado (Estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($sql_personal) === TRUE) {
        echo "✅ Tabla 'personal' creada\n";
    } else {
        die("❌ Error: " . $conn->error);
    }
    
    // ============================================================
    // 8. CREAR TABLA: contratos
    // ============================================================
    echo "📋 Creando tabla 'contratos'...\n";
    $sql_contratos = "
        CREATE TABLE IF NOT EXISTS `contratos` (
            `Id_contrato` INT AUTO_INCREMENT PRIMARY KEY,
            `Descripcion` TEXT NOT NULL,
            `Numero_pagos` INT DEFAULT 1,
            `Inicio_contrato` DATE NOT NULL,
            `Fin_contrato` DATE NOT NULL,
            `Expediente` VARCHAR(50),
            `Estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_estado (Estado),
            INDEX idx_expediente (Expediente)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($sql_contratos) === TRUE) {
        echo "✅ Tabla 'contratos' creada\n";
    } else {
        die("❌ Error: " . $conn->error);
    }
    
    // ============================================================
    // 9. CREAR TABLA: alcances
    // ============================================================
    echo "📋 Creando tabla 'alcances'...\n";
    $sql_alcances = "
        CREATE TABLE IF NOT EXISTS `alcances` (
            `Id_alcance` INT AUTO_INCREMENT PRIMARY KEY,
            `Id_contrato` INT NOT NULL,
            `Descripcion` TEXT NOT NULL,
            `Estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`Id_contrato`) REFERENCES `contratos`(`Id_contrato`) ON DELETE CASCADE,
            INDEX idx_estado (Estado),
            INDEX idx_contrato (Id_contrato)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($sql_alcances) === TRUE) {
        echo "✅ Tabla 'alcances' creada\n";
    } else {
        die("❌ Error: " . $conn->error);
    }
    
    // ============================================================
    // 10. CREAR TABLA: actividades
    // ============================================================
    echo "📋 Creando tabla 'actividades'...\n";
    $sql_actividades = "
        CREATE TABLE IF NOT EXISTS `actividades` (
            `Id_actividad` INT AUTO_INCREMENT PRIMARY KEY,
            `Id_personal` INT NOT NULL,
            `Id_alcance` INT NOT NULL,
            `Descripcion` TEXT,
            `Estado_actividad` VARCHAR(50) DEFAULT 'Pendiente' COMMENT 'Pendiente, En Progreso, Completada, Cancelada',
            `Numero_orden` INT DEFAULT 1,
            `Fecha_inicio` DATE,
            `Fecha_fin` DATE,
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`Id_personal`) REFERENCES `personal`(`Id_personal`) ON DELETE CASCADE,
            FOREIGN KEY (`Id_alcance`) REFERENCES `alcances`(`Id_alcance`) ON DELETE CASCADE,
            INDEX idx_estado (Estado_actividad),
            INDEX idx_personal (Id_personal),
            INDEX idx_fecha (Fecha_fin)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    if ($conn->query($sql_actividades) === TRUE) {
        echo "✅ Tabla 'actividades' creada\n";
    } else {
        die("❌ Error: " . $conn->error);
    }
    
    echo "\n";
    
    // ============================================================
    // INSERTAR DATOS INICIALES
    // ============================================================
    
    // 1. Crear usuario administrador
    echo "📝 Insertando usuario administrador...\n";
    
    // Verificar si el usuario ya existe
    $check_admin = $conn->query("SELECT Id_usuario FROM usuario WHERE email = '$admin_email'");
    
    if ($check_admin->num_rows == 0) {
        $admin_pass_hashed = password_hash($admin_pass, PASSWORD_BCRYPT);
        $sql_admin = "INSERT INTO usuario (email, pass, estado_usuario, conectado) 
                      VALUES ('$admin_email', '$admin_pass_hashed', 1, 0)";
        
        if ($conn->query($sql_admin) === TRUE) {
            $admin_id = $conn->insert_id;
            echo "✅ Usuario administrador creado (ID: $admin_id)\n";
        } else {
            die("❌ Error al crear admin: " . $conn->error);
        }
    } else {
        $result = $check_admin->fetch_assoc();
        $admin_id = $result['Id_usuario'];
        echo "✅ Usuario administrador ya existe (ID: $admin_id)\n";
    }
    
    // 2. Crear roles
    echo "📝 Insertando roles...\n";
    
    $roles = [
        ['Administrador', 'Acceso completo a toda la plataforma'],
        ['Gerente', 'Acceso a reportes, contratos y actividades'],
        ['Supervisor', 'Acceso a actividades y personal asignado'],
        ['Personal', 'Acceso limitado a sus propias actividades'],
        ['Visualizador', 'Acceso de solo lectura a reportes']
    ];
    
    $role_ids = [];
    foreach ($roles as $role) {
        $check = $conn->query("SELECT Id_role FROM roles WHERE Nombre = '{$role[0]}'");
        if ($check->num_rows == 0) {
            $sql = "INSERT INTO roles (Nombre, Descripcion, Estado) 
                    VALUES ('{$role[0]}', '{$role[1]}', 1)";
            
            if ($conn->query($sql) === TRUE) {
                $role_ids[$role[0]] = $conn->insert_id;
            } else {
                echo "⚠️  Error inserting role {$role[0]}: " . $conn->error . "\n";
            }
        } else {
            $result = $check->fetch_assoc();
            $role_ids[$role[0]] = $result['Id_role'];
        }
    }
    echo "✅ Roles creados/verificados\n";
    
    // 3. Crear permisos
    echo "📝 Insertando permisos...\n";
    
    $permisos = [
        // Actividades
        ['actividades.ver', 'Ver listado de actividades', 'actividades', 'ver'],
        ['actividades.crear', 'Crear nuevas actividades', 'actividades', 'crear'],
        ['actividades.editar', 'Editar actividades existentes', 'actividades', 'editar'],
        ['actividades.eliminar', 'Eliminar actividades', 'actividades', 'eliminar'],
        ['actividades.reporte', 'Generar reportes de actividades', 'actividades', 'reporte'],
        
        // Personal
        ['personal.ver', 'Ver listado de personal', 'personal', 'ver'],
        ['personal.crear', 'Crear registros de personal', 'personal', 'crear'],
        ['personal.editar', 'Editar registros de personal', 'personal', 'editar'],
        ['personal.eliminar', 'Eliminar registros de personal', 'personal', 'eliminar'],
        
        // Contratos
        ['contratos.ver', 'Ver listado de contratos', 'contratos', 'ver'],
        ['contratos.crear', 'Crear nuevos contratos', 'contratos', 'crear'],
        ['contratos.editar', 'Editar contratos existentes', 'contratos', 'editar'],
        ['contratos.eliminar', 'Eliminar contratos', 'contratos', 'eliminar'],
        
        // Alcances
        ['alcances.ver', 'Ver listado de alcances', 'alcances', 'ver'],
        ['alcances.crear', 'Crear nuevos alcances', 'alcances', 'crear'],
        ['alcances.editar', 'Editar alcances existentes', 'alcances', 'editar'],
        ['alcances.eliminar', 'Eliminar alcances', 'alcances', 'eliminar'],
        
        // Divisiones
        ['divisions.ver', 'Ver listado de divisiones', 'divisions', 'ver'],
        ['divisions.crear', 'Crear nuevas divisiones', 'divisions', 'crear'],
        ['divisions.editar', 'Editar divisiones existentes', 'divisions', 'editar'],
        ['divisions.eliminar', 'Eliminar divisiones', 'divisions', 'eliminar'],
        
        // Usuarios
        ['usuarios.ver', 'Ver listado de usuarios', 'usuarios', 'ver'],
        ['usuarios.crear', 'Crear nuevos usuarios', 'usuarios', 'crear'],
        ['usuarios.editar', 'Editar usuarios existentes', 'usuarios', 'editar'],
        ['usuarios.eliminar', 'Eliminar usuarios', 'usuarios', 'eliminar'],
        
        // Roles
        ['roles.ver', 'Ver listado de roles', 'roles', 'ver'],
        ['roles.crear', 'Crear nuevos roles', 'roles', 'crear'],
        ['roles.editar', 'Editar roles existentes', 'roles', 'editar'],
        ['roles.eliminar', 'Eliminar roles', 'roles', 'eliminar'],
        ['roles.asignar_permisos', 'Asignar permisos a roles', 'roles', 'asignar_permisos'],
        
        // Permisos
        ['permisos.ver', 'Ver listado de permisos', 'permisos', 'ver'],
        ['permisos.crear', 'Crear nuevos permisos', 'permisos', 'crear'],
        ['permisos.editar', 'Editar permisos existentes', 'permisos', 'editar'],
        ['permisos.eliminar', 'Eliminar permisos', 'permisos', 'eliminar']
    ];
    
    $permisos_count = 0;
    foreach ($permisos as $permiso) {
        $check = $conn->query("SELECT Id_permiso FROM permisos WHERE Nombre = '{$permiso[0]}'");
        if ($check->num_rows == 0) {
            $sql = "INSERT INTO permisos (Nombre, Descripcion, Modulo, Accion, Estado) 
                    VALUES ('{$permiso[0]}', '{$permiso[1]}', '{$permiso[2]}', '{$permiso[3]}', 1)";
            
            if ($conn->query($sql) === TRUE) {
                $permisos_count++;
            } else {
                echo "⚠️  Error inserting permission {$permiso[0]}: " . $conn->error . "\n";
            }
        }
    }
    echo "✅ $permisos_count Permisos creados/verificados\n";
    
    // 4. Asignar todos los permisos al rol Administrador
    echo "📝 Asignando permisos al rol Administrador...\n";
    
    if (isset($role_ids['Administrador'])) {
        $admin_role_id = $role_ids['Administrador'];
        $permisos_result = $conn->query("SELECT Id_permiso FROM permisos WHERE Estado = 1");
        
        $assigned_count = 0;
        while ($perm = $permisos_result->fetch_assoc()) {
            $check = $conn->query("SELECT Id_role FROM role_permiso WHERE Id_role = $admin_role_id AND Id_permiso = {$perm['Id_permiso']}");
            if ($check->num_rows == 0) {
                $sql = "INSERT INTO role_permiso (Id_role, Id_permiso, Estado) 
                        VALUES ($admin_role_id, {$perm['Id_permiso']}, 1)";
                
                if ($conn->query($sql) === TRUE) {
                    $assigned_count++;
                }
            }
        }
        echo "✅ Permisos asignados al Administrador\n";
    }
    
    // 5. Asignar rol Administrador al usuario admin
    echo "📝 Asignando rol Administrador al usuario...\n";
    
    if (isset($role_ids['Administrador'])) {
        $check = $conn->query("SELECT Id_usuario FROM usuario_role WHERE Id_usuario = $admin_id AND Id_role = {$role_ids['Administrador']}");
        if ($check->num_rows == 0) {
            $sql = "INSERT INTO usuario_role (Id_usuario, Id_role, Estado) 
                    VALUES ($admin_id, {$role_ids['Administrador']}, 1)";
            
            if ($conn->query($sql) === TRUE) {
                echo "✅ Rol Administrador asignado al usuario\n";
            } else {
                echo "⚠️  Error assigning role: " . $conn->error . "\n";
            }
        } else {
            echo "✅ Rol ya estaba asignado\n";
        }
    }
    
    echo "\n";
    echo "════════════════════════════════════════════════════════════\n";
    echo "🎉 ¡INSTALACIÓN COMPLETADA EXITOSAMENTE!\n";
    echo "════════════════════════════════════════════════════════════\n\n";
    
    echo "📊 DATOS DE ACCESO:\n";
    echo "   Email:    $admin_email\n";
    echo "   Password: $admin_pass\n\n";
    
    echo "📂 BASE DE DATOS:\n";
    echo "   Nombre: $db_name\n";
    echo "   Host: $db_host\n";
    echo "   Usuario: $db_user\n\n";
    
    echo "⚠️  IMPORTANTE:\n";
    echo "   1. Cambia la contraseña después del primer inicio de sesión\n";
    echo "   2. Puedes eliminar este archivo (auto_setup.php) después de instalar\n";
    echo "   3. Verifica que config/config.php tenga los datos correctos:\n";
    echo "      - DB_HOST: $db_host\n";
    echo "      - DB_USER: $db_user\n";
    echo "      - DB_PASS: " . ($db_pass ? "(configurada)" : "(vacía)") . "\n";
    echo "      - DB_NAME: $db_name\n\n";
    
    echo "🔗 Accede a: http://localhost/gero_activities/\n";
    echo "════════════════════════════════════════════════════════════\n";
    
    // Actualizar config/config.php con el nombre de la BD
    $config_file = dirname(__FILE__) . '/config/config.php';
    if (file_exists($config_file)) {
        $config_content = file_get_contents($config_file);
        $config_content_updated = preg_replace(
            "/define\('DB_NAME',\s*'[^']*'\)/",
            "define('DB_NAME', '$db_name')",
            $config_content
        );
        if (file_put_contents($config_file, $config_content_updated)) {
            echo "✅ config/config.php actualizado con DB_NAME = '$db_name'\n";
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    outputError($e->getMessage());
    if ($isWeb) {
        echo "<div class='step' style='color: #dc3545; border-left-color: #dc3545;'>";
        echo "<p>Si el problema persiste, verifica:</p>";
        echo "<ul>";
        echo "<li>Que MySQL está ejecutándose</li>";
        echo "<li>Las credenciales en config/config.php</li>";
        echo "<li>Los permisos de la carpeta</li>";
        echo "</ul>";
        echo "</div>";
        echo "</div></body></html>";
    }
    exit(1);
}

// Mensaje final
if ($isWeb) {
    echo "<div class='final'>";
    echo "<h2 style='margin-top: 0;'>🎉 ¡Instalación completada!</h2>";
    echo "<p><strong>Email:</strong> $admin_email</p>";
    echo "<p><strong>Contraseña:</strong> $admin_pass</p>";
    echo "<p><strong>Base de Datos:</strong> <code>$db_name</code></p>";
    echo "<p style='margin-top: 15px; padding: 10px; background: #e7f3ff; border-left: 3px solid #2196F3; border-radius: 3px;'>";
    echo "✅ config/config.php ha sido actualizado automáticamente";
    echo "</p>";
    echo "<p style='margin-top: 15px; font-size: 0.9em;'>";
    echo "<strong>⚠️ Importante:</strong> Cambia la contraseña después de instalar. ";
    echo "Puedes eliminar este archivo (auto_setup.php) por razones de seguridad.";
    echo "</p>";
    echo "<p style='margin-top: 15px;'>";
    echo "<a href='./' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>";
    echo "➡️ Ir al Sistema";
    echo "</a>";
    echo "</p>";
    echo "</div>";
    echo "</div></body></html>";
}
?>
