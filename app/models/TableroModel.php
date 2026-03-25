<?php
class TableroModel {
    private $db;
    private $schemaCache = [];

    public function __construct(){
        $this->db = new Database;
    }

    private function tableExists($tableName){
        $cacheKey = 'table:' . strtolower((string)$tableName);
        if(isset($this->schemaCache[$cacheKey])){
            return $this->schemaCache[$cacheKey];
        }

        try {
            $this->db->query('SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name');
            $this->db->bind(':table_name', (string)$tableName);
            $row = $this->db->single();
            $this->schemaCache[$cacheKey] = $row && (int)$row->total > 0;
        } catch(Throwable $e){
            $this->schemaCache[$cacheKey] = false;
        }

        return $this->schemaCache[$cacheKey];
    }

    private function columnExists($tableName, $columnName){
        $cacheKey = 'column:' . strtolower((string)$tableName . ':' . (string)$columnName);
        if(isset($this->schemaCache[$cacheKey])){
            return $this->schemaCache[$cacheKey];
        }

        try {
            $this->db->query('SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name');
            $this->db->bind(':table_name', (string)$tableName);
            $this->db->bind(':column_name', (string)$columnName);
            $row = $this->db->single();
            $this->schemaCache[$cacheKey] = $row && (int)$row->total > 0;
        } catch(Throwable $e){
            $this->schemaCache[$cacheKey] = false;
        }

        return $this->schemaCache[$cacheKey];
    }

    public function getTablerosByUsuario($id_usuario){
        $this->db->query('
            SELECT DISTINCT t.*
            FROM tablero t
            LEFT JOIN tablero_usuario_permiso tup
                ON tup.Id_tablero = t.Id_tablero
               AND tup.Id_usuario = :id_usuario
               AND tup.Estado = 1
            WHERE t.Estado = 1
              AND (
                    t.Id_usuario_responsable = :id_usuario_responsable
                    OR (tup.Permiso_ver = 1)
              )
            ORDER BY t.Nombre ASC
        ');
        $this->db->bind(':id_usuario', (int)$id_usuario);
        $this->db->bind(':id_usuario_responsable', (int)$id_usuario);
        return $this->db->resultSet();
    }

    public function getTableroById($id_tablero){
        $this->db->query('SELECT * FROM tablero WHERE Id_tablero = :id_tablero AND Estado = 1');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        return $this->db->single();
    }

    public function addTablero($data){
        $this->db->query('INSERT INTO tablero (Nombre, Descripcion, Id_usuario_responsable, Estado) VALUES (:nombre, :descripcion, :id_usuario_responsable, 1)');
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':descripcion', $data['descripcion']);
        $this->db->bind(':id_usuario_responsable', (int)$data['id_usuario_responsable']);
        $ok = $this->db->execute();

        if(!$ok){
            return false;
        }

        $this->db->query('SELECT LAST_INSERT_ID() AS id_tablero');
        $row = $this->db->single();
        return $row ? (int)$row->id_tablero : false;
    }

