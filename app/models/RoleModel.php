<?php

class RoleModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obtener todos los roles activos con paginación
     */
    public function getRoles($limit = 10, $offset = 0, $search = '') {
        $query = "SELECT r.*, COUNT(rp.Id_permiso) as cantidad_permisos 
                  FROM roles r 
                  LEFT JOIN role_permiso rp ON r.Id_role = rp.Id_role AND rp.Estado = 1
                  WHERE r.Estado = 1";
        
        if (!empty($search)) {
            $query .= " AND (r.Nombre LIKE :search OR r.Descripcion LIKE :search)";
        }
        
        $query .= " GROUP BY r.Id_role ORDER BY r.Fecha_creacion DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        
        if (!empty($search)) {
            $this->db->bind(':search', "%{$search}%");
        }
        
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);
        
        return $this->db->resultSet();
    }

    /**
     * Obtener total de roles para paginación
     */
    public function getTotalRoles($search = '') {
        $query = "SELECT COUNT(*) as total FROM roles WHERE Estado = 1";
        
        if (!empty($search)) {
            $query .= " AND (Nombre LIKE :search OR Descripcion LIKE :search)";
            $this->db->query($query);
            $this->db->bind(':search', "%{$search}%");
        } else {
            $this->db->query($query);
        }
        
        $result = $this->db->single();
        return $result->total ?? 0;
    }

    /**
     * Obtener un role específico por ID
     */
    public function getRoleById($id) {
        $this->db->query("SELECT * FROM roles WHERE Id_role = :id AND Estado = 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Obtener todos los roles sin paginación
     */
    public function getAllRoles() {
        $this->db->query("SELECT * FROM roles WHERE Estado = 1 ORDER BY Nombre ASC");
        return $this->db->resultSet();
    }

    /**
     * Crear un nuevo role
     */
    public function addRole($data) {
        $this->db->query("INSERT INTO roles (Nombre, Descripcion, Estado) 
                         VALUES (:nombre, :descripcion, :estado)");
        
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':descripcion', $data['descripcion'] ?? '');
        $this->db->bind(':estado', 1);
        
        return $this->db->execute();
    }

    /**
     * Actualizar un role
     */
    public function updateRole($id, $data) {
        $this->db->query("UPDATE roles 
                         SET Nombre = :nombre, 
                             Descripcion = :descripcion
                         WHERE Id_role = :id");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':descripcion', $data['descripcion'] ?? '');
        
        return $this->db->execute();
    }

    /**
     * Eliminar un role (soft delete)
     */
    public function deleteRole($id) {
        $this->db->query("UPDATE roles SET Estado = 0 WHERE Id_role = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Obtener permisos de un role
     */
    public function getRolePermisos($roleId) {
        $this->db->query("
            SELECT p.*, 
                   CASE WHEN rp.Id_permiso IS NOT NULL THEN 1 ELSE 0 END as asignado
            FROM permisos p
            LEFT JOIN role_permiso rp ON p.Id_permiso = rp.Id_permiso 
                AND rp.Id_role = :roleId AND rp.Estado = 1
            WHERE p.Estado = 1
            ORDER BY p.Modulo, p.Accion
        ");
        $this->db->bind(':roleId', $roleId);
        return $this->db->resultSet();
    }

    /**
     * Asignar permiso a un role
     */
    public function assignPermisoToRole($roleId, $permisoId) {
        // Verificar si ya existe
        $this->db->query("SELECT * FROM role_permiso WHERE Id_role = :roleId AND Id_permiso = :permisoId");
        $this->db->bind(':roleId', $roleId);
        $this->db->bind(':permisoId', $permisoId);
        $existing = $this->db->single();
        
        if ($existing) {
            // Si existe pero está inactivo, activarlo
            $this->db->query("UPDATE role_permiso SET Estado = 1 WHERE Id_role = :roleId AND Id_permiso = :permisoId");
            $this->db->bind(':roleId', $roleId);
            $this->db->bind(':permisoId', $permisoId);
        } else {
            // Crear nuevo
            $this->db->query("INSERT INTO role_permiso (Id_role, Id_permiso, Estado) VALUES (:roleId, :permisoId, 1)");
            $this->db->bind(':roleId', $roleId);
            $this->db->bind(':permisoId', $permisoId);
        }
        
        return $this->db->execute();
    }

    /**
     * Remover permiso de un role
     */
    public function removePermisoFromRole($roleId, $permisoId) {
        $this->db->query("UPDATE role_permiso SET Estado = 0 WHERE Id_role = :roleId AND Id_permiso = :permisoId");
        $this->db->bind(':roleId', $roleId);
        $this->db->bind(':permisoId', $permisoId);
        return $this->db->execute();
    }

    /**
     * Verificar si un role tiene un permiso
     */
    public function hasPermiso($roleId, $permisoName) {
        $this->db->query("
            SELECT rp.* FROM role_permiso rp
            INNER JOIN permisos p ON rp.Id_permiso = p.Id_permiso
            WHERE rp.Id_role = :roleId AND p.Nombre = :permisoName AND rp.Estado = 1 AND p.Estado = 1
        ");
        $this->db->bind(':roleId', $roleId);
        $this->db->bind(':permisoName', $permisoName);
        return $this->db->single() !== false;
    }

    /**
     * Verificar si un nombre de role ya existe
     */
    public function roleExists($nombre, $excludeId = null) {
        $query = "SELECT * FROM roles WHERE Nombre = :nombre";
        $this->db->query($query);
        $this->db->bind(':nombre', $nombre);
        
        if ($excludeId) {
            $query = "SELECT * FROM roles WHERE Nombre = :nombre AND Id_role != :id";
            $this->db->query($query);
            $this->db->bind(':nombre', $nombre);
            $this->db->bind(':id', $excludeId);
        }
        
        return $this->db->single() !== false;
    }
}
