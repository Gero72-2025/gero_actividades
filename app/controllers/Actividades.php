<?php
class Actividades extends Controller {
    private $actividadModel;
    private $alcanceModel;  // Necesario para el dropdown de Alcances
    private $personalModel; // Necesario para el dropdown de Personal
    private $contratoModel; // Necesario para el dropdown de Personal

    // Opciones estáticas para el estado de la actividad
    private $estados = ['Pendiente', 'En Progreso', 'Completada', 'Cancelada'];

    public function __construct(){
        if(!isLoggedIn()){
            redirect('users/login');
        }

        $this->actividadModel = $this->model('ActividadModel');
        $this->alcanceModel = $this->model('AlcanceModel'); 
        $this->personalModel = $this->model('PersonalModel'); 
        $this->contratoModel = $this->model('ContratoModel'); 
    }

    // Muestra la lista de actividades
    public function index(){
        // Verificar permiso para ver actividades
        $this->verificarAcceso('actividades', 'ver');
        
        // Configuración de Paginación
        $limit = 10; // Actividades por página (puedes ajustar este valor)
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $searchTerm = isset($_GET['search']) ? trim(filter_var($_GET['search'], FILTER_SANITIZE_SPECIAL_CHARS)) : '';
        
        // 1. Obtener el total de registros (con filtro si existe)
        $totalActivities = $this->actividadModel->getTotalActividadesCount($searchTerm);
        
        // 2. Calcular variables de paginación
        $totalPages = ceil($totalActivities / $limit);
        
        // Ajustar la página si está fuera de límites
        if ($page < 1) {
            $page = 1;
        } elseif ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        // 3. Obtener los datos paginados y filtrados
        $actividades = $this->actividadModel->getPaginatedActividades($page, $limit, $searchTerm); 

        $data = [
            'title' => 'Gestión de Actividades Diarias',
            'actividades' => $actividades,
            'pagination' => [
                'total_records' => $totalActivities,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'limit' => $limit,
                'search_term' => $searchTerm
            ]
        ];

        $this->view('actividades/index', $data);
    }

