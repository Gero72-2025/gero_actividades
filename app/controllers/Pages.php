<?php
class Pages extends Controller {
    private $personalModel;
    private $actividadModel;

    public function __construct(){
        if(!isLoggedIn()){
            redirect('users/login');
        }
        $this->personalModel = $this->model('PersonalModel');
        $this->actividadModel = $this->model('ActividadModel');
    }

    public function index(){
        $userId = $_SESSION['user_id'];
        
        // Verificar si el usuario es jefe de división
        if($this->personalModel->isJefeDivision($userId)){
            // Si es jefe, mostrar dashboard
            $division = $this->personalModel->getDivisionWhereChief($userId);
            $personalDivision = $this->personalModel->getPersonalByDivision($division->Id_Division);
            $stats = $this->actividadModel->getSummaryStatsDivision($division->Id_Division);
            $statsByPersonal = $this->actividadModel->getActividadesStatsByDivision($division->Id_Division);
            $statsByWeek = $this->actividadModel->getActividadesByWeekAndDivision($division->Id_Division, 4);
            
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
            // Si no es jefe, mostrar calendario de actividades
            $this->view('pages/index');
        }
    }
}
