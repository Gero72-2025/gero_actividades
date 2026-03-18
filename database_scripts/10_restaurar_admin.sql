-- ==========================================
-- SCRIPT: Restaurar Permisos del Administrador
-- ==========================================
-- Propósito: Restaurar todos los permisos del usuario
--            Administrador (Id_usuario = 1, admin@admin.com).
-- Incluye:
--   1. Asegurar que el usuario 1 tiene el rol Administrador
--   2. Asegurar que el rol Administrador tiene TODOS los permisos
--   3. Restaurar permisos granulares en TODOS los tableros
-- ==========================================

-- ------------------------------------------
-- 1) Asegurar rol Administrador en usuario 1
-- ------------------------------------------
INSERT INTO `usuario_role` (`Id_usuario`, `Id_role`, `Estado`)
VALUES (1, 1, 1)
ON DUPLICATE KEY UPDATE `Estado` = 1;

-- ------------------------------------------
-- 2) Asegurar que el rol Administrador (Id_role=1)
--    tiene TODOS los permisos activos
-- ------------------------------------------
INSERT IGNORE INTO `role_permiso` (`Id_role`, `Id_permiso`, `Estado`)
SELECT 1, p.Id_permiso, 1
FROM `permisos` p
WHERE p.Estado = 1;

-- Reactivar cualquier permiso que esté inactivo para el rol Administrador
UPDATE `role_permiso`
SET `Estado` = 1
WHERE `Id_role` = 1;

-- ------------------------------------------
-- 3) Restaurar permisos granulares (tablero_usuario_permiso)
--    para el usuario 1 en TODOS los tableros activos
-- ------------------------------------------
INSERT INTO `tablero_usuario_permiso` (
    `Id_tablero`, `Id_usuario`,
    `Permiso_ver`, `Permiso_crear`, `Permiso_editar`, `Permiso_eliminar`,
    `Permiso_tablero_ver`, `Permiso_tablero_crear`, `Permiso_tablero_editar`,
    `Permiso_tablero_eliminar`, `Permiso_tablero_asignar`,
    `Permiso_tarjeta_ver`, `Permiso_tarjeta_crear`, `Permiso_tarjeta_editar`,
    `Permiso_tarjeta_eliminar`, `Permiso_tarjeta_asignar`,
    `Permiso_lista_crear`, `Permiso_lista_editar`, `Permiso_lista_eliminar`,
    `Permiso_tarea_crear`, `Permiso_tarea_editar`, `Permiso_tarea_eliminar`,
    `Permiso_tarea_tiempo_editar`,
    `Estado`
)
SELECT
    t.Id_tablero, 1,
    1, 1, 1, 1,
    1, 1, 1, 1, 1,
    1, 1, 1, 1, 1,
    1, 1, 1,
    1, 1, 1, 1,
    1
FROM `tablero` t
WHERE t.Estado = 1
ON DUPLICATE KEY UPDATE
    `Permiso_ver`               = 1,
    `Permiso_crear`             = 1,
    `Permiso_editar`            = 1,
    `Permiso_eliminar`          = 1,
    `Permiso_tablero_ver`       = 1,
    `Permiso_tablero_crear`     = 1,
    `Permiso_tablero_editar`    = 1,
    `Permiso_tablero_eliminar`  = 1,
    `Permiso_tablero_asignar`   = 1,
    `Permiso_tarjeta_ver`       = 1,
    `Permiso_tarjeta_crear`     = 1,
    `Permiso_tarjeta_editar`    = 1,
    `Permiso_tarjeta_eliminar`  = 1,
    `Permiso_tarjeta_asignar`   = 1,
    `Permiso_lista_crear`       = 1,
    `Permiso_lista_editar`      = 1,
    `Permiso_lista_eliminar`    = 1,
    `Permiso_tarea_crear`       = 1,
    `Permiso_tarea_editar`      = 1,
    `Permiso_tarea_eliminar`    = 1,
    `Permiso_tarea_tiempo_editar` = 1,
    `Estado`                    = 1;

-- ==========================================
-- FIN DEL SCRIPT
-- ==========================================
