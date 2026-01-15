<?php
class Personal extends Controller {
    private $personalModel;
    private $divisionModel;
    private $contratoModel;
    private $usuarioModel;

    public function __construct(){
        if(!isLoggedIn()){
            redirect('users/login');
        }

        $this->personalModel = $this->model('PersonalModel');
        $this->divisionModel = $this->model('DivisionModel'); 
        $this->contratoModel = $this->model('contratoModel'); 
        $this->usuarioModel = $this->model('UsuarioModel');
    }

    // Muestra la lista de personal
    public function index(){
        // Verificar permiso para ver personal
        $this->verificarAcceso('personal', 'ver');
        
        $personal = $this->personalModel->getPersonal();

        $data = [
            'title' => 'Gestión de Personal',
            'personal' => $personal
        ];

        $this->view('personal/index', $data);
    }

    // Añadir Personal (Lógica de validación y guardado)
    public function add(){
        // Verificar permiso para crear personal
        $this->verificarAcceso('personal', 'crear');
        
        // Cargar datos para dropdowns (Necesarios tanto en GET como en POST si hay errores)
        $divisiones = $this->divisionModel->getDivisions();
        $contratos = $this->contratoModel->getContratosDisponibles();
        $usuarios = $this->usuarioModel->getUsuariosNoAsignados();
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            // 1. Saneamiento de datos POST
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'title' => 'Añadir Nuevo Personal',
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'puesto' => trim($_POST['puesto']),
                'tipo_servicio' => isset($_POST['tipo_servicio']) ? trim($_POST['tipo_servicio']) : '',
                'id_division' => trim($_POST['id_division']),
                'id_contrato' => trim($_POST['id_contrato']),
                'id_usuario' => trim($_POST['id_usuario']), 
                // Errores e información de dropdowns
                'nombre_err' => '',
                'apellido_err' => '',
                'puesto_err' => '',
                'tipo_servicio_err' => '',
                'id_usuario_err' => '',
                'divisiones' => $divisiones,
                'contratos' => $contratos,
                'usuarios' => $usuarios,
            ];

            // 2. Validación
            if(empty($data['nombre'])){
                $data['nombre_err'] = 'El nombre es obligatorio.';
            }
            if(empty($data['apellido'])){
                $data['apellido_err'] = 'El apellido es obligatorio.';
            }
            if($data['tipo_servicio'] === '' || !in_array($data['tipo_servicio'], ['0','1'], true)){
                $data['tipo_servicio_err'] = 'Seleccione el tipo de servicio.';
            }
            if(empty($data['id_usuario'])){
                $data['id_usuario_err'] = 'Debe seleccionar un usuario para vincular.';
            }
            // NOTA: 'puesto', 'id_division' e 'id_contrato' son opcionales (NULL en la BD)

