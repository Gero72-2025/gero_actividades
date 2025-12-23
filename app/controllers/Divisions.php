<?php
class Divisions extends Controller {
    private $divisionModel;
    private $personalModel;
    
    public function __construct(){
        // Restricción de seguridad: Sólo usuarios logueados pueden acceder a este módulo.
        if(!isLoggedIn()){
            redirect('users/login');
        }

        // Cargar Modelo de Division
        $this->divisionModel = $this->model('DivisionModel'); 

        // Cargar Modelo de Personal para manejar jefes de división
        $this->personalModel = $this->model('PersonalModel');
        
        // NOTA: Si necesitas información del personal para los jefes,
        // tendrías que cargar también el modelo 'Personal' aquí:
        // $this->personalModel = $this->model('Personal');
    }

    // Método por defecto: Muestra la lista de divisiones
    public function index(){
        // Verificar permiso para ver divisiones
        $this->verificarAcceso('divisions', 'ver');
        
        // Obtener las divisiones desde el modelo
        $divisions = $this->divisionModel->getDivisions();

        $data = [
            'title' => 'Gestión de Divisiones',
            'divisions' => $divisions
        ];

        $this->view('divisions/index', $data);
    }
    
    // Método para agregar una nueva división (Lógica de POST y GET)
    public function add(){
        // Verificar permiso para crear divisiones
        $this->verificarAcceso('divisions', 'crear');
        
        // 1. Chequear si es un POST request
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            // Limpiar los datos del POST
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'nombre' => trim($_POST['nombre']),
                'siglas' => trim($_POST['siglas']),
                // Nota: Usamos null si el campo está vacío para que SQL acepte el campo 'Id_personal_jefe' (NULL por defecto)
                'id_jefe' => empty($_POST['id_jefe']) ? null : (int)$_POST['id_jefe'],
                'nombre_err' => '',
                'siglas_err' => '',
                'title' => ''
            ];

            // 2. Validar los campos obligatorios
            if(empty($data['nombre'])){
                $data['nombre_err'] = 'Por favor ingrese el nombre de la división';
            }
            if(empty($data['siglas'])){
                $data['siglas_err'] = 'Por favor ingrese las siglas de la división';
            }
            
            // **TODO:** Agregar validación para que no existan nombres o siglas duplicadas (opcional, pero recomendado).

            // 3. Asegurarse de que no haya errores
            if(empty($data['nombre_err']) && empty($data['siglas_err'])){
                // Validado: Intentar agregar al modelo
                if($this->divisionModel->addDivision($data)){
                    // Redirigir al índice con un mensaje de éxito (flash message - a implementar después)
                    redirect('divisions/index');
                } else {
                    die('Algo salió mal al intentar guardar la división.');
                }
            } else {
                // Hay errores: Cargar la vista con los errores y datos ingresados
                $this->view('divisions/add', $data);
            }

        } else {
            $personal_list = $this->personalModel->getPersonalForDropdown();
            // 4. Cargar el formulario vacío (GET request)
            $data = [
                'nombre' => '',
                'siglas' => '',
                'id_jefe' => null,
                'nombre_err' => '',
                'siglas_err' => '',
                'title' => 'Añadir Nueva División',
                'personal_list' => $personal_list
            ];
            // **NOTA:** Si necesitas cargar la lista de 'personal' para el select de jefe, 
            // la cargarías aquí usando 
            
            $this->view('divisions/add', $data);
        }
    }

    // Editar una división existente
    public function edit($id){
        // Verificar permiso para editar divisiones
        $this->verificarAcceso('divisions', 'editar');
        
        // Cargar modelo de jefe (Personal) para el dropdown
        $personal_list = $this->personalModel->getPersonalForDropdown();
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Limpiar datos
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'id' => $id,
                'title' => 'Editar División',
                'nombre' => trim($_POST['nombre']),
                'siglas' => trim($_POST['siglas']),
                'id_personal_jefe' => trim($_POST['id_personal_jefe']),
                'nombre_err' => '',
                'siglas_err' => '',
                'title' => '',
                'personal_list' => $personal_list
            ];

            // Validar (mismas reglas que add)
            if(empty($data['nombre'])){
                $data['nombre_err'] = 'Por favor ingrese el nombre.';
            }
            if(empty($data['siglas'])){
                $data['siglas_err'] = 'Por favor ingrese las siglas.';
            }

            // Asegurarse de que no haya errores
            if(empty($data['nombre_err']) && empty($data['siglas_err'])){
                // Validado, proceder a actualizar
                if($this->divisionModel->updateDivision($data)){
                    redirect('divisions/index');
                } else {
                    die('Algo salió mal al intentar actualizar.');
                }
            } else {
                // Cargar vista con errores
                $this->view('divisions/edit', $data);
            }

        } else {
            // Cargar la división por ID
            $division = $this->divisionModel->getDivisionById($id);

            // Cargar formulario con datos existentes
            $data = [
                'id' => $id,
                'title' => 'Editar División',
                'nombre' => $division->Nombre,
                'siglas' => $division->Siglas,
                'id_personal_jefe' => $division->Id_personal_jefe,
                'nombre_err' => '',
                'siglas_err' => '',
                'title' => 'Editar División',
                'personal_list' => $personal_list
            ];
            
            $this->view('divisions/edit', $data);
        }
    }

    public function delete($id){
        // Verificar permiso para eliminar divisiones
        $this->verificarAcceso('divisions', 'eliminar');
        
        // Restricción de seguridad: Solo permitir POST requests para eliminar
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            // 1. Obtener la división para una verificación opcional (ej. si existe)
            $division = $this->divisionModel->getDivisionById($id);

            // OPCIONAL: Añade lógica de verificación si es necesario, ej. si $division es nulo.

            // 2. Llamar al modelo para realizar la eliminación lógica
            if($this->divisionModel->deleteDivision($id)){
                // Éxito: Redirigir al index (con un mensaje de éxito si usas sesiones)
                redirect('divisions/index');
            } else {
                // Fallo
                die('Algo salió mal al intentar eliminar la división.');
            }

        } else {
            // Si alguien intenta acceder por URL (GET), redirigir
            redirect('divisions/index');
        }
    }
    
}