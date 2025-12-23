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
                    // Crear Sesión
                    $_SESSION['user_id'] = $loggedInUser->Id_usuario;
                    $_SESSION['user_email'] = $loggedInUser->email;
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
        // 1. Destruir las variables de sesión específicas
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        
        // 2. Destruir la sesión por completo (si es necesario)
        session_destroy();

        // 3. Redirigir al usuario al login
        // La función redirect() está definida en helpers.php
        redirect('users/login'); 
    }
    // Otras funciones como register, logout, etc. irían aquí
}
