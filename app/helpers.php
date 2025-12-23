<?php
// Simple page redirect
function redirect($page){
    header('location: ' . URLROOT . '/' . $page);
    exit;
}

function isLoggedIn(){
    if(isset($_SESSION['user_id'])){
        return true;
    } else {
        return false;
    }
}

// Variable estática para cachear la instancia de PermisoModel
static $permisoModelCache = null;

/**
 * Obtiene una instancia singleton de PermisoModel
 */
function getPermisoModelInstance(){
    global $permisoModelCache;
    if($permisoModelCache === null){
        require_once APPROOT . '/models/PermisoModel.php';
        $permisoModelCache = new PermisoModel();
    }
    return $permisoModelCache;
}

/**
 * Verifica si el usuario actual tiene un permiso específico
 * @param string $modulo - Módulo (ej: "actividades")
 * @param string $accion - Acción (ej: "ver")
 * @return bool True si tiene el permiso, false si no
 */
function tieneAcceso($modulo, $accion = 'ver'){
    if(!isLoggedIn()){
        return false;
    }
    
    $permisoModel = getPermisoModelInstance();
    return $permisoModel->tieneAcceso($_SESSION['user_id'], $modulo, $accion);
}

/**
 * Verifica si el usuario actual tiene un permiso específico por nombre
 * @param string $permiso - Nombre del permiso (ej: "actividades.ver")
 * @return bool True si tiene el permiso, false si no
 */
function tienePermiso($permiso){
    if(!isLoggedIn()){
        return false;
    }
    
    $permisoModel = getPermisoModelInstance();
    return $permisoModel->tienePermiso($_SESSION['user_id'], $permiso);
}

/**
 * Obtiene el rol del usuario conectado
 * @return object|null El rol del usuario o null si no tiene
 */
function getRolUsuarioActual(){
    if(!isLoggedIn()){
        return null;
    }
    
    $permisoModel = getPermisoModelInstance();
    return $permisoModel->getRolUsuario($_SESSION['user_id']);
}

/**
 * Establece un mensaje flash en la sesión
 * @param string $key - Clave del mensaje
 * @param string $message - Mensaje a mostrar
 * @param string $type - Tipo de alerta (success, danger, warning, info)
 */
function flashMessage($key, $message, $type = 'info'){
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Obtiene y elimina un mensaje flash de la sesión
 * @param string $key - Clave del mensaje
 * @return array|null Array con 'message' y 'type', o null si no existe
 */
function getFlashMessage($key){
    if(isset($_SESSION['flash'][$key])){
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

/**
 * Verifica si existe un mensaje flash
 * @param string $key - Clave del mensaje
 * @return bool True si existe, false si no
 */
function hasFlashMessage($key){
    return isset($_SESSION['flash'][$key]);
}

/**
 * Muestra un mensaje flash en HTML con Bootstrap
 * @param string $key - Clave del mensaje
 * @return string HTML del alerta o cadena vacía si no existe
 */
function displayFlashMessage($key){
    $message = getFlashMessage($key);
    if($message === null){
        return '';
    }
    
    $type = $message['type'];
    $text = $message['message'];
    
    // Mapear tipos a clases de Bootstrap
    $alertClass = 'alert-info';
    switch($type){
        case 'success':
            $alertClass = 'alert-success';
            break;
        case 'danger':
        case 'error':
            $alertClass = 'alert-danger';
            break;
        case 'warning':
            $alertClass = 'alert-warning';
            break;
        case 'info':
            $alertClass = 'alert-info';
            break;
    }
    
    return '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($text) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}
