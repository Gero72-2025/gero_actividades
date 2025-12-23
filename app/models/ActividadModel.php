<?php
class ActividadModel {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    /**
     * Obtiene todas las actividades activas con detalles de Alcance y Personal.
     */
    // public function getActividades(){
    //     $this->db->query('
    //         SELECT 
    //             a.*, 
    //             al.Descripcion AS Alcance_Descripcion,
    //             p.Nombre_Completo AS Personal_Nombre,
    //             p.Apellido_Completo AS Personal_Apellido
    //         FROM 
    //             actividades a
    //         JOIN 
    //             alcances al ON a.Id_alcance = al.Id_alcance
    //         JOIN 
    //             personal p ON a.Id_personal = p.Id_personal
    //         WHERE 
    //             a.Estado = 1 
    //         ORDER BY 
    //             a.Fecha_ingreso DESC, a.Fecha_creacion DESC
    //     ');
    //     return $this->db->resultSet();
    // }

    /**
     * Obtiene actividades activas con detalles, con filtros de búsqueda y paginación.
     */
    public function getPaginatedActividades($page, $limit, $searchTerm = ''){
        // Calcular OFFSET
        $offset = ($page - 1) * $limit;

        $query = '
            SELECT 
                a.*, 
                al.Descripcion AS Alcance_Descripcion,
                p.Nombre_Completo AS Personal_Nombre,
                p.Apellido_Completo AS Personal_Apellido
            FROM 
                actividades a
            JOIN 
                alcances al ON a.Id_alcance = al.Id_alcance
            JOIN 
                personal p ON a.Id_personal = p.Id_personal
            WHERE 
                a.Estado = 1';
        
        $binds = [];
        
        // Agregar filtro de búsqueda
        if (!empty($searchTerm)) {
            $query .= ' AND (
                al.Descripcion LIKE :search_term OR
                p.Nombre_Completo LIKE :search_term OR
                p.Apellido_Completo LIKE :search_term OR
                a.Descripcion_realizada LIKE :search_term
            )';
            $binds[':search_term'] = '%' . $searchTerm . '%';
        }
        
        $query .= ' 
            ORDER BY 
                a.Fecha_ingreso DESC, a.Fecha_creacion DESC
            LIMIT :limit OFFSET :offset
        ';

        $this->db->query($query);
        
        // Bindear parámetros de búsqueda
        foreach ($binds as $key => $value) {
            $this->db->bind($key, $value);
        }

