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
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$configFile = __DIR__ . '/config/config.php';

function normalizeDbIdentifier($value) {
    return trim((string) preg_replace('/[^a-zA-Z0-9_]/', '', (string) $value));
}

function escapePhpSingleQuotedValue($value) {
    return str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $value);
}

function extractConfigDefine($content, $constantName, $default = '') {
    $pattern = "/define\('" . preg_quote($constantName, '/') . "',\s*'((?:\\\\'|[^'])*)'\)/";
    if (preg_match($pattern, $content, $matches)) {
        return stripcslashes($matches[1]);
    }

    return $default;
}

function readInstallerConfigDefaults($configFile) {
    $defaults = [
        'DB_HOST' => 'localhost',
        'DB_USER' => 'root',
        'DB_PASS' => '',
        'DB_NAME' => 'gestor_actividades'
    ];

    if (!file_exists($configFile)) {
        return $defaults;
    }

    $content = file_get_contents($configFile);
    if ($content === false) {
        return $defaults;
    }

    foreach ($defaults as $constant => $defaultValue) {
        $defaults[$constant] = extractConfigDefine($content, $constant, $defaultValue);
    }

    return $defaults;
}

function updateInstallerConfig($configFile, array $values, &$errorMessage = null) {
    if (!file_exists($configFile)) {
        $errorMessage = 'No se encontro config/config.php';
        return false;
    }

    $content = file_get_contents($configFile);
    if ($content === false) {
        $errorMessage = 'No se pudo leer config/config.php';
        return false;
    }

    $replacements = [
        'DB_HOST' => $values['DB_HOST'],
        'DB_USER' => $values['DB_USER'],
        'DB_PASS' => $values['DB_PASS'],
        'DB_NAME' => $values['DB_NAME']
    ];

    foreach ($replacements as $constant => $value) {
        $escapedValue = escapePhpSingleQuotedValue($value);
        $pattern = "/define\('" . preg_quote($constant, '/') . "',\s*'((?:\\\\'|[^'])*)'\)/";
        $updated = preg_replace($pattern, "define('{$constant}', '{$escapedValue}')", $content, 1, $count);
        if ($updated === null || $count !== 1) {
            $errorMessage = "No se pudo actualizar {$constant} en config/config.php";
            return false;
        }
        $content = $updated;
    }

    if (file_put_contents($configFile, $content) === false) {
        $errorMessage = 'No se pudo escribir config/config.php';
        return false;
    }

    return true;
}

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$configDefaults = readInstallerConfigDefaults($configFile);
$db_host = $configDefaults['DB_HOST'];
$db_user = $configDefaults['DB_USER'];
$db_pass = $configDefaults['DB_PASS'];
$db_name = $configDefaults['DB_NAME'];
$wizardErrors = [];
$configUpdated = false;
$configUpdateError = '';

if ($requestMethod === 'POST') {
    $db_host = trim((string) ($_POST['db_host'] ?? $db_host));
    $db_user = trim((string) ($_POST['db_user'] ?? $db_user));
    $db_pass = (string) ($_POST['db_pass'] ?? $db_pass);

    $rawDbName = trim((string) ($_POST['db_name'] ?? ''));
    $db_name = normalizeDbIdentifier($rawDbName);

    if ($db_host === '') {
        $wizardErrors[] = 'El host de la base de datos es obligatorio.';
    }

    if ($db_user === '') {
        $wizardErrors[] = 'El usuario de la base de datos es obligatorio.';
    }

    if ($rawDbName === '') {
        $wizardErrors[] = 'El nombre de la base de datos es obligatorio.';
    } elseif ($db_name !== $rawDbName) {
        $wizardErrors[] = 'El nombre de la base de datos solo puede contener letras, numeros y guion bajo (_).';
    }
}

// Si se ejecuta desde CLI, solicita host, usuario, password y nombre de BD
if (!$isWeb && $requestMethod !== 'POST') {
    echo "════════════════════════════════════════════════════════════\n";
    echo "AUTO SETUP - Gero Actividades\n";
    echo "════════════════════════════════════════════════════════════\n\n";

    echo "Host MySQL [{$db_host}]: ";
    $input = trim(fgets(STDIN));
    if ($input !== '') {
        $db_host = $input;
    }

    echo "Usuario MySQL [{$db_user}]: ";
    $input = trim(fgets(STDIN));
    if ($input !== '') {
        $db_user = $input;
    }

    echo "Password MySQL [enter para conservar actual]: ";
    $input = rtrim((string) fgets(STDIN), "\r\n");
    if ($input !== '') {
        $db_pass = $input;
    }

    echo "Base de datos [{$db_name}]: ";
    $input = trim(fgets(STDIN));
    if ($input !== '') {
        $db_name = normalizeDbIdentifier($input);
    }

    if ($db_name === '') {
        $db_name = 'gestor_actividades';
    }

    echo "\n";
}

