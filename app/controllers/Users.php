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

                    // Si la contraseña es temporal, forzar el cambio inmediato
                    if(isset($loggedInUser->password_temp) && (int)$loggedInUser->password_temp === 1){
                        redirect('users/cambiarPassword');
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

    // ══════════════════════════════════════════════════════════════════
    //  RECUPERACIÓN DE CONTRASEÑA  (rutas públicas, sin sesión)
    // ══════════════════════════════════════════════════════════════════

    /**
     * GET  users/recover       → formulario para ingresar email
     * POST users/recover       → procesar la solicitud y enviar correo
     */
    public function recover(){
        // Si ya está logueado, redirigir al dashboard
        if(isLoggedIn()){
            redirect('pages/index');
        }

        // Necesitamos el modelo de usuario
        require_once APPROOT . '/models/UsuarioModel.php';
        $usuarioModel = new UsuarioModel();

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'titulo'    => 'Recuperar Contraseña',
                'email'     => trim($_POST['email'] ?? ''),
                'email_err' => '',
                'exito'     => false,
            ];

            // ── Validar email ──────────────────────────────────────
            if(empty($data['email'])){
                $data['email_err'] = 'Por favor ingrese su correo electrónico.';
            } elseif(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                $data['email_err'] = 'Por favor ingrese un correo electrónico válido.';
            }

            if(empty($data['email_err'])){
                $usuario = $usuarioModel->getUsuarioActivoByEmail($data['email']);

                /*
                 * Por seguridad (anti-enumeración) mostramos el mismo mensaje
                 * independientemente de si el email existe o no.
                 */
                if($usuario){
                    // ── Generar contraseña temporal legible (12 chars) ──
                    $passTemp   = $this->_generarPasswordTemporal(12);
                    $passHash   = password_hash($passTemp, PASSWORD_DEFAULT);

                    // ── Generar token único (SHA-256 de bytes aleatorios) ──
                    $token      = bin2hex(random_bytes(32)); // 64 chars hex
                    $expira     = date('Y-m-d H:i:s', strtotime('+24 hours'));

                    // ── Guardar en BD (invalida token anterior automáticamente) ──
                    if($usuarioModel->guardarTokenRecuperacion(
                        $usuario->Id_usuario, $token, $passHash, $expira
                    )){
                        // ── Obtener nombre para el correo ────────────────
                        require_once APPROOT . '/models/User.php';
                        $userModel  = new User();
                        $personal   = $userModel->getNombrePersonal($usuario->Id_usuario);
                        $nombre     = $personal
                            ? trim($personal->Nombre_Completo . ' ' . $personal->Apellido_Completo)
                            : $usuario->email;

                        // ── Enviar correo ────────────────────────────────
                        require_once APPROOT . '/libraries/MailHelper.php';
                        enviarCorreoRecuperacion($usuario->email, $nombre, $passTemp);
                    }
                }

                // Mostrar mensaje genérico de éxito (siempre)
                $data['exito'] = true;
            }

            $this->view('users/recuperar', $data);

        } else {
            // GET: formulario vacío
            $data = [
                'titulo'    => 'Recuperar Contraseña',
                'email'     => '',
                'email_err' => '',
                'exito'     => false,
            ];
            $this->view('users/recuperar', $data);
        }
    }

    /**
     * Fuerza el cambio de contraseña cuando password_temp = 1.
     * También es el destino del enlace del correo.
     *
     * GET  users/cambiarPassword        → formulario (requiere sesión activa con pass temporal)
     * POST users/cambiarPassword        → procesar cambio
     */
    public function cambiarPassword(){
        // Esta ruta SÍ requiere que el usuario esté logueado (llegó aquí tras iniciar sesión
        // con la contraseña temporal que le enviamos por correo).
        if(!isLoggedIn()){
            redirect('users/login');
        }

        require_once APPROOT . '/models/UsuarioModel.php';
        $usuarioModel = new UsuarioModel();

        // Verificar que realmente tenga contraseña temporal pendiente
        if(!$usuarioModel->tienePasswordTemporal($_SESSION['user_id'])){
            redirect('pages/index');
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data = [
                'titulo'           => 'Establecer Nueva Contraseña',
                'pass'             => '',
                'confirm_pass'     => '',
                'pass_err'         => '',
                'confirm_pass_err' => '',
            ];

            $pass        = trim($_POST['pass']        ?? '');
            $confirmPass = trim($_POST['confirm_pass'] ?? '');

            // ── Validaciones ───────────────────────────────────────
            if(empty($pass)){
                $data['pass_err'] = 'Por favor ingrese una nueva contraseña.';
            } elseif(strlen($pass) < 8){
                $data['pass_err'] = 'La contraseña debe tener al menos 8 caracteres.';
            } elseif(!preg_match('/[A-Z]/', $pass)){
                $data['pass_err'] = 'La contraseña debe contener al menos una letra mayúscula.';
            } elseif(!preg_match('/[0-9]/', $pass)){
                $data['pass_err'] = 'La contraseña debe contener al menos un número.';
            }

            if($pass !== $confirmPass){
                $data['confirm_pass_err'] = 'Las contraseñas no coinciden.';
            }

            if(empty($data['pass_err']) && empty($data['confirm_pass_err'])){
                $passHash = password_hash($pass, PASSWORD_DEFAULT);

                if($usuarioModel->actualizarPasswordYLimpiarToken($_SESSION['user_id'], $passHash)){
                    flashMessage('usuario_message', 'Contraseña actualizada correctamente. ¡Bienvenido!', 'success');
                    redirect('pages/index');
                } else {
                    flashMessage('usuario_message', 'Ocurrió un error al actualizar la contraseña. Intenta de nuevo.', 'danger');
                    $this->view('users/cambiar_password', $data);
                }
            } else {
                $this->view('users/cambiar_password', $data);
            }

        } else {
            $data = [
                'titulo'           => 'Establecer Nueva Contraseña',
                'pass'             => '',
                'confirm_pass'     => '',
                'pass_err'         => '',
                'confirm_pass_err' => '',
            ];
            $this->view('users/cambiar_password', $data);
        }
    }

    /**
     * Genera una contraseña temporal segura y legible.
     * Mezcla mayúsculas, minúsculas, dígitos y símbolos básicos.
     */
    private function _generarPasswordTemporal(int $longitud = 12): string {
        $mayus   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';   // sin I, O
        $minus   = 'abcdefghjkmnpqrstuvwxyz';     // sin i, l, o
        $digitos = '23456789';                     // sin 0, 1
        $simbol  = '@#$%!';

        // Garantizar al menos 1 de cada tipo
        $pass  = $mayus[random_int(0, strlen($mayus) - 1)];
        $pass .= $minus[random_int(0, strlen($minus) - 1)];
        $pass .= $digitos[random_int(0, strlen($digitos) - 1)];
        $pass .= $simbol[random_int(0, strlen($simbol) - 1)];

        $todos = $mayus . $minus . $digitos . $simbol;
        for($i = 4; $i < $longitud; $i++){
            $pass .= $todos[random_int(0, strlen($todos) - 1)];
        }

        // Mezclar
        return str_shuffle($pass);
    }

    // Otras funciones como register, logout, etc. irían aquí
}