        // Bindear parámetros de paginación
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);
        
        return $this->db->resultSet();
    }

    /**
     * Obtiene el conteo total de actividades activas, opcionalmente filtradas.
     */
    public function getTotalActividadesCount($searchTerm = ''){
        $query = '
            SELECT 
                COUNT(*) as total_count
            FROM 
                actividades a
            JOIN 
                alcances al ON a.Id_alcance = al.Id_alcance
            JOIN 
                personal p ON a.Id_personal = p.Id_personal
            WHERE 
                a.Estado = 1';
        
        $binds = [];

        if (!empty($searchTerm)) {
            $query .= ' AND (
                al.Descripcion LIKE :search_term OR
                p.Nombre_Completo LIKE :search_term OR
                p.Apellido_Completo LIKE :search_term OR
                a.Descripcion_realizada LIKE :search_term
            )';
            $binds[':search_term'] = '%' . $searchTerm . '%';
        }

        $this->db->query($query);
        foreach ($binds as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $row = $this->db->single();
        return $row->total_count;
    }
    
    /**
     * Obtiene un solo registro de actividad por ID.
     */
    public function getActividadById($id){
        $this->db->query('SELECT * FROM actividades WHERE Id_actividad = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Obtiene un registro de actividad por ID con detalles del Personal.
     * Incluye nombre y apellido del personal responsable.
     */
    public function getActividadByIdWithPersonal($id){
        $this->db->query('
            SELECT 
                a.*,
                p.Nombre_Completo AS Personal_Nombre,
                p.Apellido_Completo AS Personal_Apellido,
                al.Descripcion AS Alcance_Descripcion
            FROM 
                actividades a
            JOIN 
                personal p ON a.Id_personal = p.Id_personal
            JOIN 
                alcances al ON a.Id_alcance = al.Id_alcance
            WHERE 
                a.Id_actividad = :id
        ');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Agrega una nueva actividad.
     */
    public function addActividad($data){
        $this->db->query('INSERT INTO actividades (Id_alcance, Id_personal, Fecha_ingreso, Descripcion_realizada, Estado_actividad) 
                          VALUES (:id_alcance, :id_personal, :fecha_ingreso, :descripcion_realizada, :estado_actividad)');
        
        $this->db->bind(':id_alcance', $data['id_alcance']);
        $this->db->bind(':id_personal', $data['id_personal']);
        $this->db->bind(':fecha_ingreso', $data['fecha_ingreso']);
        $this->db->bind(':descripcion_realizada', $data['descripcion_realizada']);
        $this->db->bind(':estado_actividad', $data['estado_actividad']);

        return $this->db->execute();
    }

    /**
     * Actualiza un registro de actividad.
     */
    public function updateActividad($data){
        $this->db->query('UPDATE actividades SET 
                          Id_alcance = :id_alcance, 
                          Id_personal = :id_personal, 
                          Fecha_ingreso = :fecha_ingreso, 
                          Descripcion_realizada = :descripcion_realizada, 
                          Estado_actividad = :estado_actividad
                          WHERE Id_actividad = :id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':id_alcance', $data['id_alcance']);
        $this->db->bind(':id_personal', $data['id_personal']);
        $this->db->bind(':fecha_ingreso', $data['fecha_ingreso']);
        $this->db->bind(':descripcion_realizada', $data['descripcion_realizada']);
        $this->db->bind(':estado_actividad', $data['estado_actividad']);

        return $this->db->execute();
    }
    
    /**
     * Eliminación Lógica (Soft Delete): Establece Estado = 0.
     */
    public function deleteActividad($id){
        $this->db->query('UPDATE actividades SET Estado = 0 WHERE Id_actividad = :id');
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    /**
     * Obtiene actividades para un mes, año y personal específicos.
     * Retorna solo los datos necesarios para el calendario (Fecha y Estado).
     */
    // public function getActividadesByMonthAndPersonal($idPersonal, $year, $month){
    //     // Calcula el primer y último día del mes
    //     $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
    //     $endDate = date('Y-m-t', strtotime($startDate)); // t = número de días del mes

    //     $this->db->query('
    //         SELECT 
    //             Fecha_ingreso,
    //             Estado_actividad
    //         FROM 
    //             actividades 
    //         WHERE 
    //             Id_personal = :id_personal AND
    //             Fecha_ingreso BETWEEN :start_date AND :end_date AND
    //             Estado = 1
    //     ');

    //     $this->db->bind(':id_personal', $idPersonal);
    //     $this->db->bind(':start_date', $startDate);
    //     $this->db->bind(':end_date', $endDate);

    //     return $this->db->resultSet();
    // }

    /**
     * Obtiene actividades para un mes, año y personal específicos.
     * Incluye Id_actividad y Descripcion_realizada para el modal y la lista.
     */
    public function getActividadesByMonthAndPersonal($idPersonal, $year, $month){
        // Calcula el primer y último día del mes
        $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $endDate = date('Y-m-t', strtotime($startDate)); // t = número de días del mes

        $this->db->query('
            SELECT 
                Id_actividad,                     
                Fecha_ingreso,
                Estado_actividad,
                Descripcion_realizada             
            FROM 
                actividades 
            WHERE 
                Id_personal = :id_personal AND
                Fecha_ingreso BETWEEN :start_date AND :end_date AND
                Estado = 1
        ');

        $this->db->bind(':id_personal', $idPersonal);
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);

        return $this->db->resultSet();
    }

    /**
     * Obtiene actividades Completadas en un rango de fechas específico.
     */
    public function getCompletedActivitiesByDateRange($idPersonal, $fechaInicio, $fechaFin){
        // Utilizamos el estado 'Completada' y el rango de fechas
        $this->db->query('
            SELECT 
                a.Id_alcance,
                a.Id_actividad,
                a.Fecha_ingreso,
                a.Descripcion_realizada,
                al.Descripcion AS Alcance_Descripcion,
                c.Expediente AS Contrato_Expediente
            FROM 
                actividades a
            JOIN 
                alcances al ON a.Id_alcance = al.Id_alcance
            JOIN
                contratos c ON al.Id_contrato = c.Id_contrato
            WHERE 
                a.Id_personal = :id_personal AND
                a.Estado_actividad = "Completada" AND
                a.Fecha_ingreso BETWEEN :fecha_inicio AND :fecha_fin AND
                a.Estado = 1
            ORDER BY 
                a.Fecha_ingreso ASC
        ');

        $this->db->bind(':id_personal', $idPersonal);
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin', $fechaFin);

        return $this->db->resultSet();
    }

    /**
     * Obtiene las actividades ingresadas en un día específico para un personal.
     * Se usa para validar si existen actividades previas al agregar nuevas.
     */
    public function getActividadesByFechaAndPersonal($idPersonal, $fecha){
        $this->db->query('
            SELECT 
                a.Id_actividad,
                a.Id_alcance,
                a.Descripcion_realizada,
                a.Estado_actividad,
                al.Descripcion AS Alcance_Descripcion
            FROM 
                actividades a
            JOIN 
                alcances al ON a.Id_alcance = al.Id_alcance
            WHERE 
                a.Id_personal = :id_personal AND
                a.Fecha_ingreso = :fecha AND
                a.Estado = 1
            ORDER BY
                a.Id_alcance ASC
        ');

        $this->db->bind(':id_personal', $idPersonal);
        $this->db->bind(':fecha', $fecha);

        return $this->db->resultSet();
    }

    /**
     * Obtiene estadísticas de actividades para personal de una división.
     * Agrupa por estado de actividad (completada, en progreso, pendiente).
     */
    public function getActividadesStatsByDivision($divisionId){
        $this->db->query('
            SELECT 
                a.Estado_actividad,
                COUNT(*) as cantidad,
                p.Id_personal,
                p.Nombre_Completo,
                p.Apellido_Completo
            FROM 
                actividades a
            JOIN 
                personal p ON a.Id_personal = p.Id_personal
            WHERE 
                p.Id_division = :division_id AND
                a.Estado = 1
            GROUP BY 
                a.Estado_actividad, p.Id_personal, p.Nombre_Completo, p.Apellido_Completo
            ORDER BY 
                p.Apellido_Completo, p.Nombre_Completo, a.Estado_actividad
        ');
        $this->db->bind(':division_id', $divisionId);
        return $this->db->resultSet();
    }

    /**
     * Obtiene actividades de una división agrupadas por semana.
     */
    public function getActividadesByWeekAndDivision($divisionId, $weeksBack = 4){
        $this->db->query('
            SELECT 
                WEEK(a.Fecha_ingreso) as week,
                YEAR(a.Fecha_ingreso) as year,
                a.Estado_actividad,
                COUNT(*) as cantidad
            FROM 
                actividades a
            JOIN 
                personal p ON a.Id_personal = p.Id_personal
            WHERE 
                p.Id_division = :division_id AND
                a.Estado = 1 AND
                a.Fecha_ingreso >= DATE_SUB(CURDATE(), INTERVAL :weeks WEEK)
            GROUP BY 
                WEEK(a.Fecha_ingreso), YEAR(a.Fecha_ingreso), a.Estado_actividad
            ORDER BY 
                year DESC, week DESC, a.Estado_actividad
        ');
        $this->db->bind(':division_id', $divisionId);
        $this->db->bind(':weeks', $weeksBack);
        return $this->db->resultSet();
    }

    /**
     * Obtiene resumen general de actividades de una división.
     */
    public function getSummaryStatsDivision($divisionId){
        $this->db->query('
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN a.Estado_actividad = "Completada" THEN 1 ELSE 0 END) as completadas,
                SUM(CASE WHEN a.Estado_actividad = "En Progreso" THEN 1 ELSE 0 END) as en_progreso,
                SUM(CASE WHEN a.Estado_actividad = "Pendiente" THEN 1 ELSE 0 END) as pendientes,
                COUNT(DISTINCT a.Id_personal) as cantidad_personal
            FROM 
                actividades a
            JOIN 
                personal p ON a.Id_personal = p.Id_personal
            WHERE 
                p.Id_division = :division_id AND
                a.Estado = 1
        ');
        $this->db->bind(':division_id', $divisionId);
        return $this->db->single();
    }

}