// Si es web y faltan datos o hay errores de validacion, muestra el asistente
if ($isWeb && ($requestMethod !== 'POST' || !empty($wizardErrors) || empty($db_name) || empty($db_host) || empty($db_user))) {
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
            .error-box {
                background: #fff1f2;
                border-left: 4px solid #dc3545;
                padding: 12px;
                margin-bottom: 20px;
                border-radius: 3px;
                color: #b42318;
            }
            .error-box ul {
                margin: 8px 0 0 18px;
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
                <p>Gero Actividades - Asistente de instalación</p>
            </div>
            
            <form method="POST" action="">
                <div class="info">
                    ℹ️ Ingresa las credenciales reales de MySQL. El sistema validará la conexión antes de actualizar la configuración y ejecutar la instalación.
                </div>

                <?php if (!empty($wizardErrors)): ?>
                    <div class="error-box">
                        <strong>No se puede continuar con los datos actuales:</strong>
                        <ul>
                            <?php foreach ($wizardErrors as $wizardError): ?>
                                <li><?php echo h($wizardError); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="db_host">Host de MySQL:</label>
                    <input 
                        type="text"
                        id="db_host"
                        name="db_host"
                        placeholder="localhost"
                        value="<?php echo h($db_host); ?>"
                        maxlength="255"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="db_user">Usuario de MySQL:</label>
                    <input 
                        type="text"
                        id="db_user"
                        name="db_user"
                        placeholder="ej: cpanel_user"
                        value="<?php echo h($db_user); ?>"
                        maxlength="255"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="db_pass">Contraseña de MySQL:</label>
                    <input 
                        type="password"
                        id="db_pass"
                        name="db_pass"
                        placeholder="Ingresa la contraseña de MySQL"
                        maxlength="255"
                    >
                    <small style="color: #666; margin-top: 5px; display: block;">
                        Por seguridad este campo no se autocompleta. Ingresa la contraseña real para validar la conexión.
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="db_name">Nombre de la Base de Datos:</label>
                    <input 
                        type="text" 
                        id="db_name" 
                        name="db_name" 
                        placeholder="ej: gestor_actividades"
                        value="<?php echo h($db_name); ?>"
                        pattern="[a-zA-Z0-9_]+"
                        maxlength="64"
                        required
                    >
                    <small style="color: #666; margin-top: 5px; display: block;">
                        Compatible con entornos locales y cPanel. Solo caracteres alfanuméricos y guiones bajos (_).
                    </small>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-submit">▶️ Validar e instalar</button>
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

function normalizeSqlCompatibilityQuery($query) {
    $normalized = $query;

    // Compatibilidad con MySQL/MariaDB antiguos (cPanel):
    // IF NOT EXISTS/IF EXISTS en ALTER TABLE ADD/DROP no siempre esta soportado.
    $patterns = [
        '/\bADD\s+COLUMN\s+IF\s+NOT\s+EXISTS\b/i' => 'ADD COLUMN',
        '/\bADD\s+INDEX\s+IF\s+NOT\s+EXISTS\b/i' => 'ADD INDEX',
        '/\bADD\s+UNIQUE\s+KEY\s+IF\s+NOT\s+EXISTS\b/i' => 'ADD UNIQUE KEY',
        '/\bDROP\s+INDEX\s+IF\s+EXISTS\b/i' => 'DROP INDEX'
    ];

    foreach ($patterns as $pattern => $replacement) {
        $candidate = preg_replace($pattern, $replacement, $normalized);
        if ($candidate !== null) {
            $normalized = $candidate;
        }
    }

    return [
        'query' => $normalized,
        'changed' => $normalized !== $query
    ];
}

function shouldIgnoreCompatibilitySqlErrorByCode($errorCode, $query) {
    $isAlterTable = preg_match('/^\s*ALTER\s+TABLE\b/i', $query) === 1;
    if (!$isAlterTable) {
        return false;
    }

    $errno = (int)$errorCode;

    // 1060: Duplicate column name (ALTER TABLE ... ADD COLUMN ...)
    if ($errno === 1060 && preg_match('/\bADD\s+COLUMN\b/i', $query) === 1) {
        return true;
    }

    // 1061: Duplicate key name (ALTER TABLE ... ADD INDEX/KEY/UNIQUE ...)
    if ($errno === 1061 && preg_match('/\bADD\s+(INDEX|KEY|UNIQUE\s+KEY)\b/i', $query) === 1) {
        return true;
    }

    // 1091: Can't DROP ... doesn't exist (ALTER TABLE ... DROP ...)
    if ($errno === 1091 && preg_match('/\bDROP\s+(INDEX|KEY|COLUMN|FOREIGN\s+KEY)\b/i', $query) === 1) {
        return true;
    }

    // 1826: Duplicate foreign key constraint name
    if ($errno === 1826 && preg_match('/\bADD\s+CONSTRAINT\b/i', $query) === 1) {
        return true;
    }

    return false;
}

function shouldIgnoreCompatibilitySqlError(mysqli $conn, $query) {
    return shouldIgnoreCompatibilitySqlErrorByCode((int)$conn->errno, $query);
}

function isTableroColumnOrderUniqueIndexQuery($query) {
    return preg_match('/^\s*ALTER\s+TABLE\s+`?tablero_columnas`?.*ADD\s+UNIQUE\s+KEY\s+`?uk_tablero_columna_orden`?/is', $query) === 1;
}

function repairTableroColumnOrderForUniqueIndex(mysqli $conn) {
    // Reparacion defensiva: asegura unicidad por tablero de forma deterministica.
    // Id_columna es PK y por lo tanto unico dentro de cada tablero.
    $repairSql = "UPDATE `tablero_columnas`\n"
        . "SET `Orden_columna` = `Id_columna`";

    return $conn->query($repairSql) !== false;
}

// Ejecuta un archivo SQL en la conexion actual y reporta resultados.
function runSqlFile(mysqli $conn, $filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("No se encontro el archivo SQL: " . basename($filePath));
    }

    $sql = file_get_contents($filePath);
    if ($sql === false) {
        throw new Exception("No se pudo leer el archivo SQL: " . basename($filePath));
    }

    // Limpia comentarios de linea para simplificar el parseo.
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    $executed = 0;
    foreach ($queries as $query) {
        if ($query === '') {
            continue;
        }

        $normalizedQueryData = normalizeSqlCompatibilityQuery($query);
        $queryToExecute = $normalizedQueryData['query'];
        $wasNormalized = $normalizedQueryData['changed'];

        try {
            $result = $conn->query($queryToExecute);
            if ($result === false) {
                if ((int)$conn->errno === 1062 && isTableroColumnOrderUniqueIndexQuery($queryToExecute)) {
                    if (repairTableroColumnOrderForUniqueIndex($conn)) {
                        $retryResult = $conn->query($queryToExecute);
                        if ($retryResult !== false) {
                            $executed++;
                            continue;
                        }
                    }
                }

                if (shouldIgnoreCompatibilitySqlError($conn, $queryToExecute)) {
                    // El esquema ya estaba en el estado deseado; continuar instalacion.
                    $executed++;
                    continue;
                }

                throw new Exception("Error en " . basename($filePath) . ": " . $conn->error . " | SQL: " . substr($queryToExecute, 0, 180));
            }
        } catch (Throwable $e) {
            $errorCode = (int)$e->getCode();
            if (shouldIgnoreCompatibilitySqlErrorByCode($errorCode, $queryToExecute)) {
                $executed++;
                continue;
            }

            throw new Exception("Error en " . basename($filePath) . ": " . $e->getMessage() . " | SQL: " . substr($queryToExecute, 0, 180));
        }
        $executed++;
    }

    return $executed;
}

function columnExists(mysqli $conn, $table, $column) {
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    $sql = "SELECT COUNT(*) AS total
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = '$table'
              AND COLUMN_NAME = '$column'";
    $res = $conn->query($sql);
    if (!$res) {
        return false;
    }
    $row = $res->fetch_assoc();
    return !empty($row) && (int)$row['total'] > 0;
}

function indexExists(mysqli $conn, $table, $indexName) {
    $table = $conn->real_escape_string($table);
    $indexName = $conn->real_escape_string($indexName);
    $sql = "SELECT COUNT(*) AS total
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = '$table'
              AND INDEX_NAME = '$indexName'";
    $res = $conn->query($sql);
    if (!$res) {
        return false;
    }
    $row = $res->fetch_assoc();
    return !empty($row) && (int)$row['total'] > 0;
}

function fkExists(mysqli $conn, $table, $fkName) {
    $table = $conn->real_escape_string($table);
    $fkName = $conn->real_escape_string($fkName);
    $sql = "SELECT COUNT(*) AS total
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = '$table'
              AND CONSTRAINT_NAME = '$fkName'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'";
    $res = $conn->query($sql);
    if (!$res) {
        return false;
    }
    $row = $res->fetch_assoc();
    return !empty($row) && (int)$row['total'] > 0;
}

function tableExists(mysqli $conn, $table) {
    $table = $conn->real_escape_string($table);
    $sql = "SELECT COUNT(*) AS total
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = '$table'";
    $res = $conn->query($sql);
    if (!$res) {
        return false;
    }
    $row = $res->fetch_assoc();
    return !empty($row) && (int)$row['total'] > 0;
}

function generateOfflineSqlBundle($dbName, $adminEmail, $adminPass) {
    $safeDb = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$dbName);
    if ($safeDb === '') {
        $safeDb = 'gestor_actividades';
    }

    $fileName = 'offline_setup_' . $safeDb . '_' . date('Ymd_His') . '.sql';
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . $fileName;
    $adminHash = str_replace("'", "''", password_hash($adminPass, PASSWORD_BCRYPT));
    $adminEmailSql = str_replace("'", "''", $adminEmail);

    $sql = "-- =====================================================\n";
    $sql .= "-- OFFLINE SETUP BUNDLE - Gero Actividades\n";
    $sql .= "-- Generado automaticamente cuando no hay conexion a MySQL\n";
    $sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- =====================================================\n\n";
    $sql .= "CREATE DATABASE IF NOT EXISTS `{$safeDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
    $sql .= "USE `{$safeDb}`;\n\n";

    $sql .= "CREATE TABLE IF NOT EXISTS `usuario` (\n";
    $sql .= "  `Id_usuario` INT AUTO_INCREMENT PRIMARY KEY,\n";
    $sql .= "  `email` VARCHAR(100) NOT NULL UNIQUE,\n";
    $sql .= "  `pass` VARCHAR(255) NOT NULL,\n";
    $sql .= "  `reset_token` VARCHAR(64) DEFAULT NULL,\n";
    $sql .= "  `token_expira` DATETIME DEFAULT NULL,\n";
    $sql .= "  `password_temp` TINYINT(1) NOT NULL DEFAULT 0,\n";
    $sql .= "  `estado_usuario` TINYINT(1) DEFAULT 1,\n";
    $sql .= "  `conectado` TINYINT(1) DEFAULT 0,\n";
    $sql .= "  `fecha_ultimo_login` TIMESTAMP NULL,\n";
    $sql .= "  `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
    $sql .= "  `Fecha_actualizacion` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP\n";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

    $sql .= "CREATE TABLE IF NOT EXISTS `roles` (\n";
    $sql .= "  `Id_role` INT AUTO_INCREMENT PRIMARY KEY,\n";
    $sql .= "  `Nombre` VARCHAR(100) NOT NULL UNIQUE,\n";
    $sql .= "  `Descripcion` TEXT,\n";
    $sql .= "  `Estado` TINYINT(1) DEFAULT 1,\n";
    $sql .= "  `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

    $sql .= "CREATE TABLE IF NOT EXISTS `permisos` (\n";
    $sql .= "  `Id_permiso` INT AUTO_INCREMENT PRIMARY KEY,\n";
    $sql .= "  `Nombre` VARCHAR(100) NOT NULL UNIQUE,\n";
    $sql .= "  `Descripcion` TEXT,\n";
    $sql .= "  `Modulo` VARCHAR(50) NOT NULL,\n";
    $sql .= "  `Accion` VARCHAR(50) NOT NULL,\n";
    $sql .= "  `Estado` TINYINT(1) DEFAULT 1,\n";
    $sql .= "  `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

    $sql .= "CREATE TABLE IF NOT EXISTS `role_permiso` (\n";
    $sql .= "  `Id_role` INT NOT NULL,\n";
    $sql .= "  `Id_permiso` INT NOT NULL,\n";
    $sql .= "  `Estado` TINYINT(1) DEFAULT 1,\n";
    $sql .= "  `Fecha_asignacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
    $sql .= "  PRIMARY KEY (`Id_role`, `Id_permiso`)\n";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

    $sql .= "CREATE TABLE IF NOT EXISTS `usuario_role` (\n";
    $sql .= "  `Id_usuario` INT NOT NULL,\n";
    $sql .= "  `Id_role` INT NOT NULL,\n";
    $sql .= "  `Estado` TINYINT(1) DEFAULT 1,\n";
    $sql .= "  `Fecha_asignacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
    $sql .= "  PRIMARY KEY (`Id_usuario`, `Id_role`)\n";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

    $sql .= "INSERT INTO `usuario` (`email`, `pass`, `estado_usuario`, `conectado`)\n";
    $sql .= "VALUES ('{$adminEmailSql}', '{$adminHash}', 1, 0)\n";
    $sql .= "ON DUPLICATE KEY UPDATE `estado_usuario` = 1;\n\n";

    $sql .= "INSERT IGNORE INTO `roles` (`Id_role`, `Nombre`, `Descripcion`, `Estado`) VALUES\n";
    $sql .= "(1, 'Administrador', 'Acceso completo a toda la plataforma', 1),\n";
    $sql .= "(2, 'Gerente', 'Acceso a reportes, contratos y actividades', 1),\n";
    $sql .= "(3, 'Jefe', 'Acceso a personal y actividades de su división', 1),\n";
    $sql .= "(4, 'Supervisor', 'Acceso a actividades y personal asignado', 1),\n";
    $sql .= "(5, 'Personal', 'Acceso limitado a sus propias actividades', 1),\n";
    $sql .= "(6, 'Visualizador', 'Acceso de solo lectura a reportes', 1);\n\n";

    $sql .= "INSERT IGNORE INTO `usuario_role` (`Id_usuario`, `Id_role`, `Estado`) VALUES (1, 1, 1);\n\n";

    $sql .= "-- Asignar todos los permisos existentes al rol Administrador\n";
    $sql .= "INSERT IGNORE INTO `role_permiso` (`Id_role`, `Id_permiso`, `Estado`)\n";
    $sql .= "SELECT 1, `Id_permiso`, 1 FROM `permisos` WHERE `Estado` = 1;\n\n";

    $sql .= "-- =====================================================\n";
    $sql .= "-- CONTENIDO DE SCRIPTS COMPLEMENTARIOS\n";
    $sql .= "-- =====================================================\n\n";

    $scripts = [
        __DIR__ . '/database_scripts/09_tablero_actividades.sql',
        __DIR__ . '/database_scripts/10_restaurar_admin.sql',
        __DIR__ . '/database_scripts/11_recuperacion_password.sql',
        __DIR__ . '/database_scripts/12_plantillas_tablero.sql',
        __DIR__ . '/database_scripts/13_plantillas_asociaciones_permisos.sql'
    ];

    foreach ($scripts as $scriptPath) {
        if (file_exists($scriptPath)) {
            $scriptName = basename($scriptPath);
            $scriptSql = file_get_contents($scriptPath);
            $sql .= "\n-- ===== BEGIN {$scriptName} =====\n";
            $sql .= $scriptSql . "\n";
            $sql .= "-- ===== END {$scriptName} =====\n\n";
        }
    }

    if (file_put_contents($filePath, $sql) === false) {
        return false;
    }

    return $filePath;
}

