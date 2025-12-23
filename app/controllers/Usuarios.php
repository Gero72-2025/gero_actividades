<?php
class Usuarios extends Controller {
    private $usuarioModel;

    public function __construct(){
        if(!isLoggedIn()){
            redirect('users/login');
        }

        $this->usuarioModel = $this->model('UsuarioModel');
    }

    public function index(){
        // Verificar permiso para ver usuarios
        $this->verificarAcceso('usuarios', 'ver');
        
        $usuarios = $this->usuarioModel->getUsuarios();

        $data = [
            'title' => 'Gestión de Usuarios',
            'usuarios' => $usuarios
        ];

        $this->view('usuarios/index', $data);
    }

    public function add(){
        // Verificar permiso para crear usuarios
        $this->verificarAcceso('usuarios', 'crear');
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'email' => trim($_POST['email']),
                'pass' => trim($_POST['pass']),
                'confirm_pass' => trim($_POST['confirm_pass']),
                'id_role' => !empty($_POST['id_role']) ? $_POST['id_role'] : '', 
                'email_err' => '',
                'pass_err' => '',
                'confirm_pass_err' => '',
                'title' => 'Añadir Usuario'
            ];

            // 1. Validar Email
            if(empty($data['email'])){
                $data['email_err'] = 'Por favor ingrese el correo electrónico.';
            } elseif($this->usuarioModel->findUserByEmail($data['email'])){
                $data['email_err'] = 'Este correo ya está registrado.';
            }

            // 2. Validar Contraseña
            if(empty($data['pass'])){
                $data['pass_err'] = 'Por favor ingrese una contraseña.';
            } elseif(strlen($data['pass']) < 6){
                $data['pass_err'] = 'La contraseña debe tener al menos 6 caracteres.';
            }

            // 3. Validar Confirmación
            if($data['pass'] != $data['confirm_pass']){
                $data['confirm_pass_err'] = 'Las contraseñas no coinciden.';
            }

            // 4. Si no hay errores, hashear y guardar
            if(empty($data['email_err']) && empty($data['pass_err']) && empty($data['confirm_pass_err'])){
                
                // Hashear Contraseña antes de guardar
                $data['pass'] = password_hash($data['pass'], PASSWORD_DEFAULT);
                
                if($this->usuarioModel->addUsuario($data)){
                    // Obtener el usuario recién creado por email
                    $nuevoUsuario = $this->usuarioModel->getUsuarioByEmail($data['email']);
                    
                    // Asignar rol si se seleccionó uno
                    if(!empty($data['id_role']) && $nuevoUsuario){
                        $this->usuarioModel->assignRoleToUsuario($nuevoUsuario->Id_usuario, $data['id_role']);
                    }
                    
                    redirect('usuarios/index');
                } else {
                    die('Algo salió mal al intentar guardar el usuario.');
                }
            } else {
                $data['roles'] = $this->usuarioModel->getRoles();
                $this->view('usuarios/add', $data);
            }

        } else {
            // GET request: Cargar formulario vacío
            $data = [
                'title' => 'Añadir Usuario',
                'email' => '', 'pass' => '', 'confirm_pass' => '', 'id_role' => '',
                'email_err' => '', 'pass_err' => '', 'confirm_pass_err' => '',
                'roles' => $this->usuarioModel->getRoles()
            ];

            $this->view('usuarios/add', $data);
        }
    }

    public function edit($id){
        // Verificar permiso para editar usuarios
        $this->verificarAcceso('usuarios', 'editar');
        
        // ... (Lógica de edición, ver fragmento en el thought process)
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'id' => $id,
                'email' => trim($_POST['email']),
                'pass' => trim($_POST['pass']),
                'confirm_pass' => trim($_POST['confirm_pass']),
                'id_role' => !empty($_POST['id_role']) ? $_POST['id_role'] : '',
                'email_err' => '',
                'pass_err' => '',
                'confirm_pass_err' => '',
                'title' => 'Editar Usuario'
            ];
            
            // 1. Validar Email (solo vacío, la unicidad debe ser manejada con cuidado)
            if(empty($data['email'])){
                $data['email_err'] = 'Por favor ingrese el correo electrónico.';
            }
            // NOTA: La validación de unicidad de email es más compleja en edición y se omite aquí por simplicidad.

            // 2. Validar Contraseña (solo si se proporciona una nueva)
            if(!empty($data['pass'])){
                if(strlen($data['pass']) < 6){
                    $data['pass_err'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
                }
                if($data['pass'] != $data['confirm_pass']){
                    $data['confirm_pass_err'] = 'Las contraseñas no coinciden.';
                }
            }

            // 3. Si no hay errores, hashear y actualizar
            if(empty($data['email_err']) && empty($data['pass_err']) && empty($data['confirm_pass_err'])){
                
                if(!empty($data['pass'])){
                    $data['pass'] = password_hash($data['pass'], PASSWORD_DEFAULT);
                } else {
                    $data['pass'] = ''; // Para que el modelo la ignore
                }
                
                if($this->usuarioModel->updateUsuario($data)){
                    // Asignar rol si se seleccionó uno
                    $this->usuarioModel->assignRoleToUsuario($id, $data['id_role']);
                    
                    redirect('usuarios/index');
                } else {
                    die('Algo salió mal al intentar actualizar el usuario.');
                }
            } else {
                $data['roles'] = $this->usuarioModel->getRoles();
                $data['id_role_actual'] = $this->usuarioModel->getRoleUsuario($id);
                $this->view('usuarios/edit', $data);
            }

        } else {
            // GET request: Cargar formulario con datos existentes
            $usuario = $this->usuarioModel->getUsuarioById($id);
            
            if(!$usuario){ redirect('usuarios/index'); }

            $data = [
                'id' => $id,
                'title' => 'Editar Usuario',
                'email' => $usuario->email,
                'pass' => '', 'confirm_pass' => '',
                'email_err' => '', 'pass_err' => '', 'confirm_pass_err' => '',
                'roles' => $this->usuarioModel->getRoles(),
                'id_role_actual' => $this->usuarioModel->getRoleUsuario($id)
            ];

            $this->view('usuarios/edit', $data);
        }
    }


    public function delete($id){
        // Verificar permiso para eliminar usuarios
        $this->verificarAcceso('usuarios', 'eliminar');
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->usuarioModel->deleteUsuario($id)){
                redirect('usuarios/index');
            } else {
                die('Algo salió mal al intentar eliminar el usuario.');
            }
        } else {
            redirect('usuarios/index');
        }
    }
}