<?php
class Alcances extends Controller {
    private $alcanceModel;
    private $contratoModel; // Necesario para el dropdown

    public function __construct(){
        if(!isLoggedIn()){
            redirect('users/login');
        }

        $this->alcanceModel = $this->model('AlcanceModel');
        // Necesitas el modelo de Contratos para poblar el dropdown de selección
        $this->contratoModel = $this->model('ContratoModel'); 
    }

    // Muestra la lista de alcances
    public function index(){
        // Verificar permiso para ver alcances
        $this->verificarAcceso('alcances', 'ver');
        
        $alcances = $this->alcanceModel->getAlcances();

        $data = [
            'title' => 'Gestión de Alcances de Contrato',
            'alcances' => $alcances
        ];

        $this->view('alcances/index', $data);
    }

    // Añadir Alcance
    public function add(){
        // Verificar permiso para crear alcances
        $this->verificarAcceso('alcances', 'crear');
        
        // Cargar datos de contratos para el dropdown
        $contratos = $this->contratoModel->getContratos();
        
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
        $contratos = $this->contratoModel->getContratos();

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
        
        // Asume que AlcanceModel::getAlcances() obtiene una lista de objetos
        $alcances = $this->alcanceModel->getAlcances(); 
        echo json_encode($alcances);
    }
}