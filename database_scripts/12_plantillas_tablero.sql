-- ==========================================
-- SCRIPT 12: Plantillas de Tarjetas y Listas de Tareas
-- ==========================================
-- Tablas:
--   - tablero_tarjetas_plantilla         (plantillas de tarjeta)
--   - tablero_tarjetas_tareas_plantilla  (encabezados de plantillas de lista)
--   - tablero_tarjetas_tareas_plantilla_detalle (tareas de la plantilla de lista)
-- ==========================================

-- ------------------------------------------
-- 1) TABLA: tablero_tarjetas_plantilla
--    Almacena valores por defecto para pre-rellenar el modal "Crear Tarjeta"
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjetas_plantilla` (
    `Id_plantilla_tarjeta` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tablero`           INT NOT NULL,
    `Nombre_plantilla`     VARCHAR(150) NOT NULL,
    `Titulo`               VARCHAR(180) NOT NULL,
    `Descripcion`          TEXT NULL,
    `Id_usuario_creador`   INT NOT NULL,
    `Estado`               TINYINT(1)  NOT NULL DEFAULT 1,
    `Fecha_creacion`       TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    `Fecha_actualizacion`  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ttp_tablero` (`Id_tablero`),
    INDEX `idx_ttp_estado`  (`Estado`),
    INDEX `idx_ttp_creador` (`Id_usuario_creador`),
    CONSTRAINT `fk_ttp_tablero`
        FOREIGN KEY (`Id_tablero`)
        REFERENCES `tablero`(`Id_tablero`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ttp_creador`
        FOREIGN KEY (`Id_usuario_creador`)
        REFERENCES `usuario`(`Id_usuario`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 2) TABLA: tablero_tarjetas_tareas_plantilla
--    Encabezado de una plantilla de listado de tareas
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjetas_tareas_plantilla` (
    `Id_plantilla_lista`  INT AUTO_INCREMENT PRIMARY KEY,
    `Id_tablero`          INT NOT NULL,
    `Nombre_plantilla`    VARCHAR(150) NOT NULL,
    `Nombre_lista`        VARCHAR(180) NOT NULL,
    `Id_usuario_creador`  INT NOT NULL,
    `Estado`              TINYINT(1)  NOT NULL DEFAULT 1,
    `Fecha_creacion`      TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    `Fecha_actualizacion` TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ttlp_tablero` (`Id_tablero`),
    INDEX `idx_ttlp_estado`  (`Estado`),
    INDEX `idx_ttlp_creador` (`Id_usuario_creador`),
    CONSTRAINT `fk_ttlp_tablero`
        FOREIGN KEY (`Id_tablero`)
        REFERENCES `tablero`(`Id_tablero`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ttlp_creador`
        FOREIGN KEY (`Id_usuario_creador`)
        REFERENCES `usuario`(`Id_usuario`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 3) TABLA: tablero_tarjetas_tareas_plantilla_detalle
--    Tareas (sin asignar) que componen la plantilla de lista
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjetas_tareas_plantilla_detalle` (
    `Id_plantilla_detalle` INT AUTO_INCREMENT PRIMARY KEY,
    `Id_plantilla_lista`   INT         NOT NULL,
    `Descripcion`          VARCHAR(255) NOT NULL,
    `Orden_detalle`        INT         NOT NULL DEFAULT 0,
    `Estado`               TINYINT(1)  NOT NULL DEFAULT 1,
    `Fecha_creacion`       TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ttlpd_lista`  (`Id_plantilla_lista`),
    INDEX `idx_ttlpd_estado` (`Estado`),
    INDEX `idx_ttlpd_orden`  (`Id_plantilla_lista`, `Orden_detalle`),
    CONSTRAINT `fk_ttlpd_lista`
        FOREIGN KEY (`Id_plantilla_lista`)
        REFERENCES `tablero_tarjetas_tareas_plantilla`(`Id_plantilla_lista`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- SCRIPT 14: Columna y Prioridad en Plantilla de Tarjeta
-- ==========================================
-- Agrega Id_columna_defecto e Id_prioridad_defecto a la tabla
-- tablero_tarjetas_plantilla para que al usar una plantilla se
-- pre-rellene la columna y prioridad destino.
-- ==========================================

ALTER TABLE `tablero_tarjetas_plantilla`
    ADD COLUMN `Id_columna_defecto`   INT NULL DEFAULT NULL AFTER `Descripcion`,
    ADD COLUMN `Id_prioridad_defecto` INT NULL DEFAULT NULL AFTER `Id_columna_defecto`;

-- ==========================================
-- Columna Archivada en tablero_tarjetas
-- ==========================================
-- Permite marcar una tarjeta como archivada sin eliminarla.
-- Las tarjetas archivadas se ocultan por defecto en el tablero
-- y se muestran solo al activar el switch "Mostrar Archivadas".
-- ==========================================

ALTER TABLE `tablero_tarjetas`
    ADD COLUMN `Archivada`         TINYINT(1)  NOT NULL DEFAULT 0 AFTER `Completado`,
    ADD COLUMN `Fecha_archivado`   TIMESTAMP   NULL DEFAULT NULL   AFTER `Archivada`;
