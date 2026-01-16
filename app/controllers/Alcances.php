<?php
class Alcances extends Controller {
    private $alcanceModel;
    private $contratoModel; // Necesario para el dropdown
    private $personalModel;

    public function __construct(){
        if(!isLoggedIn()){
            redirect('users/login');
        }

        $this->alcanceModel = $this->model('AlcanceModel');
        // Necesitas el modelo de Contratos para poblar el dropdown de selección
        $this->contratoModel = $this->model('ContratoModel'); 
        $this->personalModel = $this->model('PersonalModel');
    }

    // Muestra la lista de alcances
    public function index($page = 1){
        // Verificar permiso para ver alcances
        $this->verificarAcceso('alcances', 'ver');
        
        $divisionIdUsuario = null;
        $personal = $this->personalModel->getPersonalByUserId($_SESSION['user_id']);
        if($personal && !empty($personal->Id_division)){
            $divisionIdUsuario = $personal->Id_division;
        }

        $alcances = $this->alcanceModel->getAlcances($divisionIdUsuario);

        // Agrupar por contrato
        $agrupados = [];
        foreach($alcances as $alcance){
            $idContrato = $alcance->Id_contrato;
            if(!isset($agrupados[$idContrato])){
                $agrupados[$idContrato] = [
                    'contrato' => [
                        'Id_contrato' => $idContrato,
                        'Expediente' => $alcance->Expediente,
                        'Descripcion' => $alcance->Contrato_Descripcion,
                        'Contrato_activo' => $alcance->Contrato_activo,
                        'Inicio_contrato' => $alcance->Inicio_contrato,
                        'Fin_contrato' => $alcance->Fin_contrato,
                    ],
                    'alcances' => []
                ];
            }
            $alcance->tiene_actividades = $alcance->actividades_count > 0;
            $agrupados[$idContrato]['alcances'][] = $alcance;
        }

        // Paginación
        $itemsPerPage = 10;
        $totalContratos = count($agrupados);
        $totalPages = ceil($totalContratos / $itemsPerPage);
        $currentPage = max(1, min($page, $totalPages ?: 1));
        $offset = ($currentPage - 1) * $itemsPerPage;

        // Obtener solo los contratos de la página actual
        $agrupadosPaginados = array_slice($agrupados, $offset, $itemsPerPage, true);

        $data = [
            'title' => 'Gestión de Alcances de Contrato',
            'agrupados' => $agrupadosPaginados,
            'alcances' => $alcances,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalContratos' => $totalContratos
        ];

        $this->view('alcances/index', $data);
    }

    // Añadir Alcance
    public function add(){
        // Verificar permiso para crear alcances
        $this->verificarAcceso('alcances', 'crear');
        
        // Cargar datos de contratos para el dropdown
        $divisionIdUsuario = null;
        $personal = $this->personalModel->getPersonalByUserId($_SESSION['user_id']);
        if($personal && !empty($personal->Id_division)){
            $divisionIdUsuario = $personal->Id_division;
        }
        $contratos = $this->contratoModel->getContratos($divisionIdUsuario);
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'id_contrato' => trim($_POST['id_contrato']),
                'descripcion' => trim($_POST['descripcion']),
                'id_contrato_err' => '',
                'descripcion_err' => '',
                'title' => 'Añadir Alcance',
                'contratos' => $contratos
            ];

            // 1. Validar datos
            if(empty($data['id_contrato'])){
                $data['id_contrato_err'] = 'Debe seleccionar un contrato.';
            }
            if(empty($data['descripcion'])){
                $data['descripcion_err'] = 'La descripción del alcance es obligatoria.';
            }

            // 2. Si no hay errores, proceder
            if(empty($data['id_contrato_err']) && empty($data['descripcion_err'])){
                
                if($this->alcanceModel->addAlcance($data)){
                    redirect('alcances/index');
                } else {
                    die('Algo salió mal al intentar guardar el alcance.');
                }
            } else {
                // Cargar vista con errores
                $this->view('alcances/add', $data);
            }

        } else {
            // GET request: Cargar formulario vacío
            $data = [
                'title' => 'Añadir Alcance',
                'id_contrato' => '',
                'descripcion' => '',
                'id_contrato_err' => '',
                'descripcion_err' => '',
                'contratos' => $contratos
            ];

            $this->view('alcances/add', $data);
        }
    }

    // Editar Alcance
    public function edit($id){
        // Verificar permiso para editar alcances
        $this->verificarAcceso('alcances', 'editar');
        
        // Cargar datos de contratos para el dropdown
        $divisionIdUsuario = null;
        $personal = $this->personalModel->getPersonalByUserId($_SESSION['user_id']);
        if($personal && !empty($personal->Id_division)){
            $divisionIdUsuario = $personal->Id_division;
        }
        $contratos = $this->contratoModel->getContratos($divisionIdUsuario);

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Lógica de POST y actualización
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'id' => $id,
                'id_contrato' => trim($_POST['id_contrato']),
                'descripcion' => trim($_POST['descripcion']),
                'id_contrato_err' => '',
                'descripcion_err' => '',
                'title' => 'Editar Alcance',
                'contratos' => $contratos
            ];
            
            // 1. Validar
            if(empty($data['id_contrato'])){
                $data['id_contrato_err'] = 'Debe seleccionar un contrato.';
            }
            if(empty($data['descripcion'])){
                $data['descripcion_err'] = 'La descripción del alcance es obligatoria.';
            }
            
            // 2. Si no hay errores
            if(empty($data['id_contrato_err']) && empty($data['descripcion_err'])){
                if($this->alcanceModel->updateAlcance($data)){
                    redirect('alcances/index');
                } else {
                    die('Algo salió mal al intentar actualizar.');
                }
            } else {
                $this->view('alcances/edit', $data);
            }

        } else {
            // GET request: Cargar formulario con datos existentes
            $alcance = $this->alcanceModel->getAlcanceById($id);

            if(!$alcance){ redirect('alcances/index'); }

            $data = [
                'id' => $id,
                'title' => 'Editar Alcance',
                'id_contrato' => $alcance->Id_contrato,
                'descripcion' => $alcance->Descripcion,
                'id_contrato_err' => '',
                'descripcion_err' => '',
                'contratos' => $contratos
            ];
            
            $this->view('alcances/edit', $data);
        }
    }

    // Eliminar Alcance (Soft Delete)
    public function delete($id){
        // Verificar permiso para eliminar alcances
        $this->verificarAcceso('alcances', 'eliminar');
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->alcanceModel->deleteAlcance($id)){
                redirect('alcances/index');
            } else {
                die('Algo salió mal al intentar eliminar el alcance.');
            }
        } else {
            redirect('alcances/index');
        }
    }

    /**
     * API Endpoint para obtener todos los alcances activos (para dropdowns/modales).
     */
    public function get_all_active(){
        header('Content-Type: application/json');
        if(!isLoggedIn()){
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no autenticado']);
            return;
        }
        
        // Obtener el usuario logueado y su información de personal
        $userId = $_SESSION['user_id'];
        $personal = $this->personalModel->getPersonalByUserId($userId);
        
        // Si el usuario tiene un registro de personal, obtener solo alcances de su contrato activo
        if($personal && $personal->Id_contrato){
            $alcances = $this->alcanceModel->getAlcancesByActiveContract($personal->Id_personal);
        } else {
            // Si no tiene personal asignado, devolver lista vacía
            $alcances = [];
        }
        
        echo json_encode($alcances);
    }
}