            // 3. Comprobar errores
            if(empty($data['nombre_err']) && empty($data['apellido_err']) && empty($data['id_usuario_err']) && empty($data['tipo_servicio_err'])){
                
                // Sin errores: Guardar
                if($this->personalModel->addPersonal($data)){
                    // Redirección exitosa
                    redirect('personal/index');
                } else {
                    die('Algo salió mal al intentar guardar el personal.');
                }
            } else {
                // Errores: Cargar vista con errores
                $this->view('personal/add', $data);
            }

        } else {
            // GET request: Cargar formulario vacío
            $data = [
                'title' => 'Añadir Nuevo Personal',
                'nombre' => '',
                'apellido' => '',
                'puesto' => '',
                'tipo_servicio' => '1',
                'id_division' => '',
                'id_contrato' => '',
                'id_usuario' => '', 
                'nombre_err' => '',
                'apellido_err' => '',
                'puesto_err' => '',
                'tipo_servicio_err' => '',
                'id_usuario_err' => '',
                'divisiones' => $divisiones,
                'contratos' => $contratos,
                'usuarios' => $usuarios,
            ];

            $this->view('personal/add', $data);
        }
    }

    // Editar Personal
    public function edit($id){
        // Verificar permiso para editar personal
        $this->verificarAcceso('personal', 'editar');
        
        // Obtener el personal para saber qué contrato tiene asignado actualmente
        $personal_actual = $this->personalModel->getPersonalById($id);
        if(!$personal_actual){
            redirect('personal/index');
        }
        
        // Cargar datos para dropdowns (Necesarios tanto en GET como en POST si hay errores)
        $divisiones = $this->divisionModel->getDivisions();
        // Obtener contratos disponibles, excluyendo el que ya está asignado a este personal
        $contratos = $this->contratoModel->getContratosDisponibles($personal_actual->Id_contrato);
        
        // Cargar la lista de usuarios no asignados. Se asegura que siempre sea un array.
        $usuarios_no_asignados_raw = $this->usuarioModel->getUsuariosNoAsignados();
        $usuarios_no_asignados = is_array($usuarios_no_asignados_raw) ? $usuarios_no_asignados_raw : [];
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            // 1. Saneamiento de datos POST
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'id' => $id,
                'title' => 'Editar Personal',
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'puesto' => trim($_POST['puesto']),
                'tipo_servicio' => isset($_POST['tipo_servicio']) ? trim($_POST['tipo_servicio']) : '',
                'id_division' => trim($_POST['id_division']),
                'id_contrato' => trim($_POST['id_contrato']),
                'id_usuario' => trim($_POST['id_usuario']), 
                // Errores e información de dropdowns
                'nombre_err' => '',
                'apellido_err' => '',
                'puesto_err' => '',
                'tipo_servicio_err' => '',
                'id_usuario_err' => '',
                'divisiones' => $divisiones,
                'contratos' => $contratos,
                // 'usuarios' se define al final
            ];

            // 2. Validación (Lógica existente)
            if(empty($data['nombre'])){
                $data['nombre_err'] = 'El nombre es obligatorio.';
            }
            if(empty($data['apellido'])){
                $data['apellido_err'] = 'El apellido es obligatorio.';
            }
            if($data['tipo_servicio'] === '' || !in_array($data['tipo_servicio'], ['0','1'], true)){
                $data['tipo_servicio_err'] = 'Seleccione el tipo de servicio.';
            }
            if(empty($data['id_usuario'])){
                $data['id_usuario_err'] = 'El usuario vinculado no puede estar vacío.';
            }

            // 3. Comprobar errores
            if(empty($data['nombre_err']) && empty($data['apellido_err']) && empty($data['id_usuario_err']) && empty($data['tipo_servicio_err'])){
                
                // Sin errores: Actualizar (Lógica existente)
                if($this->personalModel->updatePersonal($data)){
                    redirect('personal/index');
                } else {
                    die('Algo salió mal al intentar actualizar.');
                }
            } else {
                // Errores: Necesitamos reconstruir la lista de usuarios para que el actual esté seleccionado.
                
                // Obtener el usuario que debería estar seleccionado 
                $usuario_actual = $this->usuarioModel->getUsuarioById($data['id_usuario']);
                
                // Inicializar la lista del dropdown con los usuarios no asignados (ya verificado como array)
                $usuarios_dropdown = $usuarios_no_asignados;
                
                if ($usuario_actual) {
                    $is_present = false;
                    
                    // Comprobación de tipo is_object() para evitar Warnings en la iteración
                    foreach ($usuarios_dropdown as $user) {
                        if (is_object($user) && $user->Id_usuario == $usuario_actual->Id_usuario) {
                            $is_present = true;
                            break;
                        }
                    }
                    if (!$is_present) {
                         // array_unshift es seguro porque $usuarios_dropdown es un array
                        array_unshift($usuarios_dropdown, $usuario_actual);
                    }
                }
                $data['usuarios'] = $usuarios_dropdown;

                // Cargar vista con errores
                $this->view('personal/edit', $data);
            }

        } else {
            // GET request: Cargar formulario con datos existentes
            $personal = $this->personalModel->getPersonalById($id);
            
            if(!$personal){
                redirect('personal/index');
            }

            // --- Lógica para el Dropdown de Usuarios en GET ---
            // Obtener el usuario actualmente asignado
            $usuario_actual = $this->usuarioModel->getUsuarioById($personal->Id_usuario);
            
            // Inicializar la lista del dropdown con los usuarios no asignados (ya verificado como array)
            $usuarios_dropdown = $usuarios_no_asignados;

            // Combinar la lista de no asignados con el usuario actual
            if ($usuario_actual) {
                $is_present = false;
                
                // Comprobación de tipo is_object() para evitar Warnings en la iteración
                foreach ($usuarios_dropdown as $user) {
                    if (is_object($user) && $user->Id_usuario == $usuario_actual->Id_usuario) {
                        $is_present = true;
                        break;
                    }
                }
                if (!$is_present) {
                     // Añadir al inicio para asegurar que el valor actual esté disponible
                    array_unshift($usuarios_dropdown, $usuario_actual);
                }
            }
            // --- Fin Lógica de Dropdown ---

            $data = [
                'id' => $id,
                'title' => 'Editar Personal',
                'nombre' => $personal->Nombre_Completo,
                'apellido' => $personal->Apellido_Completo,
                'puesto' => $personal->Puesto,
                'tipo_servicio' => $personal->Tipo_servicio,
                'id_division' => $personal->Id_division,
                'id_contrato' => $personal->Id_contrato,
                'id_usuario' => $personal->Id_usuario, 
                'nombre_err' => '',
                'apellido_err' => '',
                'puesto_err' => '',
                'tipo_servicio_err' => '',
                'id_usuario_err' => '',
                'divisiones' => $divisiones,
                'contratos' => $contratos,
                'usuarios' => $usuarios_dropdown, // Usar la lista combinada
            ];

            $this->view('personal/edit', $data);
        }
    }

    // Eliminar Personal (Lógica de Soft Delete)
    public function delete($id){
        // Verificar permiso para eliminar personal
        $this->verificarAcceso('personal', 'eliminar');
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->personalModel->deletePersonal($id)){
                // Puedes agregar un setFlashMessage() si lo tienes implementado
                redirect('personal/index');
            } else {
                die('Algo salió mal al intentar eliminar el registro de personal.');
            }
        } else {
            redirect('personal/index');
        }
    }
}