<?php
class AlcanceModel {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    /**
     * Obtiene todos los alcances activos, uniendo con la descripción del contrato.
     */
    public function getAlcances(){
        $this->db->query('
            SELECT 
                a.*, 
                c.Descripcion AS Contrato_Descripcion,
                c.Expediente 
            FROM 
                alcances a
            JOIN 
                contratos c ON a.Id_contrato = c.Id_contrato
            WHERE 
                a.Estado = 1 
            ORDER BY 
                a.Fecha_creacion DESC
        ');
        return $this->db->resultSet();
    }
    
    /**
     * Obtiene un solo registro de alcance por ID.
     */
    public function getAlcanceById($id){
        $this->db->query('SELECT * FROM alcances WHERE Id_alcance = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Obtiene todos los alcances activos para un contrato específico.
     */
    public function getAlcancesByIdContrato($idContrato){
        $this->db->query('SELECT * FROM alcances WHERE Id_contrato = :id_contrato AND Estado = 1 ORDER BY Fecha_creacion DESC');
        $this->db->bind(':id_contrato', $idContrato);
        return $this->db->resultSet();
    }

    /**
     * Agrega un nuevo alcance.
     */
    public function addAlcance($data){
        $this->db->query('INSERT INTO alcances (Id_contrato, Descripcion) 
                          VALUES (:id_contrato, :descripcion)');
        
        $this->db->bind(':id_contrato', $data['id_contrato']);
        $this->db->bind(':descripcion', $data['descripcion']);

        return $this->db->execute();
    }

    /**
     * Actualiza un registro de alcance.
     */
    public function updateAlcance($data){
        $this->db->query('UPDATE alcances SET Id_contrato = :id_contrato, Descripcion = :descripcion WHERE Id_alcance = :id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':id_contrato', $data['id_contrato']);
        $this->db->bind(':descripcion', $data['descripcion']);

        return $this->db->execute();
    }
    
    /**
     * Eliminación Lógica (Soft Delete): Establece Estado = 0.
     */
    public function deleteAlcance($id){
        $this->db->query('UPDATE alcances SET Estado = 0 WHERE Id_alcance = :id');
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }
}