<?php

class PermisoModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obtener todos los permisos activos con paginación
     */
    public function getPermisos($limit = 10, $offset = 0, $search = '') {
        $query = "SELECT * FROM permisos WHERE Estado = 1";
        
        if (!empty($search)) {
            $query .= " AND (Nombre LIKE :search OR Descripcion LIKE :search OR Modulo LIKE :search OR Accion LIKE :search)";
        }
        
        $query .= " ORDER BY Modulo, Accion ASC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        
        if (!empty($search)) {
            $this->db->bind(':search', "%{$search}%");
        }
        
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);
        
        return $this->db->resultSet();
    }

    /**
     * Obtener total de permisos para paginación
     */
    public function getTotalPermisos($search = '') {
        $query = "SELECT COUNT(*) as total FROM permisos WHERE Estado = 1";
        
        if (!empty($search)) {
            $query .= " AND (Nombre LIKE :search OR Descripcion LIKE :search OR Modulo LIKE :search OR Accion LIKE :search)";
            $this->db->query($query);
            $this->db->bind(':search', "%{$search}%");
        } else {
            $this->db->query($query);
        }
        
        $result = $this->db->single();
        return $result->total ?? 0;
    }

    /**
     * Obtener un permiso específico por ID
     */
    public function getPermisoById($id) {
        $this->db->query("SELECT * FROM permisos WHERE Id_permiso = :id AND Estado = 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Obtener todos los permisos sin paginación
     */
    public function getAllPermisos() {
        $this->db->query("SELECT * FROM permisos WHERE Estado = 1 ORDER BY Modulo, Accion ASC");
        return $this->db->resultSet();
    }

    /**
     * Obtener permisos agrupados por módulo
     */
    public function getPermisosByModulo() {
        $this->db->query("
            SELECT Modulo, GROUP_CONCAT(Nombre) as permisos, COUNT(*) as cantidad
            FROM permisos 
            WHERE Estado = 1
            GROUP BY Modulo 
            ORDER BY Modulo ASC
        ");
        return $this->db->resultSet();
    }

    /**
     * Crear un nuevo permiso
     */
    public function addPermiso($data) {
        $this->db->query("INSERT INTO permisos (Nombre, Descripcion, Modulo, Accion, Estado) 
                         VALUES (:nombre, :descripcion, :modulo, :accion, :estado)");
        
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':descripcion', $data['descripcion'] ?? '');
        $this->db->bind(':modulo', $data['modulo']);
        $this->db->bind(':accion', $data['accion']);
        $this->db->bind(':estado', 1);
        
        return $this->db->execute();
    }

    /**
     * Actualizar un permiso
     */
    public function updatePermiso($id, $data) {
        $this->db->query("UPDATE permisos 
                         SET Nombre = :nombre, 
                             Descripcion = :descripcion,
                             Modulo = :modulo,
                             Accion = :accion
                         WHERE Id_permiso = :id");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':descripcion', $data['descripcion'] ?? '');
        $this->db->bind(':modulo', $data['modulo']);
        $this->db->bind(':accion', $data['accion']);
        
        return $this->db->execute();
    }

    /**
     * Eliminar un permiso (soft delete)
     */
    public function deletePermiso($id) {
        $this->db->query("UPDATE permisos SET Estado = 0 WHERE Id_permiso = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Obtener roles que tienen un permiso
     */
    public function getRolesConPermiso($permisoId) {
        $this->db->query("
            SELECT r.*, 
                   CASE WHEN rp.Id_role IS NOT NULL THEN 1 ELSE 0 END as tiene_permiso
            FROM roles r
            LEFT JOIN role_permiso rp ON r.Id_role = rp.Id_role 
                AND rp.Id_permiso = :permisoId AND rp.Estado = 1
            WHERE r.Estado = 1
            ORDER BY r.Nombre ASC
        ");
        $this->db->bind(':permisoId', $permisoId);
        return $this->db->resultSet();
    }

    /**
     * Verificar si un nombre de permiso ya existe
     */
    public function permisoExists($nombre, $excludeId = null) {
        $query = "SELECT * FROM permisos WHERE Nombre = :nombre";
        
        $this->db->query($query);
        $this->db->bind(':nombre', $nombre);
        
        if ($excludeId) {
            $query = "SELECT * FROM permisos WHERE Nombre = :nombre AND Id_permiso != :id";
            $this->db->query($query);
            $this->db->bind(':nombre', $nombre);
            $this->db->bind(':id', $excludeId);
        }
        
        return $this->db->single() !== false;
    }

    /**
     * Obtener módulos únicos disponibles
     */
    public function getModulos() {
        $this->db->query("SELECT DISTINCT Modulo FROM permisos WHERE Estado = 1 ORDER BY Modulo ASC");
        $resultados = $this->db->resultSet();
        $modulos = [];
        foreach ($resultados as $item) {
            $modulos[] = $item->Modulo;
        }
        return $modulos;
    }

    /**
     * Obtener acciones disponibles
     */
    public function getAcciones() {
        $this->db->query("SELECT DISTINCT Accion FROM permisos WHERE Estado = 1 ORDER BY Accion ASC");
        $resultados = $this->db->resultSet();
        $acciones = [];
        foreach ($resultados as $item) {
            $acciones[] = $item->Accion;
        }
        return $acciones;
    }

    /**
     * Obtiene todos los permisos activos de un usuario basado en su rol
     * @param int $id_usuario - ID del usuario
     * @return array Array de objetos con los permisos
     */
    public function getPermisosUsuario($id_usuario){
        $this->db->query('
            SELECT DISTINCT p.Nombre, p.Modulo, p.Accion
            FROM permisos p
            INNER JOIN role_permiso rp ON p.Id_permiso = rp.Id_permiso
            INNER JOIN usuario_role ur ON rp.Id_role = ur.Id_role
            WHERE ur.Id_usuario = :id_usuario 
            AND p.Estado = 1 
            AND rp.Estado = 1 
            AND ur.Estado = 1
        ');
        $this->db->bind(':id_usuario', $id_usuario);
        return $this->db->resultSet();
    }

    /**
     * Verifica si un usuario tiene un permiso específico
     * @param int $id_usuario - ID del usuario
     * @param string $permiso - Nombre del permiso (ej: "actividades.ver")
     * @return bool True si tiene el permiso, false si no
     */
    public function tienePermiso($id_usuario, $permiso){
        $this->db->query('
            SELECT COUNT(*) as tiene
            FROM permisos p
            INNER JOIN role_permiso rp ON p.Id_permiso = rp.Id_permiso
            INNER JOIN usuario_role ur ON rp.Id_role = ur.Id_role
            WHERE ur.Id_usuario = :id_usuario 
            AND p.Nombre = :permiso
            AND p.Estado = 1 
            AND rp.Estado = 1 
            AND ur.Estado = 1
        ');
        $this->db->bind(':id_usuario', $id_usuario);
        $this->db->bind(':permiso', $permiso);
        $result = $this->db->single();
        return $result && $result->tiene > 0;
    }

    /**
     * Verifica si un usuario tiene acceso a un módulo y acción específicos
     * @param int $id_usuario - ID del usuario
     * @param string $modulo - Módulo (ej: "actividades")
     * @param string $accion - Acción (ej: "ver")
     * @return bool True si tiene el permiso, false si no
     */
    public function tieneAcceso($id_usuario, $modulo, $accion){
        $this->db->query('
            SELECT COUNT(*) as tiene
            FROM permisos p
            INNER JOIN role_permiso rp ON p.Id_permiso = rp.Id_permiso
            INNER JOIN usuario_role ur ON rp.Id_role = ur.Id_role
            WHERE ur.Id_usuario = :id_usuario 
            AND p.Modulo = :modulo
            AND p.Accion = :accion
            AND p.Estado = 1 
            AND rp.Estado = 1 
            AND ur.Estado = 1
        ');
        $this->db->bind(':id_usuario', $id_usuario);
        $this->db->bind(':modulo', $modulo);
        $this->db->bind(':accion', $accion);
        $result = $this->db->single();
        return $result && $result->tiene > 0;
    }

    /**
     * Obtiene el rol del usuario
     * @param int $id_usuario - ID del usuario
     * @return object|null El rol del usuario o null si no tiene
     */
    public function getRolUsuario($id_usuario){
        $this->db->query('
            SELECT r.Id_role, r.Nombre
            FROM roles r
            INNER JOIN usuario_role ur ON r.Id_role = ur.Id_role
            WHERE ur.Id_usuario = :id_usuario 
            AND r.Estado = 1
            AND ur.Estado = 1
            LIMIT 1
        ');
        $this->db->bind(':id_usuario', $id_usuario);
        return $this->db->single();
    }
}
