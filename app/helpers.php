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

/**
 * Obtiene el nombre de usuario para mostrar en la UI
 * @return string Nombre completo o email si no existe
 */
function getUserDisplayName(){
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'Usuario');
}

/**
 * Verifica si el usuario actual es Gerente o Administrador
 * @return bool True si es gerente o admin, false si no
 */
function isGerenteOrAdmin(){
    if(!isLoggedIn()) return false;
    
    require_once APPROOT . '/models/RoleModel.php';
    $roleModel = new RoleModel();
    $userRoles = $roleModel->getRolesByUser($_SESSION['user_id']);
    
    if(!$userRoles) return false;
    
    foreach($userRoles as $role){
        if($role->Nombre === 'Gerente' || $role->Nombre === 'Administrador'){
            return true;
        }
    }
    
    return false;
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

    $type = $message['type'] ?? 'info';
    $text = $message['message'] ?? '';

    // Mapear tipos a clases de Bootstrap 4 y su ícono
    $alertClass = 'alert-info';
    $icon = 'bi-info-circle-fill';
    switch($type){
        case 'success':
            $alertClass = 'alert-success';
            $icon = 'bi-check-circle-fill';
            break;
        case 'danger':
        case 'error':
            $alertClass = 'alert-danger';
            $icon = 'bi-x-circle-fill';
            break;
        case 'warning':
            $alertClass = 'alert-warning';
            $icon = 'bi-exclamation-triangle-fill';
            break;
        case 'info':
            $alertClass = 'alert-info';
            $icon = 'bi-info-circle-fill';
            break;
    }

    return '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">'
        . '<i class="bi ' . $icon . ' mr-2"></i>'
        . htmlspecialchars($text) .
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
        . '<span aria-hidden="true">&times;</span>'
        . '</button>'
        . '</div>';
}

/**
 * Muestra todos los mensajes flash almacenados
 * @return string HTML con todas las alertas
 */
function displayAllFlashMessages(){
    if(empty($_SESSION['flash']) || !is_array($_SESSION['flash'])){
        return '';
    }

    $alerts = '';
    foreach($_SESSION['flash'] as $key => $message){
        $type = $message['type'] ?? 'info';
        $text = $message['message'] ?? '';

        $alertClass = 'alert-info';
        $icon = 'bi-info-circle-fill';
        switch($type){
            case 'success':
                $alertClass = 'alert-success';
                $icon = 'bi-check-circle-fill';
                break;
            case 'danger':
            case 'error':
                $alertClass = 'alert-danger';
                $icon = 'bi-x-circle-fill';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                $icon = 'bi-exclamation-triangle-fill';
                break;
            case 'info':
                $alertClass = 'alert-info';
                $icon = 'bi-info-circle-fill';
                break;
        }

        $alerts .= '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">'
            . '<i class="bi ' . $icon . ' mr-2"></i>'
            . htmlspecialchars($text) .
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
            . '<span aria-hidden="true">&times;</span>'
            . '</button>'
            . '</div>';

        unset($_SESSION['flash'][$key]);
    }

    // Contenedor fijo tipo notification bar
    return '<div class="flash-stack">' . $alerts . '</div>';
}
/**
 * Genera un Breadcrumb de Bootstrap basado en la URL actual
 * Valida permisos para mostrar solo los items accesibles
 * @return string HTML del breadcrumb
 */
function generarBreadcrumb(){
    if(!isLoggedIn()) {
        return '';
    }
    
    // Mapeo de módulos a información de breadcrumb
    $breadcrumbMap = [
        'pages' => [
            'label' => 'Inicio',
            'icon' => 'bi-house-door',
            'parent' => null,
            'permission' => null
        ],
        'divisions' => [
            'label' => 'Divisiones',
            'icon' => 'bi-diagram-3',
            'parent' => 'GERO',
            'permission' => ['divisions', 'ver']
        ],
        'personal' => [
            'label' => 'Personal',
            'icon' => 'bi-people',
            'parent' => 'GERO',
            'permission' => ['personal', 'ver']
        ],
        'contratos' => [
            'label' => 'Contratos',
            'icon' => 'bi-file-earmark-text',
            'parent' => 'GERO',
            'permission' => ['contratos', 'ver']
        ],
        'alcances' => [
            'label' => 'Alcances',
            'icon' => 'bi-bullseye',
            'parent' => 'GERO',
            'permission' => ['alcances', 'ver']
        ],
        'actividades' => [
            'label' => 'Actividades',
            'icon' => 'bi-check-square',
            'parent' => 'GERO',
            'permission' => ['actividades', 'ver']
        ],
        'usuarios' => [
            'label' => 'Usuarios',
            'icon' => 'bi-person-badge',
            'parent' => 'Configuraciones',
            'permission' => ['usuarios', 'ver']
        ],
        'roles' => [
            'label' => 'Roles',
            'icon' => 'bi-shield-lock',
            'parent' => 'Configuraciones',
            'permission' => ['roles', 'ver']
        ],
        'permisos' => [
            'label' => 'Permisos',
            'icon' => 'bi-key',
            'parent' => 'Configuraciones',
            'permission' => ['permisos', 'ver']
        ]
    ];
    
    // Obtener el módulo actual de la URL
    $uri = $_SERVER['REQUEST_URI'];
    $uriParts = explode('/', trim($uri, '/'));
    
    // Buscar el módulo en la URL
    $currentModule = null;
    foreach($uriParts as $part) {
        if(isset($breadcrumbMap[$part])) {
            $currentModule = $part;
            break;
        }
    }
    
    // Si no se encuentra módulo, retornar vacío
    if(!$currentModule) {
        return '';
    }
    
    // Obtener información del módulo actual
    $moduleInfo = $breadcrumbMap[$currentModule];
    
    // Verificar permisos del módulo actual
    if($moduleInfo['permission'] && !tieneAcceso($moduleInfo['permission'][0], $moduleInfo['permission'][1])) {
        return '';
    }
    
    // Construir el breadcrumb HTML
    $breadcrumb = '<nav aria-label="breadcrumb" class="my-3">';
    $breadcrumb .= '<ol class="breadcrumb bg-light p-3 rounded">';
    
    // Item: Inicio (siempre visible)
    $breadcrumb .= '<li class="breadcrumb-item">';
    $breadcrumb .= '<a href="' . URLROOT . '/pages/index" class="text-decoration-none">';
    $breadcrumb .= '<i class="bi bi-house-door"></i> Inicio';
    $breadcrumb .= '</a></li>';
    
    // Item: Padre (GERO o Configuraciones) si existe
    if($moduleInfo['parent']) {
        $breadcrumb .= '<li class="breadcrumb-item">';
        $breadcrumb .= '<span class="text-muted">';
        if($moduleInfo['parent'] === 'GERO') {
            $breadcrumb .= '<i class="bi bi-folder-check"></i> GERO';
        } elseif($moduleInfo['parent'] === 'Configuraciones') {
            $breadcrumb .= '<i class="bi bi-gear"></i> Configuraciones';
        }
        $breadcrumb .= '</span></li>';
    }
    
    // Item: Módulo actual (activo)
    $breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">';
    $breadcrumb .= '<i class="' . $moduleInfo['icon'] . '"></i> ' . $moduleInfo['label'];
    $breadcrumb .= '</li>';
    
    $breadcrumb .= '</ol>';
    $breadcrumb .= '</nav>';
    
    return $breadcrumb;
}