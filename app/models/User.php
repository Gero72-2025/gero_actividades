<?php
class User {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    // Login User
    public function login($email, $password){
        $this->db->query('SELECT * FROM usuario WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        if($row){
            $hashed_password = $row->pass;
            if(password_verify($password, $hashed_password)){
                return $row;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // Find user by email
    public function findUserByEmail($email){
        $this->db->query('SELECT * FROM usuario WHERE email = :email');
        $this->db->bind(':email', $email);

        $row = $this->db->single();

        // Check row
        if($this->db->rowCount() > 0){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Obtiene el nombre completo del personal asociado a un usuario
     * @param int $userId ID del usuario
     * @return object|null Objeto con Nombre_Completo y Apellido_Completo o null si no existe
     */
    public function getNombrePersonal($userId){
        $this->db->query('
            SELECT Nombre_Completo, Apellido_Completo 
            FROM personal 
            WHERE Id_usuario = :userId AND Estado = 1
        ');
        $this->db->bind(':userId', $userId);
        return $this->db->single();
    }
}
