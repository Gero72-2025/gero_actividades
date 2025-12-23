<?php
  class Controller {
    protected $permisoModel;

    public function __construct(){
      // No cargar el modelo de permisos aquí para evitar problemas de autoload
      $this->permisoModel = null;
    }

    // Cargar Modelo
    public function model($model){
      // Requerir el archivo del modelo
      require_once APPROOT . '/models/' . $model . '.php';

      // Instanciar el modelo
      return new $model();
    }

    // Cargar Vista
    public function view($view, $data = []){
      // Ruta absoluta al archivo de la vista
      //$viewPath = APPROOT . '/views/' . $view . '.php';
        $viewPath = APPROOT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
      // Verificar si el archivo de la vista existe
      if(file_exists($viewPath)){
        // Extrae el array $data para que las claves sean variables ($title, $description, etc.)
        extract($data); 

        // Incluye el archivo de la vista (que ahora tiene acceso a $title, $description, etc.)
        require_once $viewPath;
      } else {
        // La vista no existe
        die('La vista ' . $viewPath . ' no existe');
      }
    }

    /**
     * Obtiene la instancia del modelo de permisos
     */
    protected function getPermisoModel(){
      if($this->permisoModel === null){
        $this->permisoModel = $this->model('PermisoModel');
      }
      return $this->permisoModel;
    }

    /**
     * Verifica si el usuario actual tiene acceso a una acción
     * @param string $modulo - Módulo (ej: "actividades")
     * @param string $accion - Acción (ej: "ver")
     * @return bool True si tiene acceso, false si no
     */
    protected function verificarAcceso($modulo, $accion = 'ver'){
      if(!isLoggedIn()){
        redirect('users/login');
      }
      
      $permisoModel = $this->getPermisoModel();
      
      if(!$permisoModel->tieneAcceso($_SESSION['user_id'], $modulo, $accion)){
        // Mostrar modal de acceso denegado
        $this->mostrarAccesoDenegado($modulo, $accion);
      }
    }

    /**
     * Muestra un modal de acceso denegado
     */
    protected function mostrarAccesoDenegado($modulo, $accion){
      http_response_code(403);
      ?>
      <!DOCTYPE html>
      <html lang="es">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso Denegado</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
      </head>
      <body>
        <div class="modal d-block" style="background-color: rgba(0,0,0,0.5);">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">⛔ Acceso Denegado</h5>
              </div>
              <div class="modal-body">
                <p>No tiene permisos para acceder a <strong><?php echo htmlspecialchars($modulo . '.' . $accion); ?></strong></p>
                <p class="text-muted">Si cree que esto es un error, contacte al administrador.</p>
              </div>
              <div class="modal-footer">
                <a href="<?php echo URLROOT; ?>/" class="btn btn-primary">Ir al Inicio</a>
                <a href="javascript:history.back()" class="btn btn-secondary">Volver Atrás</a>
              </div>
            </div>
          </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
      </body>
      </html>
      <?php
      exit;
    }

    /**
     * Obtiene los permisos del usuario actual
     * @return array Array de permisos del usuario
     */
    protected function getPermisosUsuario(){
      if(!isLoggedIn()){
        return [];
      }
      
      $permisoModel = $this->getPermisoModel();
      return $permisoModel->getPermisosUsuario($_SESSION['user_id']);
    }

    /**
     * Obtiene el rol del usuario actual
     * @return object|null El rol del usuario
     */
    protected function getRolUsuario(){
      if(!isLoggedIn()){
        return null;
      }
      
      return $this->permisoModel->getRolUsuario($_SESSION['user_id']);
    }
  }
?>
