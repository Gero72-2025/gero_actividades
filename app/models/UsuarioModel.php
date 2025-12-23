<?php
class UsuarioModel {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    /**
     * Obtiene todos los usuarios activos (estado_usuario = 1).
     */
    public function getUsuarios(){
        $this->db->query('SELECT Id_usuario, email, estado_usuario, fecha_ultimo_login, conectado, Fecha_creacion 
                          FROM usuario 
                          WHERE estado_usuario = 1 
                          ORDER BY email ASC');
        return $this->db->resultSet();
    }
    
    /**
     * Obtiene un solo registro de usuario por ID (sin la contraseña).
     */
    public function getUsuarioById($id){
        $this->db->query('SELECT Id_usuario, email, estado_usuario FROM usuario WHERE Id_usuario = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Obtiene un usuario por email.
     */
    public function getUsuarioByEmail($email){
        $this->db->query('SELECT Id_usuario, email, estado_usuario FROM usuario WHERE email = :email');
        $this->db->bind(':email', $email);
        return $this->db->single();
    }
    
    /**
     * Busca un usuario por email. Retorna true si existe, false si no.
     */
    public function findUserByEmail($email){
        $this->db->query('SELECT Id_usuario FROM usuario WHERE email = :email AND estado_usuario = 1');
        $this->db->bind(':email', $email);
        $this->db->single();
        return $this->db->rowCount() > 0;
    }

    /**
     * Obtiene usuarios activos que NO están asignados a un registro de Personal.
     */
    public function getUsuariosNoAsignados(){
        $this->db->query('
            SELECT 
                u.Id_usuario, 
                u.email
            FROM 
                usuario u
            LEFT JOIN
                personal p ON u.Id_usuario = p.Id_usuario
            WHERE 
                u.estado_usuario = 1 AND p.Id_usuario IS NULL
            ORDER BY 
                u.email ASC
        ');
        return $this->db->resultSet();
    }

    /**
     * Agrega un nuevo usuario. La contraseña debe venir hasheada.
     */
    public function addUsuario($data){
        $this->db->query('INSERT INTO usuario (email, pass) VALUES (:email, :pass)');
        
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':pass', $data['pass']); 

        return $this->db->execute();
    }

    /**
     * Actualiza email y/o contraseña. La contraseña debe venir hasheada (o vacía).
     */
    public function updateUsuario($data){
        $sql = 'UPDATE usuario SET email = :email, Fecha_actualizacion = NOW()';
        
        if(!empty($data['pass'])){
            $sql .= ', pass = :pass';
        }
        $sql .= ' WHERE Id_usuario = :id';
        
        $this->db->query($sql);
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':email', $data['email']);
        
        if(!empty($data['pass'])){
            $this->db->bind(':pass', $data['pass']);
        }

        return $this->db->execute();
    }
    
    /**
     * Eliminación Lógica (Soft Delete): Establece estado_usuario = 0.
     */
    public function deleteUsuario($id){
        $this->db->query('UPDATE usuario SET estado_usuario = 0 WHERE Id_usuario = :id');
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /**
     * Obtiene todos los roles activos.
     */
    public function getRoles(){
        $this->db->query('SELECT Id_role, Nombre FROM roles WHERE Estado = 1 ORDER BY Nombre ASC');
        return $this->db->resultSet();
    }

    /**
     * Obtiene el rol asignado a un usuario (si existe).
     */
    public function getRoleUsuario($id_usuario){
        $this->db->query('SELECT ur.Id_role FROM usuario_role ur WHERE ur.Id_usuario = :id AND ur.Estado = 1 LIMIT 1');
        $this->db->bind(':id', $id_usuario);
        $result = $this->db->single();
        return $result ? $result->Id_role : null;
    }

    /**
     * Asigna un rol a un usuario. Si ya tiene un rol, lo actualiza.
     */
    public function assignRoleToUsuario($id_usuario, $id_role){
        // Primero, desactivar cualquier rol anterior
        $this->db->query('UPDATE usuario_role SET Estado = 0 WHERE Id_usuario = :id_usuario');
        $this->db->bind(':id_usuario', $id_usuario);
        $this->db->execute();

        // Si el nuevo rol es válido (no vacío), asignar
        if(!empty($id_role)){
            $this->db->query('INSERT INTO usuario_role (Id_usuario, Id_role, Estado) VALUES (:id_usuario, :id_role, 1) 
                            ON DUPLICATE KEY UPDATE Estado = 1');
            $this->db->bind(':id_usuario', $id_usuario);
            $this->db->bind(':id_role', $id_role);
            return $this->db->execute();
        }
        return true;
    }
}