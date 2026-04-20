-- ==========================================
-- SCRIPT 13: Asociaciones Plantillas + Nuevos Permisos
-- ==========================================
-- Incluye:
--   - tablero_tarjetas_plantilla_lista_rel
--       (vincula plantillas de tarjeta con plantillas de lista)
--   - Columnas nuevas en tablero_usuario_permiso para permisos
--       de plantillas (Seccion 6 y 7)
--   - Seccion 6: Permisos Plantillas Tarjetas
--       crear, editar, eliminar, asociar
--   - Seccion 7: Permisos Plantillas Tareas
--       crear, editar, eliminar
--   - Asignacion al Rol Administrador (Id_role = 1)
-- ==========================================

-- ------------------------------------------
-- 1) TABLA: tablero_tarjetas_plantilla_lista_rel
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjetas_plantilla_lista_rel` (
    `Id_rel`               INT AUTO_INCREMENT PRIMARY KEY,
    `Id_plantilla_tarjeta` INT NOT NULL,
    `Id_plantilla_lista`   INT NOT NULL,
    `Orden`                INT NOT NULL DEFAULT 0,
    INDEX `idx_rel_tarjeta` (`Id_plantilla_tarjeta`),
    INDEX `idx_rel_lista`   (`Id_plantilla_lista`),
    UNIQUE KEY `uk_rel` (`Id_plantilla_tarjeta`, `Id_plantilla_lista`),
    CONSTRAINT `fk_rel_plantilla_tarjeta`
        FOREIGN KEY (`Id_plantilla_tarjeta`)
        REFERENCES `tablero_tarjetas_plantilla`(`Id_plantilla_tarjeta`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_rel_plantilla_lista`
        FOREIGN KEY (`Id_plantilla_lista`)
        REFERENCES `tablero_tarjetas_tareas_plantilla`(`Id_plantilla_lista`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 2) COLUMNAS: Permisos de plantillas en tablero_usuario_permiso
-- ------------------------------------------
ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_tarjeta_crear`   TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tarea_tiempo_editar`,
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_tarjeta_editar`  TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_plantilla_tarjeta_crear`,
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_tarjeta_eliminar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_plantilla_tarjeta_editar`,
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_tarjeta_asociar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_plantilla_tarjeta_eliminar`,
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_lista_crear`     TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_plantilla_tarjeta_asociar`,
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_lista_editar`    TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_plantilla_lista_crear`,
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_lista_eliminar`  TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_plantilla_lista_editar`;

-- ------------------------------------------
-- 3) ADMIN: Activar nuevas columnas para usuario 1 en todos los tableros
-- ------------------------------------------
UPDATE `tablero_usuario_permiso`
SET
    `Permiso_plantilla_tarjeta_crear`    = 1,
    `Permiso_plantilla_tarjeta_editar`   = 1,
    `Permiso_plantilla_tarjeta_eliminar` = 1,
    `Permiso_plantilla_tarjeta_asociar`  = 1,
    `Permiso_plantilla_lista_crear`      = 1,
    `Permiso_plantilla_lista_editar`     = 1,
    `Permiso_plantilla_lista_eliminar`   = 1
WHERE `Id_usuario` = 1;

-- ------------------------------------------
-- 4) PERMISOS RBAC: Seccion 6 - Plantillas Tarjetas
-- ------------------------------------------
INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_tarjeta.crear', 'Crear plantillas de tarjeta', 'plantilla_tarjeta', 'crear', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_tarjeta.crear');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_tarjeta.editar', 'Editar plantillas de tarjeta', 'plantilla_tarjeta', 'editar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_tarjeta.editar');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_tarjeta.eliminar', 'Eliminar plantillas de tarjeta', 'plantilla_tarjeta', 'eliminar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_tarjeta.eliminar');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_tarjeta.asociar', 'Asociar plantillas de lista a plantillas de tarjeta', 'plantilla_tarjeta', 'asociar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_tarjeta.asociar');

-- ------------------------------------------
-- 5) PERMISOS RBAC: Seccion 7 - Plantillas Tareas
-- ------------------------------------------
INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_lista.crear', 'Crear plantillas de listado de tareas', 'plantilla_lista', 'crear', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_lista.crear');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_lista.editar', 'Editar plantillas de listado de tareas', 'plantilla_lista', 'editar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_lista.editar');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_lista.eliminar', 'Eliminar plantillas de listado de tareas', 'plantilla_lista', 'eliminar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_lista.eliminar');

-- ------------------------------------------
-- 6) ASIGNACION AL ROL ADMINISTRADOR (Id_role = 1)
-- ------------------------------------------
INSERT IGNORE INTO `role_permiso` (`Id_role`, `Id_permiso`, `Estado`)
SELECT 1, p.Id_permiso, 1
FROM `permisos` p
WHERE p.Nombre IN (
    'plantilla_tarjeta.crear',
    'plantilla_tarjeta.editar',
    'plantilla_tarjeta.eliminar',
    'plantilla_tarjeta.asociar',
    'plantilla_lista.crear',
    'plantilla_lista.editar',
    'plantilla_lista.eliminar'
);