    // Añadir Actividad
    public function add(){
        // Verificar permiso para crear actividades
        $this->verificarAcceso('actividades', 'crear');
        
        // Cargar datos para dropdowns
        $alcances = $this->alcanceModel->getAlcances();
        // --- MODIFICACIÓN CLAVE: Restringir Personal al usuario logueado ---
        $id_usuario_logueado = $_SESSION['user_id'];
        $personal_logueado = $this->personalModel->getPersonalByUserId($id_usuario_logueado);

        // Si el usuario logueado tiene un registro de personal, se crea un array con solo él.
        $personal_para_dropdown = $personal_logueado ? [$personal_logueado] : [];
        // ------------------------------------------------------------------
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            // Saneamiento específico para la fecha (si usas date picker)
            $fecha_ingreso = trim($_POST['fecha_ingreso']);
            $id_personal_post = $personal_logueado ? $personal_logueado->Id_personal : '';
            
            $data = [
                'title' => 'Añadir Actividad',
                'id_alcance' => trim($_POST['id_alcance']),
                'id_personal' => trim($_POST['id_personal']),
                'fecha_ingreso' => $fecha_ingreso,
                'descripcion_realizada' => trim($_POST['descripcion_realizada']),
                'estado_actividad' => trim($_POST['estado_actividad']),
                
                // Errores
                'id_alcance_err' => '',
                'id_personal_err' => '',
                'fecha_ingreso_err' => '',
                'descripcion_realizada_err' => '',
                'estado_actividad_err' => '',
                
                // Dropdowns
                'alcances' => $alcances,
                'personal' => $personal_para_dropdown,
                'estados' => $this->estados
            ];

            // 1. Validar datos
            if(empty($data['id_alcance'])){
                $data['id_alcance_err'] = 'Debe seleccionar un alcance.';
            }
            if(empty($data['id_personal'])){
                $data['id_personal_err'] = 'Debe seleccionar el personal responsable.';
            }
            if(empty($data['fecha_ingreso'])){
                $data['fecha_ingreso_err'] = 'La fecha de la actividad es obligatoria.';
            }
            // NOTA: Descripcion_realizada puede ser NULL, pero la descripción es útil.
            if(empty($data['descripcion_realizada'])){
                $data['descripcion_realizada_err'] = 'Ingrese una descripción del trabajo a realizar o realizado.';
            }
            
            if(!in_array($data['estado_actividad'], $this->estados)){
                $data['estado_actividad_err'] = 'Estado de actividad no válido.';
            }


            // 2. Si no hay errores, proceder
            if(empty($data['id_alcance_err']) && empty($data['id_personal_err']) && empty($data['fecha_ingreso_err']) && empty($data['descripcion_realizada_err']) && empty($data['estado_actividad_err'])){
                
                if($this->actividadModel->addActividad($data)){
                    redirect('actividades/index');
                } else {
                    die('Algo salió mal al intentar guardar la actividad.');
                }
            } else {
                // Cargar vista con errores
                $this->view('actividades/add', $data);
            }

        } else {
            // GET request: Cargar formulario vacío
            $data = [
                'title' => 'Añadir Actividad',
                'id_alcance' => '',
                'id_personal' => $personal_logueado ? $personal_logueado->Id_personal : '',
                'fecha_ingreso' => date('Y-m-d'), // Sugerir fecha de hoy
                'descripcion_realizada' => '',
                'estado_actividad' => 'Pendiente', // Estado por defecto
                
                'id_alcance_err' => '',
                'id_personal_err' => '',
                'fecha_ingreso_err' => '',
                'descripcion_realizada_err' => '',
                'estado_actividad_err' => '',

                'alcances' => $alcances,
                'personal' => $personal_para_dropdown,
                'estados' => $this->estados
            ];

            $this->view('actividades/add', $data);
        }
    }

    // Editar Actividad
    public function edit($id){
        // Verificar permiso para editar actividades
        $this->verificarAcceso('actividades', 'editar');
        
        // Cargar datos para dropdowns
        $alcances = $this->alcanceModel->getAlcances();

        // --- MODIFICACIÓN CLAVE: Restringir Personal al usuario logueado ---
        $id_usuario_logueado = $_SESSION['user_id'];
        $personal_logueado = $this->personalModel->getPersonalByUserId($id_usuario_logueado);
        $personal_para_dropdown = $personal_logueado ? [$personal_logueado] : [];
        // ------------------------------------------------------------------

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $fecha_ingreso = trim($_POST['fecha_ingreso']);
            // El Id_personal se toma del usuario logueado para garantizar la seguridad
            $id_personal_post = $personal_logueado ? $personal_logueado->Id_personal : '';

            $data = [
                'id' => $id,
                'title' => 'Editar Actividad',
                'id_alcance' => trim($_POST['id_alcance']),
                'id_personal' => $id_personal_post,
                'fecha_ingreso' => $fecha_ingreso,
                'descripcion_realizada' => trim($_POST['descripcion_realizada']),
                'estado_actividad' => trim($_POST['estado_actividad']),

                // Errores
                'id_alcance_err' => '',
                'id_personal_err' => '',
                'fecha_ingreso_err' => '',
                'descripcion_realizada_err' => '',
                'estado_actividad_err' => '',

                // Dropdowns
                'alcances' => $alcances,
                'personal' => $personal_para_dropdown,
                'estados' => $this->estados
            ];
            
            // 1. Validar datos (misma lógica que Add)
            if(empty($data['id_alcance'])){
                $data['id_alcance_err'] = 'Debe seleccionar un alcance.';
            }
            if(empty($data['id_personal'])){
                $data['id_personal_err'] = 'Debe seleccionar el personal responsable.';
            }
            if(empty($data['fecha_ingreso'])){
                $data['fecha_ingreso_err'] = 'La fecha de la actividad es obligatoria.';
            }
            if(empty($data['descripcion_realizada'])){
                $data['descripcion_realizada_err'] = 'Ingrese una descripción del trabajo a realizar o realizado.';
            }
            if(!in_array($data['estado_actividad'], $this->estados)){
                $data['estado_actividad_err'] = 'Estado de actividad no válido.';
            }
            
            // 2. Si no hay errores
            if(empty($data['id_alcance_err']) && empty($data['id_personal_err']) && empty($data['fecha_ingreso_err']) && empty($data['descripcion_realizada_err']) && empty($data['estado_actividad_err'])){
                if($this->actividadModel->updateActividad($data)){
                    redirect('actividades/index');
                } else {
                    die('Algo salió mal al intentar actualizar.');
                }
            } else {
                $this->view('actividades/edit', $data);
            }

        } else {
            // GET request: Cargar formulario con datos existentes
            $actividad = $this->actividadModel->getActividadById($id);

            if(!$actividad){ redirect('actividades/index'); }
            // --- REGLA DE NEGOCIO: Solo el personal responsable puede editar ---
            if ($personal_logueado && $actividad->Id_personal != $personal_logueado->Id_personal) {
                 // Puedes usar flash message aquí
                 redirect('actividades/index');
            }

            $data = [
                'id' => $id,
                'title' => 'Editar Actividad',
                'id_alcance' => $actividad->Id_alcance,
                'id_personal' => $actividad->Id_personal,
                'fecha_ingreso' => $actividad->Fecha_ingreso,
                'descripcion_realizada' => $actividad->Descripcion_realizada,
                'estado_actividad' => $actividad->Estado_actividad,

                'id_alcance_err' => '',
                'id_personal_err' => '',
                'fecha_ingreso_err' => '',
                'descripcion_realizada_err' => '',
                'estado_actividad_err' => '',

                'alcances' => $alcances,
                'personal' => $personal_para_dropdown,
                'estados' => $this->estados
            ];
            
            $this->view('actividades/edit', $data);
        }
    }

    // Eliminar Actividad (Soft Delete)
    public function delete($id){
        // Verificar permiso para eliminar actividades
        $this->verificarAcceso('actividades', 'eliminar');
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->actividadModel->deleteActividad($id)){
                redirect('actividades/index');
            } else {
                die('Algo salió mal al intentar eliminar la actividad.');
            }
        } else {
            redirect('actividades/index');
        }
    }

    /**
     * API Endpoint para obtener actividades por mes/año para el usuario logueado.
     * URL esperada: /actividades/get_monthly_activities/YYYY/MM
     */
    public function get_monthly_activities($year = null, $month = null){
    
        // ⚠️ CRÍTICO: Limpiar cualquier salida inesperada (errores, warnings, espacios)
        if (ob_get_contents()) {
            ob_end_clean(); 
        }
        
        // El header debe ir DESPUÉS de limpiar el buffer
        header('Content-Type: application/json');
        
        // Función auxiliar para enviar JSON de error y terminar
        $send_error_response = function($code, $message) {
            http_response_code($code);
            echo json_encode(['error' => $message]);
            exit; // Detiene la ejecución
        };

        if(!isLoggedIn()){
            $send_error_response(401, 'Usuario no autenticado');
        }

        $id_usuario_logueado = $_SESSION['user_id'];

        $personal_logueado = $this->personalModel->getPersonalByUserId($id_usuario_logueado);
        
        if (!$personal_logueado) {
            $send_error_response(403, 'Usuario logueado no tiene registro de Personal.');
        }



        $id_personal = $personal_logueado->Id_personal;
        // Si la versión de PHP lo requiere, cambia $year ?? date('Y') a ($year !== null ? $year : date('Y'))
        $currentYear = $year ?? date('Y'); 
        $currentMonth = $month ?? date('n'); 

        if (!is_numeric($currentYear) || !is_numeric($currentMonth)) {
            echo json_encode(['error' => 'Formato de fecha inválido.']);
            exit;
        }

        if ($currentMonth < 1 || $currentMonth > 12) {
            $send_error_response(400, 'Formato de fecha inválido.');
        }
        
        // Nota: El modelo debe ser actualizado en el siguiente paso para devolver los campos necesarios.
        $actividades = $this->actividadModel->getActividadesByMonthAndPersonal($id_personal, $currentYear, $currentMonth);
        
        // Devolver los datos
        echo json_encode($actividades);
        exit; // Asegura que nada más se ejecute
    }

    /**
     * Procesa múltiples actividades desde el formulario modal.
     */
    public function process_multiple(){
        // **********************************************
        // 1. VERIFICACIÓN DE SEGURIDAD Y SESIÓN
        // **********************************************
        if(!isLoggedIn()){
            // Redirige al login si no hay sesión
            // ESTO DEBE APUNTAR AL CONTROLADOR DE LOGIN (generalmente users/login)
            redirect('users/login'); 
        }
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            
            $fecha_ingreso = trim($_POST['fecha_ingreso']);
            $alcances_data = $_POST['alcances'] ?? []; // Array: [Id_alcance => Descripcion_realizada]
            
            $id_usuario_logueado = $_SESSION['user_id'];
            $personal_logueado = $this->personalModel->getPersonalByUserId($id_usuario_logueado);
            
            if (!$personal_logueado) {
                // Deberías manejar este error con un mensaje flash y redirección
                redirect('pages/index');
                return;
            }
            $id_personal = $personal_logueado->Id_personal;
            
            $activities_to_save = [];
            $saved_count = 0;

            // 1. Filtrar y preparar los datos
            foreach ($alcances_data as $id_alcance => $descripcion) {
                $descripcion_limpia = trim($descripcion);

                // Solo guarda si la descripción no está vacía
                if (!empty($descripcion_limpia)) {
                    $activities_to_save[] = [
                        'id_alcance' => $id_alcance,
                        'id_personal' => $id_personal,
                        'fecha_ingreso' => $fecha_ingreso,
                        'descripcion_realizada' => $descripcion_limpia,
                        'estado_actividad' => 'Completada', // Asumimos que si se registra, se completó
                    ];
                }
            }

            // 2. Guardar las actividades válidas
            foreach ($activities_to_save as $data) {
                // Este método asume que addActividad está en ActividadModel
                if ($this->actividadModel->addActividad($data)) {
                    $saved_count++;
                }
            }

            if ($saved_count > 0) {
                // Configurar mensaje de éxito: $saved_count . ' actividades registradas.'
            } else {
                // Configurar mensaje de advertencia: 'No se registraron actividades, revise si dejó los campos vacíos.'
            }

            // 3. Redireccionar al calendario
            redirect('pages/index'); // Asume que la vista de calendario está en /actividades/calendar
            return;
        } else {
            redirect('pages/index');
            return;
        }
    }

    /**
     * API Endpoint para obtener los detalles de una actividad específica (para el modal).
     * URL esperada: /actividades/get_activity_details/ID
     */
    public function get_activity_details($id = null){
        header('Content-Type: application/json');
        if(!isLoggedIn() || !is_numeric($id)){
            http_response_code(400); 
            echo json_encode(['error' => 'Solicitud inválida.']);
            return;
        }
        
        $actividad = $this->actividadModel->getActividadByIdWithPersonal($id);
        
        if (!$actividad) {
            http_response_code(404);
            echo json_encode(['error' => 'Actividad no encontrada o inactiva.']);
            return;
        }

        // Opcional: Implementar chequeo de seguridad para que solo el personal responsable o admin pueda ver/editar
        $id_usuario_logueado = $_SESSION['user_id'];
        $personal_logueado = $this->personalModel->getPersonalByUserId($id_usuario_logueado);
        
        if ($personal_logueado && $actividad->Id_personal != $personal_logueado->Id_personal) {
            // Si el usuario no es el responsable, se niega el acceso
            // Opcionalmente, puedes devolver solo datos limitados si lo permites
            // Por ahora, lo dejamos simple.
        }
        
        echo json_encode($actividad);
    }

    /**
     * Obtiene las actividades registradas en una fecha específica.
     * Se usa para validar si ya existen actividades en ese día.
     */
    public function get_activities_by_date($fecha = null){
        // ⚠️ CRÍTICO: Limpiar cualquier salida inesperada
        if (ob_get_contents()) {
            ob_end_clean(); 
        }
        
        header('Content-Type: application/json');
        
        if(!isLoggedIn()){
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no autenticado']);
            exit;
        }

        if(empty($fecha)){
            http_response_code(400);
            echo json_encode(['error' => 'Fecha requerida']);
            exit;
        }

        $id_usuario_logueado = $_SESSION['user_id'];
        $personal_logueado = $this->personalModel->getPersonalByUserId($id_usuario_logueado);
        
        if (!$personal_logueado) {
            http_response_code(403);
            echo json_encode(['error' => 'Usuario no asociado a Personal']);
            exit;
        }

        $id_personal = $personal_logueado->Id_personal;

        // Obtener actividades del día
        $actividades = $this->actividadModel->getActividadesByFechaAndPersonal($id_personal, $fecha);
        
        // Convertir a array asociativo para mejor manipulación en JavaScript
        $resultado = [];
        foreach($actividades as $act) {
            $resultado[$act->Id_alcance] = [
                'Id_actividad' => $act->Id_actividad,
                'Descripcion_realizada' => $act->Descripcion_realizada,
                'Estado_actividad' => $act->Estado_actividad,
                'Alcance_Descripcion' => $act->Alcance_Descripcion
            ];
        }

        echo json_encode($resultado);
        exit;
    }

    /**
     * Procesa la edición de una actividad desde el modal del calendario.
     */
    public function edit_calendar_activity(){
        if(!isLoggedIn()){
            redirect('users/login'); 
            return;
        }
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            
            $id = trim($_POST['id']);
            
            // Obtener la actividad original para asegurar que el ID_Personal no cambie
            $originalActivity = $this->actividadModel->getActividadById($id);

            if (!$originalActivity) {
                // flash('error', 'La actividad a editar no existe.');
                redirect('pages/index'); 
                return;
            }

            $data = [
                'id' => $id,
                'id_alcance' => trim($_POST['id_alcance']),
                // Reutilizamos el ID_personal original para evitar que el usuario lo cambie
                'id_personal' => $originalActivity->Id_personal, 
                'fecha_ingreso' => $originalActivity->Fecha_ingreso, // La fecha no se edita desde este modal
                'descripcion_realizada' => trim($_POST['descripcion_realizada']),
                'estado_actividad' => trim($_POST['estado_actividad']),
            ];

            // Se deben realizar validaciones aquí (alcance, descripción, estado)
            // ... (Añadir lógica de validación similar a public function edit($id)) ...

            if ($this->actividadModel->updateActividad($data)) {
                // flash('success', 'Actividad actualizada exitosamente.');
                redirect('pages/index');
            } else {
                // flash('error', 'Hubo un error al actualizar la actividad.');
                redirect('pages/index');
            }

        } else {
            redirect('pages/index');
        }
    }

    public function get_contrato_pagos(){
        // ⚠️ Limpiar buffer de salida
        if (ob_get_level()) {
            ob_end_clean(); 
        }
        
        header('Content-Type: application/json');
        
        if(!isLoggedIn()){
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            exit;
        }

        $id_usuario_logueado = $_SESSION['user_id'];
        $personal_logueado = $this->personalModel->getPersonalByUserId($id_usuario_logueado);
        
        if (!$personal_logueado) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Personal no encontrado']);
            exit;
        }

        $id_personal = $personal_logueado->Id_personal;
        $contrato = $this->contratoModel->getContratoByIdUsuario($id_personal);

        // Validar que haya contrato
        if (!$contrato || !is_array($contrato) || count($contrato) === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false, 
                'numero_pagos' => 0,
                'error' => 'No hay contrato activo para este personal'
            ]);
            exit;
        }

        // Extraer el número de pagos del primer contrato
        $numero_pagos = isset($contrato[0]->Numero_pagos) ? (int)$contrato[0]->Numero_pagos : 0;

        echo json_encode([
            'success' => true,
            'numero_pagos' => $numero_pagos,
            'id_contrato' => $contrato[0]->Id_contrato ?? null
        ]);
        exit;
    }

    public function generar_reporte_pdf(){

        // Verificar autenticación y método POST
        if(!isLoggedIn() || $_SERVER['REQUEST_METHOD'] != 'POST'){
            redirect('actividades');
            return;
        }
        // CRÍTICO: Limpiar cualquier salida previa (incluyendo los Warnings que causaron el Fatal Error)
        if (ob_get_level()) {
            ob_clean(); 
        }

        $data = [
            'fecha_inicio' => trim($_POST['fecha_inicio']),
            'fecha_fin'    => trim($_POST['fecha_fin']),
            'numero_pago'  => isset($_POST['numero_pago']) ? (int)trim($_POST['numero_pago']) : 0,
            'mes_reporte'  => isset($_POST['mes_reporte']) ? (int)trim($_POST['mes_reporte']) : 0,
            'anio_reporte' => isset($_POST['anio_reporte']) ? (int)trim($_POST['anio_reporte']) : 0
        ];

        // 1. Validar fechas
        if (empty($data['fecha_inicio']) || empty($data['fecha_fin']) || $data['fecha_inicio'] > $data['fecha_fin']) {
            flash('reporte_error', 'Rango de fechas inválido.', 'alert-danger');
            redirect('actividades');
            return;
        }

        // 2. Validar número de pago
        if ($data['numero_pago'] <= 0) {
            flash('reporte_error', 'Debe seleccionar un número de pago válido.', 'alert-danger');
            redirect('actividades');
            return;
        }

        // 3. Validar mes y año
        if ($data['mes_reporte'] < 1 || $data['mes_reporte'] > 12) {
            flash('reporte_error', 'Debe seleccionar un mes válido.', 'alert-danger');
            redirect('actividades');
            return;
        }

        if ($data['anio_reporte'] < 2024 || $data['anio_reporte'] > 2030) {
            flash('reporte_error', 'Debe seleccionar un año válido.', 'alert-danger');
            redirect('actividades');
            return;
        }

        // 4. Obtener ID de personal
        $id_usuario_logueado = $_SESSION['user_id'];
        $personal_logueado = $this->personalModel->getPersonalByUserId($id_usuario_logueado);
        
        
        if (!$personal_logueado) {
            flash('reporte_error', 'Usuario no asociado a un registro de Personal.', 'alert-danger');
            redirect('actividades');
            return;
        }
        $id_personal = $personal_logueado->Id_personal;
        $contrato = $this->contratoModel->getContratoByIdUsuario($id_personal);
        $alcance = $this->alcanceModel->getAlcancesByIdContrato($contrato[0]->Id_contrato);

        // 5. Obtener actividades del modelo
        $actividades = $this->actividadModel->getCompletedActivitiesByDateRange(
            $id_personal, 
            $data['fecha_inicio'], 
            $data['fecha_fin']
        );

        // 6. Cargar y generar el PDF usando la nueva librería/clase
        
        // Cargar la librería (Asegúrate de que la ruta sea correcta según tu proyecto)
        require_once APPROOT . '/libraries/ReporteActividadesPdf.php'; 
        
        // Si usas FPDF, asegúrate de que esté cargado aquí
        // require_once APPROOT . '/libraries/fpdf/fpdf.php'; 

        $pdfGenerator = new ReporteActividadesPdf();
        // Llamar al método principal de la librería
        $pdfGenerator->generar(
            $actividades, 
            $data['fecha_inicio'], 
            $data['fecha_fin'], 
            $personal_logueado,
            $contrato,
            $alcance,
            $data['numero_pago'],  // Pasar el número de pago seleccionado
            $data['mes_reporte'],  // Pasar el mes seleccionado
            $data['anio_reporte']  // Pasar el año seleccionado
        );
        // Nota: El método 'generar' contiene 'exit', por lo que no se requiere nada más aquí.
    }
}