    public function addOrUpdateUsuarioPermisoTablero($data){
        $this->db->query('
            INSERT INTO tablero_usuario_permiso (
                Id_tablero,
                Id_usuario,
                Permiso_ver,
                Permiso_crear,
                Permiso_editar,
                Permiso_eliminar,
                Permiso_tablero_ver,
                Permiso_tablero_crear,
                Permiso_tablero_editar,
                Permiso_tablero_eliminar,
                Permiso_tablero_asignar,
                Permiso_columna_crear,
                Permiso_columna_editar,
                Permiso_columna_eliminar,
                Permiso_columna_ordenar,
                Permiso_tarjeta_ver,
                Permiso_tarjeta_crear,
                Permiso_tarjeta_editar,
                Permiso_tarjeta_mover,
                Permiso_tarjeta_eliminar,
                Permiso_tarjeta_asignar,
                Permiso_lista_crear,
                Permiso_lista_editar,
                Permiso_lista_eliminar,
                Permiso_tarea_crear,
                Permiso_tarea_editar,
                Permiso_tarea_eliminar,
                Permiso_tarea_tiempo_editar,
                Estado
            ) VALUES (
                :id_tablero,
                :id_usuario,
                :permiso_ver,
                :permiso_crear,
                :permiso_editar,
                :permiso_eliminar,
                :permiso_tablero_ver,
                :permiso_tablero_crear,
                :permiso_tablero_editar,
                :permiso_tablero_eliminar,
                :permiso_tablero_asignar,
                :permiso_columna_crear,
                :permiso_columna_editar,
                :permiso_columna_eliminar,
                :permiso_columna_ordenar,
                :permiso_tarjeta_ver,
                :permiso_tarjeta_crear,
                :permiso_tarjeta_editar,
                :permiso_tarjeta_mover,
                :permiso_tarjeta_eliminar,
                :permiso_tarjeta_asignar,
                :permiso_lista_crear,
                :permiso_lista_editar,
                :permiso_lista_eliminar,
                :permiso_tarea_crear,
                :permiso_tarea_editar,
                :permiso_tarea_eliminar,
                :permiso_tarea_tiempo_editar,
                1
            )
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
                Estado = 1,
                Fecha_actualizacion = NOW()
        ');
        $resolvePermission = function($granularKey, $fallbackKey = null, $default = 0) use ($data){
            if(array_key_exists($granularKey, $data)){
                return !empty($data[$granularKey]) ? 1 : 0;
            }

            if($fallbackKey !== null && array_key_exists($fallbackKey, $data)){
                return !empty($data[$fallbackKey]) ? 1 : 0;
            }

            return (int)$default;
        };

        $permTableroVer = $resolvePermission('permiso_tablero_ver', 'permiso_ver', 1);
        $permTableroCrear = $resolvePermission('permiso_tablero_crear', 'permiso_crear', 0);
        $permTableroEditar = $resolvePermission('permiso_tablero_editar', 'permiso_editar', 0);
        $permTableroEliminar = $resolvePermission('permiso_tablero_eliminar', 'permiso_eliminar', 0);
        $permTableroAsignar = $resolvePermission('permiso_tablero_asignar', 'permiso_editar', 0);
        $permColumnaCrear = $resolvePermission('permiso_columna_crear', 'permiso_tablero_editar', 0);
        $permColumnaEditar = $resolvePermission('permiso_columna_editar', 'permiso_tablero_editar', 0);
        $permColumnaEliminar = $resolvePermission('permiso_columna_eliminar', 'permiso_tablero_eliminar', 0);
        $permColumnaOrdenar = $resolvePermission('permiso_columna_ordenar', 'permiso_tablero_editar', 0);

        $permTarjetaVer = $resolvePermission('permiso_tarjeta_ver', 'permiso_ver', 1);
        $permTarjetaCrear = $resolvePermission('permiso_tarjeta_crear', 'permiso_crear', 0);
        $permTarjetaEditar = $resolvePermission('permiso_tarjeta_editar', 'permiso_editar', 0);
        $permTarjetaMover = $resolvePermission('permiso_tarjeta_mover', 'permiso_tarjeta_editar', 0);
        $permTarjetaEliminar = $resolvePermission('permiso_tarjeta_eliminar', 'permiso_eliminar', 0);
        $permTarjetaAsignar = $resolvePermission('permiso_tarjeta_asignar', 'permiso_editar', 0);

        $permListaCrear = $resolvePermission('permiso_lista_crear', 'permiso_editar', 0);
        $permListaEditar = $resolvePermission('permiso_lista_editar', 'permiso_editar', 0);
        $permListaEliminar = $resolvePermission('permiso_lista_eliminar', 'permiso_editar', 0);

        $permTareaCrear = $resolvePermission('permiso_tarea_crear', 'permiso_editar', 0);
        $permTareaEditar = $resolvePermission('permiso_tarea_editar', 'permiso_editar', 0);
        $permTareaEliminar = $resolvePermission('permiso_tarea_eliminar', 'permiso_editar', 0);
        $permTareaTiempoEditar = $resolvePermission('permiso_tarea_tiempo_editar', 'permiso_tiempo_editar', 0);

        $this->db->bind(':id_tablero', (int)$data['id_tablero']);
        $this->db->bind(':id_usuario', (int)$data['id_usuario']);
        $this->db->bind(':permiso_ver', $permTableroVer);
        $this->db->bind(':permiso_crear', $permTarjetaCrear);
        $this->db->bind(':permiso_editar', $permTarjetaEditar);
        $this->db->bind(':permiso_eliminar', $permTarjetaEliminar);

        $this->db->bind(':permiso_tablero_ver', $permTableroVer);
        $this->db->bind(':permiso_tablero_crear', $permTableroCrear);
        $this->db->bind(':permiso_tablero_editar', $permTableroEditar);
        $this->db->bind(':permiso_tablero_eliminar', $permTableroEliminar);
        $this->db->bind(':permiso_tablero_asignar', $permTableroAsignar);
        $this->db->bind(':permiso_columna_crear', $permColumnaCrear);
        $this->db->bind(':permiso_columna_editar', $permColumnaEditar);
        $this->db->bind(':permiso_columna_eliminar', $permColumnaEliminar);
        $this->db->bind(':permiso_columna_ordenar', $permColumnaOrdenar);
        $this->db->bind(':permiso_tarjeta_ver', $permTarjetaVer);
        $this->db->bind(':permiso_tarjeta_crear', $permTarjetaCrear);
        $this->db->bind(':permiso_tarjeta_editar', $permTarjetaEditar);
        $this->db->bind(':permiso_tarjeta_mover', $permTarjetaMover);
        $this->db->bind(':permiso_tarjeta_eliminar', $permTarjetaEliminar);
        $this->db->bind(':permiso_tarjeta_asignar', $permTarjetaAsignar);
        $this->db->bind(':permiso_lista_crear', $permListaCrear);
        $this->db->bind(':permiso_lista_editar', $permListaEditar);
        $this->db->bind(':permiso_lista_eliminar', $permListaEliminar);
        $this->db->bind(':permiso_tarea_crear', $permTareaCrear);
        $this->db->bind(':permiso_tarea_editar', $permTareaEditar);
        $this->db->bind(':permiso_tarea_eliminar', $permTareaEliminar);
        $this->db->bind(':permiso_tarea_tiempo_editar', $permTareaTiempoEditar);
        return $this->db->execute();
    }

    public function getPermisosUsuarioTablero($id_tablero, $id_usuario){
        $tablero = $this->getTableroById($id_tablero);
        if(!$tablero){
            return null;
        }

        $this->db->query('
            SELECT
                Permiso_ver,
                Permiso_crear,
                Permiso_editar,
                Permiso_eliminar,
                Permiso_tablero_ver,
                Permiso_tablero_crear,
                Permiso_tablero_editar,
                Permiso_tablero_eliminar,
                Permiso_tablero_asignar,
                Permiso_columna_crear,
                Permiso_columna_editar,
                Permiso_columna_eliminar,
                Permiso_columna_ordenar,
                Permiso_tarjeta_ver,
                Permiso_tarjeta_crear,
                Permiso_tarjeta_editar,
                Permiso_tarjeta_mover,
                Permiso_tarjeta_eliminar,
                Permiso_tarjeta_asignar,
                Permiso_lista_crear,
                Permiso_lista_editar,
                Permiso_lista_eliminar,
                Permiso_tarea_crear,
                Permiso_tarea_editar,
                Permiso_tarea_eliminar,
                Permiso_tarea_tiempo_editar
            FROM tablero_usuario_permiso
            WHERE Id_tablero = :id_tablero
              AND Id_usuario = :id_usuario
              AND Estado = 1
            LIMIT 1
        ');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        $this->db->bind(':id_usuario', (int)$id_usuario);
        $permisos = $this->db->single();

        if($permisos){
            return $permisos;
        }

        if((int)$tablero->Id_usuario_responsable === (int)$id_usuario){
            return (object)[
                'Permiso_ver' => 1,
                'Permiso_crear' => 1,
                'Permiso_editar' => 1,
                'Permiso_eliminar' => 1,
                'Permiso_tablero_ver' => 1,
                'Permiso_tablero_crear' => 1,
                'Permiso_tablero_editar' => 1,
                'Permiso_tablero_eliminar' => 1,
                'Permiso_tablero_asignar' => 1,
                'Permiso_columna_crear' => 1,
                'Permiso_columna_editar' => 1,
                'Permiso_columna_eliminar' => 1,
                'Permiso_columna_ordenar' => 1,
                'Permiso_tarjeta_ver' => 1,
                'Permiso_tarjeta_crear' => 1,
                'Permiso_tarjeta_editar' => 1,
                'Permiso_tarjeta_mover' => 1,
                'Permiso_tarjeta_eliminar' => 1,
                'Permiso_tarjeta_asignar' => 1,
                'Permiso_lista_crear' => 1,
                'Permiso_lista_editar' => 1,
                'Permiso_lista_eliminar' => 1,
                'Permiso_tarea_crear' => 1,
                'Permiso_tarea_editar' => 1,
                'Permiso_tarea_eliminar' => 1,
                'Permiso_tarea_tiempo_editar' => 1
            ];
        }

        return null;
    }

    public function getUsuariosActivos(){
        $this->db->query('SELECT Id_usuario, email FROM usuario WHERE estado_usuario = 1 ORDER BY email ASC');
        return $this->db->resultSet();
    }

    public function getUsuariosAsignadosTablero($id_tablero){
        $this->db->query('
            SELECT
                u.Id_usuario,
                u.email,
                p.Id_contrato,
                tup.Permiso_ver,
                tup.Permiso_crear,
                tup.Permiso_editar,
                tup.Permiso_eliminar,
                tup.Permiso_tablero_ver,
                tup.Permiso_tablero_crear,
                tup.Permiso_tablero_editar,
                tup.Permiso_tablero_eliminar,
                tup.Permiso_tablero_asignar,
                tup.Permiso_columna_crear,
                tup.Permiso_columna_editar,
                tup.Permiso_columna_eliminar,
                tup.Permiso_columna_ordenar,
                tup.Permiso_tarjeta_ver,
                tup.Permiso_tarjeta_crear,
                tup.Permiso_tarjeta_editar,
                tup.Permiso_tarjeta_mover,
                tup.Permiso_tarjeta_eliminar,
                tup.Permiso_tarjeta_asignar,
                tup.Permiso_lista_crear,
                tup.Permiso_lista_editar,
                tup.Permiso_lista_eliminar,
                tup.Permiso_tarea_crear,
                tup.Permiso_tarea_editar,
                tup.Permiso_tarea_eliminar,
                tup.Permiso_tarea_tiempo_editar
            FROM tablero_usuario_permiso tup
            INNER JOIN usuario u ON u.Id_usuario = tup.Id_usuario
            LEFT JOIN personal p ON p.Id_usuario = u.Id_usuario AND p.Estado = 1
            WHERE tup.Id_tablero = :id_tablero
              AND tup.Estado = 1
              AND u.estado_usuario = 1
            ORDER BY u.email ASC
        ');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        return $this->db->resultSet();
    }

    public function getPrioridadesByTablero($id_tablero){
        $this->db->query('
            SELECT
                p.*,
                (
                    SELECT COUNT(*)
                    FROM tablero_tarjetas t
                    WHERE t.Id_prioridad = p.Id_prioridad
                      AND t.Estado = 1
                ) AS Total_tarjetas
            FROM tablero_prioridades p
            WHERE p.Id_tablero = :id_tablero
              AND p.Estado = 1
            ORDER BY p.Valor DESC, p.Id_prioridad ASC
        ');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        return $this->db->resultSet();
    }

    public function getPrioridadById($id_prioridad){
        $this->db->query('SELECT * FROM tablero_prioridades WHERE Id_prioridad = :id_prioridad AND Estado = 1');
        $this->db->bind(':id_prioridad', (int)$id_prioridad);
        return $this->db->single();
    }

    public function getPrioridadByNombre($id_tablero, $nombre, $excludeId = null, $includeInactive = false){
        $sql = '
            SELECT *
            FROM tablero_prioridades
            WHERE Id_tablero = :id_tablero
              AND Nombre = :nombre
        ';

        if(!$includeInactive){
            $sql .= ' AND Estado = 1';
        }

        if($excludeId !== null){
            $sql .= ' AND Id_prioridad <> :exclude_id';
        }

        $sql .= ' LIMIT 1';

        $this->db->query($sql);
        $this->db->bind(':id_tablero', (int)$id_tablero);
        $this->db->bind(':nombre', $nombre);
        if($excludeId !== null){
            $this->db->bind(':exclude_id', (int)$excludeId);
        }

        return $this->db->single();
    }

    public function addPrioridad($data){
        $existing = $this->getPrioridadByNombre((int)$data['id_tablero'], $data['nombre'], null, true);
        if($existing){
            if((int)$existing->Estado === 1){
                return false;
            }

            $this->db->query('
                UPDATE tablero_prioridades
                SET
                    Valor = :valor,
                    Color = :color,
                    Estado = 1,
                    Fecha_actualizacion = NOW()
                WHERE Id_prioridad = :id_prioridad
            ');
            $this->db->bind(':valor', (int)$data['valor']);
            $this->db->bind(':color', $data['color']);
            $this->db->bind(':id_prioridad', (int)$existing->Id_prioridad);

            if(!$this->db->execute()){
                return false;
            }

            return (int)$existing->Id_prioridad;
        }

        $this->db->query('INSERT INTO tablero_prioridades (Id_tablero, Nombre, Valor, Color, Estado) VALUES (:id_tablero, :nombre, :valor, :color, 1)');
        $this->db->bind(':id_tablero', (int)$data['id_tablero']);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':valor', (int)$data['valor']);
        $this->db->bind(':color', $data['color']);

        if(!$this->db->execute()){
            return false;
        }

        $this->db->query('SELECT LAST_INSERT_ID() AS id_prioridad');
        $row = $this->db->single();
        return $row ? (int)$row->id_prioridad : false;
    }

    public function updatePrioridad($id_prioridad, $nombre, $valor, $color){
        $this->db->query('
            UPDATE tablero_prioridades
            SET
                Nombre = :nombre,
                Valor = :valor,
                Color = :color,
                Fecha_actualizacion = NOW()
            WHERE Id_prioridad = :id_prioridad
              AND Estado = 1
        ');
        $this->db->bind(':nombre', $nombre);
        $this->db->bind(':valor', (int)$valor);
        $this->db->bind(':color', $color);
        $this->db->bind(':id_prioridad', (int)$id_prioridad);
        return $this->db->execute();
    }

    public function deletePrioridad($id_prioridad){
        $this->db->query('UPDATE tablero_prioridades SET Estado = 0, Fecha_actualizacion = NOW() WHERE Id_prioridad = :id_prioridad AND Estado = 1');
        $this->db->bind(':id_prioridad', (int)$id_prioridad);
        return $this->db->execute();
    }

    public function countTarjetasActivasByPrioridad($id_prioridad){
        $this->db->query('SELECT COUNT(*) AS total FROM tablero_tarjetas WHERE Id_prioridad = :id_prioridad AND Estado = 1');
        $this->db->bind(':id_prioridad', (int)$id_prioridad);
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    public function getEtiquetasByTablero($id_tablero){
        $this->db->query('
            SELECT
                e.*,
                (
                    SELECT COUNT(*)
                    FROM tablero_tarjeta_etiqueta te
                    INNER JOIN tablero_tarjetas t ON t.Id_tarjeta = te.Id_tarjeta
                    WHERE te.Id_etiqueta = e.Id_etiqueta
                      AND te.Estado = 1
                      AND t.Estado = 1
                ) AS Total_tarjetas
            FROM tablero_etiquetas e
            WHERE e.Id_tablero = :id_tablero
              AND e.Estado = 1
            ORDER BY e.Nombre ASC, e.Id_etiqueta ASC
        ');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        return $this->db->resultSet();
    }

    public function getEtiquetaById($id_etiqueta){
        $this->db->query('SELECT * FROM tablero_etiquetas WHERE Id_etiqueta = :id_etiqueta AND Estado = 1');
        $this->db->bind(':id_etiqueta', (int)$id_etiqueta);
        return $this->db->single();
    }

    public function addEtiqueta($data){
        $this->db->query('INSERT INTO tablero_etiquetas (Id_tablero, Nombre, Color, Estado) VALUES (:id_tablero, :nombre, :color, 1)');
        $this->db->bind(':id_tablero', (int)$data['id_tablero']);
        $this->db->bind(':nombre', $data['nombre'] !== '' ? $data['nombre'] : null);
        $this->db->bind(':color', $data['color']);

        if(!$this->db->execute()){
            return false;
        }

        $this->db->query('SELECT LAST_INSERT_ID() AS id_etiqueta');
        $row = $this->db->single();
        return $row ? (int)$row->id_etiqueta : false;
    }

    public function updateEtiqueta($id_etiqueta, $nombre, $color){
        $this->db->query('
            UPDATE tablero_etiquetas
            SET
                Nombre = :nombre,
                Color = :color,
                Fecha_actualizacion = NOW()
            WHERE Id_etiqueta = :id_etiqueta
              AND Estado = 1
        ');
        $this->db->bind(':nombre', $nombre !== '' ? $nombre : null);
        $this->db->bind(':color', $color);
        $this->db->bind(':id_etiqueta', (int)$id_etiqueta);
        return $this->db->execute();
    }

    public function deleteEtiqueta($id_etiqueta){
        $this->db->query('UPDATE tablero_etiquetas SET Estado = 0, Fecha_actualizacion = NOW() WHERE Id_etiqueta = :id_etiqueta AND Estado = 1');
        $this->db->bind(':id_etiqueta', (int)$id_etiqueta);
        return $this->db->execute();
    }

    public function countTarjetasActivasByEtiqueta($id_etiqueta){
        $this->db->query('
            SELECT COUNT(*) AS total
            FROM tablero_tarjeta_etiqueta te
            INNER JOIN tablero_tarjetas t ON t.Id_tarjeta = te.Id_tarjeta
            WHERE te.Id_etiqueta = :id_etiqueta
              AND te.Estado = 1
              AND t.Estado = 1
        ');
        $this->db->bind(':id_etiqueta', (int)$id_etiqueta);
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    public function getEtiquetaIdsByTarjeta($id_tarjeta){
        $this->db->query('
            SELECT Id_etiqueta
            FROM tablero_tarjeta_etiqueta
            WHERE Id_tarjeta = :id_tarjeta
              AND Estado = 1
            ORDER BY Id_etiqueta ASC
        ');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);

        $rows = $this->db->resultSet();
        $ids = [];
        foreach($rows as $row){
            $ids[] = (int)$row->Id_etiqueta;
        }
        return $ids;
    }

    public function setEtiquetasTarjeta($id_tarjeta, $etiquetaIds){
        $this->db->query('UPDATE tablero_tarjeta_etiqueta SET Estado = 0, Fecha_actualizacion = NOW() WHERE Id_tarjeta = :id_tarjeta');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        if(!$this->db->execute()){
            return false;
        }

        $etiquetaIds = array_values(array_unique(array_map('intval', is_array($etiquetaIds) ? $etiquetaIds : [])));
        foreach($etiquetaIds as $id_etiqueta){
            if($id_etiqueta <= 0){
                continue;
            }

            $this->db->query('
                INSERT INTO tablero_tarjeta_etiqueta (Id_tarjeta, Id_etiqueta, Estado)
                VALUES (:id_tarjeta, :id_etiqueta, 1)
                ON DUPLICATE KEY UPDATE
                    Estado = 1,
                    Fecha_actualizacion = NOW()
            ');
            $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
            $this->db->bind(':id_etiqueta', (int)$id_etiqueta);

            if(!$this->db->execute()){
                return false;
            }
        }

        return true;
    }

    public function getEtiquetasByTarjetas($id_tarjetas){
        $id_tarjetas = array_values(array_unique(array_map('intval', is_array($id_tarjetas) ? $id_tarjetas : [])));
        $id_tarjetas = array_filter($id_tarjetas, function($id){
            return $id > 0;
        });

        if(empty($id_tarjetas)){
            return [];
        }

        $in = implode(',', $id_tarjetas);
        $this->db->query('
            SELECT
                te.Id_tarjeta,
                e.Id_etiqueta,
                e.Nombre,
                e.Color
            FROM tablero_tarjeta_etiqueta te
            INNER JOIN tablero_etiquetas e
                ON e.Id_etiqueta = te.Id_etiqueta
               AND e.Estado = 1
            WHERE te.Id_tarjeta IN (' . $in . ')
              AND te.Estado = 1
            ORDER BY e.Nombre ASC, e.Id_etiqueta ASC
        ');
        return $this->db->resultSet();
    }

    public function getAsignadosByTarjetas($id_tarjetas){
        $id_tarjetas = array_values(array_unique(array_map('intval', is_array($id_tarjetas) ? $id_tarjetas : [])));
        $id_tarjetas = array_filter($id_tarjetas, function($id){ return $id > 0; });

        if(empty($id_tarjetas)){
            return [];
        }

        $in = implode(',', $id_tarjetas);
        $this->db->query('
            SELECT DISTINCT
                tt.Id_tarjeta,
                d.Id_usuario_asignado,
                u.email AS Usuario_email
            FROM tablero_tarjetas_tarea_detalle d
            INNER JOIN tablero_tarjetas_tarea tt ON tt.Id_tarea = d.Id_tarea
            LEFT JOIN usuario u ON u.Id_usuario = d.Id_usuario_asignado
            WHERE tt.Id_tarjeta IN (' . $in . ')
              AND tt.Estado = 1
              AND d.Estado = 1
              AND d.Id_usuario_asignado IS NOT NULL
            ORDER BY tt.Id_tarjeta ASC, u.email ASC
        ');
        return $this->db->resultSet();
    }

    public function usuarioEstaAsignadoATablero($id_tablero, $id_usuario){
        $this->db->query('
            SELECT COUNT(*) AS total
            FROM tablero_usuario_permiso
            WHERE Id_tablero = :id_tablero
              AND Id_usuario = :id_usuario
              AND Estado = 1
        ');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        $this->db->bind(':id_usuario', (int)$id_usuario);
        $row = $this->db->single();
        return $row && (int)$row->total > 0;
    }

    public function getColumnasActivasByTablero($id_tablero){
        $this->db->query('SELECT * FROM tablero_columnas WHERE Id_tablero = :id_tablero AND Estado = 1 ORDER BY Orden_columna ASC, Id_columna ASC');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        return $this->db->resultSet();
    }

    public function getTarjetasActivasByTablero($id_tablero){
        $this->db->query('
            SELECT
                t.*,
                a.Descripcion_realizada AS Actividad_Descripcion,
                uc.email AS Creador_Email,
                ua.email AS Asignado_Email,
                p.Nombre AS Prioridad_Nombre,
                p.Valor AS Prioridad_Valor,
                p.Color AS Prioridad_Color,
                (
                    SELECT COUNT(*)
                    FROM tablero_tarjetas_tarea_detalle d
                    INNER JOIN tablero_tarjetas_tarea tt ON tt.Id_tarea = d.Id_tarea
                    WHERE tt.Id_tarjeta = t.Id_tarjeta
                      AND tt.Estado = 1
                      AND d.Estado = 1
                ) AS Total_Tareas,
                                (
                                        SELECT COUNT(*)
                                        FROM tablero_tarjetas_tarea tt
                                        WHERE tt.Id_tarjeta = t.Id_tarjeta
                                            AND tt.Estado = 1
                                ) AS Total_Listas_Tareas,
                (
                    SELECT COUNT(*)
                    FROM tablero_tarjetas_tarea_detalle d
                    INNER JOIN tablero_tarjetas_tarea tt ON tt.Id_tarea = d.Id_tarea
                    WHERE tt.Id_tarjeta = t.Id_tarjeta
                      AND tt.Estado = 1
                      AND d.Estado = 1
                      AND d.Completado = 1
                ) AS Total_Tareas_Completadas,
                (
                    SELECT COALESCE(SUM(COALESCE(tdti.duracion_segundos, TIMESTAMPDIFF(SECOND, tdti.inicio_timestamp, NOW()))), 0)
                    FROM tablero_tarjetas_tarea_detalle_tiempo tdti
                    INNER JOIN tablero_tarjetas_tarea_detalle d ON d.Id_tarea_detalle = tdti.Id_tarea_detalle
                    INNER JOIN tablero_tarjetas_tarea tt ON tt.Id_tarea = d.Id_tarea
                    WHERE tt.Id_tarjeta = t.Id_tarjeta
                      AND tdti.Estado = 1
                      AND d.Estado = 1
                      AND tt.Estado = 1
                    ) AS Tiempo_Total_Segundos,
                    (
                      SELECT COUNT(*)
                      FROM tablero_tarjetas_tarea_detalle_tiempo tdti
                      INNER JOIN tablero_tarjetas_tarea_detalle d ON d.Id_tarea_detalle = tdti.Id_tarea_detalle
                      INNER JOIN tablero_tarjetas_tarea tt ON tt.Id_tarea = d.Id_tarea
                      WHERE tt.Id_tarjeta = t.Id_tarjeta
                        AND tdti.fin_timestamp IS NULL
                        AND tdti.Estado = 1
                        AND d.Estado = 1
                        AND tt.Estado = 1
                    ) AS Total_Timers_En_Curso
            FROM tablero_tarjetas t
            LEFT JOIN actividades a ON a.Id_actividad = t.Id_actividad
            INNER JOIN usuario uc ON uc.Id_usuario = t.Id_usuario_creador
            LEFT JOIN usuario ua ON ua.Id_usuario = t.Id_usuario_asignado
                        LEFT JOIN tablero_prioridades p ON p.Id_prioridad = t.Id_prioridad AND p.Estado = 1
            WHERE t.Id_tablero = :id_tablero
              AND t.Estado = 1
            ORDER BY t.Id_columna ASC, t.Posicion ASC, t.Id_tarjeta ASC
        ');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        return $this->db->resultSet();
    }

    public function getActividadesActivas($limit = 200){
        $this->db->query('
            SELECT a.Id_actividad, a.Descripcion_realizada, a.Fecha_ingreso
            FROM actividades a
            WHERE a.Estado = 1
            ORDER BY a.Fecha_ingreso DESC, a.Id_actividad DESC
            LIMIT :limite
        ');
        $this->db->bind(':limite', (int)$limit);
        return $this->db->resultSet();
    }

    public function getAlcancesDisponiblesByTablero($id_tablero){
        $this->db->query('
            SELECT DISTINCT
                al.Id_alcance,
                al.Descripcion,
                al.Id_contrato,
                c.Expediente AS Contrato_Expediente
            FROM alcances al
            INNER JOIN contratos c
                ON c.Id_contrato = al.Id_contrato
            INNER JOIN personal p
                ON p.Id_contrato = c.Id_contrato
               AND p.Estado = 1
               AND p.Id_usuario IS NOT NULL
            INNER JOIN tablero_usuario_permiso tup
                ON tup.Id_tablero = :id_tablero
               AND tup.Id_usuario = p.Id_usuario
               AND tup.Estado = 1
               AND tup.Permiso_ver = 1
            WHERE al.Estado = 1
            ORDER BY c.Expediente ASC, al.Descripcion ASC, al.Id_alcance ASC
        ');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        return $this->db->resultSet();
    }

    private function getContratoPersonalByUsuario($id_usuario){
        $this->db->query('
            SELECT Id_contrato
            FROM personal
            WHERE Id_usuario = :id_usuario
              AND Estado = 1
            LIMIT 1
        ');
        $this->db->bind(':id_usuario', (int)$id_usuario);
        $row = $this->db->single();
        return ($row && !empty($row->Id_contrato)) ? (int)$row->Id_contrato : null;
    }

    public function getContratoPersonalUsuario($id_usuario){
        return $this->getContratoPersonalByUsuario($id_usuario);
    }

    public function getActividadesDisponiblesByTablero($id_tablero, $limit = 300){
        $sql = '
            SELECT
                a.Id_actividad,
                a.Descripcion_realizada,
                a.Fecha_ingreso,
                a.Id_alcance,
                p.Id_contrato,
                p.Id_usuario AS Actividad_Id_usuario,
                p.Nombre_Completo,
                p.Apellido_Completo,
                u.email AS Usuario_Email
            FROM actividades a
            INNER JOIN personal p
                ON p.Id_personal = a.Id_personal
               AND p.Estado = 1
            LEFT JOIN usuario u
                ON u.Id_usuario = p.Id_usuario
               AND u.estado_usuario = 1
            INNER JOIN tablero_usuario_permiso tup
                ON tup.Id_tablero = :id_tablero
               AND tup.Id_usuario = p.Id_usuario
               AND tup.Estado = 1
               AND tup.Permiso_ver = 1
            WHERE a.Estado = 1
        ';

        $sql .= '
            ORDER BY
                COALESCE(p.Apellido_Completo, "") ASC,
                COALESCE(p.Nombre_Completo, "") ASC,
                a.Fecha_ingreso DESC,
                a.Id_actividad DESC
            LIMIT :limite
        ';

        $this->db->query($sql);
        $this->db->bind(':id_tablero', (int)$id_tablero);
        $this->db->bind(':limite', (int)$limit);
        return $this->db->resultSet();
    }

    public function actividadDisponibleParaTableroUsuario($id_actividad, $id_tablero, $id_usuario_actual){
        $id_actividad = (int)$id_actividad;
        if($id_actividad <= 0){
            return false;
        }

        $id_contrato_usuario = $this->getContratoPersonalByUsuario($id_usuario_actual);

        $sql = '
            SELECT COUNT(*) AS total
            FROM actividades a
            INNER JOIN personal p
                ON p.Id_personal = a.Id_personal
               AND p.Estado = 1
            INNER JOIN tablero_usuario_permiso tup
                ON tup.Id_tablero = :id_tablero
               AND tup.Id_usuario = p.Id_usuario
               AND tup.Estado = 1
               AND tup.Permiso_ver = 1
            WHERE a.Id_actividad = :id_actividad
              AND a.Estado = 1
        ';

        if($id_contrato_usuario !== null){
            $sql .= ' AND p.Id_contrato = :id_contrato_usuario';
        } else {
            $sql .= ' AND p.Id_usuario IS NOT NULL';
        }

        $this->db->query($sql);
        $this->db->bind(':id_tablero', (int)$id_tablero);
        $this->db->bind(':id_actividad', $id_actividad);
        if($id_contrato_usuario !== null){
            $this->db->bind(':id_contrato_usuario', (int)$id_contrato_usuario);
        }

        $row = $this->db->single();
        return $row && (int)$row->total > 0;
    }

    public function alcancePerteneceAContratoUsuario($id_alcance, $id_usuario){
        $id_alcance = (int)$id_alcance;
        $id_usuario = (int)$id_usuario;
        if($id_alcance <= 0 || $id_usuario <= 0){
            return false;
        }

        $this->db->query('
            SELECT COUNT(*) AS total
            FROM alcances al
            INNER JOIN personal p
                ON p.Id_contrato = al.Id_contrato
               AND p.Id_usuario = :id_usuario
               AND p.Estado = 1
            WHERE al.Id_alcance = :id_alcance
              AND al.Estado = 1
        ');
        $this->db->bind(':id_usuario', $id_usuario);
        $this->db->bind(':id_alcance', $id_alcance);
        $row = $this->db->single();
        return $row && (int)$row->total > 0;
    }

    public function actividadPerteneceAAlcance($id_actividad, $id_alcance){
        $id_actividad = (int)$id_actividad;
        $id_alcance = (int)$id_alcance;
        if($id_actividad <= 0 || $id_alcance <= 0){
            return false;
        }

        $this->db->query('
            SELECT COUNT(*) AS total
            FROM actividades
            WHERE Id_actividad = :id_actividad
              AND Id_alcance = :id_alcance
              AND Estado = 1
        ');
        $this->db->bind(':id_actividad', $id_actividad);
        $this->db->bind(':id_alcance', $id_alcance);
        $row = $this->db->single();
        return $row && (int)$row->total > 0;
    }

    public function addColumna($data){
        $idTablero = (int)($data['id_tablero'] ?? 0);
        $nombre = (string)($data['nombre'] ?? '');
        $color = (string)($data['color'] ?? '#0d6efd');
        $orden = (int)($data['orden_columna'] ?? 0);

        if($idTablero <= 0 || trim($nombre) === ''){
            return false;
        }

        if($orden <= 0){
            $orden = $this->getSiguienteOrdenColumna($idTablero);
        }

        // Avoid race conditions or stale order values by retrying when unique index collides.
        for($intento = 0; $intento < 3; $intento++){
            try {
                $this->db->query('INSERT INTO tablero_columnas (Id_tablero, Nombre, Color, Orden_columna, Estado) VALUES (:id_tablero, :nombre, :color, :orden, 1)');
                $this->db->bind(':id_tablero', $idTablero);
                $this->db->bind(':nombre', $nombre);
                $this->db->bind(':color', $color);
                $this->db->bind(':orden', $orden);
                return $this->db->execute();
            } catch(PDOException $e){
                $errorCode = (string)$e->getCode();
                $message = (string)$e->getMessage();
                $isOrderDuplicate = $errorCode === '23000' && strpos($message, 'uk_tablero_columna_orden') !== false;

                if($isOrderDuplicate && $intento < 2){
                    $orden = $this->getSiguienteOrdenColumna($idTablero);
                    continue;
                }

                return false;
            }
        }

        return false;
    }

    public function getSiguienteOrdenColumna($id_tablero){
        $this->db->query('SELECT COALESCE(MAX(Orden_columna), 0) + 1 AS siguiente FROM tablero_columnas WHERE Id_tablero = :id_tablero');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        $row = $this->db->single();
        return $row ? (int)$row->siguiente : 1;
    }

    public function addTarjeta($data){
        $this->db->query('
            INSERT INTO tablero_tarjetas (
                Id_tablero,
                Id_columna,
                Id_alcance,
                Id_actividad,
                Id_usuario_creador,
                Id_usuario_asignado,
                Id_prioridad,
                Titulo,
                Descripcion,
                Fecha_inicio,
                Fecha_fin,
                Checklist_json,
                Estado_tarjeta,
                Completado,
                Posicion,
                Estado
            ) VALUES (
                :id_tablero,
                :id_columna,
                :id_alcance,
                :id_actividad,
                :id_usuario_creador,
                :id_usuario_asignado,
                :id_prioridad,
                :titulo,
                :descripcion,
                :fecha_inicio,
                :fecha_fin,
                :checklist_json,
                :estado_tarjeta,
                :completado,
                :posicion,
                1
            )
        ');

        $this->db->bind(':id_tablero', (int)$data['id_tablero']);
        $this->db->bind(':id_columna', (int)$data['id_columna']);
        $this->db->bind(':id_alcance', $data['id_alcance']);
        $this->db->bind(':id_actividad', $data['id_actividad']);
        $this->db->bind(':id_usuario_creador', (int)$data['id_usuario_creador']);
        $this->db->bind(':id_usuario_asignado', $data['id_usuario_asignado']);
        $this->db->bind(':id_prioridad', (int)$data['id_prioridad']);
        $this->db->bind(':titulo', $data['titulo']);
        $this->db->bind(':descripcion', $data['descripcion']);
        $this->db->bind(':fecha_inicio', $data['fecha_inicio']);
        $this->db->bind(':fecha_fin', $data['fecha_fin']);
        $this->db->bind(':checklist_json', $data['checklist_json']);
        $this->db->bind(':estado_tarjeta', $data['estado_tarjeta']);
        $this->db->bind(':completado', !empty($data['completado']) ? 1 : 0);
        $this->db->bind(':posicion', (int)$data['posicion']);

        if(!$this->db->execute()){
            return false;
        }

        $this->db->query('SELECT LAST_INSERT_ID() AS id_tarjeta');
        $row = $this->db->single();
        return $row ? (int)$row->id_tarjeta : false;
    }

    public function getMaxPosicionByColumna($id_columna){
        $this->db->query('SELECT COALESCE(MAX(Posicion), 0) AS max_pos FROM tablero_tarjetas WHERE Id_columna = :id_columna AND Estado = 1');
        $this->db->bind(':id_columna', (int)$id_columna);
        $row = $this->db->single();
        return $row ? (int)$row->max_pos : 0;
    }

    public function moveTarjeta($id_tarjeta, $id_columna, $posicion){
        $this->db->query('UPDATE tablero_tarjetas SET Id_columna = :id_columna, Posicion = :posicion, Fecha_actualizacion = NOW() WHERE Id_tarjeta = :id_tarjeta AND Estado = 1');
        $this->db->bind(':id_columna', (int)$id_columna);
        $this->db->bind(':posicion', (int)$posicion);
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        return $this->db->execute();
    }

    public function updateChecklist($id_tarjeta, $checklist_json){
        $this->db->query('UPDATE tablero_tarjetas SET Checklist_json = :checklist_json, Fecha_actualizacion = NOW() WHERE Id_tarjeta = :id_tarjeta AND Estado = 1');
        $this->db->bind(':checklist_json', $checklist_json);
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        return $this->db->execute();
    }

    public function updateAsignado($id_tarjeta, $id_usuario_asignado){
        $this->db->query('UPDATE tablero_tarjetas SET Id_usuario_asignado = :id_usuario_asignado, Fecha_actualizacion = NOW() WHERE Id_tarjeta = :id_tarjeta AND Estado = 1');
        $this->db->bind(':id_usuario_asignado', $id_usuario_asignado);
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        return $this->db->execute();
    }

    public function updateTarjeta($id_tarjeta, $data){
        $this->db->query('
            UPDATE tablero_tarjetas
            SET
                Id_columna = :id_columna,
                Id_alcance = :id_alcance,
                Id_actividad = :id_actividad,
                Id_usuario_asignado = :id_usuario_asignado,
                Id_prioridad = :id_prioridad,
                Titulo = :titulo,
                Descripcion = :descripcion,
                Fecha_inicio = :fecha_inicio,
                Fecha_fin = :fecha_fin,
                Estado_tarjeta = :estado_tarjeta,
                Completado = :completado,
                Fecha_actualizacion = NOW()
            WHERE Id_tarjeta = :id_tarjeta
              AND Estado = 1
        ');
        $this->db->bind(':id_columna', (int)$data['id_columna']);
        $this->db->bind(':id_alcance', $data['id_alcance']);
        $this->db->bind(':id_actividad', $data['id_actividad']);
        $this->db->bind(':id_usuario_asignado', $data['id_usuario_asignado']);
        $this->db->bind(':id_prioridad', (int)$data['id_prioridad']);
        $this->db->bind(':titulo', $data['titulo']);
        $this->db->bind(':descripcion', $data['descripcion']);
        $this->db->bind(':fecha_inicio', $data['fecha_inicio']);
        $this->db->bind(':fecha_fin', $data['fecha_fin']);
        $this->db->bind(':estado_tarjeta', $data['estado_tarjeta']);
        $this->db->bind(':completado', !empty($data['completado']) ? 1 : 0);
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        return $this->db->execute();
    }

    public function toggleTarjetaCompletado($id_tarjeta, $completado, $estado_tarjeta){
        $this->db->query('
            UPDATE tablero_tarjetas
            SET
                Completado = :completado,
                Estado_tarjeta = :estado_tarjeta,
                Fecha_actualizacion = NOW()
            WHERE Id_tarjeta = :id_tarjeta
              AND Estado = 1
        ');
        $this->db->bind(':completado', $completado ? 1 : 0);
        $this->db->bind(':estado_tarjeta', $estado_tarjeta);
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        return $this->db->execute();
    }

    public function deleteTarjeta($id_tarjeta){
        $this->db->query('
            UPDATE tablero_tarjetas
            SET
                Estado = 0,
                Fecha_actualizacion = NOW()
            WHERE Id_tarjeta = :id_tarjeta
              AND Estado = 1
        ');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        return $this->db->execute();
    }

    public function canDeleteTarjeta($id_tarjeta){
        $this->db->query('
            SELECT
                t.Id_tarjeta,
                t.Id_usuario_asignado,
                t.Id_alcance,
                t.Id_actividad,
                (
                    SELECT COUNT(*)
                    FROM tablero_tarjetas_tarea tt
                    WHERE tt.Id_tarjeta = t.Id_tarjeta
                      AND tt.Estado = 1
                ) AS total_listas,
                (
                    SELECT COUNT(*)
                    FROM tablero_tarjetas_tarea_detalle d
                    INNER JOIN tablero_tarjetas_tarea tt ON tt.Id_tarea = d.Id_tarea
                    WHERE tt.Id_tarjeta = t.Id_tarjeta
                      AND tt.Estado = 1
                      AND d.Estado = 1
                ) AS total_tareas
            FROM tablero_tarjetas t
            WHERE t.Id_tarjeta = :id_tarjeta
              AND t.Estado = 1
            LIMIT 1
        ');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        $row = $this->db->single();

        if(!$row){
            return false;
        }

        $sinAsignado = empty($row->Id_usuario_asignado);
        $sinAlcance = empty($row->Id_alcance);
        $sinActividad = empty($row->Id_actividad);
        $sinListas = (int)$row->total_listas === 0;
        $sinTareas = (int)$row->total_tareas === 0;

        return $sinAsignado && $sinAlcance && $sinActividad && $sinListas && $sinTareas;
    }

    public function getTarjetaById($id_tarjeta){
        $this->db->query('
            SELECT
                t.*,
                a.Descripcion_realizada AS Actividad_Descripcion,
                p.Nombre AS Prioridad_Nombre,
                p.Valor AS Prioridad_Valor,
                p.Color AS Prioridad_Color
            FROM tablero_tarjetas t
            LEFT JOIN actividades a ON a.Id_actividad = t.Id_actividad
            LEFT JOIN tablero_prioridades p ON p.Id_prioridad = t.Id_prioridad AND p.Estado = 1
            WHERE t.Id_tarjeta = :id_tarjeta
              AND t.Estado = 1
        ');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        return $this->db->single();
    }

    public function getColumnaById($id_columna){
        $this->db->query('SELECT * FROM tablero_columnas WHERE Id_columna = :id AND Estado = 1');
        $this->db->bind(':id', (int)$id_columna);
        return $this->db->single();
    }

    public function updateColumna($id_columna, $nombre, $color){
        $this->db->query('UPDATE tablero_columnas SET Nombre = :nombre, Color = :color, Fecha_actualizacion = NOW() WHERE Id_columna = :id AND Estado = 1');
        $this->db->bind(':nombre', $nombre);
        $this->db->bind(':color', $color);
        $this->db->bind(':id', (int)$id_columna);
        return $this->db->execute();
    }

    public function reorderColumnas($id_tablero, $orderedColumnIds = []){
        try {
            $idTablero = (int)$id_tablero;
            $ordered = array_values(array_unique(array_map('intval', is_array($orderedColumnIds) ? $orderedColumnIds : [])));
            if($idTablero <= 0 || empty($ordered)){
                return false;
            }

            $columnas = $this->getColumnasActivasByTablero($idTablero);
            if(empty($columnas) || count($columnas) !== count($ordered)){
                return false;
            }

            $idsActuales = [];
            foreach($columnas as $col){
                $idsActuales[] = (int)$col->Id_columna;
            }
            sort($idsActuales);

            $idsNuevos = $ordered;
            sort($idsNuevos);
            if($idsActuales !== $idsNuevos){
                return false;
            }

            $this->db->query('SELECT Id_columna FROM tablero_columnas WHERE Id_tablero = :id_tablero AND Estado <> 1 ORDER BY COALESCE(NULLIF(Orden_columna, 0), 999999), Id_columna');
            $this->db->bind(':id_tablero', $idTablero);
            $inactivas = $this->db->resultSet();

            $inactiveIds = [];
            foreach($inactivas as $colInactiva){
                $inactiveIds[] = (int)$colInactiva->Id_columna;
            }

            $allIds = array_merge($ordered, $inactiveIds);

            $this->db->query('SELECT COALESCE(MAX(Orden_columna), 0) AS max_orden FROM tablero_columnas WHERE Id_tablero = :id_tablero');
            $this->db->bind(':id_tablero', $idTablero);
            $maxRow = $this->db->single();
            $maxOrden = (int)($maxRow->max_orden ?? 0);
            $temporalBase = $maxOrden + 1000;

            // Primer paso: mover todas las columnas del tablero a un rango temporal unico.
            foreach($allIds as $idx => $idColumna){
                $ordenTemporal = $temporalBase + $idx + 1;
                $this->db->query('UPDATE tablero_columnas SET Orden_columna = :orden, Fecha_actualizacion = NOW() WHERE Id_columna = :id_columna AND Id_tablero = :id_tablero');
                $this->db->bind(':orden', (int)$ordenTemporal);
                $this->db->bind(':id_columna', (int)$idColumna);
                $this->db->bind(':id_tablero', $idTablero);
                if(!$this->db->execute()){
                    return false;
                }
            }

            // Segundo paso: activas al inicio (1..N) en el orden solicitado.
            foreach($ordered as $idx => $idColumna){
                $ordenFinal = $idx + 1;
                $this->db->query('UPDATE tablero_columnas SET Orden_columna = :orden, Fecha_actualizacion = NOW() WHERE Id_columna = :id_columna AND Id_tablero = :id_tablero AND Estado = 1');
                $this->db->bind(':orden', (int)$ordenFinal);
                $this->db->bind(':id_columna', (int)$idColumna);
                $this->db->bind(':id_tablero', $idTablero);
                if(!$this->db->execute()){
                    return false;
                }
            }

            // Tercer paso: inactivas despues de las activas, manteniendo unicidad por tablero.
            foreach($inactiveIds as $idx => $idColumna){
                $ordenFinalInactiva = count($ordered) + $idx + 1;
                $this->db->query('UPDATE tablero_columnas SET Orden_columna = :orden, Fecha_actualizacion = NOW() WHERE Id_columna = :id_columna AND Id_tablero = :id_tablero AND Estado <> 1');
                $this->db->bind(':orden', (int)$ordenFinalInactiva);
                $this->db->bind(':id_columna', (int)$idColumna);
                $this->db->bind(':id_tablero', $idTablero);
                if(!$this->db->execute()){
                    return false;
                }
            }

            return true;
        } catch (Throwable $e) {
            error_log('TableroModel::reorderColumnas error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteColumna($id_columna){
        $this->db->query('UPDATE tablero_columnas SET Estado = 0, Fecha_actualizacion = NOW() WHERE Id_columna = :id');
        $this->db->bind(':id', (int)$id_columna);
        return $this->db->execute();
    }

    public function updateTablero($id_tablero, $nombre, $descripcion){
        $this->db->query('
            UPDATE tablero
            SET
                Nombre = :nombre,
                Descripcion = :descripcion,
                Fecha_actualizacion = NOW()
            WHERE Id_tablero = :id_tablero
              AND Estado = 1
        ');
        $this->db->bind(':nombre', $nombre);
        $this->db->bind(':descripcion', $descripcion !== '' ? $descripcion : null);
        $this->db->bind(':id_tablero', (int)$id_tablero);
        return $this->db->execute();
    }

    public function getTableroDeletionSummary($id_tablero){
        $this->db->query('
            SELECT
                (SELECT COUNT(*) FROM tablero_columnas c WHERE c.Id_tablero = :id_tablero_col AND c.Estado = 1) AS total_columnas,
                (SELECT COUNT(*) FROM tablero_tarjetas t WHERE t.Id_tablero = :id_tablero_tar AND t.Estado = 1) AS total_tarjetas,
                (
                    SELECT COUNT(*)
                    FROM tablero_tarjetas_tarea tt
                    INNER JOIN tablero_tarjetas t ON t.Id_tarjeta = tt.Id_tarjeta
                    WHERE t.Id_tablero = :id_tablero_lista
                      AND t.Estado = 1
                      AND tt.Estado = 1
                ) AS total_listas,
                (
                    SELECT COUNT(*)
                    FROM tablero_tarjetas_tarea_detalle d
                    INNER JOIN tablero_tarjetas_tarea tt ON tt.Id_tarea = d.Id_tarea
                    INNER JOIN tablero_tarjetas t ON t.Id_tarjeta = tt.Id_tarjeta
                    WHERE t.Id_tablero = :id_tablero_tarea
                      AND t.Estado = 1
                      AND tt.Estado = 1
                      AND d.Estado = 1
                ) AS total_tareas
        ');
        $this->db->bind(':id_tablero_col', (int)$id_tablero);
        $this->db->bind(':id_tablero_tar', (int)$id_tablero);
        $this->db->bind(':id_tablero_lista', (int)$id_tablero);
        $this->db->bind(':id_tablero_tarea', (int)$id_tablero);

        $row = $this->db->single();
        return (object)[
            'total_columnas' => $row ? (int)$row->total_columnas : 0,
            'total_tarjetas' => $row ? (int)$row->total_tarjetas : 0,
            'total_listas' => $row ? (int)$row->total_listas : 0,
            'total_tareas' => $row ? (int)$row->total_tareas : 0
        ];
    }

    public function canDeleteTablero($id_tablero){
        $summary = $this->getTableroDeletionSummary((int)$id_tablero);
        return (int)$summary->total_columnas === 0
            && (int)$summary->total_tarjetas === 0
            && (int)$summary->total_listas === 0
            && (int)$summary->total_tareas === 0;
    }

    public function deleteTablero($id_tablero){
        $this->db->query('UPDATE tablero SET Estado = 0, Fecha_actualizacion = NOW() WHERE Id_tablero = :id_tablero AND Estado = 1');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        return $this->db->execute();
    }

    public function countTarjetasActivasByColumna($id_columna){
        $this->db->query('SELECT COUNT(*) AS total FROM tablero_tarjetas WHERE Id_columna = :id_columna AND Estado = 1');
        $this->db->bind(':id_columna', (int)$id_columna);
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    public function getRunningTimer($id_tarjeta, $id_usuario){
        $this->db->query('
            SELECT *
            FROM tablero_tiempos
            WHERE Id_tarjeta = :id_tarjeta
              AND Id_usuario = :id_usuario
              AND fin_timestamp IS NULL
              AND Estado = 1
            ORDER BY Id_tiempo DESC
            LIMIT 1
        ');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        $this->db->bind(':id_usuario', (int)$id_usuario);
        return $this->db->single();
    }

    public function startTimer($id_tarjeta, $id_usuario){
        $running = $this->getRunningTimer($id_tarjeta, $id_usuario);
        if($running){
            return $running;
        }

        $this->db->query('INSERT INTO tablero_tiempos (Id_tarjeta, Id_usuario, inicio_timestamp, Estado) VALUES (:id_tarjeta, :id_usuario, NOW(), 1)');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        $this->db->bind(':id_usuario', (int)$id_usuario);
        $this->db->execute();

        return $this->getRunningTimer($id_tarjeta, $id_usuario);
    }

    public function stopTimer($id_tarjeta, $id_usuario){
        $running = $this->getRunningTimer($id_tarjeta, $id_usuario);
        if(!$running){
            return false;
        }

        $this->db->query('
            UPDATE tablero_tiempos
            SET
                fin_timestamp = NOW(),
                duracion_segundos = TIMESTAMPDIFF(SECOND, inicio_timestamp, NOW())
            WHERE Id_tiempo = :id_tiempo
        ');
        $this->db->bind(':id_tiempo', (int)$running->Id_tiempo);

        if(!$this->db->execute()){
            return false;
        }

        return $running->Id_tiempo;
    }

    public function getTiempoTotalTarjeta($id_tarjeta){
        $this->db->query('
                        SELECT COALESCE(SUM(COALESCE(tdti.duracion_segundos, TIMESTAMPDIFF(SECOND, tdti.inicio_timestamp, NOW()))), 0) AS total_segundos
                        FROM tablero_tarjetas_tarea_detalle_tiempo tdti
                        INNER JOIN tablero_tarjetas_tarea_detalle d ON d.Id_tarea_detalle = tdti.Id_tarea_detalle
                        INNER JOIN tablero_tarjetas_tarea tt ON tt.Id_tarea = d.Id_tarea
                        WHERE tt.Id_tarjeta = :id_tarjeta
                            AND tdti.Estado = 1
                            AND d.Estado = 1
                            AND tt.Estado = 1
        ');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        $row = $this->db->single();
        return $row ? (int)$row->total_segundos : 0;
    }

    public function getTiempoEnCursoTarjeta($id_tarjeta){
        $this->db->query('
            SELECT COALESCE(SUM(TIMESTAMPDIFF(SECOND, tdti.inicio_timestamp, NOW())), 0) AS total_segundos
            FROM tablero_tarjetas_tarea_detalle_tiempo tdti
            INNER JOIN tablero_tarjetas_tarea_detalle d ON d.Id_tarea_detalle = tdti.Id_tarea_detalle
            INNER JOIN tablero_tarjetas_tarea tt ON tt.Id_tarea = d.Id_tarea
            WHERE tt.Id_tarjeta = :id_tarjeta
              AND tdti.fin_timestamp IS NULL
              AND tdti.Estado = 1
              AND d.Estado = 1
              AND tt.Estado = 1
        ');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        $row = $this->db->single();
        return $row ? (int)$row->total_segundos : 0;
    }

    public function tarjetaTieneTimerDetalleEnCurso($id_tarjeta){
        $this->db->query('
            SELECT COUNT(*) AS total
            FROM tablero_tarjetas_tarea_detalle_tiempo tdti
            INNER JOIN tablero_tarjetas_tarea_detalle d ON d.Id_tarea_detalle = tdti.Id_tarea_detalle
            INNER JOIN tablero_tarjetas_tarea tt ON tt.Id_tarea = d.Id_tarea
            WHERE tt.Id_tarjeta = :id_tarjeta
              AND tdti.fin_timestamp IS NULL
              AND tdti.Estado = 1
              AND d.Estado = 1
              AND tt.Estado = 1
        ');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        $row = $this->db->single();
        return $row && (int)$row->total > 0;
    }

    public function getTareasByTarjeta($id_tarjeta){
        $this->db->query('
            SELECT
                tt.Id_tarea,
                tt.Id_tarjeta,
                tt.Nombre_tarea,
                tt.Orden_tarea,
                (
                    SELECT COUNT(*)
                    FROM tablero_tarjetas_tarea_detalle d
                    WHERE d.Id_tarea = tt.Id_tarea
                      AND d.Estado = 1
                ) AS Total_detalles,
                (
                    SELECT COUNT(*)
                    FROM tablero_tarjetas_tarea_detalle d
                    WHERE d.Id_tarea = tt.Id_tarea
                      AND d.Estado = 1
                      AND d.Completado = 1
                ) AS Total_detalles_completados
            FROM tablero_tarjetas_tarea tt
            WHERE tt.Id_tarjeta = :id_tarjeta
              AND tt.Estado = 1
            ORDER BY tt.Orden_tarea ASC, tt.Id_tarea ASC
        ');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        return $this->db->resultSet();
    }

    public function getDetallesByTarea($id_tarea, $id_usuario = null){
                $hasAssignedColumn = $this->columnExists('tablero_tarjetas_tarea_detalle', 'Id_usuario_asignado');
                $hasUserTimeTable = $this->tableExists('tablero_tarjetas_tareas_detalle_tiempo_usuario');

                $assignedUserEmailSql = $hasAssignedColumn
                        ? 'ua.email AS Usuario_asignado_email,'
                        : 'NULL AS Usuario_asignado_email,';
                $assignedUserTimeSql = ($hasAssignedColumn && $hasUserTimeTable)
                        ? '(
                                        SELECT COALESCE(SUM(tttdtu.Tiempo_total_segundos), 0)
                                        FROM tablero_tarjetas_tareas_detalle_tiempo_usuario tttdtu
                                        WHERE tttdtu.Id_tarea_detalle = d.Id_tarea_detalle
                                            AND tttdtu.Id_usuario = d.Id_usuario_asignado
                                            AND tttdtu.Estado = 1
                                ) AS Tiempo_Usuario_Asignado_Segundos,'
                        : '0 AS Tiempo_Usuario_Asignado_Segundos,';

                $assignedJoinSql = $hasAssignedColumn
                        ? 'LEFT JOIN usuario ua ON ua.Id_usuario = d.Id_usuario_asignado'
                        : '';

                $this->db->query('
                        SELECT
                                d.*,
                                u.email AS Usuario_check_email,
                                ' . $assignedUserEmailSql . '
                                (
                                        SELECT COALESCE(SUM(COALESCE(tdti.duracion_segundos, 0)), 0)
                                        FROM tablero_tarjetas_tarea_detalle_tiempo tdti
                                        WHERE tdti.Id_tarea_detalle = d.Id_tarea_detalle
                                            AND tdti.Estado = 1
                                ) AS Tiempo_Total_Segundos,
                                ' . $assignedUserTimeSql . '
                                (
                                        SELECT tdti.inicio_timestamp
                                        FROM tablero_tarjetas_tarea_detalle_tiempo tdti
                                        WHERE tdti.Id_tarea_detalle = d.Id_tarea_detalle
                                            AND tdti.fin_timestamp IS NULL
                                            AND tdti.Estado = 1
                                        ORDER BY tdti.Id_tiempo_detalle DESC
                                        LIMIT 1
                                ) AS Running_inicio,
                                (
                                        SELECT tdti.Id_tiempo_detalle
                                        FROM tablero_tarjetas_tarea_detalle_tiempo tdti
                                        WHERE tdti.Id_tarea_detalle = d.Id_tarea_detalle
                                            AND tdti.fin_timestamp IS NULL
                                            AND tdti.Estado = 1
                                        ORDER BY tdti.Id_tiempo_detalle DESC
                                        LIMIT 1
                                ) AS Running_tiempo_detalle_id,
                                (
                                        SELECT tdti.Id_usuario
                                        FROM tablero_tarjetas_tarea_detalle_tiempo tdti
                                        WHERE tdti.Id_tarea_detalle = d.Id_tarea_detalle
                                            AND tdti.fin_timestamp IS NULL
                                            AND tdti.Estado = 1
                                        ORDER BY tdti.Id_tiempo_detalle DESC
                                        LIMIT 1
                                ) AS Running_usuario_id
                        FROM tablero_tarjetas_tarea_detalle d
                        LEFT JOIN usuario u ON u.Id_usuario = d.Id_usuario_check
                        ' . $assignedJoinSql . '
                        WHERE d.Id_tarea = :id_tarea
                            AND d.Estado = 1
                        ORDER BY d.Orden_detalle ASC, d.Id_tarea_detalle ASC
                ');
        $this->db->bind(':id_tarea', (int)$id_tarea);
        return $this->db->resultSet();
    }

    public function getTiempoDetallePorUsuario($id_tarea_detalle){
        if(!$this->tableExists('tablero_tarjetas_tareas_detalle_tiempo_usuario')){
            $this->db->query('
                SELECT
                    tdti.Id_usuario,
                    u.email,
                    COALESCE(SUM(COALESCE(tdti.duracion_segundos, 0)), 0) AS Tiempo_total_segundos,
                    COALESCE(SUM(CASE WHEN tdti.fin_timestamp IS NULL THEN TIMESTAMPDIFF(SECOND, tdti.inicio_timestamp, NOW()) ELSE 0 END), 0) AS Tiempo_en_curso_segundos
                FROM tablero_tarjetas_tarea_detalle_tiempo tdti
                INNER JOIN usuario u ON u.Id_usuario = tdti.Id_usuario
                WHERE tdti.Id_tarea_detalle = :id_tarea_detalle
                  AND tdti.Estado = 1
                GROUP BY tdti.Id_usuario, u.email
                ORDER BY (COALESCE(SUM(COALESCE(tdti.duracion_segundos, 0)), 0) + COALESCE(SUM(CASE WHEN tdti.fin_timestamp IS NULL THEN TIMESTAMPDIFF(SECOND, tdti.inicio_timestamp, NOW()) ELSE 0 END), 0)) DESC, u.email ASC
            ');
            $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
            return $this->db->resultSet();
        }

        $this->db->query('
            SELECT
                                usr.Id_usuario,
                                u.email,
                                COALESCE(tttdtu.Tiempo_total_segundos, 0) AS Tiempo_total_segundos,
                                COALESCE(running.Tiempo_en_curso_segundos, 0) AS Tiempo_en_curso_segundos
                        FROM (
                                SELECT DISTINCT Id_usuario
                                FROM tablero_tarjetas_tareas_detalle_tiempo_usuario
                                WHERE Id_tarea_detalle = :id_tarea_detalle_acumulado
                                    AND Estado = 1

                                UNION

                                SELECT DISTINCT Id_usuario
                                FROM tablero_tarjetas_tarea_detalle_tiempo
                                WHERE Id_tarea_detalle = :id_tarea_detalle_running
                                    AND fin_timestamp IS NULL
                                    AND Estado = 1
                        ) usr
                        INNER JOIN usuario u ON u.Id_usuario = usr.Id_usuario
                        LEFT JOIN tablero_tarjetas_tareas_detalle_tiempo_usuario tttdtu
                                ON tttdtu.Id_tarea_detalle = :id_tarea_detalle_join
                             AND tttdtu.Id_usuario = usr.Id_usuario
                             AND tttdtu.Estado = 1
                        LEFT JOIN (
                                SELECT Id_usuario, COALESCE(SUM(TIMESTAMPDIFF(SECOND, inicio_timestamp, NOW())), 0) AS Tiempo_en_curso_segundos
                                FROM tablero_tarjetas_tarea_detalle_tiempo
                                WHERE Id_tarea_detalle = :id_tarea_detalle_running_sum
                                    AND fin_timestamp IS NULL
                                    AND Estado = 1
                                GROUP BY Id_usuario
                        ) running ON running.Id_usuario = usr.Id_usuario
                        ORDER BY (COALESCE(tttdtu.Tiempo_total_segundos, 0) + COALESCE(running.Tiempo_en_curso_segundos, 0)) DESC, u.email ASC
        ');
                $this->db->bind(':id_tarea_detalle_acumulado', (int)$id_tarea_detalle);
                $this->db->bind(':id_tarea_detalle_running', (int)$id_tarea_detalle);
                $this->db->bind(':id_tarea_detalle_join', (int)$id_tarea_detalle);
                $this->db->bind(':id_tarea_detalle_running_sum', (int)$id_tarea_detalle);
        return $this->db->resultSet();
    }

    public function updateDetalleUsuarioAsignado($id_tarea_detalle, $id_usuario_asignado){
        if(!$this->columnExists('tablero_tarjetas_tarea_detalle', 'Id_usuario_asignado')){
            return false;
        }

        $this->db->query('
            UPDATE tablero_tarjetas_tarea_detalle
            SET
                Id_usuario_asignado = :id_usuario_asignado,
                Fecha_actualizacion = NOW()
            WHERE Id_tarea_detalle = :id_tarea_detalle
              AND Estado = 1
        ');
        $this->db->bind(':id_usuario_asignado', $id_usuario_asignado !== null ? (int)$id_usuario_asignado : null);
        $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
        return $this->db->execute();
    }

    public function getRunningDetalleTimer($id_tarea_detalle, $id_usuario){
        $this->db->query('
            SELECT *
            FROM tablero_tarjetas_tarea_detalle_tiempo
            WHERE Id_tarea_detalle = :id_tarea_detalle
              AND Id_usuario = :id_usuario
              AND fin_timestamp IS NULL
              AND Estado = 1
            ORDER BY Id_tiempo_detalle DESC
            LIMIT 1
        ');
        $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
        $this->db->bind(':id_usuario', (int)$id_usuario);
        return $this->db->single();
    }

    public function startDetalleTimer($id_tarea_detalle, $id_usuario){
        $running = $this->getRunningDetalleTimer($id_tarea_detalle, $id_usuario);
        if($running){
            return $running;
        }

        $this->db->query('
            INSERT INTO tablero_tarjetas_tarea_detalle_tiempo (Id_tarea_detalle, Id_usuario, inicio_timestamp, Estado)
            VALUES (:id_tarea_detalle, :id_usuario, NOW(), 1)
        ');
        $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
        $this->db->bind(':id_usuario', (int)$id_usuario);
        $this->db->execute();

        return $this->getRunningDetalleTimer($id_tarea_detalle, $id_usuario);
    }

    public function stopDetalleTimer($id_tarea_detalle, $id_usuario){
        $running = $this->getRunningDetalleTimer($id_tarea_detalle, $id_usuario);
        if(!$running){
            return false;
        }

        $this->db->query('
            UPDATE tablero_tarjetas_tarea_detalle_tiempo
            SET
                fin_timestamp = NOW(),
                duracion_segundos = TIMESTAMPDIFF(SECOND, inicio_timestamp, NOW())
            WHERE Id_tiempo_detalle = :id_tiempo_detalle
        ');
        $this->db->bind(':id_tiempo_detalle', (int)$running->Id_tiempo_detalle);

        if(!$this->db->execute()){
            return false;
        }

        $this->db->query('SELECT duracion_segundos FROM tablero_tarjetas_tarea_detalle_tiempo WHERE Id_tiempo_detalle = :id_tiempo_detalle LIMIT 1');
        $this->db->bind(':id_tiempo_detalle', (int)$running->Id_tiempo_detalle);
        $rowDuracion = $this->db->single();
        $duracion = $rowDuracion ? max(0, (int)$rowDuracion->duracion_segundos) : 0;

        if($duracion > 0){
            $this->incrementarTiempoDetalleUsuario($id_tarea_detalle, $id_usuario, $duracion);
        }

        return $running->Id_tiempo_detalle;
    }

    public function getTiempoTotalDetalle($id_tarea_detalle){
        $this->db->query('
                        SELECT COALESCE(SUM(COALESCE(duracion_segundos, 0)), 0) AS total_segundos
            FROM tablero_tarjetas_tarea_detalle_tiempo
            WHERE Id_tarea_detalle = :id_tarea_detalle
              AND Estado = 1
        ');
        $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
        $row = $this->db->single();
        return $row ? (int)$row->total_segundos : 0;
    }

    public function detalleTieneTimerEnCurso($id_tarea_detalle){
        $this->db->query('
            SELECT COUNT(*) AS total
            FROM tablero_tarjetas_tarea_detalle_tiempo
            WHERE Id_tarea_detalle = :id_tarea_detalle
              AND fin_timestamp IS NULL
              AND Estado = 1
        ');
        $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
        $row = $this->db->single();
        return $row && (int)$row->total > 0;
    }

    public function replaceDetalleTiempoTotal($id_tarea_detalle, $duracion_segundos, $id_usuario_editor, $id_usuario_tiempo = null){
        $id_usuario_registro = $id_usuario_tiempo !== null ? (int)$id_usuario_tiempo : (int)$id_usuario_editor;
        return $this->replaceDetalleTiempoPorUsuarios($id_tarea_detalle, [
            $id_usuario_registro => (int)$duracion_segundos
        ]);
    }

    public function replaceDetalleTiempoPorUsuarios($id_tarea_detalle, $tiempos_por_usuario = []){
        $this->db->query('
            UPDATE tablero_tarjetas_tarea_detalle_tiempo
            SET
                Estado = 0
            WHERE Id_tarea_detalle = :id_tarea_detalle
              AND Estado = 1
        ');
        $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
        if(!$this->db->execute()){
            return false;
        }

        if($this->tableExists('tablero_tarjetas_tareas_detalle_tiempo_usuario')){
            $this->db->query('
                UPDATE tablero_tarjetas_tareas_detalle_tiempo_usuario
                SET
                    Estado = 0,
                    Fecha_actualizacion = NOW()
                WHERE Id_tarea_detalle = :id_tarea_detalle
                  AND Estado = 1
            ');
            $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
            if(!$this->db->execute()){
                return false;
            }
        }

        $rows = is_array($tiempos_por_usuario) ? $tiempos_por_usuario : [];
        foreach($rows as $id_usuario => $duracion_segundos){
            $uid = (int)$id_usuario;
            $duracion = max(0, (int)$duracion_segundos);

            if($uid <= 0 || $duracion <= 0){
                continue;
            }

            if(!$this->incrementarTiempoDetalleUsuario($id_tarea_detalle, $uid, $duracion, false)){
                return false;
            }

            $this->db->query('
                INSERT INTO tablero_tarjetas_tarea_detalle_tiempo (
                    Id_tarea_detalle,
                    Id_usuario,
                    inicio_timestamp,
                    fin_timestamp,
                    duracion_segundos,
                    Estado
                ) VALUES (
                    :id_tarea_detalle,
                    :id_usuario,
                    NOW(),
                    NOW(),
                    :duracion_segundos,
                    1
                )
            ');
            $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
            $this->db->bind(':id_usuario', $uid);
            $this->db->bind(':duracion_segundos', $duracion);
            if(!$this->db->execute()){
                return false;
            }
        }

        return true;
    }

    private function incrementarTiempoDetalleUsuario($id_tarea_detalle, $id_usuario, $duracion_segundos, $acumular = true){
        $duracion = max(0, (int)$duracion_segundos);
        if($id_tarea_detalle <= 0 || $id_usuario <= 0 || $duracion <= 0){
            return true;
        }

        if(!$this->tableExists('tablero_tarjetas_tareas_detalle_tiempo_usuario')){
            return true;
        }

        if($acumular){
            $this->db->query('
                INSERT INTO tablero_tarjetas_tareas_detalle_tiempo_usuario (
                    Id_tarea_detalle,
                    Id_usuario,
                    Tiempo_total_segundos,
                    Estado
                ) VALUES (
                    :id_tarea_detalle,
                    :id_usuario,
                    :duracion_segundos,
                    1
                )
                ON DUPLICATE KEY UPDATE
                    Tiempo_total_segundos = Tiempo_total_segundos + VALUES(Tiempo_total_segundos),
                    Estado = 1,
                    Fecha_actualizacion = NOW()
            ');
        } else {
            $this->db->query('
                INSERT INTO tablero_tarjetas_tareas_detalle_tiempo_usuario (
                    Id_tarea_detalle,
                    Id_usuario,
                    Tiempo_total_segundos,
                    Estado
                ) VALUES (
                    :id_tarea_detalle,
                    :id_usuario,
                    :duracion_segundos,
                    1
                )
                ON DUPLICATE KEY UPDATE
                    Tiempo_total_segundos = VALUES(Tiempo_total_segundos),
                    Estado = 1,
                    Fecha_actualizacion = NOW()
            ');
        }
        $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
        $this->db->bind(':id_usuario', (int)$id_usuario);
        $this->db->bind(':duracion_segundos', $duracion);
        return $this->db->execute();
    }

    public function addTareaTarjeta($id_tarjeta, $nombre_tarea){
        $this->db->query('SELECT COALESCE(MAX(Orden_tarea), 0) + 1 AS siguiente FROM tablero_tarjetas_tarea WHERE Id_tarjeta = :id_tarjeta AND Estado = 1');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        $row = $this->db->single();
        $orden = $row ? (int)$row->siguiente : 1;

        $this->db->query('INSERT INTO tablero_tarjetas_tarea (Id_tarjeta, Nombre_tarea, Orden_tarea, Estado) VALUES (:id_tarjeta, :nombre_tarea, :orden_tarea, 1)');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        $this->db->bind(':nombre_tarea', $nombre_tarea);
        $this->db->bind(':orden_tarea', $orden);

        if(!$this->db->execute()){
            return false;
        }

        $this->db->query('SELECT LAST_INSERT_ID() AS id_tarea');
        $inserted = $this->db->single();
        return $inserted ? (int)$inserted->id_tarea : false;
    }

    public function addDetalleTarea($id_tarea, $descripcion, $id_usuario_asignado = null){
        $this->db->query('SELECT COALESCE(MAX(Orden_detalle), 0) + 1 AS siguiente FROM tablero_tarjetas_tarea_detalle WHERE Id_tarea = :id_tarea AND Estado = 1');
        $this->db->bind(':id_tarea', (int)$id_tarea);
        $row = $this->db->single();
        $orden = $row ? (int)$row->siguiente : 1;

        $hasAssignedColumn = $this->columnExists('tablero_tarjetas_tarea_detalle', 'Id_usuario_asignado');
        if($hasAssignedColumn){
            $this->db->query('
                INSERT INTO tablero_tarjetas_tarea_detalle (Id_tarea, Id_usuario_asignado, Descripcion, Completado, Orden_detalle, Estado)
                VALUES (:id_tarea, :id_usuario_asignado, :descripcion, 0, :orden_detalle, 1)
            ');
        } else {
            $this->db->query('
                INSERT INTO tablero_tarjetas_tarea_detalle (Id_tarea, Descripcion, Completado, Orden_detalle, Estado)
                VALUES (:id_tarea, :descripcion, 0, :orden_detalle, 1)
            ');
        }
        $this->db->bind(':id_tarea', (int)$id_tarea);
        if($hasAssignedColumn){
            $this->db->bind(':id_usuario_asignado', $id_usuario_asignado !== null ? (int)$id_usuario_asignado : null);
        }
        $this->db->bind(':descripcion', $descripcion);
        $this->db->bind(':orden_detalle', $orden);

        if(!$this->db->execute()){
            return false;
        }

        $this->db->query('SELECT LAST_INSERT_ID() AS id_tarea_detalle');
        $inserted = $this->db->single();
        return $inserted ? (int)$inserted->id_tarea_detalle : false;
    }

    public function updateTareaTarjeta($id_tarea, $nombre_tarea){
        $this->db->query('UPDATE tablero_tarjetas_tarea SET Nombre_tarea = :nombre_tarea, Fecha_actualizacion = NOW() WHERE Id_tarea = :id_tarea AND Estado = 1');
        $this->db->bind(':nombre_tarea', $nombre_tarea);
        $this->db->bind(':id_tarea', (int)$id_tarea);
        return $this->db->execute();
    }

    public function updateDetalleTarea($id_tarea_detalle, $descripcion){
        $this->db->query('UPDATE tablero_tarjetas_tarea_detalle SET Descripcion = :descripcion, Fecha_actualizacion = NOW() WHERE Id_tarea_detalle = :id_tarea_detalle AND Estado = 1');
        $this->db->bind(':descripcion', $descripcion);
        $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
        return $this->db->execute();
    }

    public function countDetallesActivosByTarea($id_tarea){
        $this->db->query('SELECT COUNT(*) AS total FROM tablero_tarjetas_tarea_detalle WHERE Id_tarea = :id_tarea AND Estado = 1');
        $this->db->bind(':id_tarea', (int)$id_tarea);
        $row = $this->db->single();
        return $row ? (int)$row->total : 0;
    }

    public function deleteTareaTarjeta($id_tarea){
        $this->db->query('UPDATE tablero_tarjetas_tarea SET Estado = 0, Fecha_actualizacion = NOW() WHERE Id_tarea = :id_tarea AND Estado = 1');
        $this->db->bind(':id_tarea', (int)$id_tarea);
        return $this->db->execute();
    }

    public function deleteDetalleTarea($id_tarea_detalle){
        $this->db->query('UPDATE tablero_tarjetas_tarea_detalle SET Estado = 0, Fecha_actualizacion = NOW() WHERE Id_tarea_detalle = :id_tarea_detalle AND Estado = 1');
        $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
        return $this->db->execute();
    }

    public function getTareaById($id_tarea){
        $this->db->query('SELECT * FROM tablero_tarjetas_tarea WHERE Id_tarea = :id_tarea AND Estado = 1');
        $this->db->bind(':id_tarea', (int)$id_tarea);
        return $this->db->single();
    }

    public function getDetalleById($id_tarea_detalle){
        $this->db->query('SELECT * FROM tablero_tarjetas_tarea_detalle WHERE Id_tarea_detalle = :id_tarea_detalle AND Estado = 1');
        $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
        return $this->db->single();
    }

    public function toggleDetalleTarea($id_tarea_detalle, $completado, $id_usuario){
        if($completado){
            $this->db->query('
                UPDATE tablero_tarjetas_tarea_detalle
                SET
                    Completado = 1,
                    Id_usuario_check = :id_usuario,
                    Fecha_check = NOW(),
                    Fecha_actualizacion = NOW()
                WHERE Id_tarea_detalle = :id_tarea_detalle
                  AND Estado = 1
            ');
            $this->db->bind(':id_usuario', (int)$id_usuario);
            $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
            return $this->db->execute();
        }

        $this->db->query('
            UPDATE tablero_tarjetas_tarea_detalle
            SET
                Completado = 0,
                Id_usuario_check = NULL,
                Fecha_check = NULL,
                Fecha_actualizacion = NOW()
            WHERE Id_tarea_detalle = :id_tarea_detalle
              AND Estado = 1
        ');
        $this->db->bind(':id_tarea_detalle', (int)$id_tarea_detalle);
        return $this->db->execute();
    }

    public function addHistorialTarjeta($id_tarjeta, $id_usuario, $tipo_evento, $mensaje, $datos = null){
        $this->db->query('
            INSERT INTO tablero_tarjetas_historial (Id_tarjeta, Id_usuario, Tipo_evento, Mensaje, Datos_json)
            VALUES (:id_tarjeta, :id_usuario, :tipo_evento, :mensaje, :datos_json)
        ');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        $this->db->bind(':id_usuario', $id_usuario !== null ? (int)$id_usuario : null);
        $this->db->bind(':tipo_evento', $tipo_evento);
        $this->db->bind(':mensaje', $mensaje);
        $this->db->bind(':datos_json', $datos ? json_encode($datos, JSON_UNESCAPED_UNICODE) : null);
        return $this->db->execute();
    }

    public function getHistorialByTarjeta($id_tarjeta, $limite = 100){
        $this->db->query('
            SELECT
                h.*,
                u.email AS Usuario_email
            FROM tablero_tarjetas_historial h
            LEFT JOIN usuario u ON u.Id_usuario = h.Id_usuario
            WHERE h.Id_tarjeta = :id_tarjeta
            ORDER BY h.Id_historial DESC
            LIMIT :limite
        ');
        $this->db->bind(':id_tarjeta', (int)$id_tarjeta);
        $this->db->bind(':limite', (int)$limite);
        return $this->db->resultSet();
    }

    public function getLatestHistorialIdByTablero($id_tablero){
        $this->db->query('
            SELECT COALESCE(MAX(h.Id_historial), 0) AS latest_id
            FROM tablero_tarjetas_historial h
            INNER JOIN tablero_tarjetas t ON t.Id_tarjeta = h.Id_tarjeta
            WHERE t.Id_tablero = :id_tablero
        ');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        $row = $this->db->single();
        return $row ? (int)($row->latest_id ?? 0) : 0;
    }

    public function hasHistorialChangesByOtherUser($id_tablero, $since_historial, $id_usuario){
        $this->db->query('
            SELECT 1
            FROM tablero_tarjetas_historial h
            INNER JOIN tablero_tarjetas t ON t.Id_tarjeta = h.Id_tarjeta
            WHERE t.Id_tablero = :id_tablero
              AND h.Id_historial > :since_historial
              AND (:id_usuario = 0 OR h.Id_usuario IS NULL OR h.Id_usuario <> :id_usuario)
            LIMIT 1
        ');
        $this->db->bind(':id_tablero', (int)$id_tablero);
        $this->db->bind(':since_historial', (int)$since_historial);
        $this->db->bind(':id_usuario', (int)$id_usuario);
        return (bool)$this->db->single();
    }
}
