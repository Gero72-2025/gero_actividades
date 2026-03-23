-- ==========================================
-- SCRIPT: Sistema de Recuperación de Contraseña
-- Fecha: 2026-03-18
-- Descripción: Agrega campos para el flujo de recuperación
--              de contraseña con token y contraseña temporal.
-- ==========================================

-- Agregar campo reset_token: almacena el token único de recuperación (SHA-256 hex = 64 chars)
ALTER TABLE `usuario`
    ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(64) NULL DEFAULT NULL COMMENT 'Token único para recuperación de contraseña'
    AFTER `pass`;

-- Agregar campo token_expira: fecha/hora de expiración del token (24 horas desde su generación)
ALTER TABLE `usuario`
    ADD COLUMN IF NOT EXISTS `token_expira` DATETIME NULL DEFAULT NULL COMMENT 'Fecha y hora de expiración del token de recuperación'
    AFTER `reset_token`;

-- Agregar campo password_temp: bandera que obliga al usuario a cambiar su contraseña al iniciar sesión
ALTER TABLE `usuario`
    ADD COLUMN IF NOT EXISTS `password_temp` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = la contraseña es temporal y debe cambiarse al ingresar'
    AFTER `token_expira`;

-- Índice para búsquedas rápidas por token (el token se limpia después de usarse, NULL no indexado)
ALTER TABLE `usuario`
    ADD INDEX IF NOT EXISTS `idx_usuario_reset_token` (`reset_token`);

-- ==========================================
-- VERIFICACIÓN: mostrar columnas agregadas
-- ==========================================
SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM
    INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuario'
    AND COLUMN_NAME IN ('reset_token', 'token_expira', 'password_temp')
ORDER BY
    ORDINAL_POSITION;
