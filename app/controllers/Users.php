<?php
class Users extends Controller {
    private $userModel;
    public function __construct(){
        $this->userModel = $this->model('User');
    }

    public function login(){
        // Comprobar si es un POST request
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Procesar el formulario
            // Limpiar los datos del POST
            // En app/controllers/Users.php, reemplace la LÍNEA 13 por:
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'email_err' => '',
                'password_err' => ''
            ];

            // Validar Email
            if(empty($data['email'])){
                $data['email_err'] = 'Por favor ingrese su email';
            }

            // Validar Password
            if(empty($data['password'])){
                $data['password_err'] = 'Por favor ingrese su contraseña';
            }

            // Validar si el usuario existe
            if($this->userModel->findUserByEmail($data['email'])){
                // Usuario encontrado
            } else {
                // Usuario no encontrado
                $data['email_err'] = 'No se encontró el usuario';
            }

            // Asegurarse que los errores esten vacios
            if(empty($data['email_err']) && empty($data['password_err'])){
                // Validado
                // Chequear y establecer el usuario logueado
                $loggedInUser = $this->userModel->login($data['email'], $data['password']);
                if($loggedInUser){
                    // Obtener nombre del personal asociado
                    $personalInfo = $this->userModel->getNombrePersonal($loggedInUser->Id_usuario);
                    
                    // Crear Sesión
                    $_SESSION['user_id'] = $loggedInUser->Id_usuario;
                    $_SESSION['user_email'] = $loggedInUser->email;
                    
                    // Guardar nombre completo si existe registro de personal
                    if($personalInfo){
                        $_SESSION['user_name'] = trim($personalInfo->Nombre_Completo . ' ' . $personalInfo->Apellido_Completo);
                    } else {
                        // Si no tiene personal asignado, usar el email como fallback
                        $_SESSION['user_name'] = $loggedInUser->email;
                    }
                    
                    redirect('pages/index');
                } else {
                    $data['password_err'] = 'Contraseña incorrecta';
                    $this->view('users/login', $data);
                }
            } else {
                // Cargar la vista con errores
                $this->view('users/login', $data);
            }

        } else {
            // Cargar el formulario de login
            $data = [
                'email' => '',
                'password' => '',
                'email_err' => '',
                'password_err' => ''
            ];
            // Cargar la vista
            $this->view('users/login', $data);
        }
    }

    public function logout(){
        // 1. Prevenir caché del navegador
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: 0");
        
        // 2. Destruir las variables de sesión específicas
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        
        // 3. Destruir la sesión por completo
        session_destroy();

        // 4. Redirigir al usuario al login
        // La función redirect() está definida en helpers.php
        redirect('users/login'); 
    }

    /**
     * Endpoint AJAX para verificar si hay sesión activa
     * Retorna JSON con el estado de la sesión
     */
    public function checksession(){
        // Indicar que es una respuesta JSON
        header('Content-Type: application/json');
        
        // Verificar si el usuario está logueado
        if(isLoggedIn()){
            echo json_encode(['logged_in' => true]);
        } else {
            echo json_encode(['logged_in' => false]);
        }
        exit;
    }
    // Otras funciones como register, logout, etc. irían aquí
}
