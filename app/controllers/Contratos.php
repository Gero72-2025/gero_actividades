<?php
class Contratos extends Controller {
    private $contratoModel;
        private $divisionModel;
        private $personalModel;

    public function __construct(){
        if(!isLoggedIn()){
            redirect('users/login');
        }

        $this->contratoModel = $this->model('ContratoModel');
            $this->divisionModel = $this->model('DivisionModel');
            $this->personalModel = $this->model('PersonalModel');
    }

    // Muestra la lista de contratos
    public function index(){
        // Verificar permiso para ver contratos
        $this->verificarAcceso('contratos', 'ver');
        
        $divisionIdUsuario = null;
        $personal = $this->personalModel->getPersonalByUserId($_SESSION['user_id']);
        if($personal && !empty($personal->Id_division)){
            $divisionIdUsuario = $personal->Id_division;
        }

        $contratos = $this->contratoModel->getContratos($divisionIdUsuario);

        // Verificar si cada contrato tiene alcances activos
        foreach($contratos as $contrato){
            $contrato->tiene_alcances = $this->contratoModel->hasAlcancesActivos($contrato->Id_contrato);
        }

        $data = [
            'title' => 'Gestión de Contratos',
            'contratos' => $contratos
        ];

        $this->view('contratos/index', $data);
    }

    // Añadir Contrato
    public function add(){
        // Verificar permiso para crear contratos
        $this->verificarAcceso('contratos', 'crear');
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'descripcion' => trim($_POST['descripcion']),
                'numero_pagos' => trim($_POST['numero_pagos']),
                'inicio_contrato' => trim($_POST['inicio_contrato']),
                'fin_contrato' => trim($_POST['fin_contrato']),
                'expediente' => trim($_POST['expediente']),
                'id_division' => trim($_POST['id_division']),
                'contrato_activo' => isset($_POST['contrato_activo']) ? trim($_POST['contrato_activo']) : 1,
                'descripcion_err' => '',
                'numero_pagos_err' => '',
                'inicio_contrato_err' => '',
                'expediente_err' => '',
                'id_division_err' => '',
                'divisiones' => $this->divisionModel->getDivisions()
            ];

            // 1. Validar datos
            if(empty($data['descripcion'])){
                $data['descripcion_err'] = 'Por favor ingrese una descripción.';
            }
            if(empty($data['inicio_contrato'])){
                $data['inicio_contrato_err'] = 'Por favor ingrese la fecha de inicio.';
            }
            // Validación simple para número de pagos
            if(empty($data['numero_pagos']) || !is_numeric($data['numero_pagos']) || $data['numero_pagos'] < 1){
                $data['numero_pagos_err'] = 'El número de pagos es requerido y debe ser un número positivo.';
            }
            if(empty($data['id_division'])){
                $data['id_division_err'] = 'Seleccione la división del contrato.';
            }

            // 2. Si no hay errores, proceder
            if(empty($data['descripcion_err']) && empty($data['inicio_contrato_err']) && empty($data['numero_pagos_err']) && empty($data['id_division_err'])){
                
                if($this->contratoModel->addContrato($data)){
                    // Si tienes flash messages, úsalos aquí
                    redirect('contratos/index');
                } else {
                    die('Algo salió mal al intentar guardar el contrato.');
                }
            } else {
                // Cargar vista con errores
                $data['title'] = 'Añadir Contrato';
                $this->view('contratos/add', $data);
            }

        } else {
            // GET request: Cargar formulario vacío
            $data = [
                'title' => 'Añadir Contrato',
                'descripcion' => '',
                'numero_pagos' => '',
                'inicio_contrato' => '',
                'fin_contrato' => '',
                'expediente' => '',
                'id_division' => '',
                'contrato_activo' => 1,
                'descripcion_err' => '',
                'numero_pagos_err' => '',
                'inicio_contrato_err' => '',
                'id_division_err' => '',
                'divisiones' => $this->divisionModel->getDivisions()
            ];

            $this->view('contratos/add', $data);
        }
    }

    // Editar Contrato
    public function edit($id){
        // Verificar permiso para editar contratos
        $this->verificarAcceso('contratos', 'editar');
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Lógica de POST y actualización
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'id' => $id,
                'descripcion' => trim($_POST['descripcion']),
                'numero_pagos' => trim($_POST['numero_pagos']),
                'inicio_contrato' => trim($_POST['inicio_contrato']),
                'fin_contrato' => trim($_POST['fin_contrato']),
                'expediente' => trim($_POST['expediente']),
                'id_division' => trim($_POST['id_division']),
                'contrato_activo' => isset($_POST['contrato_activo']) ? trim($_POST['contrato_activo']) : 1,
                'descripcion_err' => '',
                'numero_pagos_err' => '',
                'inicio_contrato_err' => '',
                'id_division_err' => '',
                'title' => 'Editar Contrato'
            ];
            
            // 1. Validar
            if(empty($data['descripcion'])){ $data['descripcion_err'] = 'Por favor ingrese una descripción.'; }
            if(empty($data['inicio_contrato'])){ $data['inicio_contrato_err'] = 'Por favor ingrese la fecha de inicio.'; }
            if(empty($data['numero_pagos']) || !is_numeric($data['numero_pagos']) || $data['numero_pagos'] < 1){
                $data['numero_pagos_err'] = 'El número de pagos es requerido y debe ser un número positivo.';
            }
            if(empty($data['id_division'])){
                $data['id_division_err'] = 'Seleccione la división del contrato.';
            }
            
            // 2. Si no hay errores
            if(empty($data['descripcion_err']) && empty($data['inicio_contrato_err']) && empty($data['numero_pagos_err']) && empty($data['id_division_err'])){
                if($this->contratoModel->updateContrato($data)){
                    redirect('contratos/index');
                } else {
                    die('Algo salió mal al intentar actualizar.');
                }
            } else {
                $data['divisiones'] = $this->divisionModel->getDivisions();
                $this->view('contratos/edit', $data);
            }

        } else {
            // GET request: Cargar formulario con datos existentes
            $contrato = $this->contratoModel->getContratoById($id);

            $data = [
                'id' => $id,
                'title' => 'Editar Contrato',
                'descripcion' => $contrato->Descripcion,
                'numero_pagos' => $contrato->Numero_pagos,
                'expediente' => $contrato->Expediente,
                'inicio_contrato' => $contrato->Inicio_contrato,
                'fin_contrato' => $contrato->Fin_contrato,
                'id_division' => $contrato->Id_division,
                'contrato_activo' => $contrato->Contrato_activo,
                'descripcion_err' => '',
                'numero_pagos_err' => '',
                'inicio_contrato_err' => '',
                'id_division_err' => '',
                'divisiones' => $this->divisionModel->getDivisions()
            ];
            
            $this->view('contratos/edit', $data);
        }
    }

    // Eliminar Contrato (Soft Delete)
    public function delete($id){
        // Verificar permiso para eliminar contratos
        $this->verificarAcceso('contratos', 'eliminar');
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->contratoModel->deleteContrato($id)){
                redirect('contratos/index');
            } else {
                die('Algo salió mal al intentar eliminar el contrato.');
            }
        } else {
            redirect('contratos/index');
        }
    }
}