<?php
class PersonalModel {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    /**
     * Obtiene todo el personal ACTIVO (Estado = 1) con nombres de división, usuario (Email) y contrato.
     */
    public function getPersonal(){
        $this->db->query('
            SELECT 
                p.Id_personal,
                p.Nombre_Completo,
                p.Apellido_Completo,
                p.Puesto,
                p.Tipo_servicio,
                d.Nombre AS division_nombre,
                u.Email AS usuario_email,
                c.Expediente AS contrato_expediente,
                c.Descripcion AS contrato_descripcion,
                c.Contrato_activo
            FROM 
                personal p
            LEFT JOIN 
                division d ON p.Id_division = d.Id_Division
            LEFT JOIN
                usuario u ON p.Id_usuario = u.Id_usuario
            LEFT JOIN
                contratos c ON p.Id_contrato = c.Id_contrato
            WHERE 
                p.Estado = 1
            ORDER BY 
                p.Apellido_Completo, p.Nombre_Completo ASC
        ');
        return $this->db->resultSet();
    }
    
    /**
     * Obtiene personal activo formateado para usarse en dropdowns (ej. para asignar un jefe de división).
     */
    public function getPersonalForDropdown(){
        $this->db->query('
            SELECT 
                Id_personal, 
                CONCAT(Apellido_Completo, ", ", Nombre_Completo) AS Nombre
            FROM 
                personal
            WHERE 
                Estado = 1
            ORDER BY 
                Apellido_Completo ASC
        ');
        return $this->db->resultSet();
    }
    
    /**
     * Obtiene un solo registro de personal por ID.
     */
    public function getPersonalById($id){
        $this->db->query('SELECT * FROM personal WHERE Id_personal = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

     /**
     * Obtiene el registro de Personal asociado a un ID de Usuario (Id_usuario).
     */
    public function getPersonalByUserId($userId){
        $this->db->query('
            SELECT 
                p.*,
                d.Nombre AS division_nombre,
                d.Id_Division
            FROM 
                personal p
            LEFT JOIN 
                division d ON p.Id_division = d.Id_Division
            WHERE 
                p.Id_usuario = :user_id AND p.Estado = 1
        ');
        $this->db->bind(':user_id', $userId);
        return $this->db->single(); 
    }
    

    /**
     * Agrega un nuevo registro de Personal (asumiendo que Id_usuario es requerido).
     */
    public function addPersonal($data){
        $this->db->query('INSERT INTO personal (Nombre_Completo, Apellido_Completo, Puesto, Tipo_servicio, Id_division, Id_usuario, Id_contrato) 
                  VALUES (:nombre, :apellido, :puesto, :tipo_servicio, :id_division, :id_usuario, :id_contrato)');
        
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':apellido', $data['apellido']);
        $this->db->bind(':puesto', $data['puesto']);
        $this->db->bind(':tipo_servicio', $data['tipo_servicio']);
        $this->db->bind(':id_division', $data['id_division'] ?: null); 
        $this->db->bind(':id_contrato', $data['id_contrato'] ?: null);
        $this->db->bind(':id_usuario', $data['id_usuario']); 

        return $this->db->execute();
    }

    /**
     * Actualiza un registro de Personal.
     */
    public function updatePersonal($data){
        $this->db->query('UPDATE personal SET Nombre_Completo = :nombre, Apellido_Completo = :apellido, Puesto = :puesto, Tipo_servicio = :tipo_servicio, Id_division = :id_division, Id_contrato = :id_contrato WHERE Id_personal = :id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':apellido', $data['apellido']);
        $this->db->bind(':puesto', $data['puesto']);
        $this->db->bind(':tipo_servicio', $data['tipo_servicio']);
        $this->db->bind(':id_division', $data['id_division'] ?: null); 
        $this->db->bind(':id_contrato', $data['id_contrato'] ?: null); 

        return $this->db->execute();
    }
    
    /**
     * Eliminación Lógica (Soft Delete): Establece Estado = 0.
     */
    public function deletePersonal($id){
        $this->db->query('UPDATE personal SET Estado = 0 WHERE Id_personal = :id');
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /**
     * Verifica si un usuario es jefe de división.
     * Retorna true si el usuario logueado es jefe de alguna división.
     */
    public function isJefeDivision($userId){
        $this->db->query('
            SELECT d.Id_Division 
            FROM division d
            INNER JOIN personal p ON d.Id_personal_jefe = p.Id_personal
            WHERE p.Id_usuario = :user_id
            LIMIT 1
        ');
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return !empty($result);
    }

    /**
     * Obtiene la división donde el usuario es jefe.
     * Retorna el objeto división o null si no es jefe de ninguna.
     */
    public function getDivisionWhereChief($userId){
        $this->db->query('
            SELECT d.* 
            FROM division d
            INNER JOIN personal p ON d.Id_personal_jefe = p.Id_personal
            WHERE p.Id_usuario = :user_id
            LIMIT 1
        ');
        $this->db->bind(':user_id', $userId);
        return $this->db->single();
    }

    /**
     * Obtiene todo el personal asignado a una división específica.
     */
    public function getPersonalByDivision($divisionId){
        $this->db->query('
            SELECT 
                p.Id_personal,
                p.Nombre_Completo,
                p.Apellido_Completo,
                p.Puesto,
                p.Tipo_servicio,
                p.Id_usuario,
                d.Nombre AS division_nombre,
                u.Email AS usuario_email,
                c.Expediente AS contrato_expediente,
                c.Descripcion AS contrato_descripcion,
                c.Contrato_activo
            FROM 
                personal p
            LEFT JOIN 
                division d ON p.Id_division = d.Id_Division
            LEFT JOIN
                usuario u ON p.Id_usuario = u.Id_usuario
            LEFT JOIN
                contratos c ON p.Id_contrato = c.Id_contrato
            WHERE 
                p.Id_division = :division_id AND p.Estado = 1
            ORDER BY 
                p.Apellido_Completo, p.Nombre_Completo ASC
        ');
        $this->db->bind(':division_id', $divisionId);
        return $this->db->resultSet();
    }
}