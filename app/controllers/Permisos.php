<?php

class Permisos extends Controller {
    protected $permisoModel;
    private $itemsPerPage = 10;

    public function __construct() {
        // Verificar autenticación
        if (!isLoggedIn()) {
            redirect('users/login');
        }

        $this->permisoModel = $this->model('PermisoModel');
    }

    /**
     * Mostrar listado de permisos
     */
    public function index() {
        // Verificar permiso para ver permisos
        $this->verificarAcceso('permisos', 'ver');
        
        // Obtener parámetros
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        
        $page = max(1, (int)$page);
        $offset = ($page - 1) * $this->itemsPerPage;
        
        // Obtener permisos y total
        $permisos = $this->permisoModel->getPermisos($this->itemsPerPage, $offset, $search);
        $totalPermisos = $this->permisoModel->getTotalPermisos($search);
        $totalPages = ceil($totalPermisos / $this->itemsPerPage);
        
        $data = [
            'title' => 'Gestión de Permisos',
            'permisos' => $permisos,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $totalPermisos,
                'search_term' => $search
            ]
        ];
        
        $this->view('permisos/index', $data);
    }

    /**
     * Formulario para crear nuevo permiso
     */
    public function add() {
        // Verificar permiso para crear permisos
        $this->verificarAcceso('permisos', 'crear');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar datos
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'modulo' => trim($_POST['modulo'] ?? ''),
                'accion' => trim($_POST['accion'] ?? ''),
                'nombre_err' => '',
                'descripcion_err' => '',
                'modulo_err' => '',
                'accion_err' => ''
            ];
            
            // Validaciones
            if (empty($data['nombre'])) {
                $data['nombre_err'] = 'El nombre del permiso es requerido';
            }
            
            if (empty($data['modulo'])) {
                $data['modulo_err'] = 'El módulo es requerido';
            }
            
            if (empty($data['accion'])) {
                $data['accion_err'] = 'La acción es requerida';
            }
            
            if ($this->permisoModel->permisoExists($data['nombre'])) {
                $data['nombre_err'] = 'Este nombre de permiso ya existe';
            }
            
            // Si no hay errores, guardar
            if (empty($data['nombre_err']) && empty($data['modulo_err']) && 
                empty($data['accion_err']) && empty($data['descripcion_err'])) {
                if ($this->permisoModel->addPermiso($data)) {
                    flashMessage('permiso_message', 'Permiso creado exitosamente', 'success');
                    redirect('permisos/index');
                } else {
                    flashMessage('permiso_message', 'Error al crear el permiso', 'danger');
                }
            } else {
                $data['modulos'] = $this->permisoModel->getModulos();
                $data['acciones'] = $this->permisoModel->getAcciones();
                $this->view('permisos/add', $data);
                return;
            }
        }
        
        $data = [
            'title' => 'Crear Nuevo Permiso',
            'nombre' => '',
            'descripcion' => '',
            'modulo' => '',
            'accion' => '',
            'nombre_err' => '',
            'descripcion_err' => '',
            'modulo_err' => '',
            'accion_err' => '',
            'modulos' => $this->permisoModel->getModulos(),
            'acciones' => $this->permisoModel->getAcciones()
        ];
        
        $this->view('permisos/add', $data);
    }

    /**
     * Formulario para editar permiso
     */
    public function edit($id = null) {
        // Verificar permiso para editar permisos
        $this->verificarAcceso('permisos', 'editar');
        
        if (!$id) {
            flashMessage('permiso_message', 'ID de permiso no proporcionado', 'danger');
            redirect('permisos/index');
        }
        
        $permiso = $this->permisoModel->getPermisoById($id);
        
        if (!$permiso) {
            flashMessage('permiso_message', 'Permiso no encontrado', 'danger');
            redirect('permisos/index');
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'id' => $id,
                'nombre' => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'modulo' => trim($_POST['modulo'] ?? ''),
                'accion' => trim($_POST['accion'] ?? ''),
                'nombre_err' => '',
                'descripcion_err' => '',
                'modulo_err' => '',
                'accion_err' => ''
            ];
            
            // Validaciones
            if (empty($data['nombre'])) {
                $data['nombre_err'] = 'El nombre del permiso es requerido';
            }
            
            if (empty($data['modulo'])) {
                $data['modulo_err'] = 'El módulo es requerido';
            }
            
            if (empty($data['accion'])) {
                $data['accion_err'] = 'La acción es requerida';
            }
            
            if ($this->permisoModel->permisoExists($data['nombre'], $id)) {
                $data['nombre_err'] = 'Este nombre de permiso ya existe';
            }
            
            // Si no hay errores, actualizar
            if (empty($data['nombre_err']) && empty($data['modulo_err']) && 
                empty($data['accion_err']) && empty($data['descripcion_err'])) {
                if ($this->permisoModel->updatePermiso($id, $data)) {
                    flashMessage('permiso_message', 'Permiso actualizado exitosamente', 'success');
                    redirect('permisos/index');
                } else {
                    flashMessage('permiso_message', 'Error al actualizar el permiso', 'danger');
                }
            } else {
                $data['modulos'] = $this->permisoModel->getModulos();
                $data['acciones'] = $this->permisoModel->getAcciones();
                $this->view('permisos/edit', $data);
                return;
            }
        }
        
        $data = [
            'title' => 'Editar Permiso',
            'id' => $permiso->Id_permiso,
            'nombre' => $permiso->Nombre,
            'descripcion' => $permiso->Descripcion,
            'modulo' => $permiso->Modulo,
            'accion' => $permiso->Accion,
            'nombre_err' => '',
            'descripcion_err' => '',
            'modulo_err' => '',
            'accion_err' => '',
            'modulos' => $this->permisoModel->getModulos(),
            'acciones' => $this->permisoModel->getAcciones()
        ];
        
        $this->view('permisos/edit', $data);
    }

    /**
     * Eliminar permiso (soft delete)
     */
    public function delete() {
        // Verificar permiso para eliminar permisos
        $this->verificarAcceso('permisos', 'eliminar');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                json_response(['success' => false, 'message' => 'ID no proporcionado']);
            }
            
            if ($this->permisoModel->deletePermiso($id)) {
                json_response(['success' => true, 'message' => 'Permiso eliminado exitosamente']);
            } else {
                json_response(['success' => false, 'message' => 'Error al eliminar el permiso']);
            }
        }
    }
}
