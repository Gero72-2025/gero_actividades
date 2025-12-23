<?php
class DivisionModel {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    // Obtener todas las Divisiones
    public function getDivisions(){
        // Consulta para obtener todas las divisiones activas, ordenadas por nombre
        $this->db->query('
            SELECT 
                d.*, 
                p.Nombre_Completo AS jefe_nombre 
            FROM 
                division d
            LEFT JOIN 
                personal p ON d.Id_personal_jefe = p.Id_Personal
            WHERE 
                d.Estado_division = 1
            ORDER BY 
                d.Nombre ASC
        ');

        $results = $this->db->resultSet(); // Devuelve un array de objetos

        return $results;
    }

    public function getDivisionById($id){
        // 1. Prepara la consulta SQL
        // Asumiendo que tu tabla se llama 'division' y la columna de ID es 'Id_Division'
        $this->db->query('SELECT * FROM division WHERE Id_Division = :id and Estado_division = 1');

        // 2. Vincula el ID (para evitar inyección SQL)
        $this->db->bind(':id', $id);

        // 3. Ejecuta la consulta y devuelve un único resultado como objeto
        // El método single() debe encargarse de ejecutar y retornar fetch(PDO::FETCH_OBJ)
        $row = $this->db->single();

        return $row;
    }

    // Función para agregar una nueva División
    public function addDivision($data){
        $this->db->query('
            INSERT INTO division (Nombre, Siglas, Id_personal_jefe) 
            VALUES (:nombre, :siglas, :id_jefe)
        ');
        
        // Vincular valores
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':siglas', $data['siglas']);
        // Maneja el caso de que Id_personal_jefe sea NULL
        $this->db->bind(':id_jefe', $data['id_jefe'], is_null($data['id_jefe']) ? PDO::PARAM_NULL : PDO::PARAM_INT);

        // Ejecutar
        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function updateDivision($data){
        $this->db->query('UPDATE division SET Nombre = :nombre, Siglas = :siglas, Id_personal_jefe = :jefe_id WHERE Id_Division = :id');
        
        // Bind values
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':siglas', $data['siglas']);
        // Manejo de NULL para jefe_id
        $this->db->bind(':jefe_id', $data['id_personal_jefe'] ?: null); 

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function deleteDivision($id){
        $this->db->query('UPDATE division SET Estado_division = 0 WHERE Id_Division = :id');
        $this->db->bind(':id', $id);

        // Ejecuta la consulta
        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }
    
}