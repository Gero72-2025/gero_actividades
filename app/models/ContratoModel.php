<?php
class ContratoModel {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    /**
     * Obtiene todos los contratos activos (Estado = 1).
     */
    public function getContratos(){
        $this->db->query('SELECT * FROM contratos WHERE Estado = 1 ORDER BY Inicio_contrato DESC');
        return $this->db->resultSet();
    }
    
    /**
     * Obtiene un solo registro de contrato por ID.
     */
    public function getContratoById($id){
        $this->db->query('SELECT * FROM contratos WHERE Id_contrato = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }


    /**
     * Agrega un nuevo contrato.
     */
    public function addContrato($data){
        $this->db->query('INSERT INTO contratos (Descripcion, Numero_pagos, Inicio_contrato, Fin_contrato, Expediente) 
                          VALUES (:descripcion, :pagos, :inicio, :fin, :expediente)');
        
        $this->db->bind(':descripcion', $data['descripcion']);
        $this->db->bind(':pagos', $data['numero_pagos']);
        $this->db->bind(':inicio', $data['inicio_contrato']);
        $this->db->bind(':fin', $data['fin_contrato']);
        $this->db->bind(':expediente', $data['expediente'] ?: null);

        return $this->db->execute();
    }

    /**
     * Actualiza un registro de contrato.
     */
    public function updateContrato($data){
        $this->db->query('UPDATE contratos SET Descripcion = :descripcion, Numero_pagos = :pagos, Inicio_contrato = :inicio, Fin_contrato = :fin, Expediente = :expediente WHERE Id_contrato = :id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':descripcion', $data['descripcion']);
        $this->db->bind(':pagos', $data['numero_pagos']);
        $this->db->bind(':inicio', $data['inicio_contrato']);
        $this->db->bind(':fin', $data['fin_contrato']);
        $this->db->bind(':expediente', $data['expediente'] ?: null);

        return $this->db->execute();
    }
    
    /**
     * Eliminación Lógica (Soft Delete): Establece Estado = 0.
     */
    public function deleteContrato($id){
        $this->db->query('UPDATE contratos SET Estado = 0 WHERE Id_contrato = :id');
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    public function getContratoByIdUsuario($Id){
        $this->db->query('SELECT c.Expediente, c.Numero_pagos, c.Descripcion, c.Id_contrato FROM personal p
                            INNER JOIN contratos c
                            ON p.Id_contrato = c.Id_contrato
                            WHERE p.Id_personal = :id AND c.Estado = 1');
        $this->db->bind(':id', $Id);
        return $this->db->resultSet(); 
    }

    /**
     * Obtiene contratos disponibles (no asignados a otro personal y con Estado = 1).
     * Opcionalmente puede excluir un contrato específico (útil en edición para mantener el contrato actual).
     */
    public function getContratosDisponibles($excludeContratoId = null){
        if($excludeContratoId){
            $this->db->query('
                SELECT c.* 
                FROM contratos c
                WHERE c.Estado = 1
                AND (
                    c.Id_contrato NOT IN (SELECT Id_contrato FROM personal WHERE Id_contrato IS NOT NULL)
                    OR c.Id_contrato = :exclude_id
                )
                ORDER BY c.Inicio_contrato DESC
            ');
            $this->db->bind(':exclude_id', $excludeContratoId);
        } else {
            $this->db->query('
                SELECT c.* 
                FROM contratos c
                WHERE c.Estado = 1
                AND c.Id_contrato NOT IN (SELECT Id_contrato FROM personal WHERE Id_contrato IS NOT NULL)
                ORDER BY c.Inicio_contrato DESC
            ');
        }
        return $this->db->resultSet();
    }
}