// Mostrar inicio
if ($isWeb) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Auto Setup - Instalación</title>";
    echo "<style>body{font-family: Arial; background: #f5f5f5; padding: 20px;} ";
    echo ".content{max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; white-space: pre-line;} ";
    echo ".success{color: #28a745;} .error{color: #dc3545;} .info{color: #17a2b8;} ";
    echo ".step{margin: 15px 0; padding: 10px; border-left: 4px solid #667eea;} ";
    echo ".check{color: #28a745;} h1{color: #667eea;} .final{padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; color: #155724; margin-top: 20px;}</style></head><body><div class='content'>";
    echo "<h1>🚀 Auto Setup - Instalación en progreso...</h1>";
    echo "<p class='info'>Conectando a <strong>" . h($db_host) . "</strong> con el usuario <strong>" . h($db_user) . "</strong>. Base de datos objetivo: <strong>" . h($db_name) . "</strong></p>";
    echo "<hr style='margin: 20px 0;'>";
} else {
    echo "════════════════════════════════════════════════════════════\n";
    echo "🚀 AUTO SETUP - Gero Actividades\n";
    echo "════════════════════════════════════════════════════════════\n";
    echo "Base de datos: $db_name\n";
    echo "Host: $db_host\n";
    echo "Usuario: $db_user\n";
    echo "════════════════════════════════════════════════════════════\n\n";
}