-- ==========================================
-- FIN DEL SCRIPT
-- ==========================================

-- Incluye:
--   - tablero_tarjetas_plantilla_lista_rel
--       (vincula plantillas de tarjeta con plantillas de lista)
--   - Columnas nuevas en tablero_usuario_permiso para permisos
--       de plantillas (Seccion 6 y 7)
--   - Seccion 6: Permisos Plantillas Tarjetas
--       crear, editar, eliminar, asociar
--   - Seccion 7: Permisos Plantillas Tareas
--       crear, editar, eliminar
--   - Asignacion al Rol Administrador (Id_role = 1)
-- ==========================================

-- ------------------------------------------
-- 1) TABLA: tablero_tarjetas_plantilla_lista_rel
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjetas_plantilla_lista_rel` (
    `Id_rel`               INT AUTO_INCREMENT PRIMARY KEY,
    `Id_plantilla_tarjeta` INT NOT NULL,
    `Id_plantilla_lista`   INT NOT NULL,
    `Orden`                INT NOT NULL DEFAULT 0,
    INDEX `idx_rel_tarjeta` (`Id_plantilla_tarjeta`),
    INDEX `idx_rel_lista`   (`Id_plantilla_lista`),
    UNIQUE KEY `uk_rel` (`Id_plantilla_tarjeta`, `Id_plantilla_lista`),
    CONSTRAINT `fk_rel_plantilla_tarjeta`
        FOREIGN KEY (`Id_plantilla_tarjeta`)
        REFERENCES `tablero_tarjetas_plantilla`(`Id_plantilla_tarjeta`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_rel_plantilla_lista`
        FOREIGN KEY (`Id_plantilla_lista`)
        REFERENCES `tablero_tarjetas_tareas_plantilla`(`Id_plantilla_lista`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 2) COLUMNAS: Permisos de plantillas en tablero_usuario_permiso
