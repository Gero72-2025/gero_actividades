<?php
  /*
   * Clase Principal de la App
   * Crea la URL y carga el controlador principal
   * Formato de URL - /controlador/metodo/parametros
   */
  class Core {
    protected $currentController = 'Users'; // Controlador por defecto
    protected $currentMethod = 'login';     // Método por defecto
    protected $params = [];

    public function __construct(){
      $url = $this->getUrl();

      // 1. Busca en controladores si el controlador existe
      if(isset($url[0]) && file_exists(APPROOT . '/controllers/' . ucwords($url[0]). '.php')){
        $this->currentController = ucwords($url[0]);
        unset($url[0]);
      }

      // 2. Requiere el controlador (USANDO APPROOT)
      require_once APPROOT . '/controllers/'. $this->currentController . '.php';

      // 3. Instancia el controlador
      $this->currentController = new $this->currentController;
      
      // 4. Reindexar URL después de quitar el controlador
      $url = $url ? array_values($url) : [];

      // 5. Revisa la primera parte de la url restante (el método)
      if(isset($url[0])){ // <--- CAMBIO: Ahora buscamos en [0]
        if(method_exists($this->currentController, $url[0])){
          $this->currentMethod = $url[0];
          unset($url[0]);
        }
      }
      
      // 6. Obtiene los parámetros (reindexando de nuevo)
      $this->params = $url ? array_values($url) : [];

      // 7. Llama al método con los parámetros
      call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl(){
        if(isset($_GET['url'])){
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        // Si no hay URL, Pages/index es el default, pero debemos devolver un array vacío
        return []; 
    }
  }
?>