// Conexión sin seleccionar BD (para crearla)
    
try {
    $conn = new mysqli($db_host, $db_user, $db_pass);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    output("✅ Conectado a MySQL");

    // Detectar si la BD ya existia antes de crear/verificar.
    $dbEscaped = $conn->real_escape_string($db_name);
    $existsRes = $conn->query("SELECT COUNT(*) AS total FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '$dbEscaped'");
    $dbExisted = false;
    if ($existsRes) {
        $existsRow = $existsRes->fetch_assoc();
        $dbExisted = !empty($existsRow) && (int)$existsRow['total'] > 0;
    }
    
    // Crear base de datos. En cPanel puede fallar la creacion aunque la BD ya exista y sea utilizable.
    $sql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        output("✅ Base de datos '$db_name' creada/verificada");
    } elseif ($conn->select_db($db_name)) {
        output("⚠️  No se pudo crear la base de datos por permisos, pero la base '$db_name' ya existe y sera utilizada");
    } else {
        throw new Exception("No se pudo crear ni seleccionar la BD '$db_name': " . $conn->error);
    }
    
    // Seleccionar la BD
    if (!$conn->select_db($db_name)) {
        throw new Exception("No se pudo seleccionar la BD '$db_name': " . $conn->error);
    }
    output("✅ BD '$db_name' seleccionada");
    if ($dbExisted) {
        output("ℹ️  Base existente detectada: se ejecutara sincronizacion de esquema (tablas/columnas faltantes)");
    } else {
        output("ℹ️  Base nueva detectada: se creara esquema completo inicial");
    }
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
            `reset_token` VARCHAR(64) DEFAULT NULL,
            `token_expira` DATETIME DEFAULT NULL,
            `password_temp` TINYINT(1) NOT NULL DEFAULT 0,
            `estado_usuario` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `conectado` TINYINT(1) DEFAULT 0,
            `fecha_ultimo_login` TIMESTAMP NULL,
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `Fecha_actualizacion` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
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
            `Tipo_servicio` TINYINT(1) DEFAULT 1 COMMENT '1=Profesionales, 0=Técnicos',
            `Id_division` INT NULL,
            `Id_usuario` INT NOT NULL,
            `Id_contrato` INT NULL,
            `Estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_usuario (Id_usuario),
            FOREIGN KEY (`Id_usuario`) REFERENCES `usuario`(`Id_usuario`) ON DELETE CASCADE,
            FOREIGN KEY (`Id_division`) REFERENCES `division`(`Id_Division`) ON DELETE SET NULL,
            INDEX idx_estado (Estado),
            INDEX idx_tipo_servicio (Tipo_servicio)
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
            `Contrato_activo` TINYINT(1) DEFAULT 1 COMMENT '1=Contrato Activo, 0=Contrato Vencido',
            `Id_division` INT NULL,
            `Estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`Id_division`) REFERENCES `division`(`Id_Division`) ON DELETE SET NULL,
            INDEX idx_estado (Estado),
            INDEX idx_expediente (Expediente),
            INDEX idx_contrato_activo (Contrato_activo),
            INDEX idx_id_division (Id_division)
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
            `es_recurrente` TINYINT(1) DEFAULT 0 COMMENT '1=Recurrente (diario), 0=No recurrente',
            `Estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`Id_contrato`) REFERENCES `contratos`(`Id_contrato`) ON DELETE CASCADE,
            INDEX idx_estado (Estado),
            INDEX idx_contrato (Id_contrato),
            INDEX idx_recurrente (es_recurrente)
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
            `Fecha_ingreso` DATE NOT NULL COMMENT 'Fecha en que se realizó la actividad',
            `Descripcion_realizada` TEXT COMMENT 'Descripción del trabajo realizado',
            `cantidad_realizada` INT DEFAULT NULL COMMENT 'Cantidad de repeticiones si el alcance es recurrente',
            `Estado_actividad` VARCHAR(50) DEFAULT 'Pendiente' COMMENT 'Pendiente, En Progreso, Completada, Cancelada',
            `Estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
            `Numero_orden` INT DEFAULT 1,
            `Fecha_inicio` DATE,
            `Fecha_fin` DATE,
            `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`Id_personal`) REFERENCES `personal`(`Id_personal`) ON DELETE CASCADE,
            FOREIGN KEY (`Id_alcance`) REFERENCES `alcances`(`Id_alcance`) ON DELETE CASCADE,
            INDEX idx_estado (Estado_actividad),
            INDEX idx_personal (Id_personal),
            INDEX idx_fecha_ingreso (Fecha_ingreso),
            INDEX idx_cantidad (cantidad_realizada)
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
        ['Jefe', 'Acceso a personal y actividades de su división'],
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
        ['roles.permisos', 'Gestionar permisos de roles', 'roles', 'permisos'],
        
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

    // ============================================================
    // 5.1 MIGRACION DE COMPATIBILIDAD PARA BD EXISTENTES
    // ============================================================
    echo "📝 Verificando columnas de compatibilidad para bases existentes...\n";

    $requiredSchema = [
        'actividades' => ['Fecha_ingreso', 'Descripcion_realizada', 'Estado', 'cantidad_realizada'],
        'alcances' => ['es_recurrente'],
        'personal' => ['Tipo_servicio'],
        'contratos' => ['Contrato_activo', 'Id_division']
    ];

    foreach ($requiredSchema as $tableName => $columns) {
        if (!tableExists($conn, $tableName)) {
            echo "⚠️  Tabla faltante detectada: $tableName (se espera que se cree en flujo base o script 09)\n";
            continue;
        }

        $missing = [];
        foreach ($columns as $colName) {
            if (!columnExists($conn, $tableName, $colName)) {
                $missing[] = $colName;
            }
        }

        if (!empty($missing)) {
            echo "ℹ️  $tableName tiene columnas faltantes: " . implode(', ', $missing) . "\n";
        }
    }

    if (!columnExists($conn, 'actividades', 'Fecha_ingreso')) {
        $conn->query("ALTER TABLE `actividades` ADD COLUMN `Fecha_ingreso` DATE NULL COMMENT 'Fecha en que se realizó la actividad'");
        echo "✅ Se agrego actividades.Fecha_ingreso\n";
    }

    if (!indexExists($conn, 'actividades', 'idx_fecha_ingreso')) {
        $conn->query("ALTER TABLE `actividades` ADD INDEX `idx_fecha_ingreso` (`Fecha_ingreso`)");
        echo "✅ Se agrego indice idx_fecha_ingreso en actividades\n";
    }

    if (!columnExists($conn, 'actividades', 'Descripcion_realizada')) {
        $posicionDescripcion = columnExists($conn, 'actividades', 'Fecha_ingreso') ? ' AFTER `Fecha_ingreso`' : '';
        $conn->query("ALTER TABLE `actividades` ADD COLUMN `Descripcion_realizada` TEXT NULL" . $posicionDescripcion);

        if (columnExists($conn, 'actividades', 'Descripcion')) {
            $conn->query("UPDATE `actividades` SET `Descripcion_realizada` = `Descripcion` WHERE `Descripcion_realizada` IS NULL");
            echo "✅ Se agrego actividades.Descripcion_realizada y se migro desde Descripcion\n";
        } else {
            echo "✅ Se agrego actividades.Descripcion_realizada\n";
        }
    }

    if (!columnExists($conn, 'actividades', 'Estado')) {
        $posicionEstado = columnExists($conn, 'actividades', 'Estado_actividad') ? ' AFTER `Estado_actividad`' : '';
        $conn->query("ALTER TABLE `actividades` ADD COLUMN `Estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo'" . $posicionEstado);

        if (columnExists($conn, 'actividades', 'Estado_actividad')) {
            // Si existe estado descriptivo previo, marcar inactiva solo la actividad cancelada.
            $conn->query("UPDATE `actividades` SET `Estado` = CASE WHEN `Estado_actividad` = 'Cancelada' THEN 0 ELSE 1 END");
        }

        echo "✅ Se agrego actividades.Estado\n";
    }

    if (!indexExists($conn, 'actividades', 'idx_estado')) {
        $conn->query("ALTER TABLE `actividades` ADD INDEX `idx_estado` (`Estado`)");
        echo "✅ Se agrego indice idx_estado en actividades\n";
    }

    if (!columnExists($conn, 'actividades', 'cantidad_realizada')) {
        $conn->query("ALTER TABLE `actividades` ADD COLUMN `cantidad_realizada` INT DEFAULT NULL COMMENT 'Cantidad de repeticiones si el alcance es recurrente' AFTER `Descripcion_realizada`");
        echo "✅ Se agrego actividades.cantidad_realizada\n";
    }

    if (!indexExists($conn, 'actividades', 'idx_cantidad')) {
        $conn->query("ALTER TABLE `actividades` ADD INDEX `idx_cantidad` (`cantidad_realizada`)");
        echo "✅ Se agrego indice idx_cantidad en actividades\n";
    }

    if (!columnExists($conn, 'alcances', 'es_recurrente')) {
        $conn->query("ALTER TABLE `alcances` ADD COLUMN `es_recurrente` TINYINT(1) DEFAULT 0 COMMENT '1=Recurrente (diario), 0=No recurrente' AFTER `Descripcion`");
        echo "✅ Se agrego alcances.es_recurrente\n";
    }

    if (!indexExists($conn, 'alcances', 'idx_recurrente')) {
        $conn->query("ALTER TABLE `alcances` ADD INDEX `idx_recurrente` (`es_recurrente`)");
        echo "✅ Se agrego indice idx_recurrente en alcances\n";
    }

    if (!columnExists($conn, 'personal', 'Tipo_servicio')) {
        $conn->query("ALTER TABLE `personal` ADD COLUMN `Tipo_servicio` TINYINT(1) DEFAULT 1 COMMENT '1=Profesionales, 0=Técnicos' AFTER `Puesto`");
        echo "✅ Se agrego personal.Tipo_servicio\n";
    }

    if (!indexExists($conn, 'personal', 'idx_tipo_servicio')) {
        $conn->query("ALTER TABLE `personal` ADD INDEX `idx_tipo_servicio` (`Tipo_servicio`)");
        echo "✅ Se agrego indice idx_tipo_servicio en personal\n";
    }

    if (!columnExists($conn, 'contratos', 'Contrato_activo')) {
        $conn->query("ALTER TABLE `contratos` ADD COLUMN `Contrato_activo` TINYINT(1) DEFAULT 1 COMMENT '1=Contrato Activo, 0=Contrato Vencido' AFTER `Fin_contrato`");
        echo "✅ Se agrego contratos.Contrato_activo\n";
    }

    if (!indexExists($conn, 'contratos', 'idx_contrato_activo')) {
        $conn->query("ALTER TABLE `contratos` ADD INDEX `idx_contrato_activo` (`Contrato_activo`)");
        echo "✅ Se agrego indice idx_contrato_activo en contratos\n";
    }

    if (!columnExists($conn, 'contratos', 'Id_division')) {
        $conn->query("ALTER TABLE `contratos` ADD COLUMN `Id_division` INT NULL AFTER `Contrato_activo`");
        echo "✅ Se agrego contratos.Id_division\n";
    }

    if (!indexExists($conn, 'contratos', 'idx_id_division')) {
        $conn->query("ALTER TABLE `contratos` ADD INDEX `idx_id_division` (`Id_division`)");
        echo "✅ Se agrego indice idx_id_division en contratos\n";
    }

    if (!fkExists($conn, 'contratos', 'fk_contratos_division')) {
        // Intentar crear FK sin bloquear la instalacion si hay datos huerfanos.
        if ($conn->query("ALTER TABLE `contratos` ADD CONSTRAINT `fk_contratos_division` FOREIGN KEY (`Id_division`) REFERENCES `division`(`Id_Division`) ON DELETE SET NULL") === TRUE) {
            echo "✅ Se agrego FK fk_contratos_division\n";
        } else {
            echo "⚠️  No se pudo crear FK fk_contratos_division (posibles datos inconsistentes): " . $conn->error . "\n";
        }
    }

    // ============================================================
    // 6. EJECUTAR SCRIPT DEL MODULO TABLERO (09)
    // ============================================================
    echo "📝 Ejecutando script del módulo Tablero (09_tablero_actividades.sql)...\n";
    $tableroScriptPath = __DIR__ . '/database_scripts/09_tablero_actividades.sql';
    $queries09 = runSqlFile($conn, $tableroScriptPath);
    echo "✅ Script 09 ejecutado correctamente ($queries09 sentencias)\n";

    // ============================================================
    // 7. EJECUTAR SCRIPT DE RESTAURAR ADMIN (10)
    // ============================================================
    echo "📝 Ejecutando script de restaurar admin (10_restaurar_admin.sql)...\n";
    $restoreAdminScriptPath = __DIR__ . '/database_scripts/10_restaurar_admin.sql';
    $queries10 = runSqlFile($conn, $restoreAdminScriptPath);
    echo "✅ Script 10 ejecutado correctamente ($queries10 sentencias)\n";

    // ============================================================
    // 8. EJECUTAR SCRIPT DE RECUPERACION DE CONTRASENA (11)
    // ============================================================
    echo "📝 Ejecutando script de recuperación de contraseña (11_recuperacion_password.sql)...\n";
    $passwordResetScriptPath = __DIR__ . '/database_scripts/11_recuperacion_password.sql';
    $queries11 = runSqlFile($conn, $passwordResetScriptPath);
    echo "✅ Script 11 ejecutado correctamente ($queries11 sentencias)\n";

    // ============================================================
    // 9. EJECUTAR SCRIPT DE PLANTILLAS DE TABLERO (12)
    // ============================================================
    echo "📝 Ejecutando script de plantillas de tablero (12_plantillas_tablero.sql)...\n";
    $plantillasTableroScriptPath = __DIR__ . '/database_scripts/12_plantillas_tablero.sql';
    $queries12 = runSqlFile($conn, $plantillasTableroScriptPath);
    echo "✅ Script 12 ejecutado correctamente ($queries12 sentencias)\n";

    // ============================================================
    // 10. EJECUTAR SCRIPT DE ASOCIACIONES PLANTILLAS Y PERMISOS (13)
    // ============================================================
    echo "📝 Ejecutando script de asociaciones y permisos de plantillas (13_plantillas_asociaciones_permisos.sql)...\n";
    $plantillasPermisosScriptPath = __DIR__ . '/database_scripts/13_plantillas_asociaciones_permisos.sql';
    $queries13 = runSqlFile($conn, $plantillasPermisosScriptPath);
    echo "✅ Script 13 ejecutado correctamente ($queries13 sentencias)\n";

    // Asegurar que el admin real del setup tenga permisos de tablero (si no es Id 1).
    if ((int)$admin_id !== 1) {
        echo "📝 Sincronizando permisos de tablero para el admin real (ID: $admin_id)...\n";

        $syncSql = "
            INSERT INTO tablero_usuario_permiso (
                Id_tablero, Id_usuario,
                Permiso_ver, Permiso_crear, Permiso_editar, Permiso_eliminar,
                Permiso_tablero_ver, Permiso_tablero_crear, Permiso_tablero_editar, Permiso_tablero_eliminar, Permiso_tablero_asignar,
                Permiso_columna_crear, Permiso_columna_editar, Permiso_columna_eliminar, Permiso_columna_ordenar,
                Permiso_tarjeta_ver, Permiso_tarjeta_crear, Permiso_tarjeta_editar, Permiso_tarjeta_mover, Permiso_tarjeta_eliminar, Permiso_tarjeta_asignar,
                Permiso_lista_crear, Permiso_lista_editar, Permiso_lista_eliminar,
                Permiso_tarea_crear, Permiso_tarea_editar, Permiso_tarea_eliminar, Permiso_tarea_tiempo_editar,
                Estado
            )
            SELECT
                t.Id_tablero, $admin_id,
                1,1,1,1,
                1,1,1,1,1,
                1,1,1,1,
                1,1,1,1,1,1,
                1,1,1,
                1,1,1,1,
                1
            FROM tablero t
            WHERE t.Estado = 1
            ON DUPLICATE KEY UPDATE
                Permiso_ver=1, Permiso_crear=1, Permiso_editar=1, Permiso_eliminar=1,
                Permiso_tablero_ver=1, Permiso_tablero_crear=1, Permiso_tablero_editar=1, Permiso_tablero_eliminar=1, Permiso_tablero_asignar=1,
                Permiso_columna_crear=1, Permiso_columna_editar=1, Permiso_columna_eliminar=1, Permiso_columna_ordenar=1,
                Permiso_tarjeta_ver=1, Permiso_tarjeta_crear=1, Permiso_tarjeta_editar=1, Permiso_tarjeta_mover=1, Permiso_tarjeta_eliminar=1, Permiso_tarjeta_asignar=1,
                Permiso_lista_crear=1, Permiso_lista_editar=1, Permiso_lista_eliminar=1,
                Permiso_tarea_crear=1, Permiso_tarea_editar=1, Permiso_tarea_eliminar=1, Permiso_tarea_tiempo_editar=1,
                Estado=1
        ";

        if ($conn->query($syncSql) === TRUE) {
            echo "✅ Permisos de tablero sincronizados para admin ID $admin_id\n";
        } else {
            echo "⚠️  No se pudo sincronizar permisos de tablero para admin ID $admin_id: " . $conn->error . "\n";
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

    $configUpdated = updateInstallerConfig(
        $configFile,
        [
            'DB_HOST' => $db_host,
            'DB_USER' => $db_user,
            'DB_PASS' => $db_pass,
            'DB_NAME' => $db_name
        ],
        $configUpdateError
    );

    if ($configUpdated) {
        outputSuccess("config/config.php actualizado con las credenciales validadas de MySQL");
    } else {
        outputError("La instalación terminó, pero no se pudo actualizar config/config.php: " . $configUpdateError);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    outputError($errorMessage);

    $isConnectionIssue =
        stripos($errorMessage, 'Error de conexión') !== false ||
        stripos($errorMessage, 'connect_error') !== false ||
        stripos($errorMessage, 'Access denied') !== false ||
        stripos($errorMessage, 'Connection refused') !== false ||
        stripos($errorMessage, 'Can\'t connect') !== false ||
        stripos($errorMessage, 'php_network_getaddresses') !== false;

    $offlineSqlPath = false;
    if ($isConnectionIssue) {
        $offlineSqlPath = generateOfflineSqlBundle($db_name, $admin_email, $admin_pass);
        if ($offlineSqlPath !== false) {
            outputSuccess("Se genero SQL offline de contingencia: " . $offlineSqlPath);
            output("ℹ️  Puedes importar este archivo manualmente en phpMyAdmin/CLI cuando tengas acceso a la instancia.");
        } else {
            outputError("No se pudo generar el SQL offline de contingencia.");
        }
    }

    if ($isWeb) {
        echo "<div class='step' style='color: #dc3545; border-left-color: #dc3545;'>";
        echo "<p>Si el problema persiste, verifica:</p>";
        echo "<ul>";
        echo "<li>Que MySQL está ejecutándose</li>";
        echo "<li>Las credenciales ingresadas en el asistente</li>";
        echo "<li>Los permisos de la carpeta</li>";
        if ($isConnectionIssue && $offlineSqlPath !== false) {
            echo "<li>Importa el SQL offline generado: <code>" . htmlspecialchars($offlineSqlPath) . "</code></li>";
        }
        echo "</ul>";
        echo "<p><a href='auto_setup.php' style='display: inline-block; margin-top: 10px; color: #667eea;'>Volver al asistente</a></p>";
        echo "</div>";
        echo "</div></body></html>";
    } else {
        echo "\nAlternativas sugeridas:\n";
        echo "1) Verifica que MySQL esté activo y credenciales correctas.\n";
        echo "2) Si no hay conectividad, importa el SQL offline generado manualmente.\n";
        echo "3) Reintenta auto_setup cuando la instancia esté disponible.\n";
        if ($isConnectionIssue && $offlineSqlPath !== false) {
            echo "SQL offline: " . $offlineSqlPath . "\n";
        }
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
    if ($configUpdated) {
        echo "<p style='margin-top: 15px; padding: 10px; background: #e7f3ff; border-left: 3px solid #2196F3; border-radius: 3px;'>";
        echo "✅ config/config.php fue actualizado con host, usuario, contraseña y base de datos validados";
        echo "</p>";
    } else {
        echo "<p style='margin-top: 15px; padding: 10px; background: #fff4e5; border-left: 3px solid #ff9800; border-radius: 3px;'>";
        echo "⚠️ La instalación terminó, pero debes revisar manualmente config/config.php: " . h($configUpdateError);
        echo "</p>";
    }
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