-- ------------------------------------------
ALTER TABLE `tablero_usuario_permiso`
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_tarjeta_crear`   TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_tarea_tiempo_editar`,
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_tarjeta_editar`  TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_plantilla_tarjeta_crear`,
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_tarjeta_eliminar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_plantilla_tarjeta_editar`,
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_tarjeta_asociar` TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_plantilla_tarjeta_eliminar`,
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_lista_crear`     TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_plantilla_tarjeta_asociar`,
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_lista_editar`    TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_plantilla_lista_crear`,
    ADD COLUMN IF NOT EXISTS `Permiso_plantilla_lista_eliminar`  TINYINT(1) NOT NULL DEFAULT 0 AFTER `Permiso_plantilla_lista_editar`;

-- ------------------------------------------
-- 3) ADMIN: Activar nuevas columnas para usuario 1 en todos los tableros
-- ------------------------------------------
UPDATE `tablero_usuario_permiso`
SET
    `Permiso_plantilla_tarjeta_crear`    = 1,
    `Permiso_plantilla_tarjeta_editar`   = 1,
    `Permiso_plantilla_tarjeta_eliminar` = 1,
    `Permiso_plantilla_tarjeta_asociar`  = 1,
    `Permiso_plantilla_lista_crear`      = 1,
    `Permiso_plantilla_lista_editar`     = 1,
    `Permiso_plantilla_lista_eliminar`   = 1
WHERE `Id_usuario` = 1;

-- ------------------------------------------
-- 4) PERMISOS RBAC: Seccion 6 - Plantillas Tarjetas
-- ------------------------------------------
INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_tarjeta.crear', 'Crear plantillas de tarjeta', 'plantilla_tarjeta', 'crear', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_tarjeta.crear');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_tarjeta.editar', 'Editar plantillas de tarjeta', 'plantilla_tarjeta', 'editar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_tarjeta.editar');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_tarjeta.eliminar', 'Eliminar plantillas de tarjeta', 'plantilla_tarjeta', 'eliminar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_tarjeta.eliminar');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_tarjeta.asociar', 'Asociar plantillas de lista a plantillas de tarjeta', 'plantilla_tarjeta', 'asociar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_tarjeta.asociar');

-- ------------------------------------------
-- 5) PERMISOS RBAC: Seccion 7 - Plantillas Tareas
-- ------------------------------------------
INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_lista.crear', 'Crear plantillas de listado de tareas', 'plantilla_lista', 'crear', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_lista.crear');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_lista.editar', 'Editar plantillas de listado de tareas', 'plantilla_lista', 'editar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_lista.editar');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_lista.eliminar', 'Eliminar plantillas de listado de tareas', 'plantilla_lista', 'eliminar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_lista.eliminar');

-- ------------------------------------------
-- 6) ASIGNACION AL ROL ADMINISTRADOR (Id_role = 1)
-- ------------------------------------------
INSERT IGNORE INTO `role_permiso` (`Id_role`, `Id_permiso`, `Estado`)
SELECT 1, p.Id_permiso, 1
FROM `permisos` p
WHERE p.Nombre IN (
    'plantilla_tarjeta.crear',
    'plantilla_tarjeta.editar',
    'plantilla_tarjeta.eliminar',
    'plantilla_tarjeta.asociar',
    'plantilla_lista.crear',
    'plantilla_lista.editar',
    'plantilla_lista.eliminar'
);

-- ==========================================
-- FIN DEL SCRIPT
-- ==========================================

-- Incluye:
--   - tablero_tarjetas_plantilla_lista_rel
--       (vincula plantillas de tarjeta con plantillas de lista)
--   - Seccion 6: Permisos Plantillas Tarjetas
--       crear, editar, eliminar, asociar
--   - Seccion 7: Permisos Plantillas Tareas
--       crear, editar, eliminar
--   - Asignacion al Rol Administrador (Id_role = 1)
-- ==========================================

-- ------------------------------------------
-- 1) TABLA: tablero_tarjetas_plantilla_lista_rel
--    Vincula una plantilla de tarjeta con una o varias
--    plantillas de lista. Al usar la plantilla de tarjeta,
--    se crean automaticamente los listados asociados.
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS `tablero_tarjetas_plantilla_lista_rel` (
    `Id_rel`               INT AUTO_INCREMENT PRIMARY KEY,
    `Id_plantilla_tarjeta` INT NOT NULL,
    `Id_plantilla_lista`   INT NOT NULL,
    `Orden`                INT NOT NULL DEFAULT 0,
    INDEX `idx_rel_tarjeta` (`Id_plantilla_tarjeta`),
    INDEX `idx_rel_lista`   (`Id_plantilla_lista`),
    UNIQUE KEY `uk_rel` (`Id_plantilla_tarjeta`, `Id_plantilla_lista`),
    CONSTRAINT `fk_rel_plantilla_tarjeta`
        FOREIGN KEY (`Id_plantilla_tarjeta`)
        REFERENCES `tablero_tarjetas_plantilla`(`Id_plantilla_tarjeta`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_rel_plantilla_lista`
        FOREIGN KEY (`Id_plantilla_lista`)
        REFERENCES `tablero_tarjetas_tareas_plantilla`(`Id_plantilla_lista`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- 2) PERMISOS: Seccion 6 - Plantillas Tarjetas
-- ------------------------------------------
INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_tarjeta.crear', 'Crear plantillas de tarjeta', 'plantilla_tarjeta', 'crear', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_tarjeta.crear');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_tarjeta.editar', 'Editar plantillas de tarjeta', 'plantilla_tarjeta', 'editar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_tarjeta.editar');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_tarjeta.eliminar', 'Eliminar plantillas de tarjeta', 'plantilla_tarjeta', 'eliminar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_tarjeta.eliminar');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_tarjeta.asociar', 'Asociar plantillas de lista a plantillas de tarjeta', 'plantilla_tarjeta', 'asociar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_tarjeta.asociar');

-- ------------------------------------------
-- 3) PERMISOS: Seccion 7 - Plantillas Tareas
-- ------------------------------------------
INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_lista.crear', 'Crear plantillas de listado de tareas', 'plantilla_lista', 'crear', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_lista.crear');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_lista.editar', 'Editar plantillas de listado de tareas', 'plantilla_lista', 'editar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_lista.editar');

INSERT INTO `permisos` (`Nombre`, `Descripcion`, `Modulo`, `Accion`, `Estado`)
SELECT 'plantilla_lista.eliminar', 'Eliminar plantillas de listado de tareas', 'plantilla_lista', 'eliminar', 1
WHERE NOT EXISTS (SELECT 1 FROM `permisos` WHERE `Nombre` = 'plantilla_lista.eliminar');

-- ------------------------------------------
-- 4) ASIGNACION AL ROL ADMINISTRADOR (Id_role = 1)
-- ------------------------------------------
INSERT IGNORE INTO `role_permiso` (`Id_role`, `Id_permiso`, `Estado`)
SELECT 1, p.Id_permiso, 1
FROM `permisos` p
WHERE p.Nombre IN (
    'plantilla_tarjeta.crear',
    'plantilla_tarjeta.editar',
    'plantilla_tarjeta.eliminar',
    'plantilla_tarjeta.asociar',
    'plantilla_lista.crear',
    'plantilla_lista.editar',
    'plantilla_lista.eliminar'
);

-- ==========================================
-- FIN DEL SCRIPT
-- ==========================================
