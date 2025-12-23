<?php

class Roles extends Controller {
    private $roleModel;
    protected $permisoModel;
    private $itemsPerPage = 10;

    public function __construct() {
        // Verificar autenticación
        if (!isLoggedIn()) {
            redirect('users/login');
        }

        $this->roleModel = $this->model('RoleModel');
        $this->permisoModel = $this->model('PermisoModel');
    }

    /**
     * Mostrar listado de roles
     */
    public function index() {
        // Verificar permiso para ver roles
        $this->verificarAcceso('roles', 'ver');
        
        // Obtener parámetros
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        
        $page = max(1, (int)$page);
        $offset = ($page - 1) * $this->itemsPerPage;
        
        // Obtener roles y total
        $roles = $this->roleModel->getRoles($this->itemsPerPage, $offset, $search);
        $totalRoles = $this->roleModel->getTotalRoles($search);
        $totalPages = ceil($totalRoles / $this->itemsPerPage);
        
        $data = [
            'title' => 'Gestión de Roles',
            'roles' => $roles,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $totalRoles,
                'search_term' => $search
            ]
        ];
        
        $this->view('roles/index', $data);
    }

    /**
     * Formulario para crear nuevo role
     */
    public function add() {
        // Verificar permiso para crear roles
        $this->verificarAcceso('roles', 'crear');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validar datos
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'nombre_err' => '',
                'descripcion_err' => ''
            ];
            
            // Validaciones
            if (empty($data['nombre'])) {
                $data['nombre_err'] = 'El nombre del role es requerido';
            }
            
            if ($this->roleModel->roleExists($data['nombre'])) {
                $data['nombre_err'] = 'Este nombre de role ya existe';
            }
            
            // Si no hay errores, guardar
            if (empty($data['nombre_err']) && empty($data['descripcion_err'])) {
                if ($this->roleModel->addRole($data)) {
                    flashMessage('role_message', 'Role creado exitosamente', 'success');
                    redirect('roles/index');
                } else {
                    flashMessage('role_message', 'Error al crear el role', 'danger');
                }
            } else {
                $this->view('roles/add', $data);
                return;
            }
        }
        
        $data = [
            'title' => 'Crear Nuevo Role',
            'nombre' => '',
            'descripcion' => '',
            'nombre_err' => '',
            'descripcion_err' => ''
        ];
        
        $this->view('roles/add', $data);
    }

    /**
     * Formulario para editar role
     */
    public function edit($id = null) {
        // Verificar permiso para editar roles
        $this->verificarAcceso('roles', 'editar');
        
        if (!$id) {
            flashMessage('role_message', 'ID de role no proporcionado', 'danger');
            redirect('roles/index');
        }
        
        $role = $this->roleModel->getRoleById($id);
        
        if (!$role) {
            flashMessage('role_message', 'Role no encontrado', 'danger');
            redirect('roles/index');
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'id' => $id,
                'nombre' => trim($_POST['nombre'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'nombre_err' => '',
                'descripcion_err' => ''
            ];
            
            // Validaciones
            if (empty($data['nombre'])) {
                $data['nombre_err'] = 'El nombre del role es requerido';
            }
            
            if ($this->roleModel->roleExists($data['nombre'], $id)) {
                $data['nombre_err'] = 'Este nombre de role ya existe';
            }
            
            // Si no hay errores, actualizar
            if (empty($data['nombre_err']) && empty($data['descripcion_err'])) {
                if ($this->roleModel->updateRole($id, $data)) {
                    flashMessage('role_message', 'Role actualizado exitosamente', 'success');
                    redirect('roles/index');
                } else {
                    flashMessage('role_message', 'Error al actualizar el role', 'danger');
                }
            } else {
                $this->view('roles/edit', $data);
                return;
            }
        }
        
        $data = [
            'title' => 'Editar Role',
            'id' => $role->Id_role,
            'nombre' => $role->Nombre,
            'descripcion' => $role->Descripcion,
            'nombre_err' => '',
            'descripcion_err' => ''
        ];
        
        $this->view('roles/edit', $data);
    }

    /**
     * Gestionar permisos de un role
     */
    public function permisos($id = null) {
        // Verificar permiso para asignar permisos a roles
        $this->verificarAcceso('roles', 'permisos');
        
        if (!$id) {
            flashMessage('role_message', 'ID de role no proporcionado', 'danger');
            redirect('roles/index');
        }
        
        $role = $this->roleModel->getRoleById($id);
        
        if (!$role) {
            flashMessage('role_message', 'Role no encontrado', 'danger');
            redirect('roles/index');
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST);
            
            $permisos_asignados = $_POST['permisos'] ?? [];
            
            // Obtener permisos actuales
            $permisos_actuales = $this->roleModel->getRolePermisos($id);
            
            foreach ($permisos_actuales as $permiso) {
                if (in_array($permiso->Id_permiso, $permisos_asignados)) {
                    if (!$permiso->asignado) {
                        $this->roleModel->assignPermisoToRole($id, $permiso->Id_permiso);
                    }
                } else {
                    if ($permiso->asignado) {
                        $this->roleModel->removePermisoFromRole($id, $permiso->Id_permiso);
                    }
                }
            }
            
            flashMessage('role_message', 'Permisos del role actualizados exitosamente', 'success');
            redirect('roles/permisos/' . $id);
        }
        
        $permisos = $this->roleModel->getRolePermisos($id);
        
        // Agrupar permisos por módulo
        $permisos_agrupados = [];
        foreach ($permisos as $permiso) {
            if (!isset($permisos_agrupados[$permiso->Modulo])) {
                $permisos_agrupados[$permiso->Modulo] = [];
            }
            $permisos_agrupados[$permiso->Modulo][] = $permiso;
        }
        
        $data = [
            'title' => 'Gestionar Permisos - ' . $role->Nombre,
            'role' => $role,
            'permisos_agrupados' => $permisos_agrupados
        ];
        
        $this->view('roles/permisos', $data);
    }

    /**
     * Eliminar role (soft delete)
     */
    public function delete() {
        // Verificar permiso para eliminar roles
        $this->verificarAcceso('roles', 'eliminar');
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                json_response(['success' => false, 'message' => 'ID no proporcionado']);
            }
            
            if ($this->roleModel->deleteRole($id)) {
                json_response(['success' => true, 'message' => 'Role eliminado exitosamente']);
            } else {
                json_response(['success' => false, 'message' => 'Error al eliminar el role']);
            }
        }
    }
}
