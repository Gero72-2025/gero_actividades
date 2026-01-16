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
            
            $data = [
                'title' => 'Dashboard - Gerente',
                'divisions' => $allDivisions,
                'stats_by_division' => $statsAllDivisions,
                'stats_by_week' => $statsByWeek
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
            // Si no es jefe ni gerente, mostrar vista general
            $this->view('pages/index');
        }
    }
}
