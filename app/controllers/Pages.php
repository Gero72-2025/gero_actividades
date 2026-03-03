<?php
class Pages extends Controller {
    private $personalModel;
    private $actividadModel;
    private $roleModel;

    public function __construct(){
        if(!isLoggedIn()){
            redirect('users/login');
        }
        $this->personalModel = $this->model('PersonalModel');
        $this->actividadModel = $this->model('ActividadModel');
        $this->roleModel = $this->model('RoleModel');
    }

    public function index(){
        $userId = $_SESSION['user_id'];
        
        // Verificar si el usuario es Gerente o Administrador
        $userRoles = $this->roleModel->getRolesByUser($userId);
        $isGerenteOrAdmin = false;
        
        if($userRoles){
            foreach($userRoles as $role){
                if($role->Nombre === 'Gerente' || $role->Nombre === 'Administrador'){
                    $isGerenteOrAdmin = true;
                    break;
                }
            }
        }
        
        if($isGerenteOrAdmin){
            // Mostrar dashboard de gerente (todas las divisiones)
            $allDivisions = $this->actividadModel->getAllDivisions();
            $statsAllDivisions = $this->actividadModel->getSummaryStatsAllDivisions();
            $statsByWeek = $this->actividadModel->getActividadesByWeekAllDivisions(4);
            $statsByMonth = $this->actividadModel->getActividadesByMonthAllDivisions(6);
            $detailedStats = $this->actividadModel->getDetailedStatsByAllDivisions();
            
            $data = [
                'title' => 'Dashboard - Gerente',
                'divisions' => $allDivisions,
                'stats_by_division' => $statsAllDivisions,
                'stats_by_week' => $statsByWeek,
                'stats_by_month' => $statsByMonth,
                'detailed_stats' => $detailedStats
            ];
            
            $this->view('pages/dashboard_gerente', $data);
        } else if($this->personalModel->isJefeDivision($userId)){
            // Si es jefe, mostrar dashboard de jefe
            $division = $this->personalModel->getDivisionWhereChief($userId);
            $personalDivision = $this->personalModel->getPersonalByDivision($division->Id_Division);
            $stats = $this->actividadModel->getSummaryStatsDivision($division->Id_Division);
            $statsByPersonal = $this->actividadModel->getActividadesStatsByDivision($division->Id_Division);
            $statsByWeek = $this->actividadModel->getActividadesByWeekAndDivision($division->Id_Division, 4);
            
            // Corregir cantidad_personal: debe ser el total de personal activo en la división
            if($stats){
                $stats->cantidad_personal = count($personalDivision);
            }
            
            $data = [
                'title' => 'Dashboard - Jefe de División',
                'division' => $division,
                'personal_list' => $personalDivision,
                'stats' => $stats,
                'stats_by_personal' => $statsByPersonal,
                'stats_by_week' => $statsByWeek
            ];
            
            $this->view('pages/dashboard_jefe', $data);
        } else {
            // Si no es jefe ni gerente, mostrar vista general con calendario
            $personal = $this->personalModel->getPersonalByUserId($userId);
            $contrato = null;
            $fechaInicio = null;
            $fechaFin = null;
            
            if($personal && $personal->Id_contrato){
                $contratoModel = $this->model('ContratoModel');
                $contrato = $contratoModel->getContratoById($personal->Id_contrato);
                if($contrato){
                    $fechaInicio = $contrato->Inicio_contrato;
                    $fechaFin = $contrato->Fin_contrato;
                }
            }
            
            $data = [
                'title' => 'Calendario de Actividades',
                'fecha_inicio_contrato' => $fechaInicio,
                'fecha_fin_contrato' => $fechaFin
            ];
            
            $this->view('pages/index', $data);
        }
    }

    /**
     * Devuelve en JSON el detalle de actividades por personal para una división.
     * Se usa en el modal del dashboard de gerente.
     */
    public function divisionStats($divisionId = null){
        header('Content-Type: application/json');

        if(!$divisionId || !is_numeric($divisionId)){
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de división inválido']);
            return;
        }

        // Validar rol de acceso (solo Gerente o Administrador)
        $userId = $_SESSION['user_id'] ?? null;
        $userRoles = $this->roleModel->getRolesByUser($userId);
        $isGerenteOrAdmin = false;

        if($userRoles){
            foreach($userRoles as $role){
                if($role->Nombre === 'Gerente' || $role->Nombre === 'Administrador'){
                    $isGerenteOrAdmin = true;
                    break;
                }
            }
        }

        if(!$isGerenteOrAdmin){
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $rawStats = $this->actividadModel->getActividadesStatsByDivisionWithZeros((int)$divisionId);

        $totals = [
            'completadas' => 0,
            'en_progreso' => 0,
            'pendientes' => 0,
            'total' => 0
        ];

        $data = [];

        if($rawStats){
            foreach($rawStats as $row){
                $item = [
                    'id_personal' => $row->Id_personal,
                    'nombre' => trim($row->Apellido_Completo . ' ' . $row->Nombre_Completo),
                    'completadas' => (int)$row->completadas,
                    'en_progreso' => (int)$row->en_progreso,
                    'pendientes' => (int)$row->pendientes,
                    'total' => (int)$row->total
                ];

                $totals['completadas'] += $item['completadas'];
                $totals['en_progreso'] += $item['en_progreso'];
                $totals['pendientes'] += $item['pendientes'];
                $totals['total'] += $item['total'];

                $data[] = $item;
            }
        }

        echo json_encode([
            'success' => true,
            'data' => $data,
            'totals' => $totals
        ]);
    }
}
