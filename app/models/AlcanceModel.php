<?php
class AlcanceModel {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    /**
     * Obtiene todos los alcances activos, uniendo con la descripción del contrato.
     */
    public function getAlcances($divisionId = null){
        $sql = '
            SELECT 
                a.*, 
                c.Descripcion AS Contrato_Descripcion,
                c.Expediente,
                c.Contrato_activo,
                c.Id_division,
                c.Inicio_contrato,
                c.Fin_contrato,
                (SELECT COUNT(*) FROM actividades act WHERE act.Id_alcance = a.Id_alcance AND act.Estado = 1) AS actividades_count
            FROM 
                alcances a
            JOIN 
                contratos c ON a.Id_contrato = c.Id_contrato
            WHERE 
                a.Estado = 1
                AND c.Estado = 1
        ';

        if($divisionId !== null){
            $sql .= ' AND c.Id_division = :division_id';
        }

        $sql .= ' ORDER BY c.Inicio_contrato DESC, a.Fecha_creacion DESC';

        $this->db->query($sql);

        if($divisionId !== null){
            $this->db->bind(':division_id', $divisionId);
        }

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
     * Obtiene alcances del contrato activo de un usuario específico.
     * Filtra alcances donde el contrato está activo (Contrato_activo = 1)
     */
    public function getAlcancesByActiveContract($idPersonal){
        $this->db->query('
            SELECT 
                a.*, 
                c.Descripcion AS Contrato_Descripcion,
                c.Expediente,
                c.Contrato_activo,
                c.Id_division,
                c.Inicio_contrato,
                c.Fin_contrato
            FROM 
                alcances a
            JOIN 
                contratos c ON a.Id_contrato = c.Id_contrato
            JOIN
                personal p ON c.Id_contrato = p.Id_contrato
            WHERE 
                a.Estado = 1
                AND c.Estado = 1
                AND c.Contrato_activo = 1
                AND p.Id_personal = :id_personal
            ORDER BY 
                a.Fecha_creacion DESC
        ');
        $this->db->bind(':id_personal', $idPersonal);
        return $this->db->resultSet();
    }

    /**
     * Verifica si un alcance tiene actividades activas.
     */
    public function hasActividadesActivas($idAlcance){
        $this->db->query('SELECT COUNT(*) as count FROM actividades WHERE Id_alcance = :id_alcance AND Estado = 1');
        $this->db->bind(':id_alcance', $idAlcance);
        $result = $this->db->single();
        return $result && $result->count > 0;
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