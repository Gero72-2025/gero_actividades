-- ==========================================
-- SCRIPT: Modulo Tablero de Actividades (Multi-Tablero)
-- ==========================================
-- Incluye:
--   - tablero (contenedor principal)
--   - tablero_usuario_permiso (acceso por usuario y tablero)
--   - tablero_columnas (por tablero)
--   - tablero_tarjetas (por tablero)
--   - tablero_tiempos
-- ==========================================

-- ------------------------------------------
-- 1) TABLA: tablero
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero` (
    `Id_tablero` INT AUTO_INCREMENT PRIMARY KEY,
    `Nombre` VARCHAR(150) NOT NULL,
    `Descripcion` TEXT NULL,
    `Id_usuario_responsable` INT NOT NULL,
    `Estado` TINYINT(1) NOT NULL DEFAULT 1,
    `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tablero_estado` (`Estado`),
    INDEX `idx_tablero_responsable` (`Id_usuario_responsable`),
    CONSTRAINT `fk_tablero_responsable`
        FOREIGN KEY (`Id_usuario_responsable`) REFERENCES `usuario`(`Id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 2) TABLA: tablero_usuario_permiso
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_usuario_permiso` (
    `Id_tablero_usuario_permiso` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tablero` INT NOT NULL,
    `Id_usuario` INT NOT NULL,
    `Permiso_ver` TINYINT(1) NOT NULL DEFAULT 1,
    `Permiso_crear` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_editar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_eliminar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tablero_ver` TINYINT(1) NOT NULL DEFAULT 1,
    `Permiso_tablero_crear` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tablero_editar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tablero_eliminar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tablero_asignar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_columna_crear` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_columna_editar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_columna_eliminar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_columna_ordenar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tarjeta_ver` TINYINT(1) NOT NULL DEFAULT 1,
    `Permiso_tarjeta_crear` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tarjeta_editar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tarjeta_mover` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tarjeta_eliminar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tarjeta_asignar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_lista_crear` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_lista_editar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_lista_eliminar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tarea_crear` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tarea_editar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tarea_eliminar` TINYINT(1) NOT NULL DEFAULT 0,
    `Permiso_tarea_tiempo_editar` TINYINT(1) NOT NULL DEFAULT 0,
    `Estado` TINYINT(1) NOT NULL DEFAULT 1,
    `Fecha_asignacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tablero_usuario` (`Id_tablero`, `Id_usuario`),
    INDEX `idx_tablero_usuario_estado` (`Estado`),
    CONSTRAINT `fk_tablero_usuario_permiso_tablero`
        FOREIGN KEY (`Id_tablero`) REFERENCES `tablero`(`Id_tablero`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tablero_usuario_permiso_usuario`
        FOREIGN KEY (`Id_usuario`) REFERENCES `usuario`(`Id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 3) TABLA: tablero_columnas
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_columnas` (
    `Id_columna` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tablero` INT NOT NULL,
    `Nombre` VARCHAR(120) NOT NULL,
    `Color` VARCHAR(20) DEFAULT '#0d6efd',
    `Orden_columna` INT NOT NULL DEFAULT 0,
    `Estado` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Activa, 0=Inactiva',
    `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tablero_columna_orden` (`Id_tablero`, `Orden_columna`),
    INDEX `idx_tablero_columna_estado` (`Estado`),
    INDEX `idx_tablero_columna_tablero` (`Id_tablero`),
    CONSTRAINT `fk_tablero_columna_tablero`
        FOREIGN KEY (`Id_tablero`) REFERENCES `tablero`(`Id_tablero`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 4) TABLA: tablero_tarjetas
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjetas` (
    `Id_tarjeta` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tablero` INT NOT NULL,
    `Id_columna` INT NOT NULL,
    `Id_alcance` INT NULL,
    `Id_actividad` INT NULL,
    `Id_usuario_creador` INT NOT NULL,
    `Id_usuario_asignado` INT NULL,
    `Id_prioridad` INT NULL,
    `Titulo` VARCHAR(180) NOT NULL,
    `Descripcion` TEXT,
    `Fecha_inicio` DATE NULL,
    `Fecha_fin` DATE NULL,
    `Checklist_json` JSON NULL,
    `Estado_tarjeta` ENUM('Pendiente','En Progreso','Completado','Completada','Bloqueada') DEFAULT 'Pendiente',
    `Completado` TINYINT(1) NOT NULL DEFAULT 0,
    `Posicion` INT NOT NULL DEFAULT 0,
    `Estado` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Activa, 0=Inactiva',
    `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tablero_tarjeta_tablero` (`Id_tablero`),
    INDEX `idx_tablero_tarjeta_columna` (`Id_columna`),
    INDEX `idx_tablero_tarjeta_alcance` (`Id_alcance`),
    INDEX `idx_tablero_tarjeta_actividad` (`Id_actividad`),
    INDEX `idx_tablero_tarjeta_asignado` (`Id_usuario_asignado`),
    INDEX `idx_tablero_tarjeta_prioridad` (`Id_prioridad`),
    INDEX `idx_tablero_tarjeta_fecha_inicio` (`Fecha_inicio`),
    INDEX `idx_tablero_tarjeta_fecha_fin` (`Fecha_fin`),
    INDEX `idx_tablero_tarjeta_estado` (`Estado`),
    INDEX `idx_tablero_tarjeta_completado` (`Completado`),
    INDEX `idx_tablero_tarjeta_posicion` (`Id_columna`, `Posicion`),
    CONSTRAINT `fk_tablero_tarjeta_tablero`
        FOREIGN KEY (`Id_tablero`) REFERENCES `tablero`(`Id_tablero`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tablero_tarjeta_columna`
        FOREIGN KEY (`Id_columna`) REFERENCES `tablero_columnas`(`Id_columna`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_tablero_tarjeta_alcance`
        FOREIGN KEY (`Id_alcance`) REFERENCES `alcances`(`Id_alcance`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_tablero_tarjeta_actividad`
        FOREIGN KEY (`Id_actividad`) REFERENCES `actividades`(`Id_actividad`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_tablero_tarjeta_usuario_creador`
        FOREIGN KEY (`Id_usuario_creador`) REFERENCES `usuario`(`Id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_tablero_tarjeta_usuario_asignado`
        FOREIGN KEY (`Id_usuario_asignado`) REFERENCES `usuario`(`Id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 4.1) TABLA: tablero_prioridades
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_prioridades` (
    `Id_prioridad` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tablero` INT NOT NULL,
    `Nombre` VARCHAR(80) NOT NULL,
    `Valor` INT NOT NULL,
    `Color` VARCHAR(20) NOT NULL DEFAULT '#6c757d',
    `Estado` TINYINT(1) NOT NULL DEFAULT 1,
    `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tablero_prioridad_nombre` (`Id_tablero`, `Nombre`),
    INDEX `idx_tablero_prioridad_tablero` (`Id_tablero`),
    INDEX `idx_tablero_prioridad_estado` (`Estado`),
    INDEX `idx_tablero_prioridad_valor` (`Valor`),
    CONSTRAINT `fk_tablero_prioridad_tablero`
        FOREIGN KEY (`Id_tablero`) REFERENCES `tablero`(`Id_tablero`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 4.2) TABLA: tablero_etiquetas
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_etiquetas` (
    `Id_etiqueta` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tablero` INT NOT NULL,
    `Nombre` VARCHAR(120) NULL,
    `Color` VARCHAR(20) NOT NULL,
    `Estado` TINYINT(1) NOT NULL DEFAULT 1,
    `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tablero_etiquetas_tablero` (`Id_tablero`),
    INDEX `idx_tablero_etiquetas_estado` (`Estado`),
    CONSTRAINT `fk_tablero_etiquetas_tablero`
        FOREIGN KEY (`Id_tablero`) REFERENCES `tablero`(`Id_tablero`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 4.3) TABLA: tablero_tarjeta_etiqueta
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjeta_etiqueta` (
    `Id_tablero_tarjeta_etiqueta` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tarjeta` INT NOT NULL,
    `Id_etiqueta` INT NOT NULL,
    `Estado` TINYINT(1) NOT NULL DEFAULT 1,
    `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tablero_tarjeta_etiqueta` (`Id_tarjeta`, `Id_etiqueta`),
    INDEX `idx_tablero_tarjeta_etiqueta_tarjeta` (`Id_tarjeta`),
    INDEX `idx_tablero_tarjeta_etiqueta_etiqueta` (`Id_etiqueta`),
    INDEX `idx_tablero_tarjeta_etiqueta_estado` (`Estado`),
    CONSTRAINT `fk_tablero_tarjeta_etiqueta_tarjeta`
        FOREIGN KEY (`Id_tarjeta`) REFERENCES `tablero_tarjetas`(`Id_tarjeta`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tablero_tarjeta_etiqueta_etiqueta`
        FOREIGN KEY (`Id_etiqueta`) REFERENCES `tablero_etiquetas`(`Id_etiqueta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 5) TABLA: tablero_tiempos
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tiempos` (
    `Id_tiempo` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tarjeta` INT NOT NULL,
    `Id_usuario` INT NOT NULL,
    `inicio_timestamp` DATETIME NOT NULL,
    `fin_timestamp` DATETIME NULL,
    `duracion_segundos` INT NULL,
    `Estado` TINYINT(1) NOT NULL DEFAULT 1,
    `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tablero_tiempos_tarjeta` (`Id_tarjeta`),
    INDEX `idx_tablero_tiempos_usuario` (`Id_usuario`),
    INDEX `idx_tablero_tiempos_inicio` (`inicio_timestamp`),
    INDEX `idx_tablero_tiempos_fin` (`fin_timestamp`),
    CONSTRAINT `fk_tablero_tiempo_tarjeta`
        FOREIGN KEY (`Id_tarjeta`) REFERENCES `tablero_tarjetas`(`Id_tarjeta`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tablero_tiempo_usuario`
        FOREIGN KEY (`Id_usuario`) REFERENCES `usuario`(`Id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 5.1) TABLA: tablero_tarjetas_tarea (encabezados)
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjetas_tarea` (
    `Id_tarea` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tarjeta` INT NOT NULL,
    `Nombre_tarea` VARCHAR(180) NOT NULL,
    `Orden_tarea` INT NOT NULL DEFAULT 0,
    `Estado` TINYINT(1) NOT NULL DEFAULT 1,
    `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ttt_tarjeta` (`Id_tarjeta`),
    INDEX `idx_ttt_estado` (`Estado`),
    INDEX `idx_ttt_orden` (`Id_tarjeta`, `Orden_tarea`),
    CONSTRAINT `fk_ttt_tarjeta`
        FOREIGN KEY (`Id_tarjeta`) REFERENCES `tablero_tarjetas`(`Id_tarjeta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 5.2) TABLA: tablero_tarjetas_tarea_detalle (checkboxes)
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjetas_tarea_detalle` (
    `Id_tarea_detalle` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tarea` INT NOT NULL,
    `Descripcion` VARCHAR(255) NOT NULL,
    `Completado` TINYINT(1) NOT NULL DEFAULT 0,
    `Orden_detalle` INT NOT NULL DEFAULT 0,
    `Id_usuario_check` INT NULL,
    `Fecha_check` DATETIME NULL,
    `Estado` TINYINT(1) NOT NULL DEFAULT 1,
    `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tttd_tarea` (`Id_tarea`),
    INDEX `idx_tttd_estado` (`Estado`),
    INDEX `idx_tttd_orden` (`Id_tarea`, `Orden_detalle`),
    CONSTRAINT `fk_tttd_tarea`
        FOREIGN KEY (`Id_tarea`) REFERENCES `tablero_tarjetas_tarea`(`Id_tarea`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tttd_usuario_check`
        FOREIGN KEY (`Id_usuario_check`) REFERENCES `usuario`(`Id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 5.3) TABLA: tablero_tarjetas_historial (bitacora)
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjetas_historial` (
    `Id_historial` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tarjeta` INT NOT NULL,
    `Id_usuario` INT NULL,
    `Tipo_evento` VARCHAR(80) NOT NULL,
    `Mensaje` VARCHAR(255) NOT NULL,
    `Datos_json` JSON NULL,
    `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tth_tarjeta` (`Id_tarjeta`),
    INDEX `idx_tth_usuario` (`Id_usuario`),
    INDEX `idx_tth_tipo` (`Tipo_evento`),
    INDEX `idx_tth_fecha` (`Fecha_creacion`),
    CONSTRAINT `fk_tth_tarjeta`
        FOREIGN KEY (`Id_tarjeta`) REFERENCES `tablero_tarjetas`(`Id_tarjeta`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tth_usuario`
        FOREIGN KEY (`Id_usuario`) REFERENCES `usuario`(`Id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 5.4) TABLA: tablero_tarjetas_tarea_detalle_tiempo
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjetas_tarea_detalle_tiempo` (
    `Id_tiempo_detalle` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tarea_detalle` INT NOT NULL,
    `Id_usuario` INT NOT NULL,
    `inicio_timestamp` DATETIME NOT NULL,
    `fin_timestamp` DATETIME NULL,
    `duracion_segundos` INT NULL,
    `Estado` TINYINT(1) NOT NULL DEFAULT 1,
    `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tttdt_detalle` (`Id_tarea_detalle`),
    INDEX `idx_tttdt_usuario` (`Id_usuario`),
    INDEX `idx_tttdt_inicio` (`inicio_timestamp`),
    INDEX `idx_tttdt_fin` (`fin_timestamp`),
    CONSTRAINT `fk_tttdt_detalle`
        FOREIGN KEY (`Id_tarea_detalle`) REFERENCES `tablero_tarjetas_tarea_detalle`(`Id_tarea_detalle`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tttdt_usuario`
        FOREIGN KEY (`Id_usuario`) REFERENCES `usuario`(`Id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 5.5) TABLA: tablero_tarjetas_tareas_detalle_tiempo_usuario
--      Acumulado por tarea-detalle y usuario
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjetas_tareas_detalle_tiempo_usuario` (
    `Id_tarea_detalle_tiempo_usuario` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tarea_detalle` INT NOT NULL,
    `Id_usuario` INT NOT NULL,
    `Tiempo_total_segundos` INT NOT NULL DEFAULT 0,
    `Estado` TINYINT(1) NOT NULL DEFAULT 1,
    `Fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tttdtu_detalle_usuario` (`Id_tarea_detalle`, `Id_usuario`),
    INDEX `idx_tttdtu_detalle` (`Id_tarea_detalle`),
    INDEX `idx_tttdtu_usuario` (`Id_usuario`),
    INDEX `idx_tttdtu_estado` (`Estado`),
    CONSTRAINT `fk_tttdtu_detalle`
        FOREIGN KEY (`Id_tarea_detalle`) REFERENCES `tablero_tarjetas_tarea_detalle`(`Id_tarea_detalle`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tttdtu_usuario`
        FOREIGN KEY (`Id_usuario`) REFERENCES `usuario`(`Id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 6) MIGRACION BASICA SI EXISTIAN TABLAS PREVIAS
-- ------------------------------------------
ALTER TABLE `tablero_columnas`
    ADD COLUMN IF NOT EXISTS `Id_tablero` INT NULL AFTER `Id_columna`;

ALTER TABLE `tablero_tarjetas`
    ADD COLUMN IF NOT EXISTS `Id_tablero` INT NULL AFTER `Id_tarjeta`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tablero_ver` TINYINT(1) NOT NULL DEFAULT 1 AFTER `Permiso_eliminar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tablero_crear` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tablero_ver`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tablero_editar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tablero_crear`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tablero_eliminar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tablero_editar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tablero_asignar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tablero_eliminar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_columna_crear` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tablero_asignar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_columna_editar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_columna_crear`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_columna_eliminar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_columna_editar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_columna_ordenar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_columna_eliminar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tarjeta_ver` TINYINT(1) NOT NULL DEFAULT 1 AFTER `Permiso_columna_ordenar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tarjeta_crear` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tarjeta_ver`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tarjeta_editar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tarjeta_crear`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tarjeta_mover` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tarjeta_editar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tarjeta_eliminar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tarjeta_mover`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tarjeta_asignar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tarjeta_eliminar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_lista_crear` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tarjeta_asignar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_lista_editar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_lista_crear`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_lista_eliminar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_lista_editar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tarea_crear` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_lista_eliminar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tarea_editar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tarea_crear`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tarea_eliminar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tarea_editar`;

ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_tarea_tiempo_editar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tarea_eliminar`;

UPDATE `tablero_usuario_permiso`
SET
    `Permiso_tablero_ver` = COALESCE(`Permiso_tablero_ver`, `Permiso_ver`, 1),
    `Permiso_tablero_crear` = COALESCE(`Permiso_tablero_crear`, `Permiso_crear`, 0),
    `Permiso_tablero_editar` = COALESCE(`Permiso_tablero_editar`, `Permiso_editar`, 0),
    `Permiso_tablero_eliminar` = COALESCE(`Permiso_tablero_eliminar`, `Permiso_eliminar`, 0),
    `Permiso_tablero_asignar` = COALESCE(`Permiso_tablero_asignar`, `Permiso_editar`, 0),
    `Permiso_columna_crear` = COALESCE(`Permiso_columna_crear`, `Permiso_tablero_editar`, `Permiso_editar`, 0),
    `Permiso_columna_editar` = COALESCE(`Permiso_columna_editar`, `Permiso_tablero_editar`, `Permiso_editar`, 0),
    `Permiso_columna_eliminar` = COALESCE(`Permiso_columna_eliminar`, `Permiso_tablero_eliminar`, `Permiso_eliminar`, 0),
    `Permiso_columna_ordenar` = COALESCE(`Permiso_columna_ordenar`, `Permiso_tablero_editar`, `Permiso_editar`, 0),
    `Permiso_tarjeta_ver` = COALESCE(`Permiso_tarjeta_ver`, `Permiso_ver`, 1),
    `Permiso_tarjeta_crear` = COALESCE(`Permiso_tarjeta_crear`, `Permiso_crear`, 0),
    `Permiso_tarjeta_editar` = COALESCE(`Permiso_tarjeta_editar`, `Permiso_editar`, 0),
    `Permiso_tarjeta_mover` = COALESCE(`Permiso_tarjeta_mover`, `Permiso_tarjeta_editar`, `Permiso_editar`, 0),
    `Permiso_tarjeta_eliminar` = COALESCE(`Permiso_tarjeta_eliminar`, `Permiso_eliminar`, 0),
    `Permiso_tarjeta_asignar` = COALESCE(`Permiso_tarjeta_asignar`, `Permiso_editar`, 0),
    `Permiso_lista_crear` = COALESCE(`Permiso_lista_crear`, `Permiso_editar`, 0),
    `Permiso_lista_editar` = COALESCE(`Permiso_lista_editar`, `Permiso_editar`, 0),
    `Permiso_lista_eliminar` = COALESCE(`Permiso_lista_eliminar`, `Permiso_editar`, 0),
    `Permiso_tarea_crear` = COALESCE(`Permiso_tarea_crear`, `Permiso_editar`, 0),
    `Permiso_tarea_editar` = COALESCE(`Permiso_tarea_editar`, `Permiso_editar`, 0),
    `Permiso_tarea_eliminar` = COALESCE(`Permiso_tarea_eliminar`, `Permiso_editar`, 0),
    `Permiso_tarea_tiempo_editar` = COALESCE(`Permiso_tarea_tiempo_editar`, 0);

ALTER TABLE `tablero_tarjetas`
    ADD COLUMN IF NOT EXISTS `Id_alcance` INT NULL AFTER `Id_columna`;

ALTER TABLE `tablero_tarjetas_tarea_detalle`
    ADD COLUMN IF NOT EXISTS `Id_usuario_asignado` INT NULL AFTER `Id_tarea`;

ALTER TABLE `tablero_tarjetas_tarea_detalle`
    ADD INDEX IF NOT EXISTS `idx_tttd_usuario_asignado` (`Id_usuario_asignado`);

SET @fk_tttd_usuario_asignado_exists := (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tablero_tarjetas_tarea_detalle'
      AND CONSTRAINT_NAME = 'fk_tttd_usuario_asignado'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql_fk_tttd_usuario_asignado := IF(
    @fk_tttd_usuario_asignado_exists = 0,
    'ALTER TABLE `tablero_tarjetas_tarea_detalle` ADD CONSTRAINT `fk_tttd_usuario_asignado` FOREIGN KEY (`Id_usuario_asignado`) REFERENCES `usuario`(`Id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1'
);

PREPARE stmt_fk_tttd_usuario_asignado FROM @sql_fk_tttd_usuario_asignado;
EXECUTE stmt_fk_tttd_usuario_asignado;
DEALLOCATE PREPARE stmt_fk_tttd_usuario_asignado;

ALTER TABLE `tablero_tarjetas`
    ADD INDEX IF NOT EXISTS `idx_tablero_tarjeta_alcance` (`Id_alcance`);

SET @fk_tablero_tarjeta_alcance_exists := (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tablero_tarjetas'
      AND CONSTRAINT_NAME = 'fk_tablero_tarjeta_alcance'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql_fk_tablero_tarjeta_alcance := IF(
    @fk_tablero_tarjeta_alcance_exists = 0,
    'ALTER TABLE `tablero_tarjetas` ADD CONSTRAINT `fk_tablero_tarjeta_alcance` FOREIGN KEY (`Id_alcance`) REFERENCES `alcances`(`Id_alcance`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1'
);

PREPARE stmt_fk_tablero_tarjeta_alcance FROM @sql_fk_tablero_tarjeta_alcance;
EXECUTE stmt_fk_tablero_tarjeta_alcance;
DEALLOCATE PREPARE stmt_fk_tablero_tarjeta_alcance;

ALTER TABLE `tablero_tarjetas`
    ADD COLUMN IF NOT EXISTS `Id_prioridad` INT NULL AFTER `Id_usuario_asignado`;

ALTER TABLE `tablero_tarjetas`
    ADD COLUMN IF NOT EXISTS `Fecha_inicio` DATE NULL AFTER `Descripcion`;

ALTER TABLE `tablero_tarjetas`
    ADD COLUMN IF NOT EXISTS `Fecha_fin` DATE NULL AFTER `Fecha_inicio`;

ALTER TABLE `tablero_tarjetas`
    ADD COLUMN IF NOT EXISTS `Completado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Estado_tarjeta`;

ALTER TABLE `tablero_tarjetas`
    MODIFY COLUMN `Estado_tarjeta` ENUM('Pendiente','En Progreso','Completado','Completada','Bloqueada') DEFAULT 'Pendiente';

ALTER TABLE `tablero_tarjetas`
    ADD INDEX IF NOT EXISTS `idx_tablero_tarjeta_prioridad` (`Id_prioridad`);

ALTER TABLE `tablero_tarjetas`
    ADD INDEX IF NOT EXISTS `idx_tablero_tarjeta_fecha_inicio` (`Fecha_inicio`);

ALTER TABLE `tablero_tarjetas`
    ADD INDEX IF NOT EXISTS `idx_tablero_tarjeta_fecha_fin` (`Fecha_fin`);

ALTER TABLE `tablero_tarjetas`
        ADD INDEX IF NOT EXISTS `idx_tablero_tarjeta_completado` (`Completado`);

UPDATE `tablero_tarjetas`
SET `Completado` = 1
WHERE `Estado_tarjeta` IN ('Completada', 'Completado');

UPDATE `tablero_tarjetas`
SET `Estado_tarjeta` = 'Completado'
WHERE `Completado` = 1
    AND `Estado_tarjeta` <> 'Completado';

-- Backfill de acumulado por usuario usando historico existente.
INSERT INTO `tablero_tarjetas_tareas_detalle_tiempo_usuario` (
    `Id_tarea_detalle`,
    `Id_usuario`,
    `Tiempo_total_segundos`,
    `Estado`
)
SELECT
    tdti.Id_tarea_detalle,
    tdti.Id_usuario,
    SUM(COALESCE(tdti.duracion_segundos, TIMESTAMPDIFF(SECOND, tdti.inicio_timestamp, COALESCE(tdti.fin_timestamp, NOW())))) AS total_segundos,
    1
FROM `tablero_tarjetas_tarea_detalle_tiempo` tdti
INNER JOIN `tablero_tarjetas_tarea_detalle` d ON d.Id_tarea_detalle = tdti.Id_tarea_detalle
WHERE tdti.Estado = 1
  AND d.Estado = 1
GROUP BY tdti.Id_tarea_detalle, tdti.Id_usuario
ON DUPLICATE KEY UPDATE
    Tiempo_total_segundos = VALUES(Tiempo_total_segundos),
    Estado = 1,
    Fecha_actualizacion = NOW();

-- Si venias de la version anterior, este indice unico era global por Orden_columna
-- y provoca errores al crear columnas de otro tablero (Duplicate entry '1').
ALTER TABLE `tablero_columnas`
    ADD COLUMN IF NOT EXISTS `Orden_columna` INT NOT NULL DEFAULT 0 AFTER `Color`;

ALTER TABLE `tablero_columnas`
    DROP INDEX IF EXISTS `uk_tablero_columna_orden`;

SET @orden_prev_tablero := 0;
SET @orden_seq := 0;

UPDATE `tablero_columnas` c
INNER JOIN (
    SELECT
        base.Id_columna,
        base.Id_tablero,
        (@orden_seq := IF(@orden_prev_tablero = base.Id_tablero, @orden_seq + 1, 1)) AS nuevo_orden,
        (@orden_prev_tablero := base.Id_tablero) AS _tmp
    FROM (
        SELECT Id_columna, Id_tablero, COALESCE(NULLIF(Orden_columna, 0), 999999) AS orden_actual
        FROM `tablero_columnas`
        ORDER BY Id_tablero ASC, orden_actual ASC, Id_columna ASC
    ) base
) seq ON seq.Id_columna = c.Id_columna
SET c.Orden_columna = seq.nuevo_orden;

-- Recrear unicidad por tablero + orden (correcto para multi-tablero).
ALTER TABLE `tablero_columnas`
    ADD UNIQUE KEY `uk_tablero_columna_orden` (`Id_tablero`, `Orden_columna`);

-- Prioridad por defecto para tarjetas antiguas (MEDIA del tablero)
UPDATE `tablero_tarjetas` tt
INNER JOIN `tablero_prioridades` tp
    ON tp.Id_tablero = tt.Id_tablero
   AND tp.Nombre = 'MEDIA'
   AND tp.Estado = 1
SET tt.Id_prioridad = tp.Id_prioridad
WHERE tt.Id_prioridad IS NULL;

-- ------------------------------------------
-- 7) PERMISOS RBAC DEL MODULO TABLERO
-- ------------------------------------------
INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'tablero.ver', 'Ver tableros asignados', 'tablero', 'ver', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'tablero.ver');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'tablero.dashboard', 'Acceso global a vista Dashboard de tablero', 'tablero', 'dashboard', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'tablero.dashboard');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'tablero.calendario', 'Acceso global a vista Calendario de tablero', 'tablero', 'calendario', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'tablero.calendario');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'tablero.reporteria', 'Acceso global a vista Reporteria de tablero', 'tablero', 'reporteria', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'tablero.reporteria');

-- Desactivar permisos globales antiguos del modulo tablero.
-- Las acciones internas ahora se gobiernan por permisos granulares en tablero_usuario_permiso.
UPDATE `permisos`
SET `Estado` = 0
WHERE `Nombre` IN (
    'tablero.crear',
    'tablero.editar',
    'tablero.eliminar',
    'tablero.columnas',
    'tablero.columnas_eliminar',
    'tablero.asignar',
    'tablero.tiempo',
    'tablero.tiempo_editar'
);

UPDATE `role_permiso` rp
INNER JOIN `permisos` p ON p.Id_permiso = rp.Id_permiso
SET rp.`Estado` = 0
WHERE p.`Nombre` IN (
    'tablero.crear',
    'tablero.editar',
    'tablero.eliminar',
    'tablero.columnas',
    'tablero.columnas_eliminar',
    'tablero.asignar',
    'tablero.tiempo',
    'tablero.tiempo_editar'
);

-- ------------------------------------------
-- 8) ASIGNACION RBAC SUGERIDA
-- ------------------------------------------
INSERT IGNORE INTO `role_permiso` (`Id_role`, `Id_permiso`, `Estado`)
SELECT 1, p.Id_permiso, 1
FROM `permisos` p
WHERE p.Nombre IN (
    'tablero.ver','tablero.dashboard','tablero.calendario','tablero.reporteria'
);

INSERT IGNORE INTO `role_permiso` (`Id_role`, `Id_permiso`, `Estado`)
SELECT 2, p.Id_permiso, 1
FROM `permisos` p
WHERE p.Nombre IN ('tablero.ver','tablero.dashboard','tablero.calendario','tablero.reporteria');

INSERT IGNORE INTO `role_permiso` (`Id_role`, `Id_permiso`, `Estado`)
SELECT 3, p.Id_permiso, 1
FROM `permisos` p
WHERE p.Nombre IN ('tablero.ver','tablero.dashboard','tablero.calendario','tablero.reporteria');

INSERT IGNORE INTO `role_permiso` (`Id_role`, `Id_permiso`, `Estado`)
SELECT 4, p.Id_permiso, 1
FROM `permisos` p
WHERE p.Nombre IN ('tablero.ver','tablero.dashboard','tablero.calendario','tablero.reporteria');

-- ------------------------------------------
-- 9) SEMILLA OPCIONAL DE TABLERO DE EJEMPLO
-- ------------------------------------------
-- NOTA: Ajusta el usuario responsable segun tu ambiente.
INSERT INTO `tablero` (`Nombre`, `Descripcion`, `Id_usuario_responsable`, `Estado`)
SELECT 'Tablero General', 'Tablero inicial de actividades', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `tablero` WHERE `Nombre` = 'Tablero General' AND `Estado` = 1);

-- Dar permisos completos del tablero general al usuario 1
INSERT INTO `tablero_usuario_permiso` (
    `Id_tablero`, `Id_usuario`,
    `Permiso_ver`, `Permiso_crear`, `Permiso_editar`, `Permiso_eliminar`,
    `Permiso_tablero_ver`, `Permiso_tablero_crear`, `Permiso_tablero_editar`, `Permiso_tablero_eliminar`, `Permiso_tablero_asignar`,
    `Permiso_columna_crear`, `Permiso_columna_editar`, `Permiso_columna_eliminar`, `Permiso_columna_ordenar`,
    `Permiso_tarjeta_ver`, `Permiso_tarjeta_crear`, `Permiso_tarjeta_editar`, `Permiso_tarjeta_mover`, `Permiso_tarjeta_eliminar`, `Permiso_tarjeta_asignar`,
    `Permiso_lista_crear`, `Permiso_lista_editar`, `Permiso_lista_eliminar`,
    `Permiso_tarea_crear`, `Permiso_tarea_editar`, `Permiso_tarea_eliminar`, `Permiso_tarea_tiempo_editar`,
    `Estado`
)
SELECT t.Id_tablero, 1,
       1, 1, 1, 1,
       1, 1, 1, 1, 1,
    1, 1, 1, 1,
    1, 1, 1, 1, 1, 1,
       1, 1, 1,
       1, 1, 1, 1,
       1
FROM `tablero` t
WHERE t.Nombre = 'Tablero General'
ON DUPLICATE KEY UPDATE
    Permiso_ver = VALUES(Permiso_ver),
    Permiso_crear = VALUES(Permiso_crear),
    Permiso_editar = VALUES(Permiso_editar),
    Permiso_eliminar = VALUES(Permiso_eliminar),
    Permiso_tablero_ver = VALUES(Permiso_tablero_ver),
    Permiso_tablero_crear = VALUES(Permiso_tablero_crear),
    Permiso_tablero_editar = VALUES(Permiso_tablero_editar),
    Permiso_tablero_eliminar = VALUES(Permiso_tablero_eliminar),
    Permiso_tablero_asignar = VALUES(Permiso_tablero_asignar),
    Permiso_columna_crear = VALUES(Permiso_columna_crear),
    Permiso_columna_editar = VALUES(Permiso_columna_editar),
    Permiso_columna_eliminar = VALUES(Permiso_columna_eliminar),
    Permiso_columna_ordenar = VALUES(Permiso_columna_ordenar),
    Permiso_tarjeta_ver = VALUES(Permiso_tarjeta_ver),
    Permiso_tarjeta_crear = VALUES(Permiso_tarjeta_crear),
    Permiso_tarjeta_editar = VALUES(Permiso_tarjeta_editar),
    Permiso_tarjeta_mover = VALUES(Permiso_tarjeta_mover),
    Permiso_tarjeta_eliminar = VALUES(Permiso_tarjeta_eliminar),
    Permiso_tarjeta_asignar = VALUES(Permiso_tarjeta_asignar),
    Permiso_lista_crear = VALUES(Permiso_lista_crear),
    Permiso_lista_editar = VALUES(Permiso_lista_editar),
    Permiso_lista_eliminar = VALUES(Permiso_lista_eliminar),
    Permiso_tarea_crear = VALUES(Permiso_tarea_crear),
    Permiso_tarea_editar = VALUES(Permiso_tarea_editar),
    Permiso_tarea_eliminar = VALUES(Permiso_tarea_eliminar),
    Permiso_tarea_tiempo_editar = VALUES(Permiso_tarea_tiempo_editar),
    Estado = 1;

-- Columnas base para Tablero General
INSERT INTO `tablero_columnas` (`Id_tablero`, `Nombre`, `Color`, `Orden_columna`, `Estado`)
SELECT t.Id_tablero, 'Pendiente', '#6c757d', 1, 1
FROM `tablero` t
WHERE t.Nombre = 'Tablero General'
  AND NOT EXISTS (
      SELECT 1 FROM `tablero_columnas` c
            WHERE c.Id_tablero = t.Id_tablero AND c.Orden_columna = 1
  );

INSERT INTO `tablero_columnas` (`Id_tablero`, `Nombre`, `Color`, `Orden_columna`, `Estado`)
SELECT t.Id_tablero, 'En Progreso', '#0d6efd', 2, 1
FROM `tablero` t
WHERE t.Nombre = 'Tablero General'
  AND NOT EXISTS (
      SELECT 1 FROM `tablero_columnas` c
            WHERE c.Id_tablero = t.Id_tablero AND c.Orden_columna = 2
  );

INSERT INTO `tablero_columnas` (`Id_tablero`, `Nombre`, `Color`, `Orden_columna`, `Estado`)
SELECT t.Id_tablero, 'En Revision', '#fd7e14', 3, 1
FROM `tablero` t
WHERE t.Nombre = 'Tablero General'
  AND NOT EXISTS (
      SELECT 1 FROM `tablero_columnas` c
            WHERE c.Id_tablero = t.Id_tablero AND c.Orden_columna = 3
  );

INSERT INTO `tablero_columnas` (`Id_tablero`, `Nombre`, `Color`, `Orden_columna`, `Estado`)
SELECT t.Id_tablero, 'Completada', '#198754', 4, 1
FROM `tablero` t
WHERE t.Nombre = 'Tablero General'
  AND NOT EXISTS (
      SELECT 1 FROM `tablero_columnas` c
            WHERE c.Id_tablero = t.Id_tablero AND c.Orden_columna = 4
  );

-- Prioridades base por tablero existente
INSERT INTO `tablero_prioridades` (`Id_tablero`, `Nombre`, `Valor`, `Color`, `Estado`)
SELECT t.Id_tablero, 'ALTA', 10, '#dc3545', 1
FROM `tablero` t
WHERE t.Estado = 1
    AND NOT EXISTS (
            SELECT 1 FROM `tablero_prioridades` p
            WHERE p.Id_tablero = t.Id_tablero AND p.Nombre = 'ALTA' AND p.Estado = 1
    );

INSERT INTO `tablero_prioridades` (`Id_tablero`, `Nombre`, `Valor`, `Color`, `Estado`)
SELECT t.Id_tablero, 'MEDIA', 5, '#fd7e14', 1
FROM `tablero` t
WHERE t.Estado = 1
    AND NOT EXISTS (
            SELECT 1 FROM `tablero_prioridades` p
            WHERE p.Id_tablero = t.Id_tablero AND p.Nombre = 'MEDIA' AND p.Estado = 1
    );

INSERT INTO `tablero_prioridades` (`Id_tablero`, `Nombre`, `Valor`, `Color`, `Estado`)
SELECT t.Id_tablero, 'BAJA', 1, '#198754', 1
FROM `tablero` t
WHERE t.Estado = 1
    AND NOT EXISTS (
            SELECT 1 FROM `tablero_prioridades` p
            WHERE p.Id_tablero = t.Id_tablero AND p.Nombre = 'BAJA' AND p.Estado = 1
    );

-- Asegurar prioridad MEDIA en tarjetas existentes sin prioridad
UPDATE `tablero_tarjetas` tt
INNER JOIN `tablero_prioridades` tp
        ON tp.Id_tablero = tt.Id_tablero
     AND tp.Nombre = 'MEDIA'
     AND tp.Estado = 1
SET tt.Id_prioridad = tp.Id_prioridad
WHERE tt.Id_prioridad IS NULL;

-- Etiquetas base para Tablero General
INSERT INTO `tablero_etiquetas` (`Id_tablero`, `Nombre`, `Color`, `Estado`)
SELECT t.Id_tablero, 'Rojo', '#dc3545', 1
FROM `tablero` t
WHERE t.Nombre = 'Tablero General'
    AND NOT EXISTS (
            SELECT 1 FROM `tablero_etiquetas` e
            WHERE e.Id_tablero = t.Id_tablero AND e.Nombre = 'Rojo' AND e.Color = '#dc3545' AND e.Estado = 1
    );

INSERT INTO `tablero_etiquetas` (`Id_tablero`, `Nombre`, `Color`, `Estado`)
SELECT t.Id_tablero, 'Verde', '#198754', 1
FROM `tablero` t
WHERE t.Nombre = 'Tablero General'
    AND NOT EXISTS (
            SELECT 1 FROM `tablero_etiquetas` e
            WHERE e.Id_tablero = t.Id_tablero AND e.Nombre = 'Verde' AND e.Color = '#198754' AND e.Estado = 1
    );

INSERT INTO `tablero_etiquetas` (`Id_tablero`, `Nombre`, `Color`, `Estado`)
SELECT t.Id_tablero, 'Azul', '#0d6efd', 1
FROM `tablero` t
WHERE t.Nombre = 'Tablero General'
    AND NOT EXISTS (
            SELECT 1 FROM `tablero_etiquetas` e
            WHERE e.Id_tablero = t.Id_tablero AND e.Nombre = 'Azul' AND e.Color = '#0d6efd' AND e.Estado = 1
    );

INSERT INTO `tablero_etiquetas` (`Id_tablero`, `Nombre`, `Color`, `Estado`)
SELECT t.Id_tablero, 'Anaranjado', '#fd7e14', 1
FROM `tablero` t
WHERE t.Nombre = 'Tablero General'
    AND NOT EXISTS (
            SELECT 1 FROM `tablero_etiquetas` e
            WHERE e.Id_tablero = t.Id_tablero AND e.Nombre = 'Anaranjado' AND e.Color = '#fd7e14' AND e.Estado = 1
    );
