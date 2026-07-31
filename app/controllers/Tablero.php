<?php
class Tablero extends Controller {
    private $tableroModel;

    public function __construct(){
        if(!isLoggedIn()){
            redirect('users/login');
        }

        $this->tableroModel = $this->model('TableroModel');
    }

    public function index(){
        $this->verificarAcceso('tablero', 'ver');

        $id_usuario = (int)$_SESSION['user_id'];
        $tableros = $this->tableroModel->getTablerosByUsuario($id_usuario);

        if(empty($tableros)){
            $data = [
                'title' => 'Tablero de Actividades',
                'tableros' => [],
                'tableroActual' => null,
                'columnas' => [],
                'tarjetasPorColumna' => [],
                'etiquetas' => [],
                'prioridades' => [],
                'usuarios' => [],
                'usuariosAsignados' => [],
                'contratoUsuarioActual' => null,
                'actividades' => [],
                'actividadesAgrupadas' => [],
                'alcances' => [],
                'alcancesAgrupados' => [],
                'permisosTablero' => $this->getEmptyBoardPermissions(),
                'tableroDeleteSummary' => (object)[
                    'total_columnas' => 0,
                    'total_tarjetas' => 0,
                    'total_listas' => 0,
                    'total_tareas' => 0
                ],
                'canDeleteTablero' => false
            ];
            return $this->view('tablero/index', $data);
        }

        $id_tablero = isset($_GET['tablero_id']) && is_numeric($_GET['tablero_id'])
            ? (int)$_GET['tablero_id']
            : (int)$tableros[0]->Id_tablero;

        $tableroActual = null;
        foreach($tableros as $tab){
            if((int)$tab->Id_tablero === $id_tablero){
                $tableroActual = $tab;
                break;
            }
        }

        if(!$tableroActual){
            $id_tablero = (int)$tableros[0]->Id_tablero;
            $tableroActual = $tableros[0];
        }

        $permObj = $this->tableroModel->getPermisosUsuarioTablero($id_tablero, $id_usuario);
        $permisosTablero = $this->buildBoardPermissionsArray($permObj);

        if(!$permisosTablero['tablero_ver']){
            flashMessage('tablero_error', 'No tiene permiso para ver este tablero.', 'danger');
            redirect('tablero/index');
        }

        $tableroDeleteSummary = $this->tableroModel->getTableroDeletionSummary($id_tablero);
        $canDeleteTablero = $this->tableroModel->canDeleteTablero($id_tablero);

        $columnas = $this->tableroModel->getColumnasActivasByTablero($id_tablero);
        $tarjetas = $this->tableroModel->getTarjetasActivasByTablero($id_tablero, true); // incluir archivadas, el filtrado es cliente
        $etiquetas = $this->tableroModel->getEtiquetasByTablero($id_tablero);
        $prioridades = $this->tableroModel->getPrioridadesByTablero($id_tablero);
        $usuarios = $this->tableroModel->getUsuariosActivos();
        $usuariosAsignados = $this->tableroModel->getUsuariosAsignadosTablero($id_tablero);
        $contratoUsuarioActual = $this->tableroModel->getContratoPersonalUsuario($id_usuario);
        $actividades = $this->tableroModel->getActividadesDisponiblesByTablero($id_tablero, 300);
        $alcances = $this->tableroModel->getAlcancesDisponiblesByTablero($id_tablero);
        $actividadesAgrupadas = [];
        foreach($actividades as $actividad){
            $idUsuarioActividad = !empty($actividad->Actividad_Id_usuario) ? (int)$actividad->Actividad_Id_usuario : 0;
            $nombreCompleto = trim(($actividad->Nombre_Completo ?? '') . ' ' . ($actividad->Apellido_Completo ?? ''));
            $email = trim((string)($actividad->Usuario_Email ?? ''));

            $grupoLabel = $nombreCompleto !== '' ? $nombreCompleto : ($email !== '' ? $email : 'Usuario sin nombre');
            if($email !== '' && stripos($grupoLabel, $email) === false){
                $grupoLabel .= ' (' . $email . ')';
            }

            $groupKey = $idUsuarioActividad > 0 ? ('usuario_' . $idUsuarioActividad) : ('grupo_' . md5($grupoLabel));
            if(!isset($actividadesAgrupadas[$groupKey])){
                $actividadesAgrupadas[$groupKey] = [
                    'label' => $grupoLabel,
                    'items' => []
                ];
            }

            $actividadesAgrupadas[$groupKey]['items'][] = $actividad;
        }

        $alcancesAgrupados = [];
        foreach($alcances as $alcance){
            $contratoId = !empty($alcance->Id_contrato) ? (int)$alcance->Id_contrato : 0;
            $contratoExp = trim((string)($alcance->Contrato_Expediente ?? ''));
            $label = $contratoExp !== '' ? ('Contrato ' . $contratoExp) : ('Contrato #' . $contratoId);
            $groupKey = $contratoId > 0 ? ('contrato_' . $contratoId) : ('grupo_' . md5($label));

            if(!isset($alcancesAgrupados[$groupKey])){
                $alcancesAgrupados[$groupKey] = [
                    'label' => $label,
                    'items' => []
                ];
            }

            $alcancesAgrupados[$groupKey]['items'][] = $alcance;
        }

        $tarjetaIds = [];
        foreach($tarjetas as $tarjeta){
            $tarjetaIds[] = (int)$tarjeta->Id_tarjeta;
        }

        $asignadosPorTarjeta = [];
        foreach($this->tableroModel->getAsignadosByTarjetas($tarjetaIds) as $row){
            $idTarjeta = (int)$row->Id_tarjeta;
            if(!isset($asignadosPorTarjeta[$idTarjeta])){
                $asignadosPorTarjeta[$idTarjeta] = [];
            }
            $asignadosPorTarjeta[$idTarjeta][] = (object)[
                'Id_usuario' => (int)$row->Id_usuario_asignado,
                'Email' => $row->Usuario_email ?? ''
            ];
        }

        $etiquetasPorTarjeta = [];
        $etiquetaIdsPorTarjeta = [];
        foreach($this->tableroModel->getEtiquetasByTarjetas($tarjetaIds) as $etiquetaTarjeta){
            $idTarjeta = (int)$etiquetaTarjeta->Id_tarjeta;
            if(!isset($etiquetasPorTarjeta[$idTarjeta])){
                $etiquetasPorTarjeta[$idTarjeta] = [];
                $etiquetaIdsPorTarjeta[$idTarjeta] = [];
            }

            $etiquetasPorTarjeta[$idTarjeta][] = (object)[
                'Id_etiqueta' => (int)$etiquetaTarjeta->Id_etiqueta,
                'Nombre' => $etiquetaTarjeta->Nombre ?? '',
                'Color' => $etiquetaTarjeta->Color
            ];
            $etiquetaIdsPorTarjeta[$idTarjeta][] = (int)$etiquetaTarjeta->Id_etiqueta;
        }

        $tarjetasPorColumna = [];
        foreach($columnas as $columna){
            $tarjetasPorColumna[$columna->Id_columna] = [];
        }

        foreach($tarjetas as $tarjeta){
            if(!isset($tarjetasPorColumna[$tarjeta->Id_columna])){
                $tarjetasPorColumna[$tarjeta->Id_columna] = [];
            }
            $tarjeta->Tiempo_Total_Segundos = isset($tarjeta->Tiempo_Total_Segundos) ? (int)$tarjeta->Tiempo_Total_Segundos : 0;
            $tarjeta->Etiquetas = $etiquetasPorTarjeta[(int)$tarjeta->Id_tarjeta] ?? [];
            $tarjeta->EtiquetaIds = $etiquetaIdsPorTarjeta[(int)$tarjeta->Id_tarjeta] ?? [];
            $tarjeta->AsignadosDetalle = $asignadosPorTarjeta[(int)$tarjeta->Id_tarjeta] ?? [];
            $tarjeta->Can_Delete =
                empty($tarjeta->Id_usuario_asignado)
                && empty($tarjeta->Id_alcance)
                && empty($tarjeta->Id_actividad)
                && ((int)($tarjeta->Total_Listas_Tareas ?? 0) === 0)
                && ((int)($tarjeta->Total_Tareas ?? 0) === 0);

            $tarjetasPorColumna[$tarjeta->Id_columna][] = $tarjeta;
        }

        $data = [
            'title' => 'Tablero de Actividades',
            'tableros' => $tableros,
            'tableroActual' => $tableroActual,
            'columnas' => $columnas,
            'tarjetasPorColumna' => $tarjetasPorColumna,
            'etiquetas' => $etiquetas,
            'prioridades' => $prioridades,
            'usuarios' => $usuarios,
            'usuariosAsignados' => $usuariosAsignados,
            'contratoUsuarioActual' => $contratoUsuarioActual,
            'actividades' => $actividades,
            'actividadesAgrupadas' => array_values($actividadesAgrupadas),
            'alcances' => $alcances,
            'alcancesAgrupados' => array_values($alcancesAgrupados),
            'permisosTablero' => $permisosTablero,
            'tableroDeleteSummary' => $tableroDeleteSummary,
            'canDeleteTablero' => $canDeleteTablero
        ];

        $this->view('tablero/index', $data);
    }

    public function reporteria(){
        $this->verificarAcceso('tablero', 'reporteria');

        $id_usuario = (int)$_SESSION['user_id'];
        $context = $this->resolveReporteriaContext($id_usuario);

        if(empty($context['tableros'])){
            $data = [
                'title' => 'Reporte de Tablero',
                'tableros' => [],
                'tableroActual' => null,
                'permisosTablero' => $this->getEmptyBoardPermissions(),
                'reporteAgrupado' => [],
                'filtros' => []
            ];
            return $this->view('tablero/reporteria', $data);
        }

        if(!$context['permisosTablero']['tablero_ver']){
            flashMessage('tablero_error', 'No tiene permiso para ver este tablero.', 'danger');
            redirect('tablero/index');
        }

        $filters = $this->getReporteriaFilters();
        $tableroActual = $context['tableroActual'];
        $tarjetas = $this->tableroModel->getTarjetasActivasByTablero($context['id_tablero']);
        $tarjetaIds = array_map(function($tarjeta){ return (int)$tarjeta->Id_tarjeta; }, $tarjetas);

        $assignedUsers = [];
        foreach($tarjetas as $tarjeta){
            $email = trim((string)($tarjeta->Asignado_Email ?? ''));
            if($email !== ''){
                $assignedUsers[$email] = $email;
            }
        }
        foreach($this->tableroModel->getAsignadosByTarjetas($tarjetaIds) as $row){
            $email = trim((string)($row->Usuario_email ?? ''));
            if($email !== ''){
                $assignedUsers[$email] = $email;
            }
        }
        asort($assignedUsers);

        $data = [
            'title' => 'Reporte de Tablero',
            'tableros' => $context['tableros'],
            'tableroActual' => $tableroActual,
            'permisosTablero' => $context['permisosTablero'],
            'reporteAgrupado' => $this->buildReporteAgrupado($context['id_tablero'], $filters),
            'filtros' => $filters,
            'filtrosUsuariosAsignados' => $assignedUsers,
            'filtrosEtiquetas' => $this->tableroModel->getEtiquetasByTablero($context['id_tablero']),
            'filtrosEtapas' => $this->tableroModel->getColumnasActivasByTablero($context['id_tablero'])
        ];

        $data['resumenTiempoUsuarios'] = $this->buildResumenTiempoUsuarios($data['reporteAgrupado']);

        $this->view('tablero/reporteria', $data);
    }

    public function export_reporteria(){
        $this->verificarAcceso('tablero', 'reporteria');

        if($_SERVER['REQUEST_METHOD'] !== 'GET'){
            redirect('tablero/reporteria');
        }

        $formato = strtolower(trim((string)($_GET['format'] ?? '')));
        if(!in_array($formato, ['csv', 'xlsx', 'pdf'], true)){
            flashMessage('tablero_error', 'Formato de exportacion no valido.', 'danger');
            redirect('tablero/reporteria');
        }

        $id_usuario = (int)$_SESSION['user_id'];
        $context = $this->resolveReporteriaContext($id_usuario);

        if(empty($context['tableros'])){
            flashMessage('tablero_error', 'No hay tableros disponibles para exportar.', 'danger');
            redirect('tablero/reporteria');
        }

        if(!$context['permisosTablero']['tablero_ver']){
            flashMessage('tablero_error', 'No tiene permiso para exportar este tablero.', 'danger');
            redirect('tablero/index');
        }

        $filters = $this->getReporteriaFilters();
        $reporteAgrupado = $this->buildReporteAgrupado($context['id_tablero'], $filters);
        $rows = $this->flattenReporteAgrupado($reporteAgrupado);
        $headers = $this->getReporteriaExportHeaders();
        $filenameBase = $this->buildReporteriaFilenameBase($context['tableroActual']);

        if($formato === 'csv'){
            $this->downloadReporteriaCsv($filenameBase . '.csv', $headers, $rows);
        }

        if($formato === 'xlsx'){
            try {
                require_once APPROOT . '/libraries/TableroReporteriaXlsx.php';
                $exporter = new TableroReporteriaXlsx();
                $exporter->download(
                    $filenameBase . '.xlsx',
                    $headers,
                    $rows,
                    $context['tableroActual'] ? (string)$context['tableroActual']->Nombre : 'Reporte Tablero'
                );
            } catch(Throwable $e) {
                flashMessage('tablero_error', 'No se pudo generar el archivo XLSX: ' . $e->getMessage(), 'danger');
                redirect('tablero/reporteria?tablero_id=' . $context['id_tablero']);
            }
        }

        try {
            require_once APPROOT . '/libraries/TableroReporteriaPdf.php';
            $exporter = new TableroReporteriaPdf();
            $exporter->download(
                $filenameBase . '.pdf',
                $headers,
                $rows,
                $context['tableroActual'] ? (string)$context['tableroActual']->Nombre : 'Reporte Tablero'
            );
        } catch(Throwable $e) {
            flashMessage('tablero_error', 'No se pudo generar el archivo PDF: ' . $e->getMessage(), 'danger');
            redirect('tablero/reporteria?tablero_id=' . $context['id_tablero']);
        }
    }

    public function dashboard(){
        $this->verificarAcceso('tablero', 'dashboard');

        $id_usuario = (int)$_SESSION['user_id'];
        $context = $this->resolveReporteriaContext($id_usuario);

        if(empty($context['tableros'])){
            $data = [
                'title' => 'Dashboard de Tablero',
                'tableros' => [],
                'tableroActual' => null,
                'permisosTablero' => $this->getEmptyBoardPermissions(),
                'dashboardMetrics' => $this->getEmptyDashboardMetrics()
            ];
            return $this->view('tablero/dashboard', $data);
        }

        if(!$context['permisosTablero']['tablero_ver']){
            flashMessage('tablero_error', 'No tiene permiso para ver este tablero.', 'danger');
            redirect('tablero/index');
        }

        $data = [
            'title' => 'Dashboard de Tablero',
            'tableros' => $context['tableros'],
            'tableroActual' => $context['tableroActual'],
            'permisosTablero' => $context['permisosTablero'],
            'dashboardMetrics' => $this->buildDashboardMetrics($context['id_tablero'])
        ];

        $this->view('tablero/dashboard', $data);
    }

    public function calendario(){
        $this->verificarAcceso('tablero', 'calendario');

        $id_usuario = (int)$_SESSION['user_id'];
        $context = $this->resolveReporteriaContext($id_usuario);

        if(empty($context['tableros'])){
            $data = [
                'title' => 'Calendario de Tarjetas',
                'tableros' => [],
                'tableroActual' => null,
                'permisosTablero' => $this->getEmptyBoardPermissions(),
                'calendarEvents' => []
            ];
            return $this->view('tablero/calendario', $data);
        }

        if(!$context['permisosTablero']['tablero_ver']){
            flashMessage('tablero_error', 'No tiene permiso para ver este tablero.', 'danger');
            redirect('tablero/index');
        }

        $data = [
            'title' => 'Calendario de Tarjetas',
            'tableros' => $context['tableros'],
            'tableroActual' => $context['tableroActual'],
            'permisosTablero' => $context['permisosTablero'],
            'calendarEvents' => $this->buildCalendarEvents($context['id_tablero'])
        ];

        $this->view('tablero/calendario', $data);
    }

    private function formatSegundosReporte($total){
        $sec = max(0, (int)$total);
        $hh = str_pad((string)floor($sec / 3600), 2, '0', STR_PAD_LEFT);
        $mm = str_pad((string)floor(($sec % 3600) / 60), 2, '0', STR_PAD_LEFT);
        $ss = str_pad((string)($sec % 60), 2, '0', STR_PAD_LEFT);
        return $hh . ':' . $mm . ':' . $ss;
    }

    private function getReporteriaFilters(){
        return [
            'assigned_user' => trim((string)($_GET['assigned_user'] ?? '')),
            'etiqueta_id' => isset($_GET['etiqueta_id']) && is_numeric($_GET['etiqueta_id']) ? (int)$_GET['etiqueta_id'] : 0,
            'etapa_id' => isset($_GET['etapa_id']) && is_numeric($_GET['etapa_id']) ? (int)$_GET['etapa_id'] : 0,
            'fecha_inicio' => trim((string)($_GET['fecha_inicio'] ?? '')),
            'fecha_fin' => trim((string)($_GET['fecha_fin'] ?? ''))
        ];
    }

    private function resolveReporteriaContext($id_usuario){
        $tableros = $this->tableroModel->getTablerosByUsuario((int)$id_usuario);
        if(empty($tableros)){
            return [
                'tableros' => [],
                'tableroActual' => null,
                'id_tablero' => 0,
                'permisosTablero' => $this->getEmptyBoardPermissions()
            ];
        }

        $id_tablero = isset($_GET['tablero_id']) && is_numeric($_GET['tablero_id'])
            ? (int)$_GET['tablero_id']
            : (int)$tableros[0]->Id_tablero;

        $tableroActual = null;
        foreach($tableros as $tab){
            if((int)$tab->Id_tablero === $id_tablero){
                $tableroActual = $tab;
                break;
            }
        }

        if(!$tableroActual){
            $id_tablero = (int)$tableros[0]->Id_tablero;
            $tableroActual = $tableros[0];
        }

        $permObj = $this->tableroModel->getPermisosUsuarioTablero($id_tablero, (int)$id_usuario);

        return [
            'tableros' => $tableros,
            'tableroActual' => $tableroActual,
            'id_tablero' => $id_tablero,
            'permisosTablero' => $this->buildBoardPermissionsArray($permObj)
        ];
    }

    private function buildReporteAgrupado($id_tablero, array $filters = []){
        $columnas = $this->tableroModel->getColumnasActivasByTablero($id_tablero);
        $columnasMap = [];
        foreach($columnas as $columna){
            $columnasMap[(int)$columna->Id_columna] = $columna->Nombre;
        }

        $tarjetas = $this->tableroModel->getTarjetasActivasByTablero($id_tablero);
        $tarjetaIds = [];
        foreach($tarjetas as $tarjeta){
            $tarjetaIds[] = (int)$tarjeta->Id_tarjeta;
        }

        $taskAssignedUsersByTarjeta = [];
        foreach($this->tableroModel->getAsignadosByTarjetas($tarjetaIds) as $asignadoTarjeta){
            $idTarjeta = (int)$asignadoTarjeta->Id_tarjeta;
            if(!isset($taskAssignedUsersByTarjeta[$idTarjeta])){
                $taskAssignedUsersByTarjeta[$idTarjeta] = [];
            }
            $email = trim((string)($asignadoTarjeta->Usuario_email ?? ''));
            if($email !== '' && !in_array($email, $taskAssignedUsersByTarjeta[$idTarjeta], true)){
                $taskAssignedUsersByTarjeta[$idTarjeta][] = $email;
            }
        }

        $etiquetasPorTarjeta = [];
        foreach($this->tableroModel->getEtiquetasByTarjetas($tarjetaIds) as $etiquetaTarjeta){
            $idTarjeta = (int)$etiquetaTarjeta->Id_tarjeta;
            if(!isset($etiquetasPorTarjeta[$idTarjeta])){
                $etiquetasPorTarjeta[$idTarjeta] = [];
            }
            $etiquetasPorTarjeta[$idTarjeta][] = $etiquetaTarjeta;
        }

        $filters = array_merge([
            'assigned_user' => '',
            'etiqueta_id' => 0,
            'etapa_id' => 0,
            'fecha_inicio' => '',
            'fecha_fin' => ''
        ], $filters);

        $reporteAgrupado = [];
        foreach($tarjetas as $tarjeta){
            $idTarjeta = (int)$tarjeta->Id_tarjeta;
            $idColumna = (int)$tarjeta->Id_columna;

            $assignedFilter = trim((string)$filters['assigned_user']);
            $tagFilter = isset($filters['etiqueta_id']) ? (int)$filters['etiqueta_id'] : 0;
            $etapaFilter = isset($filters['etapa_id']) ? (int)$filters['etapa_id'] : 0;
            $fechaInicio = trim((string)$filters['fecha_inicio']);
            $fechaFin = trim((string)$filters['fecha_fin']);
            $fechaCreacion = trim((string)($tarjeta->Fecha_creacion ?? ''));

            if($assignedFilter !== ''){
                $assignedEmail = !empty($tarjeta->Asignado_Email) ? trim((string)$tarjeta->Asignado_Email) : '';
                $taskUsers = $taskAssignedUsersByTarjeta[$idTarjeta] ?? [];
                if($assignedEmail !== $assignedFilter && !in_array($assignedFilter, $taskUsers, true)){
                    continue;
                }
            }
            if($tagFilter > 0){
                $hasTag = false;
                foreach($etiquetasPorTarjeta[$idTarjeta] ?? [] as $etq){
                    if((int)($etq->Id_etiqueta ?? 0) === $tagFilter){
                        $hasTag = true;
                        break;
                    }
                }
                if(!$hasTag){
                    continue;
                }
            }
            if($etapaFilter > 0 && $idColumna !== $etapaFilter){
                continue;
            }
            if($fechaInicio !== '' || $fechaFin !== ''){
                $timestamp = $fechaCreacion !== '' ? strtotime($fechaCreacion) : false;
                if($timestamp === false){
                    continue;
                }
                if($fechaInicio !== ''){
                    $startTs = strtotime($fechaInicio . ' 00:00:00');
                    if($startTs !== false && $timestamp < $startTs){
                        continue;
                    }
                }
                if($fechaFin !== ''){
                    $endTs = strtotime($fechaFin . ' 23:59:59');
                    if($endTs !== false && $timestamp > $endTs){
                        continue;
                    }
                }
            }

            $etiquetasTarjeta = $etiquetasPorTarjeta[$idTarjeta] ?? [];
            $etiquetasTexto = [];
            foreach($etiquetasTarjeta as $etq){
                $nombre = trim((string)($etq->Nombre ?? ''));
                $etiquetasTexto[] = $nombre !== '' ? $nombre : 'Sin texto';
            }

            $tareas = $this->tableroModel->getTareasByTarjeta($idTarjeta);
            $listasTexto = [];
            $tareasTexto = [];
            $tiempoTexto = [];
            $tiempoPorUsuarioTexto = [];
            $tiempoPorUsuarioSegundos = [];
            $totalTareasTarjeta = 0;
            $totalTiempoTarjetaSegundos = 0;
            $totalTiempoEnCursoTarjetaSegundos = $this->tableroModel->getTiempoEnCursoTarjeta($idTarjeta);

            foreach($tareas as $tarea){
                $nombreLista = trim((string)($tarea->Nombre_tarea ?? ''));
                if($nombreLista === ''){
                    $nombreLista = 'Lista sin nombre';
                }
                $listasTexto[] = $nombreLista;

                $detalles = $this->tableroModel->getDetallesByTarea((int)$tarea->Id_tarea, null);
                foreach($detalles as $detalle){
                    $descripcionDetalle = trim((string)($detalle->Descripcion ?? ''));
                    if($descripcionDetalle === ''){
                        $descripcionDetalle = 'Tarea sin descripcion';
                    }

                    $isDone = isset($detalle->Completado) && (int)$detalle->Completado === 1;
                    $tareasTexto[] = $descripcionDetalle . ($isDone ? ' (Completada)' : '');
                    $totalTareasTarjeta++;

                    $segundos = isset($detalle->Tiempo_Total_Segundos) ? (int)$detalle->Tiempo_Total_Segundos : 0;
                    $totalTiempoTarjetaSegundos += $segundos;
                    $tiempoTexto[] = $descripcionDetalle . ': ' . $this->formatSegundosReporte($segundos);

                    $tiempoUsuariosDetalle = $this->tableroModel->getTiempoDetallePorUsuario((int)$detalle->Id_tarea_detalle);
                    foreach($tiempoUsuariosDetalle as $tiempoUsuario){
                        $emailUsuario = trim((string)($tiempoUsuario->email ?? ''));
                        $idUsuarioTiempo = isset($tiempoUsuario->Id_usuario) ? (int)$tiempoUsuario->Id_usuario : 0;
                        $labelUsuario = $emailUsuario !== '' ? $emailUsuario : ('Usuario #' . $idUsuarioTiempo);
                        $segBase = isset($tiempoUsuario->Tiempo_total_segundos) ? (int)$tiempoUsuario->Tiempo_total_segundos : 0;
                        $segEnCurso = isset($tiempoUsuario->Tiempo_en_curso_segundos) ? (int)$tiempoUsuario->Tiempo_en_curso_segundos : 0;
                        $segTotalUsuario = max(0, $segBase + $segEnCurso);

                        if($segTotalUsuario <= 0){
                            continue;
                        }

                        $tiempoPorUsuarioTexto[] = $descripcionDetalle . ' / ' . $labelUsuario . ': ' . $this->formatSegundosReporte($segTotalUsuario);
                        if(!isset($tiempoPorUsuarioSegundos[$labelUsuario])){
                            $tiempoPorUsuarioSegundos[$labelUsuario] = 0;
                        }
                        $tiempoPorUsuarioSegundos[$labelUsuario] += $segTotalUsuario;
                    }
                }
            }

            $asignado = !empty($tarjeta->Asignado_Email) ? (string)$tarjeta->Asignado_Email : null;
            if($asignado === null && !empty($taskAssignedUsersByTarjeta[$idTarjeta])){
                $asignado = implode(', ', $taskAssignedUsersByTarjeta[$idTarjeta]);
            }
            if($asignado === null || trim($asignado) === ''){
                $asignado = 'Sin asignar';
            }

            if(!isset($reporteAgrupado[$asignado])){
                $reporteAgrupado[$asignado] = [];
            }

            $reporteAgrupado[$asignado][] = [
                'asignado' => $asignado,
                'descripcion' => trim((string)$tarjeta->Titulo),
                'descripcion_detalle' => trim((string)($tarjeta->Descripcion ?? '')),
                'etapa' => $columnasMap[$idColumna] ?? ('Columna #' . $idColumna),
                'prioridad' => trim((string)($tarjeta->Prioridad_Nombre ?? 'Sin prioridad')),
                'puntos_prioridad' => isset($tarjeta->Prioridad_Valor) ? (int)$tarjeta->Prioridad_Valor : 0,
                'etiquetas' => $etiquetasTexto,
                'listas_tareas' => $listasTexto,
                'tareas' => $tareasTexto,
                'tiempos' => $tiempoTexto,
                'tiempo_por_usuario' => $tiempoPorUsuarioTexto,
                'tiempo_por_usuario_segundos' => $tiempoPorUsuarioSegundos,
                'total_tareas' => $totalTareasTarjeta,
                'total_tiempo_segundos' => $totalTiempoTarjetaSegundos,
                'total_tiempo_en_curso_segundos' => $totalTiempoEnCursoTarjetaSegundos
            ];
        }

        ksort($reporteAgrupado);

        return $reporteAgrupado;
    }

    private function buildResumenTiempoUsuarios($reporteAgrupado){
        $summary = [];

        foreach($reporteAgrupado as $items){
            foreach((array)$items as $row){
                $tiempoUsuarios = isset($row['tiempo_por_usuario_segundos']) && is_array($row['tiempo_por_usuario_segundos'])
                    ? $row['tiempo_por_usuario_segundos']
                    : [];

                foreach($tiempoUsuarios as $usuario => $segundos){
                    $label = trim((string)$usuario);
                    if($label === ''){
                        $label = 'Usuario sin nombre';
                    }

                    if(!isset($summary[$label])){
                        $summary[$label] = [
                            'usuario' => $label,
                            'total_segundos' => 0,
                            'tarjetas' => []
                        ];
                    }

                    $seg = max(0, (int)$segundos);
                    $summary[$label]['total_segundos'] += $seg;

                    $tituloTarjeta = trim((string)($row['descripcion'] ?? 'Tarjeta sin titulo'));
                    if($tituloTarjeta === ''){
                        $tituloTarjeta = 'Tarjeta sin titulo';
                    }
                    $summary[$label]['tarjetas'][$tituloTarjeta] = true;
                }
            }
        }

        foreach($summary as &$item){
            $item['total_tarjetas'] = count($item['tarjetas']);
            unset($item['tarjetas']);
        }
        unset($item);

        usort($summary, function($left, $right){
            $cmp = (int)$right['total_segundos'] <=> (int)$left['total_segundos'];
            if($cmp !== 0){
                return $cmp;
            }
            return strcmp((string)$left['usuario'], (string)$right['usuario']);
        });

        return $summary;
    }

    private function buildDashboardMetrics($id_tablero){
        $columnas = $this->tableroModel->getColumnasActivasByTablero($id_tablero);
        $tarjetas = $this->tableroModel->getTarjetasActivasByTablero($id_tablero);

        $totalCardsCount = count($tarjetas);
        $totalTimeAllCards = 0;
        $totalPendingTasksAllCards = 0;
        foreach($tarjetas as $tarjetaBase){
            $totalTimeAllCards += isset($tarjetaBase->Tiempo_Total_Segundos) ? (int)$tarjetaBase->Tiempo_Total_Segundos : 0;
            $totalBaseTasks = isset($tarjetaBase->Total_Tareas) ? (int)$tarjetaBase->Total_Tareas : 0;
            $totalBaseCompleted = isset($tarjetaBase->Total_Tareas_Completadas) ? (int)$tarjetaBase->Total_Tareas_Completadas : 0;
            $totalPendingTasksAllCards += max(0, $totalBaseTasks - $totalBaseCompleted);
        }
        $avgTimeBaseline = $totalCardsCount > 0 ? (int)round($totalTimeAllCards / $totalCardsCount) : 0;
        $avgPendingTasksBaseline = $totalCardsCount > 0 ? ($totalPendingTasksAllCards / $totalCardsCount) : 0;

        $tarjetaIds = [];
        foreach($tarjetas as $tarjeta){
            $tarjetaIds[] = (int)$tarjeta->Id_tarjeta;
        }

        $taskAssignedUsersByTarjeta = [];
        foreach($this->tableroModel->getAsignadosByTarjetas($tarjetaIds) as $asignadoTarjeta){
            $idTarjeta = (int)$asignadoTarjeta->Id_tarjeta;
            if(!isset($taskAssignedUsersByTarjeta[$idTarjeta])){
                $taskAssignedUsersByTarjeta[$idTarjeta] = [];
            }
            $email = trim((string)($asignadoTarjeta->Usuario_email ?? ''));
            if($email !== '' && !in_array($email, $taskAssignedUsersByTarjeta[$idTarjeta], true)){
                $taskAssignedUsersByTarjeta[$idTarjeta][] = $email;
            }
        }

        $etiquetasPorTarjeta = [];
        foreach($this->tableroModel->getEtiquetasByTarjetas($tarjetaIds) as $etiquetaTarjeta){
            $idTarjeta = (int)$etiquetaTarjeta->Id_tarjeta;
            if(!isset($etiquetasPorTarjeta[$idTarjeta])){
                $etiquetasPorTarjeta[$idTarjeta] = [];
            }

            $nombreEtiqueta = trim((string)($etiquetaTarjeta->Nombre ?? ''));
            $etiquetasPorTarjeta[$idTarjeta][] = [
                'nombre' => $nombreEtiqueta !== '' ? $nombreEtiqueta : 'Sin texto',
                'color' => (string)($etiquetaTarjeta->Color ?? '#6c757d')
            ];
        }

        $columnStats = [];
        foreach($columnas as $columna){
            $columnStats[(int)$columna->Id_columna] = [
                'id_columna' => (int)$columna->Id_columna,
                'nombre' => (string)$columna->Nombre,
                'color' => (string)$columna->Color,
                'card_count' => 0,
                'time_seconds' => 0,
                'running_time_seconds' => 0,
                'running_cards' => 0,
                'total_tasks' => 0,
                'completed_tasks' => 0,
                'priority_points' => 0,
                'completed_column' => $this->isCompletionColumnName((string)$columna->Nombre)
            ];
        }

        $summary = [
            'total_cards' => 0,
            'cards_running' => 0,
            'total_time_seconds' => 0,
            'running_time_seconds' => 0,
            'avg_time_per_card_seconds' => 0,
            'total_tasks' => 0,
            'completed_tasks' => 0,
            'pending_tasks' => 0,
            'task_completion_percent' => 0,
            'completed_column_cards' => 0,
            'completed_column_percent' => 0,
            'total_priority_points' => 0,
            'avg_priority_points' => 0,
            'high_priority_cards' => 0,
            'active_columns' => count($columnStats),
            'bottleneck_red_cards' => 0,
            'bottleneck_yellow_cards' => 0,
            'bottleneck_green_cards' => 0,
            'bottleneck_index' => 0
        ];

        $priorityStats = [];
        $assignedStats = [];
        $cardRows = [];

        foreach($tarjetas as $tarjeta){
            $idTarjeta = (int)$tarjeta->Id_tarjeta;
            $idColumna = (int)$tarjeta->Id_columna;
            $totalTimeSeconds = isset($tarjeta->Tiempo_Total_Segundos) ? (int)$tarjeta->Tiempo_Total_Segundos : 0;
            $runningTimeSeconds = $this->tableroModel->getTiempoEnCursoTarjeta($idTarjeta);
            $hasRunning = !empty($tarjeta->Total_Timers_En_Curso) && (int)$tarjeta->Total_Timers_En_Curso > 0;
            $totalTasks = isset($tarjeta->Total_Tareas) ? (int)$tarjeta->Total_Tareas : 0;
            $completedTasks = isset($tarjeta->Total_Tareas_Completadas) ? (int)$tarjeta->Total_Tareas_Completadas : 0;
            $priorityValue = isset($tarjeta->Prioridad_Valor) ? (int)$tarjeta->Prioridad_Valor : 0;
            $priorityName = trim((string)($tarjeta->Prioridad_Nombre ?? 'Sin prioridad'));
            $priorityColor = (string)($tarjeta->Prioridad_Color ?? '#6c757d');
            $assignedName = !empty($tarjeta->Asignado_Email)
                ? (string)$tarjeta->Asignado_Email
                : null;
            if($assignedName === null && !empty($taskAssignedUsersByTarjeta[$idTarjeta])){
                $assignedName = $taskAssignedUsersByTarjeta[$idTarjeta][0];
            }
            if($assignedName === null){
                $assignedName = 'Sin asignar';
            }
            $columnName = isset($columnStats[$idColumna]) ? (string)$columnStats[$idColumna]['nombre'] : ('Columna #' . $idColumna);
            $columnColor = isset($columnStats[$idColumna]) ? (string)$columnStats[$idColumna]['color'] : '#6c757d';
            $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
            $pendingTasks = max(0, $totalTasks - $completedTasks);
            $labels = $etiquetasPorTarjeta[$idTarjeta] ?? [];
            $trafficLight = $this->buildCardTrafficLight([
                'total_time_seconds' => $totalTimeSeconds,
                'running_time_seconds' => $runningTimeSeconds,
                'has_running' => $hasRunning,
                'pending_tasks' => $pendingTasks,
                'completion_rate' => $completionRate,
                'priority_value' => $priorityValue
            ], [
                'avg_time_seconds' => $avgTimeBaseline,
                'avg_pending_tasks' => $avgPendingTasksBaseline
            ]);

            $summary['total_cards']++;
            $summary['total_time_seconds'] += $totalTimeSeconds;
            $summary['running_time_seconds'] += $runningTimeSeconds;
            $summary['total_tasks'] += $totalTasks;
            $summary['completed_tasks'] += $completedTasks;
            $summary['pending_tasks'] += $pendingTasks;
            $summary['total_priority_points'] += $priorityValue;

            if($hasRunning){
                $summary['cards_running']++;
            }
            if($priorityValue >= 8){
                $summary['high_priority_cards']++;
            }
            if($trafficLight['level'] === 'red'){
                $summary['bottleneck_red_cards']++;
            } elseif($trafficLight['level'] === 'yellow'){
                $summary['bottleneck_yellow_cards']++;
            } else {
                $summary['bottleneck_green_cards']++;
            }

            if(isset($columnStats[$idColumna])){
                $columnStats[$idColumna]['card_count']++;
                $columnStats[$idColumna]['time_seconds'] += $totalTimeSeconds;
                $columnStats[$idColumna]['running_time_seconds'] += $runningTimeSeconds;
                $columnStats[$idColumna]['total_tasks'] += $totalTasks;
                $columnStats[$idColumna]['completed_tasks'] += $completedTasks;
                $columnStats[$idColumna]['priority_points'] += $priorityValue;
                if($hasRunning){
                    $columnStats[$idColumna]['running_cards']++;
                }
                if(!empty($columnStats[$idColumna]['completed_column'])){
                    $summary['completed_column_cards']++;
                }
            }

            if(!isset($priorityStats[$priorityName])){
                $priorityStats[$priorityName] = [
                    'nombre' => $priorityName,
                    'valor' => $priorityValue,
                    'color' => $priorityColor,
                    'card_count' => 0,
                    'time_seconds' => 0,
                    'running_cards' => 0
                ];
            }
            $priorityStats[$priorityName]['card_count']++;
            $priorityStats[$priorityName]['time_seconds'] += $totalTimeSeconds;
            if($hasRunning){
                $priorityStats[$priorityName]['running_cards']++;
            }

            if(!isset($assignedStats[$assignedName])){
                $assignedStats[$assignedName] = [
                    'nombre' => $assignedName,
                    'card_count' => 0,
                    'time_seconds' => 0,
                    'running_cards' => 0,
                    'total_tasks' => 0,
                    'completed_tasks' => 0,
                    'priority_points' => 0
                ];
            }
            $assignedStats[$assignedName]['card_count']++;
            $assignedStats[$assignedName]['time_seconds'] += $totalTimeSeconds;
            $assignedStats[$assignedName]['total_tasks'] += $totalTasks;
            $assignedStats[$assignedName]['completed_tasks'] += $completedTasks;
            $assignedStats[$assignedName]['priority_points'] += $priorityValue;
            if($hasRunning){
                $assignedStats[$assignedName]['running_cards']++;
            }

            $cardRows[] = [
                'id_tarjeta' => $idTarjeta,
                'titulo' => trim((string)($tarjeta->Titulo ?? '')),
                'descripcion' => trim((string)($tarjeta->Descripcion ?? '')),
                'columna' => $columnName,
                'columna_color' => $columnColor,
                'prioridad' => $priorityName,
                'prioridad_color' => $priorityColor,
                'prioridad_valor' => $priorityValue,
                'asignado' => $assignedName,
                'total_time_seconds' => $totalTimeSeconds,
                'running_time_seconds' => $runningTimeSeconds,
                'has_running' => $hasRunning,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'completion_rate' => $completionRate,
                'labels' => $labels,
                'traffic_light' => $trafficLight
            ];
        }

        foreach($columnStats as &$columnStat){
            $columnStat['avg_time_per_card_seconds'] = $columnStat['card_count'] > 0
                ? (int)round($columnStat['time_seconds'] / $columnStat['card_count'])
                : 0;
            $columnStat['completion_percent'] = $columnStat['total_tasks'] > 0
                ? round(($columnStat['completed_tasks'] / $columnStat['total_tasks']) * 100, 1)
                : 0;
        }
        unset($columnStat);

        foreach($priorityStats as &$priorityStat){
            $priorityStat['avg_time_per_card_seconds'] = $priorityStat['card_count'] > 0
                ? (int)round($priorityStat['time_seconds'] / $priorityStat['card_count'])
                : 0;
        }
        unset($priorityStat);

        foreach($assignedStats as &$assignedStat){
            $assignedStat['avg_time_per_card_seconds'] = $assignedStat['card_count'] > 0
                ? (int)round($assignedStat['time_seconds'] / $assignedStat['card_count'])
                : 0;
            $assignedStat['completion_percent'] = $assignedStat['total_tasks'] > 0
                ? round(($assignedStat['completed_tasks'] / $assignedStat['total_tasks']) * 100, 1)
                : 0;
        }
        unset($assignedStat);

        if($summary['total_cards'] > 0){
            $summary['avg_time_per_card_seconds'] = (int)round($summary['total_time_seconds'] / $summary['total_cards']);
            $summary['avg_priority_points'] = round($summary['total_priority_points'] / $summary['total_cards'], 1);
            $summary['completed_column_percent'] = round(($summary['completed_column_cards'] / $summary['total_cards']) * 100, 1);
            $summary['bottleneck_index'] = round((($summary['bottleneck_red_cards'] + ($summary['bottleneck_yellow_cards'] * 0.5)) / $summary['total_cards']) * 100, 1);
        }

        if($summary['total_tasks'] > 0){
            $summary['task_completion_percent'] = round(($summary['completed_tasks'] / $summary['total_tasks']) * 100, 1);
        }

        $columnStats = array_values($columnStats);
        $priorityStats = array_values($priorityStats);
        $assignedStats = array_values($assignedStats);

        usort($columnStats, function($left, $right){
            return $right['time_seconds'] <=> $left['time_seconds'];
        });
        usort($priorityStats, function($left, $right){
            return $right['time_seconds'] <=> $left['time_seconds'];
        });
        usort($assignedStats, function($left, $right){
            return $right['time_seconds'] <=> $left['time_seconds'];
        });
        usort($cardRows, function($left, $right){
            return $right['total_time_seconds'] <=> $left['total_time_seconds'];
        });

        $bottleneckCards = $cardRows;
        usort($bottleneckCards, function($left, $right){
            $leftScore = (int)($left['traffic_light']['score'] ?? 0);
            $rightScore = (int)($right['traffic_light']['score'] ?? 0);
            if($rightScore === $leftScore){
                return $right['total_time_seconds'] <=> $left['total_time_seconds'];
            }
            return $rightScore <=> $leftScore;
        });

        $chartData = [
            'columns' => [
                'labels' => array_map(function($item){ return $item['nombre']; }, $columnStats),
                'time_seconds' => array_map(function($item){ return $item['time_seconds']; }, $columnStats),
                'card_counts' => array_map(function($item){ return $item['card_count']; }, $columnStats),
                'completion_percent' => array_map(function($item){ return $item['completion_percent']; }, $columnStats),
                'colors' => array_map(function($item){ return $item['color']; }, $columnStats)
            ],
            'priorities' => [
                'labels' => array_map(function($item){ return $item['nombre']; }, $priorityStats),
                'time_seconds' => array_map(function($item){ return $item['time_seconds']; }, $priorityStats),
                'card_counts' => array_map(function($item){ return $item['card_count']; }, $priorityStats),
                'colors' => array_map(function($item){ return $item['color']; }, $priorityStats)
            ],
            'assigned' => [
                'labels' => array_map(function($item){ return $item['nombre']; }, array_slice($assignedStats, 0, 8)),
                'time_seconds' => array_map(function($item){ return $item['time_seconds']; }, array_slice($assignedStats, 0, 8)),
                'card_counts' => array_map(function($item){ return $item['card_count']; }, array_slice($assignedStats, 0, 8)),
                'completion_percent' => array_map(function($item){ return $item['completion_percent']; }, array_slice($assignedStats, 0, 8))
            ]
        ];

        return [
            'summary' => $summary,
            'columns' => $columnStats,
            'priorities' => $priorityStats,
            'assigned' => $assignedStats,
            'cards' => $cardRows,
            'top_cards' => array_slice($cardRows, 0, 8),
            'bottleneck_cards' => array_slice($bottleneckCards, 0, 8),
            'critical_columns' => array_slice($columnStats, 0, 5),
            'chart_data' => $chartData
        ];
    }

    private function buildCardTrafficLight($card, $baseline){
        $score = 0;
        $reasons = [];

        $totalTime = (int)($card['total_time_seconds'] ?? 0);
        $runningTime = (int)($card['running_time_seconds'] ?? 0);
        $hasRunning = !empty($card['has_running']);
        $pendingTasks = (int)($card['pending_tasks'] ?? 0);
        $completionRate = (float)($card['completion_rate'] ?? 0);
        $priorityValue = (int)($card['priority_value'] ?? 0);
        $avgTime = (int)($baseline['avg_time_seconds'] ?? 0);
        $avgPending = (float)($baseline['avg_pending_tasks'] ?? 0);

        if($avgTime > 0){
            if($totalTime >= ($avgTime * 2)){
                $score += 40;
                $reasons[] = 'Tiempo muy por encima del promedio';
            } elseif($totalTime >= (int)round($avgTime * 1.4)){
                $score += 25;
                $reasons[] = 'Tiempo por encima del promedio';
            }
        } elseif($totalTime >= 7200){
            $score += 20;
            $reasons[] = 'Acumulacion alta de tiempo';
        }

        if($hasRunning && $runningTime >= 3600){
            $score += 15;
            $reasons[] = 'Cronometro activo de larga duracion';
        }

        if($avgPending > 0){
            if($pendingTasks >= (int)ceil($avgPending * 2)){
                $score += 25;
                $reasons[] = 'Pendientes muy por encima del promedio';
            } elseif($pendingTasks >= (int)ceil($avgPending * 1.25) && $pendingTasks >= 2){
                $score += 15;
                $reasons[] = 'Pendientes por encima del promedio';
            }
        } elseif($pendingTasks >= 3){
            $score += 15;
            $reasons[] = 'Varias tareas pendientes';
        }

        if($completionRate < 40 && $pendingTasks > 0){
            $score += 15;
            $reasons[] = 'Bajo avance de tareas';
        } elseif($completionRate < 65 && $pendingTasks > 0){
            $score += 8;
            $reasons[] = 'Avance parcial de tareas';
        }

        if($priorityValue >= 10){
            $score += 20;
            $reasons[] = 'Prioridad critica';
        } elseif($priorityValue >= 8){
            $score += 12;
            $reasons[] = 'Prioridad alta';
        } elseif($priorityValue >= 5){
            $score += 6;
            $reasons[] = 'Prioridad media';
        }

        if($pendingTasks === 0 && $completionRate >= 100 && !$hasRunning){
            $score = max(0, $score - 25);
        }

        $level = 'green';
        $label = 'Verde';
        $color = '#2f9e44';
        if($score >= 60){
            $level = 'red';
            $label = 'Rojo';
            $color = '#d62828';
        } elseif($score >= 32){
            $level = 'yellow';
            $label = 'Amarillo';
            $color = '#f08c00';
        }

        return [
            'level' => $level,
            'label' => $label,
            'color' => $color,
            'score' => $score,
            'reason' => empty($reasons) ? 'Flujo sin alertas relevantes' : implode(' | ', array_slice($reasons, 0, 2))
        ];
    }

    private function buildCalendarEvents($id_tablero){
        $columnas = $this->tableroModel->getColumnasActivasByTablero($id_tablero);
        $columnasMap = [];
        foreach($columnas as $columna){
            $columnasMap[(int)$columna->Id_columna] = [
                'nombre' => (string)($columna->Nombre ?? 'Columna'),
                'color' => (string)($columna->Color ?? '#6c757d')
            ];
        }

        $tarjetas = $this->tableroModel->getTarjetasActivasByTablero($id_tablero);
        $events = [];

        foreach($tarjetas as $tarjeta){
            $fechaInicio = $this->sanitizeOptionalDateInput($tarjeta->Fecha_inicio ?? null, true);
            $fechaFin = $this->sanitizeOptionalDateInput($tarjeta->Fecha_fin ?? null, true);

            if($fechaInicio === null && $fechaFin === null){
                continue;
            }

            $startDate = $fechaInicio ?? $fechaFin;
            $endDate = $fechaFin ?? $fechaInicio;

            if($startDate !== null && $endDate !== null && strcmp($endDate, $startDate) < 0){
                $tmp = $startDate;
                $startDate = $endDate;
                $endDate = $tmp;
            }

            $eventType = 'single';
            if($fechaInicio !== null && $fechaFin !== null){
                $eventType = 'range';
            } elseif($fechaInicio !== null){
                $eventType = 'start';
            } elseif($fechaFin !== null){
                $eventType = 'end';
            }

            $idColumna = (int)($tarjeta->Id_columna ?? 0);
            $columnaNombre = isset($columnasMap[$idColumna]) ? $columnasMap[$idColumna]['nombre'] : ('Columna #' . $idColumna);
            $columnaColor = isset($columnasMap[$idColumna]) ? $columnasMap[$idColumna]['color'] : '#6c757d';

            $events[] = [
                'id_tarjeta' => (int)$tarjeta->Id_tarjeta,
                'id_tablero' => (int)$id_tablero,
                'titulo' => trim((string)($tarjeta->Titulo ?? 'Sin titulo')),
                'descripcion' => trim((string)($tarjeta->Descripcion ?? '')),
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'event_type' => $eventType,
                'columna' => $columnaNombre,
                'columna_color' => $columnaColor,
                'prioridad' => trim((string)($tarjeta->Prioridad_Nombre ?? 'Sin prioridad')),
                'prioridad_valor' => isset($tarjeta->Prioridad_Valor) ? (int)$tarjeta->Prioridad_Valor : 0,
                'prioridad_color' => (string)($tarjeta->Prioridad_Color ?? '#6c757d'),
                'asignado' => !empty($tarjeta->Asignado_Email) ? (string)$tarjeta->Asignado_Email : 'Sin asignar',
                'actividad' => trim((string)($tarjeta->Actividad_Descripcion ?? ''))
            ];
        }

        usort($events, function($left, $right){
            $leftStart = (string)($left['start_date'] ?? '');
            $rightStart = (string)($right['start_date'] ?? '');
            if($leftStart !== $rightStart){
                return strcmp($leftStart, $rightStart);
            }

            $leftPriority = (int)($left['prioridad_valor'] ?? 0);
            $rightPriority = (int)($right['prioridad_valor'] ?? 0);
            if($rightPriority !== $leftPriority){
                return $rightPriority <=> $leftPriority;
            }

            return strcmp((string)($left['titulo'] ?? ''), (string)($right['titulo'] ?? ''));
        });

        return $events;
    }

    private function flattenReporteAgrupado($reporteAgrupado){
        $rows = [];
        foreach($reporteAgrupado as $asignado => $items){
            foreach($items as $row){
                $tiempoUsuarioValues = [];
                foreach((array)($row['tiempo_por_usuario'] ?? []) as $tiempoUsuarioText){
                    $parts = explode(': ', (string)$tiempoUsuarioText);
                    $tiempoUsuarioValues[] = trim((string)array_pop($parts));
                }

                $rows[] = [
                    'Asignado' => $asignado,
                    'Descripcion' => (string)($row['descripcion'] ?? ''),
                    'Descripcion detalle' => (string)($row['descripcion_detalle'] ?? ''),
                    'Etapa' => (string)($row['etapa'] ?? ''),
                    'Prioridad' => (string)($row['prioridad'] ?? ''),
                    'Etiquetas' => implode(' | ', (array)($row['etiquetas'] ?? [])),
                    'Listado tareas' => implode(' | ', (array)($row['listas_tareas'] ?? [])),
                    'Tareas' => implode(' | ', (array)($row['tareas'] ?? [])),
                    'Tiempo detalle' => implode(' | ', (array)($row['tiempos'] ?? [])),
                    'Tiempo total' => $this->formatSegundosReporte((int)($row['total_tiempo_segundos'] ?? 0)),
                    'Tiempo en curso' => $this->formatSegundosReporte((int)($row['total_tiempo_en_curso_segundos'] ?? 0))
                ];
            }
        }

        return $rows;
    }

    private function getReporteriaExportHeaders(){
        return [
            'Asignado',
            'Descripcion',
            'Descripcion detalle',
            'Etapa',
            'Prioridad',
            'Etiquetas',
            'Listado tareas',
            'Tareas',
            'Tiempo detalle',
            'Tiempo total',
            'Tiempo en curso'
        ];
    }

    private function downloadReporteriaCsv($filename, $headers, $rows){
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        if($output === false){
            exit;
        }

        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers);
        foreach($rows as $row){
            $line = [];
            foreach($headers as $header){
                $line[] = $row[$header] ?? '';
            }
            fputcsv($output, $line);
        }

        fclose($output);
        exit;
    }

    private function buildReporteriaFilenameBase($tableroActual){
        $nombre = $tableroActual ? (string)($tableroActual->Nombre ?? 'tablero') : 'tablero';
        $nombre = preg_replace('/[^A-Za-z0-9]+/', '_', trim($nombre));
        $nombre = trim((string)$nombre, '_');
        if($nombre === ''){
            $nombre = 'tablero';
        }

        return 'Reporte_Tablero_' . $nombre . '_' . date('Ymd_His');
    }

    private function getEmptyBoardPermissions(){
        return [
            'tablero_ver' => false,
            'tablero_crear' => false,
            'tablero_editar' => false,
            'tablero_eliminar' => false,
            'tablero_asignar' => false,
            'columna_crear' => false,
            'columna_editar' => false,
            'columna_eliminar' => false,
            'columna_ordenar' => false,
            'tarjeta_ver' => false,
            'tarjeta_crear' => false,
            'tarjeta_editar' => false,
            'tarjeta_mover' => false,
            'tarjeta_eliminar' => false,
            'tarjeta_asignar' => false,
            'lista_crear' => false,
            'lista_editar' => false,
            'lista_eliminar' => false,
            'tarea_crear' => false,
            'tarea_editar' => false,
            'tarea_eliminar' => false,
            'tarea_tiempo_editar' => false,
            'plantilla_tarjeta_crear' => false,
            'plantilla_tarjeta_editar' => false,
            'plantilla_tarjeta_eliminar' => false,
            'plantilla_tarjeta_asociar' => false,
            'plantilla_lista_crear' => false,
            'plantilla_lista_editar' => false,
            'plantilla_lista_eliminar' => false
        ];
    }

    private function buildBoardPermissionsArray($permObj){
        if(!$permObj){
            return $this->getEmptyBoardPermissions();
        }

        $tableroVer = (int)($permObj->Permiso_tablero_ver ?? $permObj->Permiso_ver ?? 0) === 1;
        $tableroCrear = (int)($permObj->Permiso_tablero_crear ?? $permObj->Permiso_crear ?? 0) === 1;
        $tableroEditar = (int)($permObj->Permiso_tablero_editar ?? $permObj->Permiso_editar ?? 0) === 1;
        $tableroEliminar = (int)($permObj->Permiso_tablero_eliminar ?? $permObj->Permiso_eliminar ?? 0) === 1;
        $tableroAsignar = (int)($permObj->Permiso_tablero_asignar ?? $permObj->Permiso_editar ?? 0) === 1;
        $columnaCrear = (int)($permObj->Permiso_columna_crear ?? $permObj->Permiso_tablero_editar ?? $permObj->Permiso_editar ?? 0) === 1;
        $columnaEditar = (int)($permObj->Permiso_columna_editar ?? $permObj->Permiso_tablero_editar ?? $permObj->Permiso_editar ?? 0) === 1;
        $columnaEliminar = (int)($permObj->Permiso_columna_eliminar ?? $permObj->Permiso_tablero_eliminar ?? $permObj->Permiso_eliminar ?? 0) === 1;
        $columnaOrdenar = (int)($permObj->Permiso_columna_ordenar ?? $permObj->Permiso_tablero_editar ?? $permObj->Permiso_editar ?? 0) === 1;

        return [
            'tablero_ver' => $tableroVer,
            'tablero_crear' => $tableroCrear,
            'tablero_editar' => $tableroEditar,
            'tablero_eliminar' => $tableroEliminar,
            'tablero_asignar' => $tableroAsignar,
            'columna_crear' => $columnaCrear,
            'columna_editar' => $columnaEditar,
            'columna_eliminar' => $columnaEliminar,
            'columna_ordenar' => $columnaOrdenar,
            'tarjeta_ver' => (int)($permObj->Permiso_tarjeta_ver ?? $permObj->Permiso_ver ?? 0) === 1,
            'tarjeta_crear' => (int)($permObj->Permiso_tarjeta_crear ?? $permObj->Permiso_crear ?? 0) === 1,
            'tarjeta_editar' => (int)($permObj->Permiso_tarjeta_editar ?? $permObj->Permiso_editar ?? 0) === 1,
            'tarjeta_mover' => (int)($permObj->Permiso_tarjeta_mover ?? $permObj->Permiso_tarjeta_editar ?? $permObj->Permiso_editar ?? 0) === 1,
            'tarjeta_eliminar' => (int)($permObj->Permiso_tarjeta_eliminar ?? $permObj->Permiso_eliminar ?? 0) === 1,
            'tarjeta_asignar' => (int)($permObj->Permiso_tarjeta_asignar ?? $permObj->Permiso_editar ?? 0) === 1,
            'lista_crear' => (int)($permObj->Permiso_lista_crear ?? $permObj->Permiso_editar ?? 0) === 1,
            'lista_editar' => (int)($permObj->Permiso_lista_editar ?? $permObj->Permiso_editar ?? 0) === 1,
            'lista_eliminar' => (int)($permObj->Permiso_lista_eliminar ?? $permObj->Permiso_editar ?? 0) === 1,
            'tarea_crear' => (int)($permObj->Permiso_tarea_crear ?? $permObj->Permiso_editar ?? 0) === 1,
            'tarea_editar' => (int)($permObj->Permiso_tarea_editar ?? $permObj->Permiso_editar ?? 0) === 1,
            'tarea_eliminar' => (int)($permObj->Permiso_tarea_eliminar ?? $permObj->Permiso_editar ?? 0) === 1,
            'tarea_tiempo_editar' => (int)($permObj->Permiso_tarea_tiempo_editar ?? 0) === 1,
            'plantilla_tarjeta_crear' => (int)($permObj->Permiso_plantilla_tarjeta_crear ?? 0) === 1,
            'plantilla_tarjeta_editar' => (int)($permObj->Permiso_plantilla_tarjeta_editar ?? 0) === 1,
            'plantilla_tarjeta_eliminar' => (int)($permObj->Permiso_plantilla_tarjeta_eliminar ?? 0) === 1,
            'plantilla_tarjeta_asociar' => (int)($permObj->Permiso_plantilla_tarjeta_asociar ?? 0) === 1,
            'plantilla_lista_crear' => (int)($permObj->Permiso_plantilla_lista_crear ?? 0) === 1,
            'plantilla_lista_editar' => (int)($permObj->Permiso_plantilla_lista_editar ?? 0) === 1,
            'plantilla_lista_eliminar' => (int)($permObj->Permiso_plantilla_lista_eliminar ?? 0) === 1
        ];
    }

    private function getEmptyDashboardMetrics(){
        return [
            'summary' => [
                'total_cards' => 0,
                'cards_running' => 0,
                'total_time_seconds' => 0,
                'running_time_seconds' => 0,
                'avg_time_per_card_seconds' => 0,
                'total_tasks' => 0,
                'completed_tasks' => 0,
                'pending_tasks' => 0,
                'task_completion_percent' => 0,
                'completed_column_cards' => 0,
                'completed_column_percent' => 0,
                'total_priority_points' => 0,
                'avg_priority_points' => 0,
                'high_priority_cards' => 0,
                'active_columns' => 0
            ],
            'columns' => [],
            'priorities' => [],
            'assigned' => [],
            'cards' => [],
            'top_cards' => [],
            'bottleneck_cards' => [],
            'critical_columns' => [],
            'chart_data' => [
                'columns' => ['labels' => [], 'time_seconds' => [], 'card_counts' => [], 'completion_percent' => [], 'colors' => []],
                'priorities' => ['labels' => [], 'time_seconds' => [], 'card_counts' => [], 'colors' => []],
                'assigned' => ['labels' => [], 'time_seconds' => [], 'card_counts' => [], 'completion_percent' => []]
            ]
        ];
    }

    private function isCompletionColumnName($name){
        $name = strtolower(trim((string)$name));
        if($name === ''){
            return false;
        }

        $keywords = ['complet', 'finaliz', 'terminad', 'cerrad', 'done'];
        foreach($keywords as $keyword){
            if(strpos($name, $keyword) !== false){
                return true;
            }
        }

        return false;
    }

    public function create_tablero(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if($nombre === ''){
            flashMessage('tablero_error', 'El nombre del tablero es obligatorio.', 'danger');
            redirect('tablero/index');
        }

        $id_usuario = (int)$_SESSION['user_id'];
        $id_tablero = $this->tableroModel->addTablero([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'id_usuario_responsable' => $id_usuario
        ]);

        if(!$id_tablero){
            flashMessage('tablero_error', 'No se pudo crear el tablero.', 'danger');
            redirect('tablero/index');
        }

        $this->tableroModel->addOrUpdateUsuarioPermisoTablero([
            'id_tablero' => $id_tablero,
            'id_usuario' => $id_usuario,
            'permiso_ver' => 1,
            'permiso_crear' => 1,
            'permiso_editar' => 1,
            'permiso_eliminar' => 1,
            'permiso_tablero_ver' => 1,
            'permiso_tablero_crear' => 1,
            'permiso_tablero_editar' => 1,
            'permiso_tablero_eliminar' => 1,
            'permiso_tablero_asignar' => 1,
            'permiso_columna_crear' => 1,
            'permiso_columna_editar' => 1,
            'permiso_columna_eliminar' => 1,
            'permiso_columna_ordenar' => 1,
            'permiso_tarjeta_ver' => 1,
            'permiso_tarjeta_crear' => 1,
            'permiso_tarjeta_editar' => 1,
            'permiso_tarjeta_mover' => 1,
            'permiso_tarjeta_eliminar' => 1,
            'permiso_tarjeta_asignar' => 1,
            'permiso_lista_crear' => 1,
            'permiso_lista_editar' => 1,
            'permiso_lista_eliminar' => 1,
            'permiso_tarea_crear' => 1,
            'permiso_tarea_editar' => 1,
            'permiso_tarea_eliminar' => 1,
            'permiso_tarea_tiempo_editar' => 1,
            'permiso_plantilla_tarjeta_crear' => 1,
            'permiso_plantilla_tarjeta_editar' => 1,
            'permiso_plantilla_tarjeta_eliminar' => 1,
            'permiso_plantilla_tarjeta_asociar' => 1,
            'permiso_plantilla_lista_crear' => 1,
            'permiso_plantilla_lista_editar' => 1,
            'permiso_plantilla_lista_eliminar' => 1
        ]);

        $defaultColumns = [
            ['Pendiente', '#6c757d', 1],
            ['En Progreso', '#0d6efd', 2],
            ['En Revision', '#fd7e14', 3],
            ['Completada', '#198754', 4]
        ];

        foreach($defaultColumns as $col){
            $this->tableroModel->addColumna([
                'id_tablero' => $id_tablero,
                'nombre' => $col[0],
                'color' => $col[1],
                'orden_columna' => $col[2]
            ]);
        }

        foreach($this->getDefaultBoardLabels() as $etiqueta){
            $this->tableroModel->addEtiqueta([
                'id_tablero' => $id_tablero,
                'nombre' => $etiqueta['nombre'],
                'color' => $etiqueta['color']
            ]);
        }

        foreach($this->getDefaultBoardPriorities() as $prioridad){
            $this->tableroModel->addPrioridad([
                'id_tablero' => $id_tablero,
                'nombre' => $prioridad['nombre'],
                'valor' => $prioridad['valor'],
                'color' => $prioridad['color']
            ]);
        }

        flashMessage('tablero_message', 'Tablero creado correctamente.', 'success');
        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function update_tablero($id = null){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_numeric($id)){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_tablero = (int)$id;
        $id_tablero_post = (int)($_POST['id_tablero'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if($id_tablero <= 0 || $id_tablero_post !== $id_tablero){
            flashMessage('tablero_error', 'Solicitud invalida para editar tablero.', 'danger');
            redirect('tablero/index');
        }

        if(!$this->hasBoardPermission($id_tablero, 'tablero_editar')){
            flashMessage('tablero_error', 'No tiene permisos para editar este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($nombre === ''){
            flashMessage('tablero_error', 'El nombre del tablero es obligatorio.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $tablero = $this->tableroModel->getTableroById($id_tablero);
        if(!$tablero){
            flashMessage('tablero_error', 'El tablero no existe o ya no esta activo.', 'danger');
            redirect('tablero/index');
        }

        if($this->tableroModel->updateTablero($id_tablero, $nombre, $descripcion)){
            flashMessage('tablero_message', 'Tablero actualizado correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo actualizar el tablero.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function delete_tablero($id = null){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_numeric($id)){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_tablero = (int)$id;
        $id_tablero_post = (int)($_POST['id_tablero'] ?? 0);

        if($id_tablero <= 0 || $id_tablero_post !== $id_tablero){
            flashMessage('tablero_error', 'Solicitud invalida para eliminar tablero.', 'danger');
            redirect('tablero/index');
        }

        if(!$this->hasBoardPermission($id_tablero, 'tablero_eliminar')){
            flashMessage('tablero_error', 'No tiene permisos para eliminar este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $tablero = $this->tableroModel->getTableroById($id_tablero);
        if(!$tablero){
            flashMessage('tablero_error', 'El tablero no existe o ya no esta activo.', 'danger');
            redirect('tablero/index');
        }

        $summary = $this->tableroModel->getTableroDeletionSummary($id_tablero);
        if(!$this->tableroModel->canDeleteTablero($id_tablero)){
            flashMessage(
                'tablero_error',
                'No se puede eliminar el tablero. Debe estar vacio (Columnas: ' . (int)$summary->total_columnas .
                ', Tarjetas: ' . (int)$summary->total_tarjetas .
                ', Listas: ' . (int)$summary->total_listas .
                ', Tareas: ' . (int)$summary->total_tareas . ').',
                'danger'
            );
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($this->tableroModel->deleteTablero($id_tablero)){
            flashMessage('tablero_message', 'Tablero eliminado correctamente.', 'success');
            redirect('tablero/index');
        }

        flashMessage('tablero_error', 'No se pudo eliminar el tablero.', 'danger');
        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function assign_usuario_tablero(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_tablero = (int)($_POST['id_tablero'] ?? 0);
        $id_usuario = (int)($_POST['id_usuario'] ?? 0);
        $permiso_tablero_ver = !empty($_POST['permiso_tablero_ver']) ? 1 : 0;
        $permiso_tablero_crear = !empty($_POST['permiso_tablero_crear']) ? 1 : 0;
        $permiso_tablero_editar = !empty($_POST['permiso_tablero_editar']) ? 1 : 0;
        $permiso_tablero_eliminar = !empty($_POST['permiso_tablero_eliminar']) ? 1 : 0;
        $permiso_tablero_asignar = !empty($_POST['permiso_tablero_asignar']) ? 1 : 0;
        $permiso_columna_crear = !empty($_POST['permiso_columna_crear']) ? 1 : 0;
        $permiso_columna_editar = !empty($_POST['permiso_columna_editar']) ? 1 : 0;
        $permiso_columna_eliminar = !empty($_POST['permiso_columna_eliminar']) ? 1 : 0;
        $permiso_columna_ordenar = !empty($_POST['permiso_columna_ordenar']) ? 1 : 0;

        $permiso_tarjeta_ver = !empty($_POST['permiso_tarjeta_ver']) ? 1 : 0;
        $permiso_tarjeta_crear = !empty($_POST['permiso_tarjeta_crear']) ? 1 : 0;
        $permiso_tarjeta_editar = !empty($_POST['permiso_tarjeta_editar']) ? 1 : 0;
        $permiso_tarjeta_mover = !empty($_POST['permiso_tarjeta_mover']) ? 1 : 0;
        $permiso_tarjeta_eliminar = !empty($_POST['permiso_tarjeta_eliminar']) ? 1 : 0;
        $permiso_tarjeta_asignar = !empty($_POST['permiso_tarjeta_asignar']) ? 1 : 0;

        $permiso_lista_crear = !empty($_POST['permiso_lista_crear']) ? 1 : 0;
        $permiso_lista_editar = !empty($_POST['permiso_lista_editar']) ? 1 : 0;
        $permiso_lista_eliminar = !empty($_POST['permiso_lista_eliminar']) ? 1 : 0;

        $permiso_tarea_crear = !empty($_POST['permiso_tarea_crear']) ? 1 : 0;
        $permiso_tarea_editar = !empty($_POST['permiso_tarea_editar']) ? 1 : 0;
        $permiso_tarea_eliminar = !empty($_POST['permiso_tarea_eliminar']) ? 1 : 0;
        $permiso_tarea_tiempo_editar = !empty($_POST['permiso_tarea_tiempo_editar']) ? 1 : 0;

        $permiso_plantilla_tarjeta_crear    = !empty($_POST['permiso_plantilla_tarjeta_crear']) ? 1 : 0;
        $permiso_plantilla_tarjeta_editar   = !empty($_POST['permiso_plantilla_tarjeta_editar']) ? 1 : 0;
        $permiso_plantilla_tarjeta_eliminar = !empty($_POST['permiso_plantilla_tarjeta_eliminar']) ? 1 : 0;
        $permiso_plantilla_tarjeta_asociar  = !empty($_POST['permiso_plantilla_tarjeta_asociar']) ? 1 : 0;
        $permiso_plantilla_lista_crear      = !empty($_POST['permiso_plantilla_lista_crear']) ? 1 : 0;
        $permiso_plantilla_lista_editar     = !empty($_POST['permiso_plantilla_lista_editar']) ? 1 : 0;
        $permiso_plantilla_lista_eliminar   = !empty($_POST['permiso_plantilla_lista_eliminar']) ? 1 : 0;

        if($id_tablero <= 0 || $id_usuario <= 0){
            flashMessage('tablero_error', 'Datos invalidos para asignacion.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if(!$this->hasBoardPermission($id_tablero, 'tablero_asignar')){
            flashMessage('tablero_error', 'No tiene permisos para gestionar usuarios en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $ok = $this->tableroModel->addOrUpdateUsuarioPermisoTablero([
            'id_tablero' => $id_tablero,
            'id_usuario' => $id_usuario,
            'permiso_ver' => $permiso_tablero_ver,
            'permiso_crear' => $permiso_tarjeta_crear,
            'permiso_editar' => $permiso_tarjeta_editar,
            'permiso_eliminar' => $permiso_tarjeta_eliminar,
            'permiso_tablero_ver' => $permiso_tablero_ver,
            'permiso_tablero_crear' => $permiso_tablero_crear,
            'permiso_tablero_editar' => $permiso_tablero_editar,
            'permiso_tablero_eliminar' => $permiso_tablero_eliminar,
            'permiso_tablero_asignar' => $permiso_tablero_asignar,
            'permiso_columna_crear' => $permiso_columna_crear,
            'permiso_columna_editar' => $permiso_columna_editar,
            'permiso_columna_eliminar' => $permiso_columna_eliminar,
            'permiso_columna_ordenar' => $permiso_columna_ordenar,
            'permiso_tarjeta_ver' => $permiso_tarjeta_ver,
            'permiso_tarjeta_crear' => $permiso_tarjeta_crear,
            'permiso_tarjeta_editar' => $permiso_tarjeta_editar,
            'permiso_tarjeta_mover' => $permiso_tarjeta_mover,
            'permiso_tarjeta_eliminar' => $permiso_tarjeta_eliminar,
            'permiso_tarjeta_asignar' => $permiso_tarjeta_asignar,
            'permiso_lista_crear' => $permiso_lista_crear,
            'permiso_lista_editar' => $permiso_lista_editar,
            'permiso_lista_eliminar' => $permiso_lista_eliminar,
            'permiso_tarea_crear' => $permiso_tarea_crear,
            'permiso_tarea_editar' => $permiso_tarea_editar,
            'permiso_tarea_eliminar' => $permiso_tarea_eliminar,
            'permiso_tarea_tiempo_editar' => $permiso_tarea_tiempo_editar,
            'permiso_plantilla_tarjeta_crear' => $permiso_plantilla_tarjeta_crear,
            'permiso_plantilla_tarjeta_editar' => $permiso_plantilla_tarjeta_editar,
            'permiso_plantilla_tarjeta_eliminar' => $permiso_plantilla_tarjeta_eliminar,
            'permiso_plantilla_tarjeta_asociar' => $permiso_plantilla_tarjeta_asociar,
            'permiso_plantilla_lista_crear' => $permiso_plantilla_lista_crear,
            'permiso_plantilla_lista_editar' => $permiso_plantilla_lista_editar,
            'permiso_plantilla_lista_eliminar' => $permiso_plantilla_lista_eliminar
        ]);

        if($ok){
            flashMessage('tablero_message', 'Permisos de usuario actualizados en el tablero.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo actualizar permisos del usuario.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function get_usuario_permiso_tablero(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'GET'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $id_tablero = isset($_GET['id_tablero']) ? (int)$_GET['id_tablero'] : 0;
        $id_usuario = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : 0;

        if($id_tablero <= 0 || $id_usuario <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Parametros invalidos'], 400);
        }

        if(!$this->hasBoardPermission($id_tablero, 'tablero_asignar')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        $permObj = $this->tableroModel->getPermisosUsuarioTablero($id_tablero, $id_usuario);
        if(!$permObj){
            return $this->jsonResponse([
                'success' => true,
                'assigned' => false,
                'permisos' => $this->getEmptyBoardPermissions()
            ]);
        }

        return $this->jsonResponse([
            'success' => true,
            'assigned' => true,
            'permisos' => $this->buildBoardPermissionsArray($permObj)
        ]);
    }

    public function get_tablero_sync_status(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'GET'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $id_tablero = isset($_GET['id_tablero']) ? (int)$_GET['id_tablero'] : 0;
        $since_historial = isset($_GET['since_historial']) ? (int)$_GET['since_historial'] : 0;
        if($since_historial < 0){
            $since_historial = 0;
        }

        if($id_tablero <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Parametros invalidos'], 400);
        }

        if(!$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        $latestHistorialId = (int)$this->tableroModel->getLatestHistorialIdByTablero($id_tablero);
        $hasChanges = false;
        if($since_historial > 0 && $latestHistorialId > $since_historial){
            $hasChanges = $this->tableroModel->hasHistorialChangesByOtherUser(
                $id_tablero,
                $since_historial,
                (int)($_SESSION['user_id'] ?? 0)
            );
        }

        return $this->jsonResponse([
            'success' => true,
            'latest_historial_id' => $latestHistorialId,
            'has_changes' => (bool)$hasChanges
        ]);
    }

    public function create_columna(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_tablero = (int)($_POST['id_tablero'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $color = trim($_POST['color'] ?? '#0d6efd');

        if(!$this->hasBoardPermission($id_tablero, 'columna_crear')){
            flashMessage('tablero_error', 'No tiene permisos para crear columnas en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if(empty($nombre)){
            flashMessage('tablero_error', 'El nombre de la columna es obligatorio.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $data = [
            'id_tablero' => $id_tablero,
            'nombre' => $nombre,
            'color' => $color,
            'orden_columna' => $this->tableroModel->getSiguienteOrdenColumna($id_tablero)
        ];

        if($this->tableroModel->addColumna($data)){
            flashMessage('tablero_message', 'Columna creada correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo crear la columna.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function create_etiqueta(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_tablero = (int)($_POST['id_tablero'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $color = $this->sanitizeColor($_POST['color'] ?? '#0d6efd');

        if(!$this->hasBoardPermission($id_tablero, 'tablero_editar')){
            flashMessage('tablero_error', 'No tiene permisos para gestionar etiquetas en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $ok = $this->tableroModel->addEtiqueta([
            'id_tablero' => $id_tablero,
            'nombre' => $nombre,
            'color' => $color
        ]);

        if($ok){
            flashMessage('tablero_message', 'Etiqueta creada correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo crear la etiqueta.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function update_etiqueta($id = null){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_numeric($id)){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_etiqueta = (int)$id;
        $id_tablero = (int)($_POST['id_tablero'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $color = $this->sanitizeColor($_POST['color'] ?? '#0d6efd');

        if(!$this->hasBoardPermission($id_tablero, 'tablero_editar')){
            flashMessage('tablero_error', 'No tiene permisos para actualizar etiquetas en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $etiqueta = $this->tableroModel->getEtiquetaById($id_etiqueta);
        if(!$etiqueta || (int)$etiqueta->Id_tablero !== $id_tablero){
            flashMessage('tablero_error', 'La etiqueta no pertenece al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $ok = $this->tableroModel->updateEtiqueta($id_etiqueta, $nombre, $color);
        if($ok){
            flashMessage('tablero_message', 'Etiqueta actualizada correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo actualizar la etiqueta.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function delete_etiqueta($id = null){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_numeric($id)){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_etiqueta = (int)$id;
        $id_tablero = (int)($_POST['id_tablero'] ?? 0);

        if(!$this->hasBoardPermission($id_tablero, 'tablero_editar')){
            flashMessage('tablero_error', 'No tiene permisos para eliminar etiquetas en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $etiqueta = $this->tableroModel->getEtiquetaById($id_etiqueta);
        if(!$etiqueta || (int)$etiqueta->Id_tablero !== $id_tablero){
            flashMessage('tablero_error', 'La etiqueta no pertenece al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($this->tableroModel->countTarjetasActivasByEtiqueta($id_etiqueta) > 0){
            flashMessage('tablero_error', 'No se puede eliminar una etiqueta que ya esta asignada a tarjetas activas.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $ok = $this->tableroModel->deleteEtiqueta($id_etiqueta);
        if($ok){
            flashMessage('tablero_message', 'Etiqueta eliminada correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo eliminar la etiqueta.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function create_prioridad(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_tablero = (int)($_POST['id_tablero'] ?? 0);
        $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
        $valor = (int)($_POST['valor'] ?? 0);
        $color = $this->sanitizeColor($_POST['color'] ?? '#6c757d', '#6c757d');

        if(!$this->hasBoardPermission($id_tablero, 'tablero_editar')){
            flashMessage('tablero_error', 'No tiene permisos para gestionar prioridades en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($nombre === '' || $valor <= 0){
            flashMessage('tablero_error', 'Debe indicar nombre y valor valido para la prioridad.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $ok = $this->tableroModel->addPrioridad([
            'id_tablero' => $id_tablero,
            'nombre' => $nombre,
            'valor' => $valor,
            'color' => $color
        ]);

        if($ok){
            flashMessage('tablero_message', 'Prioridad creada correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo crear la prioridad. Ya existe una prioridad activa con ese nombre en este tablero.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function update_prioridad($id = null){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_numeric($id)){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_prioridad = (int)$id;
        $id_tablero = (int)($_POST['id_tablero'] ?? 0);
        $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
        $valor = (int)($_POST['valor'] ?? 0);
        $color = $this->sanitizeColor($_POST['color'] ?? '#6c757d', '#6c757d');

        if(!$this->hasBoardPermission($id_tablero, 'tablero_editar')){
            flashMessage('tablero_error', 'No tiene permisos para actualizar prioridades en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($nombre === '' || $valor <= 0){
            flashMessage('tablero_error', 'Debe indicar nombre y valor valido para la prioridad.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $prioridad = $this->tableroModel->getPrioridadById($id_prioridad);
        if(!$prioridad || (int)$prioridad->Id_tablero !== $id_tablero){
            flashMessage('tablero_error', 'La prioridad no pertenece al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $existing = $this->tableroModel->getPrioridadByNombre($id_tablero, $nombre, $id_prioridad, true);
        if($existing){
            flashMessage('tablero_error', 'Ya existe otra prioridad con ese nombre en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $ok = $this->tableroModel->updatePrioridad($id_prioridad, $nombre, $valor, $color);
        if($ok){
            flashMessage('tablero_message', 'Prioridad actualizada correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo actualizar la prioridad. Verifique que el nombre no exista en este tablero.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function delete_prioridad($id = null){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_numeric($id)){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_prioridad = (int)$id;
        $id_tablero = (int)($_POST['id_tablero'] ?? 0);

        if(!$this->hasBoardPermission($id_tablero, 'tablero_editar')){
            flashMessage('tablero_error', 'No tiene permisos para eliminar prioridades en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $prioridad = $this->tableroModel->getPrioridadById($id_prioridad);
        if(!$prioridad || (int)$prioridad->Id_tablero !== $id_tablero){
            flashMessage('tablero_error', 'La prioridad no pertenece al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($this->tableroModel->countTarjetasActivasByPrioridad($id_prioridad) > 0){
            flashMessage('tablero_error', 'No se puede eliminar una prioridad asignada a tarjetas activas.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $ok = $this->tableroModel->deletePrioridad($id_prioridad);
        if($ok){
            flashMessage('tablero_message', 'Prioridad eliminada correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo eliminar la prioridad.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function create_tarjeta(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_tablero = (int)($_POST['id_tablero'] ?? 0);
        $id_columna = (int)($_POST['id_columna'] ?? 0);
        $id_alcance = !empty($_POST['id_alcance']) ? (int)$_POST['id_alcance'] : null;
        $id_actividad = !empty($_POST['id_actividad']) ? (int)$_POST['id_actividad'] : null;
        $id_usuario_asignado = !empty($_POST['id_usuario_asignado']) ? (int)$_POST['id_usuario_asignado'] : null;
        $id_prioridad = !empty($_POST['id_prioridad']) ? (int)$_POST['id_prioridad'] : 0;
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $usarFechas = !empty($_POST['usar_fechas']);
        $fechaInicio = $this->sanitizeOptionalDateInput($_POST['fecha_inicio'] ?? '', $usarFechas);
        $fechaFin = $this->sanitizeOptionalDateInput($_POST['fecha_fin'] ?? '', $usarFechas);
        $completadoTarjeta = !empty($_POST['completado']);
        $checklist_lines = trim($_POST['checklist'] ?? '');
        $etiquetaIds = $this->extractEtiquetaIdsFromPost();
        $estadoTarjeta = $this->resolveTarjetaEstado($completadoTarjeta);

        if(!$this->hasBoardPermission($id_tablero, 'tarjeta_crear')){
            flashMessage('tablero_error', 'No tiene permisos para crear tarjetas en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($id_columna <= 0 || empty($titulo)){
            flashMessage('tablero_error', 'Debe seleccionar columna y escribir el titulo de la tarjeta.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($id_prioridad <= 0){
            flashMessage('tablero_error', 'Debe seleccionar una prioridad para la tarjeta.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($usarFechas && ($_POST['fecha_inicio'] ?? '') !== '' && $fechaInicio === null){
            flashMessage('tablero_error', 'La fecha de inicio no tiene un formato valido.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($usarFechas && ($_POST['fecha_fin'] ?? '') !== '' && $fechaFin === null){
            flashMessage('tablero_error', 'La fecha final no tiene un formato valido.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($fechaInicio !== null && $fechaFin !== null && strtotime($fechaInicio) > strtotime($fechaFin)){
            flashMessage('tablero_error', 'La fecha de inicio no puede ser mayor que la fecha final.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $columna = $this->tableroModel->getColumnaById($id_columna);
        if(!$columna || (int)$columna->Id_tablero !== $id_tablero){
            flashMessage('tablero_error', 'La columna seleccionada no pertenece al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($id_usuario_asignado !== null){
            $this->verificarAcceso('tablero', 'ver');

            if(!$this->hasBoardPermission($id_tablero, 'tarjeta_asignar')){
                flashMessage('tablero_error', 'No tiene permisos para asignar usuarios a tarjetas en este tablero.', 'danger');
                redirect('tablero/index?tablero_id=' . $id_tablero);
            }

            if(!$this->tableroModel->usuarioEstaAsignadoATablero($id_tablero, $id_usuario_asignado)){
                flashMessage('tablero_error', 'El usuario seleccionado no esta asignado al tablero activo.', 'danger');
                redirect('tablero/index?tablero_id=' . $id_tablero);
            }
        }

        $idUsuarioContratoObjetivo = $id_usuario_asignado !== null ? $id_usuario_asignado : (int)$_SESSION['user_id'];
        if($id_alcance !== null && !$this->tableroModel->alcancePerteneceAContratoUsuario($id_alcance, $idUsuarioContratoObjetivo)){
            flashMessage('tablero_error', 'El alcance seleccionado no pertenece al contrato del usuario asignado.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($id_actividad !== null && $id_alcance === null){
            flashMessage('tablero_error', 'Para vincular una actividad, primero debe seleccionar un alcance.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($id_actividad !== null && !$this->tableroModel->actividadPerteneceAAlcance($id_actividad, (int)$id_alcance)){
            flashMessage('tablero_error', 'La actividad seleccionada no pertenece al alcance indicado.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $prioridad = $this->tableroModel->getPrioridadById($id_prioridad);
        if(!$prioridad || (int)$prioridad->Id_tablero !== $id_tablero){
            flashMessage('tablero_error', 'La prioridad seleccionada no pertenece al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $etiquetasValidas = $this->filterValidBoardLabelIds($id_tablero, $etiquetaIds);
        if(count($etiquetasValidas) !== count($etiquetaIds)){
            flashMessage('tablero_error', 'Una o mas etiquetas seleccionadas no pertenecen al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $checklistItems = [];
        if(!empty($checklist_lines)){
            $lines = preg_split('/\r\n|\r|\n/', $checklist_lines);
            foreach($lines as $line){
                $text = trim($line);
                if($text !== ''){
                    $checklistItems[] = [
                        'text' => $text,
                        'done' => false
                    ];
                }
            }
        }

        $posicion = $this->tableroModel->getMaxPosicionByColumna($id_columna) + 1;

        $data = [
            'id_tablero' => $id_tablero,
            'id_columna' => $id_columna,
            'id_alcance' => $id_alcance,
            'id_actividad' => $id_actividad,
            'id_usuario_creador' => (int)$_SESSION['user_id'],
            'id_usuario_asignado' => $id_usuario_asignado,
            'id_prioridad' => $id_prioridad,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'checklist_json' => json_encode($checklistItems),
            'estado_tarjeta' => $estadoTarjeta,
            'completado' => $completadoTarjeta ? 1 : 0,
            'posicion' => $posicion
        ];

        $id_tarjeta_nueva = $this->tableroModel->addTarjeta($data);
        if($id_tarjeta_nueva){
            $this->tableroModel->setEtiquetasTarjeta((int)$id_tarjeta_nueva, $etiquetasValidas);

            // Auto-crear listados de tareas desde plantillas asociadas
            $plantilla_listas_ids_raw = trim($_POST['plantilla_listas_ids'] ?? '');
            if($plantilla_listas_ids_raw !== ''){
                $ids_lista = array_filter(array_map('intval', explode(',', $plantilla_listas_ids_raw)));
                foreach($ids_lista as $id_lista){
                    $plantillaLista = $this->tableroModel->getTareasPlantillaById($id_lista, $id_tablero);
                    if(!$plantillaLista) continue;
                    $id_tarea = $this->tableroModel->addTareaTarjeta((int)$id_tarjeta_nueva, $plantillaLista->Nombre_lista);
                    if(!$id_tarea) continue;
                    $detalles = $this->tableroModel->getTareasPlantillaDetalles($id_lista);
                    foreach($detalles as $detalle){
                        $this->tableroModel->addDetalleTarea($id_tarea, $detalle->Descripcion, null);
                    }
                }
            }

            $detalleCreacion = 'Se creo la tarjeta "' . $titulo . '".';
            if(!empty($etiquetasValidas)){
                $detalleCreacion .= ' Etiquetas: ' . $this->buildLabelHistoryText($id_tablero, $etiquetasValidas) . '.';
            }
            $detalleCreacion .= ' Prioridad: ' . strtoupper((string)$prioridad->Nombre) . ' (' . (int)$prioridad->Valor . ').';
            if($fechaInicio !== null || $fechaFin !== null){
                $detalleCreacion .= ' Fechas: ' . ($fechaInicio ?? 'Sin inicio') . ' a ' . ($fechaFin ?? 'Sin fin') . '.';
            }
            $detalleCreacion .= ' Estado: ' . $estadoTarjeta . '.';

            $this->tableroModel->addHistorialTarjeta(
                (int)$id_tarjeta_nueva,
                (int)$_SESSION['user_id'],
                'tarjeta_creada',
                $detalleCreacion,
                [
                    'etiquetas' => $etiquetasValidas,
                    'id_prioridad' => $id_prioridad,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'estado_tarjeta' => $estadoTarjeta,
                    'completado' => $completadoTarjeta ? 1 : 0
                ]
            );
            flashMessage('tablero_message', 'Tarjeta creada correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo crear la tarjeta.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function update_tarjeta($id = null){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_numeric($id)){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_tarjeta = (int)$id;
        $id_tablero = (int)($_POST['id_tablero'] ?? 0);
        $id_columna = (int)($_POST['id_columna'] ?? 0);
        $id_alcance = !empty($_POST['id_alcance']) ? (int)$_POST['id_alcance'] : null;
        $id_actividad = !empty($_POST['id_actividad']) ? (int)$_POST['id_actividad'] : null;
        $id_usuario_asignado = !empty($_POST['id_usuario_asignado']) ? (int)$_POST['id_usuario_asignado'] : null;
        $id_prioridad = !empty($_POST['id_prioridad']) ? (int)$_POST['id_prioridad'] : 0;
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $usarFechas = !empty($_POST['usar_fechas']);
        $fechaInicio = $this->sanitizeOptionalDateInput($_POST['fecha_inicio'] ?? '', $usarFechas);
        $fechaFin = $this->sanitizeOptionalDateInput($_POST['fecha_fin'] ?? '', $usarFechas);
        $completadoTarjeta = !empty($_POST['completado']);
        $etiquetaIds = $this->extractEtiquetaIdsFromPost();
        $estadoTarjeta = $this->resolveTarjetaEstado($completadoTarjeta);

        if(!$this->hasBoardPermission($id_tablero, 'tarjeta_editar')){
            flashMessage('tablero_error', 'No tiene permisos para editar tarjetas en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($id_columna <= 0 || $titulo === ''){
            flashMessage('tablero_error', 'Debe seleccionar columna y escribir el titulo de la tarjeta.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($id_prioridad <= 0){
            flashMessage('tablero_error', 'Debe seleccionar una prioridad para la tarjeta.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($usarFechas && ($_POST['fecha_inicio'] ?? '') !== '' && $fechaInicio === null){
            flashMessage('tablero_error', 'La fecha de inicio no tiene un formato valido.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($usarFechas && ($_POST['fecha_fin'] ?? '') !== '' && $fechaFin === null){
            flashMessage('tablero_error', 'La fecha final no tiene un formato valido.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($fechaInicio !== null && $fechaFin !== null && strtotime($fechaInicio) > strtotime($fechaFin)){
            flashMessage('tablero_error', 'La fecha de inicio no puede ser mayor que la fecha final.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $tarjeta = $this->tableroModel->getTarjetaById($id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            flashMessage('tablero_error', 'La tarjeta no pertenece al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $asignadoAnterior = !empty($tarjeta->Id_usuario_asignado) ? (int)$tarjeta->Id_usuario_asignado : null;
        if($asignadoAnterior !== $id_usuario_asignado){
            if(!$this->hasBoardPermission($id_tablero, 'tarjeta_asignar')){
                flashMessage('tablero_error', 'No tiene permisos para cambiar el usuario asignado de la tarjeta.', 'danger');
                redirect('tablero/index?tablero_id=' . $id_tablero);
            }

            if($id_usuario_asignado !== null){
                $this->verificarAcceso('tablero', 'ver');
            }
        }

        $columna = $this->tableroModel->getColumnaById($id_columna);
        if(!$columna || (int)$columna->Id_tablero !== $id_tablero){
            flashMessage('tablero_error', 'La columna seleccionada no pertenece al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($id_usuario_asignado !== null && !$this->tableroModel->usuarioEstaAsignadoATablero($id_tablero, $id_usuario_asignado)){
            flashMessage('tablero_error', 'El usuario seleccionado no esta asignado al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $idUsuarioContratoObjetivo = $id_usuario_asignado !== null ? $id_usuario_asignado : (int)$_SESSION['user_id'];
        if($id_alcance !== null && !$this->tableroModel->alcancePerteneceAContratoUsuario($id_alcance, $idUsuarioContratoObjetivo)){
            flashMessage('tablero_error', 'El alcance seleccionado no pertenece al contrato del usuario asignado.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($id_actividad !== null && $id_alcance === null){
            flashMessage('tablero_error', 'Para vincular una actividad, primero debe seleccionar un alcance.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($id_actividad !== null && !$this->tableroModel->actividadPerteneceAAlcance($id_actividad, (int)$id_alcance)){
            flashMessage('tablero_error', 'La actividad seleccionada no pertenece al alcance indicado.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $prioridad = $this->tableroModel->getPrioridadById($id_prioridad);
        if(!$prioridad || (int)$prioridad->Id_tablero !== $id_tablero){
            flashMessage('tablero_error', 'La prioridad seleccionada no pertenece al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $etiquetasValidas = $this->filterValidBoardLabelIds($id_tablero, $etiquetaIds);
        if(count($etiquetasValidas) !== count($etiquetaIds)){
            flashMessage('tablero_error', 'Una o mas etiquetas seleccionadas no pertenecen al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $etiquetasAntes = $this->tableroModel->getEtiquetaIdsByTarjeta($id_tarjeta);

        $ok = $this->tableroModel->updateTarjeta($id_tarjeta, [
            'id_columna' => $id_columna,
            'id_alcance' => $id_alcance,
            'id_actividad' => $id_actividad,
            'id_usuario_asignado' => $id_usuario_asignado,
            'id_prioridad' => $id_prioridad,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'estado_tarjeta' => $estadoTarjeta,
            'completado' => $completadoTarjeta ? 1 : 0
        ]);

        if($ok){
            $this->tableroModel->setEtiquetasTarjeta($id_tarjeta, $etiquetasValidas);

            $etiquetasAntesOrdenadas = $etiquetasAntes;
            $etiquetasNuevasOrdenadas = $etiquetasValidas;
            sort($etiquetasAntesOrdenadas);
            sort($etiquetasNuevasOrdenadas);
            $etiquetasCambiaron = $etiquetasAntesOrdenadas !== $etiquetasNuevasOrdenadas;

            $mensajeHistorial = 'Actualizo los datos de la tarjeta.';
            if($etiquetasCambiaron){
                $mensajeHistorial = 'Actualizo los datos de la tarjeta y sus etiquetas a: ' . $this->buildLabelHistoryText($id_tablero, $etiquetasValidas) . '.';
            }
            $mensajeHistorial .= ' Prioridad: ' . strtoupper((string)$prioridad->Nombre) . ' (' . (int)$prioridad->Valor . ').';
            if($fechaInicio !== null || $fechaFin !== null){
                $mensajeHistorial .= ' Fechas: ' . ($fechaInicio ?? 'Sin inicio') . ' a ' . ($fechaFin ?? 'Sin fin') . '.';
            }
            $mensajeHistorial .= ' Estado: ' . $estadoTarjeta . '.';

            $this->tableroModel->addHistorialTarjeta(
                $id_tarjeta,
                (int)$_SESSION['user_id'],
                'tarjeta_editada',
                $mensajeHistorial,
                [
                    'id_columna' => $id_columna,
                    'id_alcance' => $id_alcance,
                    'id_actividad' => $id_actividad,
                    'id_usuario_asignado' => $id_usuario_asignado,
                    'id_prioridad' => $id_prioridad,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'estado_tarjeta' => $estadoTarjeta,
                    'completado' => $completadoTarjeta ? 1 : 0,
                    'etiquetas' => $etiquetasValidas
                ]
            );
            flashMessage('tablero_message', 'Tarjeta actualizada correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo actualizar la tarjeta.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function delete_tarjeta($id = null){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_numeric($id)){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_tarjeta = (int)$id;
        $id_tablero = (int)($_POST['id_tablero'] ?? 0);

        if(!$this->hasBoardPermission($id_tablero, 'tarjeta_eliminar')){
            flashMessage('tablero_error', 'No tiene permisos para eliminar tarjetas en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $tarjeta = $this->tableroModel->getTarjetaById($id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            flashMessage('tablero_error', 'La tarjeta no pertenece al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if(!$this->tableroModel->canDeleteTarjeta($id_tarjeta)){
            flashMessage('tablero_error', 'La tarjeta no se puede eliminar porque ya tiene listas/tareas o vinculos asignados (personal, alcance o actividad).', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $this->tableroModel->addHistorialTarjeta(
            $id_tarjeta,
            (int)$_SESSION['user_id'],
            'tarjeta_eliminada',
            'Elimino logicamente la tarjeta "' . trim((string)$tarjeta->Titulo) . '".',
            ['id_tarjeta' => $id_tarjeta]
        );

        $ok = $this->tableroModel->deleteTarjeta($id_tarjeta);
        if($ok){
            flashMessage('tablero_message', 'Tarjeta eliminada correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo eliminar la tarjeta.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function archivar_tarjeta(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload    = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarjeta = isset($payload['id_tarjeta']) ? (int)$payload['id_tarjeta'] : 0;
        $archivar   = !empty($payload['archivar']);

        if($id_tablero <= 0 || $id_tarjeta <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos'], 400);
        }

        if(!$this->hasBoardPermission($id_tablero, 'tarjeta_editar')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso para editar tarjetas en este tablero'], 403);
        }

        $tarjeta = $this->tableroModel->getTarjetaById($id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta no encontrada'], 404);
        }

        $ok = $this->tableroModel->toggleArchivarTarjeta($id_tarjeta, $id_tablero, $archivar);
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo actualizar la tarjeta'], 500);
        }

        $accion  = $archivar ? 'tarjeta_archivada' : 'tarjeta_desarchivada';
        $mensaje = $archivar
            ? 'Archivó la tarjeta "' . trim((string)$tarjeta->Titulo) . '".'
            : 'Desarchivó la tarjeta "' . trim((string)$tarjeta->Titulo) . '".';
        $this->tableroModel->addHistorialTarjeta($id_tarjeta, (int)$_SESSION['user_id'], $accion, $mensaje, ['archivada' => $archivar]);

        return $this->jsonResponse(['success' => true, 'archivada' => $archivar]);
    }

    public function move_tarjeta(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();

        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarjeta = isset($payload['id_tarjeta']) ? (int)$payload['id_tarjeta'] : 0;
        $id_columna = isset($payload['id_columna']) ? (int)$payload['id_columna'] : 0;
        $posicion = isset($payload['posicion']) ? (int)$payload['posicion'] : 0;

        if(!$this->hasBoardPermission($id_tablero, 'tarjeta_mover')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarjeta <= 0 || $id_columna <= 0 || $posicion < 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Datos invalidos'], 400);
        }

        $tarjeta = $this->tableroModel->getTarjetaById($id_tarjeta);
        $columna = $this->tableroModel->getColumnaById($id_columna);
        if(!$tarjeta || !$columna || (int)$tarjeta->Id_tablero !== $id_tablero || (int)$columna->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta/columna no pertenecen al tablero'], 403);
        }

        $ok = $this->tableroModel->moveTarjeta($id_tarjeta, $id_columna, $posicion);
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo mover la tarjeta'], 500);
        }

        $columnaOrigen = $this->tableroModel->getColumnaById((int)$tarjeta->Id_columna);
        $mensaje = 'Tarjeta movida';
        if($columnaOrigen && $columna){
            $mensaje = 'Movio la tarjeta de "' . $columnaOrigen->Nombre . '" a "' . $columna->Nombre . '".';
        }
        $this->tableroModel->addHistorialTarjeta(
            $id_tarjeta,
            (int)$_SESSION['user_id'],
            'tarjeta_movida',
            $mensaje,
            [
                'id_columna_origen' => (int)$tarjeta->Id_columna,
                'id_columna_destino' => $id_columna,
                'posicion' => $posicion
            ]
        );

        return $this->jsonResponse(['success' => true]);
    }

    public function update_checklist(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarjeta = isset($payload['id_tarjeta']) ? (int)$payload['id_tarjeta'] : 0;
        $checklist = $payload['checklist'] ?? [];

        if(!$this->hasBoardPermission($id_tablero, 'tarjeta_editar')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarjeta <= 0 || !is_array($checklist)){
            return $this->jsonResponse(['success' => false, 'error' => 'Datos invalidos'], 400);
        }

        $tarjeta = $this->tableroModel->getTarjetaById($id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta no pertenece al tablero'], 403);
        }

        $cleanChecklist = [];
        foreach($checklist as $item){
            if(!is_array($item)){
                continue;
            }
            $text = trim($item['text'] ?? '');
            if($text === ''){
                continue;
            }
            $cleanChecklist[] = [
                'text' => $text,
                'done' => !empty($item['done'])
            ];
        }

        $ok = $this->tableroModel->updateChecklist($id_tarjeta, json_encode($cleanChecklist));
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo actualizar checklist'], 500);
        }

        return $this->jsonResponse(['success' => true]);
    }

    public function toggle_tarjeta_completado(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarjeta = isset($payload['id_tarjeta']) ? (int)$payload['id_tarjeta'] : 0;
        $completado = !empty($payload['completado']) ? 1 : 0;
        $estadoTarjeta = $this->resolveTarjetaEstado($completado === 1);

        if(!$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarjeta <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta invalida'], 400);
        }

        $tarjeta = $this->tableroModel->getTarjetaById($id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta no pertenece al tablero'], 403);
        }

        $ok = $this->tableroModel->toggleTarjetaCompletado($id_tarjeta, $completado, $estadoTarjeta);
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo actualizar el estado de la tarjeta'], 500);
        }

        $this->tableroModel->addHistorialTarjeta(
            $id_tarjeta,
            (int)$_SESSION['user_id'],
            'tarjeta_estado',
            $completado === 1 ? 'Marco la tarjeta como completada.' : 'Marco la tarjeta como pendiente.',
            ['completado' => $completado, 'estado_tarjeta' => $estadoTarjeta]
        );

        return $this->jsonResponse([
            'success' => true,
            'completado' => $completado,
            'estado_tarjeta' => $estadoTarjeta
        ]);
    }

    public function assign_personal(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarjeta = isset($payload['id_tarjeta']) ? (int)$payload['id_tarjeta'] : 0;
        $id_usuario_asignado = isset($payload['id_usuario_asignado']) && $payload['id_usuario_asignado'] !== ''
            ? (int)$payload['id_usuario_asignado']
            : null;

        if(!$this->hasBoardPermission($id_tablero, 'tarjeta_asignar')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarjeta <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta invalida'], 400);
        }

        $tarjeta = $this->tableroModel->getTarjetaById($id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta no pertenece al tablero'], 403);
        }

        if($id_usuario_asignado !== null && !$this->tableroModel->usuarioEstaAsignadoATablero($id_tablero, $id_usuario_asignado)){
            return $this->jsonResponse(['success' => false, 'error' => 'El usuario no esta asignado a este tablero'], 400);
        }

        $ok = $this->tableroModel->updateAsignado($id_tarjeta, $id_usuario_asignado);
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo asignar personal'], 500);
        }

        $mensaje = $id_usuario_asignado === null
            ? 'Se retiro el usuario asignado de la tarjeta.'
            : 'Se asigno usuario a la tarjeta.';
        $this->tableroModel->addHistorialTarjeta(
            $id_tarjeta,
            (int)$_SESSION['user_id'],
            'tarjeta_asignacion',
            $mensaje,
            ['id_usuario_asignado' => $id_usuario_asignado]
        );

        return $this->jsonResponse(['success' => true]);
    }

    public function start_timer(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarjeta = isset($payload['id_tarjeta']) ? (int)$payload['id_tarjeta'] : 0;

        if(!$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarjeta <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta invalida'], 400);
        }

        $tarjeta = $this->tableroModel->getTarjetaById($id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta no pertenece al tablero'], 403);
        }

        $timer = $this->tableroModel->startTimer($id_tarjeta, (int)$_SESSION['user_id']);
        if(!$timer){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo iniciar el cronometro'], 500);
        }

        $total = $this->tableroModel->getTiempoTotalTarjeta($id_tarjeta);

        $this->tableroModel->addHistorialTarjeta(
            $id_tarjeta,
            (int)$_SESSION['user_id'],
            'timer_inicio',
            'Inicio cronometro en la tarjeta.'
        );

        return $this->jsonResponse([
            'success' => true,
            'id_tiempo' => (int)$timer->Id_tiempo,
            'inicio_timestamp' => $timer->inicio_timestamp,
            'total_segundos' => $total
        ]);
    }

    public function stop_timer(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarjeta = isset($payload['id_tarjeta']) ? (int)$payload['id_tarjeta'] : 0;

        if(!$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarjeta <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta invalida'], 400);
        }

        $tarjeta = $this->tableroModel->getTarjetaById($id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta no pertenece al tablero'], 403);
        }

        $stopped = $this->tableroModel->stopTimer($id_tarjeta, (int)$_SESSION['user_id']);
        if(!$stopped){
            return $this->jsonResponse(['success' => false, 'error' => 'No hay cronometro en curso para esta tarjeta'], 400);
        }

        $total = $this->tableroModel->getTiempoTotalTarjeta($id_tarjeta);

        $this->tableroModel->addHistorialTarjeta(
            $id_tarjeta,
            (int)$_SESSION['user_id'],
            'timer_fin',
            'Detuvo cronometro en la tarjeta.',
            ['id_tiempo' => (int)$stopped]
        );

        return $this->jsonResponse([
            'success' => true,
            'id_tiempo' => (int)$stopped,
            'total_segundos' => $total
        ]);
    }

    public function get_tarjeta_tareas(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarjeta = isset($payload['id_tarjeta']) ? (int)$payload['id_tarjeta'] : 0;

        if(!$this->hasBoardPermission($id_tablero, 'tarjeta_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        $tarjeta = $this->tableroModel->getTarjetaById($id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta no pertenece al tablero'], 403);
        }

        $tareas = $this->tableroModel->getTareasByTarjeta($id_tarjeta);
        foreach($tareas as $t){
            $t->detalles = $this->tableroModel->getDetallesByTarea((int)$t->Id_tarea, (int)$_SESSION['user_id']);
            foreach($t->detalles as $detalleItem){
                $detalleItem->Tiempo_por_usuario = $this->tableroModel->getTiempoDetallePorUsuario((int)$detalleItem->Id_tarea_detalle);
            }
        }

        $etiquetas = $this->tableroModel->getEtiquetasByTarjetas([$id_tarjeta]);
        $etiquetasTarjeta = [];
        foreach($etiquetas as $etiqueta){
            if((int)$etiqueta->Id_tarjeta === $id_tarjeta){
                $etiquetasTarjeta[] = [
                    'id_etiqueta' => (int)$etiqueta->Id_etiqueta,
                    'nombre' => $etiqueta->Nombre ?? '',
                    'color' => $etiqueta->Color ?? '#6c757d'
                ];
            }
        }

        $historial = $this->tableroModel->getHistorialByTarjeta($id_tarjeta, 80);

        return $this->jsonResponse([
            'success' => true,
            'tareas' => $tareas,
            'historial' => $historial,
            'total_tarjeta_segundos' => $this->tableroModel->getTiempoTotalTarjeta($id_tarjeta),
            'en_curso_tiempo' => $this->tableroModel->tarjetaTieneTimerDetalleEnCurso($id_tarjeta),
            'tarjeta_titulo' => $tarjeta->Titulo ?? '',
            'tarjeta_descripcion' => $tarjeta->Descripcion ?? '',
            'tarjeta_actividad_id' => !empty($tarjeta->Id_actividad) ? (int)$tarjeta->Id_actividad : null,
            'tarjeta_actividad_descripcion' => $tarjeta->Actividad_Descripcion ?? '',
            'tarjeta_prioridad_nombre' => $tarjeta->Prioridad_Nombre ?? '',
            'tarjeta_prioridad_valor' => isset($tarjeta->Prioridad_Valor) ? (int)$tarjeta->Prioridad_Valor : null,
            'tarjeta_prioridad_color' => $tarjeta->Prioridad_Color ?? '#6c757d',
            'tarjeta_estado' => $tarjeta->Estado_tarjeta ?? 'Pendiente',
            'tarjeta_completado' => !empty($tarjeta->Completado) ? 1 : 0,
            'tarjeta_etiquetas' => $etiquetasTarjeta
        ]);
    }

    public function create_tarjeta_tarea(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarjeta = isset($payload['id_tarjeta']) ? (int)$payload['id_tarjeta'] : 0;
        $nombre_tarea = trim($payload['nombre_tarea'] ?? '');

        if(!$this->hasBoardPermission($id_tablero, 'lista_crear')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarjeta <= 0 || $nombre_tarea === ''){
            return $this->jsonResponse(['success' => false, 'error' => 'Datos invalidos'], 400);
        }

        $tarjeta = $this->tableroModel->getTarjetaById($id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarjeta no pertenece al tablero'], 403);
        }

        $id_tarea = $this->tableroModel->addTareaTarjeta($id_tarjeta, $nombre_tarea);
        if(!$id_tarea){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo crear la lista de tareas'], 500);
        }

        $this->tableroModel->addHistorialTarjeta(
            $id_tarjeta,
            (int)$_SESSION['user_id'],
            'tarea_creada',
            'Creo la lista de tareas "' . $nombre_tarea . '".'
        );

        return $this->jsonResponse(['success' => true, 'id_tarea' => (int)$id_tarea]);
    }

    public function create_tarjeta_tarea_detalle(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarea = isset($payload['id_tarea']) ? (int)$payload['id_tarea'] : 0;
        $id_usuario_asignado = isset($payload['id_usuario_asignado']) && $payload['id_usuario_asignado'] !== ''
            ? (int)$payload['id_usuario_asignado']
            : null;
        $descripcion = trim($payload['descripcion'] ?? '');

        if(!$this->hasBoardPermission($id_tablero, 'tarea_crear')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarea <= 0 || $descripcion === ''){
            return $this->jsonResponse(['success' => false, 'error' => 'Datos invalidos'], 400);
        }

        $tarea = $this->tableroModel->getTareaById($id_tarea);
        if(!$tarea){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarea no encontrada'], 404);
        }

        $tarjeta = $this->tableroModel->getTarjetaById((int)$tarea->Id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarea fuera del tablero activo'], 403);
        }

        if($id_usuario_asignado === null && !empty($tarjeta->Id_usuario_asignado)){
            $id_usuario_asignado = (int)$tarjeta->Id_usuario_asignado;
        }

        if($id_usuario_asignado !== null && !$this->hasBoardPermission($id_tablero, 'tarjeta_asignar')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso para asignar tareas en este tablero'], 403);
        }

        if($id_usuario_asignado !== null && !$this->tableroModel->usuarioEstaAsignadoATablero($id_tablero, $id_usuario_asignado)){
            return $this->jsonResponse(['success' => false, 'error' => 'El usuario asignado no pertenece al tablero activo'], 400);
        }

        $id_detalle = $this->tableroModel->addDetalleTarea($id_tarea, $descripcion, $id_usuario_asignado);
        if(!$id_detalle){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo agregar el detalle'], 500);
        }

        $this->tableroModel->addHistorialTarjeta(
            (int)$tarjeta->Id_tarjeta,
            (int)$_SESSION['user_id'],
            'tarea_detalle_creado',
            'Agrego un item de tarea: "' . $descripcion . '".'
        );

        return $this->jsonResponse(['success' => true, 'id_tarea_detalle' => (int)$id_detalle]);
    }

    public function assign_tarea_detalle_usuario(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarea_detalle = isset($payload['id_tarea_detalle']) ? (int)$payload['id_tarea_detalle'] : 0;
        $id_usuario_asignado = isset($payload['id_usuario_asignado']) && $payload['id_usuario_asignado'] !== ''
            ? (int)$payload['id_usuario_asignado']
            : null;

        if(!$this->hasBoardPermission($id_tablero, 'tarjeta_asignar')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso para asignar tareas en este tablero'], 403);
        }

        if($id_tarea_detalle <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle invalido'], 400);
        }

        $detalle = $this->tableroModel->getDetalleById($id_tarea_detalle);
        if(!$detalle){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle no encontrado'], 404);
        }

        $tarea = $this->tableroModel->getTareaById((int)$detalle->Id_tarea);
        $tarjeta = $tarea ? $this->tableroModel->getTarjetaById((int)$tarea->Id_tarjeta) : null;
        if(!$tarea || !$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle fuera del tablero activo'], 403);
        }

        if($this->tableroModel->detalleTieneTimerEnCurso($id_tarea_detalle)){
            return $this->jsonResponse(['success' => false, 'error' => 'No se puede reasignar mientras existe un cronometro en curso.'], 400);
        }

        if($id_usuario_asignado !== null && !$this->tableroModel->usuarioEstaAsignadoATablero($id_tablero, $id_usuario_asignado)){
            return $this->jsonResponse(['success' => false, 'error' => 'El usuario seleccionado no pertenece al tablero activo'], 400);
        }

        $ok = $this->tableroModel->updateDetalleUsuarioAsignado($id_tarea_detalle, $id_usuario_asignado);
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo actualizar la asignacion'], 500);
        }

        $mensaje = $id_usuario_asignado === null
            ? 'Se retiro el usuario asignado de una tarea.'
            : 'Se asigno usuario a una tarea.';
        $this->tableroModel->addHistorialTarjeta(
            (int)$tarjeta->Id_tarjeta,
            (int)$_SESSION['user_id'],
            'tarea_detalle_asignacion',
            $mensaje,
            ['id_tarea_detalle' => (int)$id_tarea_detalle, 'id_usuario_asignado' => $id_usuario_asignado]
        );

        return $this->jsonResponse([
            'success' => true,
            'tiempo_por_usuario' => $this->tableroModel->getTiempoDetallePorUsuario((int)$id_tarea_detalle)
        ]);
    }

    public function update_tarjeta_tarea(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarea = isset($payload['id_tarea']) ? (int)$payload['id_tarea'] : 0;
        $nombre_tarea = trim($payload['nombre_tarea'] ?? '');

        if(!$this->hasBoardPermission($id_tablero, 'lista_editar')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarea <= 0 || $nombre_tarea === ''){
            return $this->jsonResponse(['success' => false, 'error' => 'Datos invalidos'], 400);
        }

        $tarea = $this->tableroModel->getTareaById($id_tarea);
        if(!$tarea){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarea no encontrada'], 404);
        }

        $tarjeta = $this->tableroModel->getTarjetaById((int)$tarea->Id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarea fuera del tablero activo'], 403);
        }

        $ok = $this->tableroModel->updateTareaTarjeta($id_tarea, $nombre_tarea);
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo actualizar la lista'], 500);
        }

        $this->tableroModel->addHistorialTarjeta(
            (int)$tarjeta->Id_tarjeta,
            (int)$_SESSION['user_id'],
            'tarea_lista_editada',
            'Edito una lista de tareas: "' . $nombre_tarea . '".',
            ['id_tarea' => $id_tarea]
        );

        return $this->jsonResponse(['success' => true]);
    }

    public function update_tarjeta_tarea_detalle(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarea_detalle = isset($payload['id_tarea_detalle']) ? (int)$payload['id_tarea_detalle'] : 0;
        $descripcion = trim($payload['descripcion'] ?? '');

        if(!$this->hasBoardPermission($id_tablero, 'tarea_editar')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarea_detalle <= 0 || $descripcion === ''){
            return $this->jsonResponse(['success' => false, 'error' => 'Datos invalidos'], 400);
        }

        $detalle = $this->tableroModel->getDetalleById($id_tarea_detalle);
        if(!$detalle){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle no encontrado'], 404);
        }

        $tarea = $this->tableroModel->getTareaById((int)$detalle->Id_tarea);
        $tarjeta = $tarea ? $this->tableroModel->getTarjetaById((int)$tarea->Id_tarjeta) : null;
        if(!$tarea || !$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle fuera del tablero activo'], 403);
        }

        $ok = $this->tableroModel->updateDetalleTarea($id_tarea_detalle, $descripcion);
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo actualizar la tarea'], 500);
        }

        $this->tableroModel->addHistorialTarjeta(
            (int)$tarjeta->Id_tarjeta,
            (int)$_SESSION['user_id'],
            'tarea_detalle_editado',
            'Edito una tarea: "' . $descripcion . '".',
            ['id_tarea_detalle' => $id_tarea_detalle]
        );

        return $this->jsonResponse(['success' => true]);
    }

    public function toggle_tarjeta_tarea_detalle(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarea_detalle = isset($payload['id_tarea_detalle']) ? (int)$payload['id_tarea_detalle'] : 0;
        $completado = !empty($payload['completado']) ? 1 : 0;

        if(!$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarea_detalle <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle invalido'], 400);
        }

        $detalle = $this->tableroModel->getDetalleById($id_tarea_detalle);
        if(!$detalle){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle no encontrado'], 404);
        }

        $tarea = $this->tableroModel->getTareaById((int)$detalle->Id_tarea);
        if(!$tarea){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarea no encontrada'], 404);
        }

        $tarjeta = $this->tableroModel->getTarjetaById((int)$tarea->Id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle fuera del tablero activo'], 403);
        }

        $ok = $this->tableroModel->toggleDetalleTarea($id_tarea_detalle, $completado, (int)$_SESSION['user_id']);
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo actualizar el detalle'], 500);
        }

        $this->tableroModel->addHistorialTarjeta(
            (int)$tarjeta->Id_tarjeta,
            (int)$_SESSION['user_id'],
            'tarea_detalle_estado',
            $completado ? 'Marco una tarea como completada.' : 'Marco una tarea como pendiente.',
            ['id_tarea_detalle' => $id_tarea_detalle, 'completado' => $completado]
        );

        return $this->jsonResponse(['success' => true]);
    }

    public function delete_tarjeta_tarea(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarea = isset($payload['id_tarea']) ? (int)$payload['id_tarea'] : 0;

        if(!$this->hasBoardPermission($id_tablero, 'lista_eliminar')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarea <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarea invalida'], 400);
        }

        $tarea = $this->tableroModel->getTareaById($id_tarea);
        if(!$tarea){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarea no encontrada'], 404);
        }

        $tarjeta = $this->tableroModel->getTarjetaById((int)$tarea->Id_tarjeta);
        if(!$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Tarea fuera del tablero activo'], 403);
        }

        $totalDetalles = $this->tableroModel->countDetallesActivosByTarea($id_tarea);
        if($totalDetalles > 0){
            return $this->jsonResponse(['success' => false, 'error' => 'No se puede eliminar la lista porque tiene tareas creadas.'], 400);
        }

        $ok = $this->tableroModel->deleteTareaTarjeta($id_tarea);
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo eliminar la lista de tareas'], 500);
        }

        $this->tableroModel->addHistorialTarjeta(
            (int)$tarjeta->Id_tarjeta,
            (int)$_SESSION['user_id'],
            'tarea_lista_eliminada',
            'Elimino una lista de tareas: "' . $tarea->Nombre_tarea . '".'
        );

        return $this->jsonResponse(['success' => true]);
    }

    public function delete_tarjeta_tarea_detalle(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarea_detalle = isset($payload['id_tarea_detalle']) ? (int)$payload['id_tarea_detalle'] : 0;

        if(!$this->hasBoardPermission($id_tablero, 'tarea_eliminar')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarea_detalle <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle invalido'], 400);
        }

        $detalle = $this->tableroModel->getDetalleById($id_tarea_detalle);
        if(!$detalle){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle no encontrado'], 404);
        }

        $tarea = $this->tableroModel->getTareaById((int)$detalle->Id_tarea);
        $tarjeta = $tarea ? $this->tableroModel->getTarjetaById((int)$tarea->Id_tarjeta) : null;
        if(!$tarea || !$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle fuera del tablero activo'], 403);
        }

        $ok = $this->tableroModel->deleteDetalleTarea($id_tarea_detalle);
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo eliminar la tarea'], 500);
        }

        $this->tableroModel->addHistorialTarjeta(
            (int)$tarjeta->Id_tarjeta,
            (int)$_SESSION['user_id'],
            'tarea_detalle_eliminado',
            'Elimino una tarea: "' . $detalle->Descripcion . '".',
            ['id_tarea_detalle' => $id_tarea_detalle]
        );

        return $this->jsonResponse(['success' => true]);
    }

    public function start_tarea_detalle_timer(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarea_detalle = isset($payload['id_tarea_detalle']) ? (int)$payload['id_tarea_detalle'] : 0;

        if(!$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarea_detalle <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle invalido'], 400);
        }

        $detalle = $this->tableroModel->getDetalleById($id_tarea_detalle);
        if(!$detalle){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle no encontrado'], 404);
        }

        $tarea = $this->tableroModel->getTareaById((int)$detalle->Id_tarea);
        $tarjeta = $tarea ? $this->tableroModel->getTarjetaById((int)$tarea->Id_tarjeta) : null;
        if(!$tarea || !$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle fuera del tablero activo'], 403);
        }

        $id_usuario_asignado = !empty($detalle->Id_usuario_asignado) ? (int)$detalle->Id_usuario_asignado : null;
        $id_usuario_actual = (int)$_SESSION['user_id'];
        if($id_usuario_asignado === null){
            return $this->jsonResponse(['success' => false, 'error' => 'Debe asignar un usuario a la tarea para iniciar el cronometro.'], 403);
        }

        if($id_usuario_asignado !== $id_usuario_actual){
            return $this->jsonResponse(['success' => false, 'error' => 'Solo el usuario asignado puede iniciar el cronometro de esta tarea.'], 403);
        }

        $id_usuario_timer = $id_usuario_asignado;

        $timer = $this->tableroModel->startDetalleTimer($id_tarea_detalle, $id_usuario_timer);
        if(!$timer){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo iniciar el cronometro'], 500);
        }

        $this->tableroModel->addHistorialTarjeta(
            (int)$tarjeta->Id_tarjeta,
            (int)$_SESSION['user_id'],
            'timer_detalle_inicio',
            'Inicio cronometro en una tarea: "' . $detalle->Descripcion . '".',
            ['id_tarea_detalle' => $id_tarea_detalle]
        );

        return $this->jsonResponse([
            'success' => true,
            'id_tiempo_detalle' => (int)$timer->Id_tiempo_detalle,
            'inicio_timestamp' => $timer->inicio_timestamp,
            'total_detalle_segundos' => $this->tableroModel->getTiempoTotalDetalle($id_tarea_detalle),
            'total_tarjeta_segundos' => $this->tableroModel->getTiempoTotalTarjeta((int)$tarjeta->Id_tarjeta),
            'en_curso_tiempo' => $this->tableroModel->tarjetaTieneTimerDetalleEnCurso((int)$tarjeta->Id_tarjeta)
        ]);
    }

    public function stop_tarea_detalle_timer(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarea_detalle = isset($payload['id_tarea_detalle']) ? (int)$payload['id_tarea_detalle'] : 0;

        if(!$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarea_detalle <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle invalido'], 400);
        }

        $detalle = $this->tableroModel->getDetalleById($id_tarea_detalle);
        if(!$detalle){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle no encontrado'], 404);
        }

        $tarea = $this->tableroModel->getTareaById((int)$detalle->Id_tarea);
        $tarjeta = $tarea ? $this->tableroModel->getTarjetaById((int)$tarea->Id_tarjeta) : null;
        if(!$tarea || !$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle fuera del tablero activo'], 403);
        }

        $id_usuario_asignado = !empty($detalle->Id_usuario_asignado) ? (int)$detalle->Id_usuario_asignado : null;
        $id_usuario_actual = (int)$_SESSION['user_id'];
        if($id_usuario_asignado === null){
            return $this->jsonResponse(['success' => false, 'error' => 'Debe asignar un usuario a la tarea para detener el cronometro.'], 403);
        }

        if($id_usuario_asignado !== $id_usuario_actual){
            return $this->jsonResponse(['success' => false, 'error' => 'Solo el usuario asignado puede detener el cronometro de esta tarea.'], 403);
        }

        $id_usuario_timer = $id_usuario_asignado;

        $stopped = $this->tableroModel->stopDetalleTimer($id_tarea_detalle, $id_usuario_timer);
        if(!$stopped){
            return $this->jsonResponse(['success' => false, 'error' => 'No hay cronometro en curso para esta tarea'], 400);
        }

        $this->tableroModel->addHistorialTarjeta(
            (int)$tarjeta->Id_tarjeta,
            (int)$_SESSION['user_id'],
            'timer_detalle_fin',
            'Detuvo cronometro en una tarea: "' . $detalle->Descripcion . '".',
            ['id_tarea_detalle' => $id_tarea_detalle, 'id_tiempo_detalle' => (int)$stopped]
        );

        return $this->jsonResponse([
            'success' => true,
            'id_tiempo_detalle' => (int)$stopped,
            'total_detalle_segundos' => $this->tableroModel->getTiempoTotalDetalle($id_tarea_detalle),
            'total_tarjeta_segundos' => $this->tableroModel->getTiempoTotalTarjeta((int)$tarjeta->Id_tarjeta),
            'en_curso_tiempo' => $this->tableroModel->tarjetaTieneTimerDetalleEnCurso((int)$tarjeta->Id_tarjeta)
        ]);
    }

    public function update_tarea_detalle_tiempo_manual(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarea_detalle = isset($payload['id_tarea_detalle']) ? (int)$payload['id_tarea_detalle'] : 0;
        $tiempo_hms = trim((string)($payload['tiempo_hms'] ?? ''));

        if(!$this->hasBoardPermission($id_tablero, 'tarea_tiempo_editar')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarea_detalle <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle invalido'], 400);
        }

        $seconds = $this->parseDurationHmsToSeconds($tiempo_hms);
        if($seconds === null){
            return $this->jsonResponse(['success' => false, 'error' => 'Formato invalido. Use hh:mm:ss.'], 400);
        }

        $detalle = $this->tableroModel->getDetalleById($id_tarea_detalle);
        if(!$detalle){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle no encontrado'], 404);
        }

        $tarea = $this->tableroModel->getTareaById((int)$detalle->Id_tarea);
        $tarjeta = $tarea ? $this->tableroModel->getTarjetaById((int)$tarea->Id_tarjeta) : null;
        if(!$tarea || !$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle fuera del tablero activo'], 403);
        }

        $id_usuario_asignado = !empty($detalle->Id_usuario_asignado) ? (int)$detalle->Id_usuario_asignado : null;
        $id_usuario_actual = (int)$_SESSION['user_id'];
        if($id_usuario_asignado !== null && $id_usuario_asignado !== $id_usuario_actual){
            $tienetiempoEnDetalle = false;
            $tiemposExistentes = $this->tableroModel->getTiempoDetallePorUsuario($id_tarea_detalle);
            foreach($tiemposExistentes as $_t){
                if((int)($_t->Id_usuario ?? 0) === $id_usuario_actual){
                    $tienetiempoEnDetalle = true;
                    break;
                }
            }
            if(!$tienetiempoEnDetalle && !isSupervisorOrJefeRol() && !isAdministradorRol()){
                return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso para editar manualmente el cronometro de otro usuario.'], 403);
            }
        }

        if($this->tableroModel->detalleTieneTimerEnCurso($id_tarea_detalle)){
            return $this->jsonResponse(['success' => false, 'error' => 'No se puede editar mientras el cronometro esta en curso.'], 400);
        }

        $totalAnterior = $this->tableroModel->getTiempoTotalDetalle($id_tarea_detalle);
        $id_usuario_tiempo = !empty($detalle->Id_usuario_asignado)
            ? (int)$detalle->Id_usuario_asignado
            : (int)$_SESSION['user_id'];
        $ok = $this->tableroModel->replaceDetalleTiempoTotal($id_tarea_detalle, $seconds, (int)$_SESSION['user_id'], $id_usuario_tiempo);
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo actualizar el tiempo manualmente'], 500);
        }

        $this->tableroModel->addHistorialTarjeta(
            (int)$tarjeta->Id_tarjeta,
            (int)$_SESSION['user_id'],
            'timer_detalle_manual',
            'Actualizo manualmente el cronometro de una tarea de ' . $this->formatSecondsToHms($totalAnterior) . ' a ' . $this->formatSecondsToHms($seconds) . '.',
            [
                'id_tarea_detalle' => $id_tarea_detalle,
                'segundos_anterior' => (int)$totalAnterior,
                'segundos_nuevo' => (int)$seconds
            ]
        );

        return $this->jsonResponse([
            'success' => true,
            'total_detalle_segundos' => $this->tableroModel->getTiempoTotalDetalle($id_tarea_detalle),
            'total_tarjeta_segundos' => $this->tableroModel->getTiempoTotalTarjeta((int)$tarjeta->Id_tarjeta),
            'en_curso_tiempo' => $this->tableroModel->tarjetaTieneTimerDetalleEnCurso((int)$tarjeta->Id_tarjeta)
        ]);
    }

    public function update_tarea_detalle_tiempo_manual_usuarios(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_tarea_detalle = isset($payload['id_tarea_detalle']) ? (int)$payload['id_tarea_detalle'] : 0;
        $updates = isset($payload['updates']) && is_array($payload['updates']) ? $payload['updates'] : [];

        if(!$this->hasBoardPermission($id_tablero, 'tarea_tiempo_editar')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_tarea_detalle <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle invalido'], 400);
        }

        if(empty($updates)){
            return $this->jsonResponse(['success' => false, 'error' => 'No se recibieron usuarios para editar'], 400);
        }

        $detalle = $this->tableroModel->getDetalleById($id_tarea_detalle);
        if(!$detalle){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle no encontrado'], 404);
        }

        $tarea = $this->tableroModel->getTareaById((int)$detalle->Id_tarea);
        $tarjeta = $tarea ? $this->tableroModel->getTarjetaById((int)$tarea->Id_tarjeta) : null;
        if(!$tarea || !$tarjeta || (int)$tarjeta->Id_tablero !== $id_tablero){
            return $this->jsonResponse(['success' => false, 'error' => 'Detalle fuera del tablero activo'], 403);
        }

        $id_usuario_asignado = !empty($detalle->Id_usuario_asignado) ? (int)$detalle->Id_usuario_asignado : null;
        $id_usuario_actual = (int)$_SESSION['user_id'];
        if($id_usuario_asignado !== null && $id_usuario_asignado !== $id_usuario_actual){
            $tienetiempoEnDetalle = false;
            $tiemposExistentesCheck = $this->tableroModel->getTiempoDetallePorUsuario($id_tarea_detalle);
            foreach($tiemposExistentesCheck as $_t){
                if((int)($_t->Id_usuario ?? 0) === $id_usuario_actual){
                    $tienetiempoEnDetalle = true;
                    break;
                }
            }
            if(!$tienetiempoEnDetalle && !isSupervisorOrJefeRol() && !isAdministradorRol()){
                return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso para editar manualmente el cronometro de otro usuario.'], 403);
            }
        }

        if($this->tableroModel->detalleTieneTimerEnCurso($id_tarea_detalle)){
            return $this->jsonResponse(['success' => false, 'error' => 'No se puede editar mientras el cronometro esta en curso.'], 400);
        }

        $tiemposActuales = $this->tableroModel->getTiempoDetallePorUsuario($id_tarea_detalle);
        $mapaFinal = [];
        foreach($tiemposActuales as $item){
            $uid = isset($item->Id_usuario) ? (int)$item->Id_usuario : 0;
            if($uid <= 0){
                continue;
            }

            $base = isset($item->Tiempo_total_segundos) ? (int)$item->Tiempo_total_segundos : 0;
            $running = isset($item->Tiempo_en_curso_segundos) ? (int)$item->Tiempo_en_curso_segundos : 0;
            $mapaFinal[$uid] = max(0, $base + $running);
        }

        if(empty($mapaFinal)){
            return $this->jsonResponse(['success' => false, 'error' => 'No hay tiempos por usuario para editar en este detalle.'], 400);
        }

        foreach($updates as $row){
            $uid = isset($row['id_usuario']) ? (int)$row['id_usuario'] : 0;
            $hms = trim((string)($row['tiempo_hms'] ?? ''));

            if($uid <= 0){
                continue;
            }

            if(!array_key_exists($uid, $mapaFinal)){
                return $this->jsonResponse(['success' => false, 'error' => 'Uno de los usuarios no pertenece al detalle de tiempo.'], 400);
            }

            $seconds = $this->parseDurationHmsToSeconds($hms);
            if($seconds === null){
                return $this->jsonResponse(['success' => false, 'error' => 'Formato invalido. Use hh:mm:ss.'], 400);
            }

            $mapaFinal[$uid] = (int)$seconds;
        }

        $totalAnterior = $this->tableroModel->getTiempoTotalDetalle($id_tarea_detalle);
        $ok = $this->tableroModel->replaceDetalleTiempoPorUsuarios($id_tarea_detalle, $mapaFinal);
        if(!$ok){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo actualizar el tiempo por usuario'], 500);
        }

        $totalNuevo = 0;
        foreach($mapaFinal as $seconds){
            $totalNuevo += max(0, (int)$seconds);
        }

        $this->tableroModel->addHistorialTarjeta(
            (int)$tarjeta->Id_tarjeta,
            (int)$_SESSION['user_id'],
            'timer_detalle_manual_usuarios',
            'Actualizo manualmente tiempos por usuario de una tarea de ' . $this->formatSecondsToHms($totalAnterior) . ' a ' . $this->formatSecondsToHms($totalNuevo) . '.',
            [
                'id_tarea_detalle' => $id_tarea_detalle,
                'segundos_anterior' => (int)$totalAnterior,
                'segundos_nuevo' => (int)$totalNuevo,
                'usuarios_actualizados' => count($updates)
            ]
        );

        return $this->jsonResponse([
            'success' => true,
            'total_detalle_segundos' => $this->tableroModel->getTiempoTotalDetalle($id_tarea_detalle),
            'total_tarjeta_segundos' => $this->tableroModel->getTiempoTotalTarjeta((int)$tarjeta->Id_tarjeta),
            'en_curso_tiempo' => $this->tableroModel->tarjetaTieneTimerDetalleEnCurso((int)$tarjeta->Id_tarjeta),
            'tiempo_por_usuario' => $this->tableroModel->getTiempoDetallePorUsuario($id_tarea_detalle)
        ]);
    }

    public function update_columna($id = null){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_numeric($id)){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_tablero = (int)($_POST['id_tablero'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $color  = trim($_POST['color']  ?? '#0d6efd');

        if(!$this->hasBoardPermission($id_tablero, 'columna_editar')){
            flashMessage('tablero_error', 'No tiene permisos para editar columnas de este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if(empty($nombre)){
            flashMessage('tablero_error', 'El nombre de la columna es obligatorio.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $columna = $this->tableroModel->getColumnaById((int)$id);
        if(!$columna || (int)$columna->Id_tablero !== $id_tablero){
            flashMessage('tablero_error', 'La columna no pertenece al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($this->tableroModel->updateColumna((int)$id, $nombre, $color)){
            flashMessage('tablero_message', 'Columna actualizada correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo actualizar la columna.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function delete_columna($id = null){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_numeric($id)){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $id_tablero = (int)($_POST['id_tablero'] ?? 0);

        if(!$this->hasBoardPermission($id_tablero, 'columna_eliminar')){
            flashMessage('tablero_error', 'No tiene permisos para eliminar columnas de este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $columna = $this->tableroModel->getColumnaById((int)$id);
        if(!$columna || (int)$columna->Id_tablero !== $id_tablero){
            flashMessage('tablero_error', 'La columna no pertenece al tablero activo.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $count = $this->tableroModel->countTarjetasActivasByColumna((int)$id);
        if($count > 0){
            flashMessage('tablero_error', 'No se puede eliminar la columna porque tiene tarjetas activas.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($this->tableroModel->deleteColumna((int)$id)){
            flashMessage('tablero_message', 'Columna eliminada correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo eliminar la columna.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    public function reorder_columnas(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            redirect('tablero/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $id_tablero = (int)($_POST['id_tablero'] ?? 0);
        $rawOrden = trim((string)($_POST['orden_columnas'] ?? ''));

        if(!$this->hasBoardPermission($id_tablero, 'columna_ordenar')){
            flashMessage('tablero_error', 'No tiene permisos para ordenar columnas en este tablero.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        if($id_tablero <= 0 || $rawOrden === ''){
            flashMessage('tablero_error', 'Datos invalidos para ordenar columnas.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $rawOrden)), function($v){
            return $v > 0;
        })));

        if(empty($ids)){
            flashMessage('tablero_error', 'No se recibio un orden valido de columnas.', 'danger');
            redirect('tablero/index?tablero_id=' . $id_tablero);
        }

        $ok = $this->tableroModel->reorderColumnas($id_tablero, $ids);
        if($ok){
            flashMessage('tablero_message', 'Orden de columnas actualizado correctamente.', 'success');
        } else {
            flashMessage('tablero_error', 'No se pudo actualizar el orden de columnas.', 'danger');
        }

        redirect('tablero/index?tablero_id=' . $id_tablero);
    }

    private function hasBoardPermission($id_tablero, $action){
        if($id_tablero <= 0){
            return false;
        }

        $permObj = $this->tableroModel->getPermisosUsuarioTablero((int)$id_tablero, (int)$_SESSION['user_id']);
        if(!$permObj){
            return false;
        }

        $perms = $this->buildBoardPermissionsArray($permObj);

        if($action === 'ver'){
            return !empty($perms['tablero_ver']);
        }
        if($action === 'crear'){
            return !empty($perms['tarjeta_crear']);
        }
        if($action === 'editar'){
            return !empty($perms['tarjeta_editar']);
        }
        if($action === 'eliminar'){
            return !empty($perms['tarjeta_eliminar']);
        }

        return !empty($perms[$action]);
    }

    private function extractEtiquetaIdsFromPost(){
        $raw = filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        if(!is_array($raw)){
            $raw = $_POST['etiquetas'] ?? [];
        }

        $ids = [];
        foreach((array)$raw as $id){
            $id = (int)$id;
            if($id > 0){
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function filterValidBoardLabelIds($id_tablero, $labelIds){
        $labelIds = array_values(array_unique(array_map('intval', is_array($labelIds) ? $labelIds : [])));
        if(empty($labelIds)){
            return [];
        }

        $allowed = [];
        foreach($this->tableroModel->getEtiquetasByTablero($id_tablero) as $etiqueta){
            $allowed[(int)$etiqueta->Id_etiqueta] = true;
        }

        $valid = [];
        foreach($labelIds as $id){
            if(isset($allowed[$id])){
                $valid[] = $id;
            }
        }

        return $valid;
    }

    private function buildLabelHistoryText($id_tablero, $labelIds){
        if(empty($labelIds)){
            return 'sin etiquetas';
        }

        $labelIds = array_values(array_unique(array_map('intval', $labelIds)));
        $namesById = [];
        foreach($this->tableroModel->getEtiquetasByTablero($id_tablero) as $etiqueta){
            $namesById[(int)$etiqueta->Id_etiqueta] = trim((string)($etiqueta->Nombre ?? ''));
        }

        $names = [];
        foreach($labelIds as $id){
            if(!isset($namesById[$id])){
                continue;
            }
            $names[] = $namesById[$id] !== '' ? $namesById[$id] : 'Sin texto';
        }

        return empty($names) ? 'sin etiquetas' : implode(', ', $names);
    }

    private function sanitizeColor($color, $default = '#0d6efd'){
        $color = trim((string)$color);
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : $default;
    }

    private function sanitizeOptionalDateInput($value, $enabled = true){
        if(!$enabled){
            return null;
        }

        $value = trim((string)$value);
        if($value === ''){
            return null;
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);
        if(!$date || $date->format('Y-m-d') !== $value){
            return null;
        }

        return $value;
    }

    private function parseDurationHmsToSeconds($value){
        $value = trim((string)$value);
        if(!preg_match('/^(\d{1,3}):([0-5]\d):([0-5]\d)$/', $value, $matches)){
            return null;
        }

        $hours = (int)$matches[1];
        $minutes = (int)$matches[2];
        $seconds = (int)$matches[3];
        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    private function formatSecondsToHms($total){
        $total = max(0, (int)$total);
        $hours = floor($total / 3600);
        $minutes = floor(($total % 3600) / 60);
        $seconds = $total % 60;
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    private function resolveTarjetaEstado($completado){
        return $completado ? 'Completado' : 'Pendiente';
    }

    private function getDefaultBoardLabels(){
        return [
            ['nombre' => 'Rojo', 'color' => '#dc3545'],
            ['nombre' => 'Verde', 'color' => '#198754'],
            ['nombre' => 'Azul', 'color' => '#0d6efd'],
            ['nombre' => 'Anaranjado', 'color' => '#fd7e14']
        ];
    }

    private function getDefaultBoardPriorities(){
        return [
            ['nombre' => 'ALTA', 'valor' => 10, 'color' => '#dc3545'],
            ['nombre' => 'MEDIA', 'valor' => 5, 'color' => '#fd7e14'],
            ['nombre' => 'BAJA', 'valor' => 1, 'color' => '#198754']
        ];
    }

    // ------------------------------------------------------------------
    // PLANTILLAS DE TARJETA
    // ------------------------------------------------------------------

    public function get_plantillas_tarjeta(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload    = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;

        if($id_tablero <= 0 || !$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        $plantillas = $this->tableroModel->getTarjetasPlantillas($id_tablero);
        // Incluir lista_ids asociadas a cada plantilla
        $asociaciones = $this->tableroModel->getTodasAsociacionesListasByTablero($id_tablero);
        $mapa = [];
        foreach($asociaciones as $a){
            $mapa[(int)$a->Id_plantilla_tarjeta][] = (int)$a->Id_plantilla_lista;
        }
        foreach($plantillas as &$p){
            $pid = (int)$p->Id_plantilla_tarjeta;
            $p->lista_ids = $mapa[$pid] ?? [];
        }
        unset($p);
        return $this->jsonResponse(['success' => true, 'plantillas' => $plantillas]);
    }

    public function create_plantilla_tarjeta(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload          = $this->getJsonInput();
        $id_tablero       = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $nombre_plantilla = trim($payload['nombre_plantilla'] ?? '');
        $titulo           = trim($payload['titulo'] ?? '');
        $descripcion      = trim($payload['descripcion'] ?? '');
        $lista_ids          = isset($payload['lista_ids']) && is_array($payload['lista_ids'])
                              ? array_values(array_filter(array_map('intval', $payload['lista_ids'])))
                              : [];
        $id_columna_defecto   = isset($payload['id_columna_defecto'])   && $payload['id_columna_defecto']   ? (int)$payload['id_columna_defecto']   : null;
        $id_prioridad_defecto = isset($payload['id_prioridad_defecto']) && $payload['id_prioridad_defecto'] ? (int)$payload['id_prioridad_defecto'] : null;

        if($id_tablero <= 0 || !$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($nombre_plantilla === '' || $titulo === ''){
            return $this->jsonResponse(['success' => false, 'error' => 'El nombre de plantilla y el titulo son obligatorios'], 400);
        }

        $id = $this->tableroModel->createTarjetaPlantilla(
            $nombre_plantilla,
            $titulo,
            $descripcion,
            (int)$_SESSION['user_id'],
            $id_tablero,
            $id_columna_defecto,
            $id_prioridad_defecto
        );

        if(!$id){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo guardar la plantilla'], 500);
        }

        if(!empty($lista_ids)){
            $this->tableroModel->setListasAsociadasPlantillaTarjeta($id, $lista_ids);
        }

        return $this->jsonResponse(['success' => true, 'id_plantilla_tarjeta' => $id]);
    }

    // ------------------------------------------------------------------
    // PLANTILLAS DE LISTA DE TAREAS
    // ------------------------------------------------------------------

    public function get_plantillas_lista(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload    = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;

        if($id_tablero <= 0 || !$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        $plantillas = $this->tableroModel->getTareasPlantillas($id_tablero);
        return $this->jsonResponse(['success' => true, 'plantillas' => $plantillas]);
    }

    public function create_plantilla_lista(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload          = $this->getJsonInput();
        $id_tablero       = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $nombre_plantilla = trim($payload['nombre_plantilla'] ?? '');
        $nombre_lista     = trim($payload['nombre_lista'] ?? '');
        $tareas           = isset($payload['tareas']) && is_array($payload['tareas']) ? $payload['tareas'] : [];

        if($id_tablero <= 0 || !$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($nombre_plantilla === '' || $nombre_lista === ''){
            return $this->jsonResponse(['success' => false, 'error' => 'El nombre de plantilla y el nombre del listado son obligatorios'], 400);
        }

        $id_plantilla_lista = $this->tableroModel->createTareasPlantilla(
            $nombre_plantilla,
            $nombre_lista,
            (int)$_SESSION['user_id'],
            $id_tablero
        );

        if(!$id_plantilla_lista){
            return $this->jsonResponse(['success' => false, 'error' => 'No se pudo guardar la plantilla de lista'], 500);
        }

        $orden = 0;
        foreach($tareas as $tarea){
            $desc = trim((string)($tarea['descripcion'] ?? ''));
            if($desc === '') continue;
            $this->tableroModel->addTareasPlantillaDetalle($id_plantilla_lista, $desc, $orden++);
        }

        return $this->jsonResponse(['success' => true, 'id_plantilla_lista' => (int)$id_plantilla_lista]);
    }

    public function get_plantilla_lista_detalle(){
        $this->verificarAcceso('tablero', 'ver');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }

        $payload            = $this->getJsonInput();
        $id_tablero         = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id_plantilla_lista = isset($payload['id_plantilla_lista']) ? (int)$payload['id_plantilla_lista'] : 0;

        if($id_tablero <= 0 || !$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }

        if($id_plantilla_lista <= 0){
            return $this->jsonResponse(['success' => false, 'error' => 'Plantilla invalida'], 400);
        }

        $plantilla = $this->tableroModel->getTareasPlantillaById($id_plantilla_lista, $id_tablero);
        if(!$plantilla){
            return $this->jsonResponse(['success' => false, 'error' => 'Plantilla no encontrada'], 404);
        }

        $detalles = $this->tableroModel->getTareasPlantillaDetalles($id_plantilla_lista);

        return $this->jsonResponse([
            'success'   => true,
            'plantilla' => [
                'Id_plantilla_lista' => (int)$plantilla->Id_plantilla_lista,
                'Nombre_plantilla'   => $plantilla->Nombre_plantilla,
                'Nombre_lista'       => $plantilla->Nombre_lista,
                'detalles'           => $detalles
            ]
        ]);
    }

    public function update_plantilla_tarjeta(){
        $this->verificarAcceso('tablero', 'ver');
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }
        $payload          = $this->getJsonInput();
        $id_tablero       = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id               = isset($payload['id_plantilla_tarjeta']) ? (int)$payload['id_plantilla_tarjeta'] : 0;
        $nombre_plantilla = trim($payload['nombre_plantilla'] ?? '');
        $titulo           = trim($payload['titulo'] ?? '');
        $descripcion      = trim($payload['descripcion'] ?? '');
        $lista_ids          = isset($payload['lista_ids']) && is_array($payload['lista_ids'])
                              ? array_values(array_filter(array_map('intval', $payload['lista_ids'])))
                              : [];
        $id_columna_defecto   = isset($payload['id_columna_defecto'])   && $payload['id_columna_defecto']   ? (int)$payload['id_columna_defecto']   : null;
        $id_prioridad_defecto = isset($payload['id_prioridad_defecto']) && $payload['id_prioridad_defecto'] ? (int)$payload['id_prioridad_defecto'] : null;

        if($id_tablero <= 0 || !$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }
        if($id <= 0 || $nombre_plantilla === '' || $titulo === ''){
            return $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos'], 400);
        }
        $ok = $this->tableroModel->updateTarjetaPlantilla($id, $id_tablero, $nombre_plantilla, $titulo, $descripcion, $id_columna_defecto, $id_prioridad_defecto);
        if(!$ok) return $this->jsonResponse(['success' => false, 'error' => 'No se pudo actualizar la plantilla'], 500);
        $this->tableroModel->setListasAsociadasPlantillaTarjeta($id, $lista_ids);
        return $this->jsonResponse(['success' => true]);
    }

    public function delete_plantilla_tarjeta(){
        $this->verificarAcceso('tablero', 'ver');
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }
        $payload    = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id         = isset($payload['id_plantilla_tarjeta']) ? (int)$payload['id_plantilla_tarjeta'] : 0;

        if($id_tablero <= 0 || !$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }
        if($id <= 0) return $this->jsonResponse(['success' => false, 'error' => 'ID invalido'], 400);
        $ok = $this->tableroModel->deleteTarjetaPlantilla($id, $id_tablero);
        if(!$ok) return $this->jsonResponse(['success' => false, 'error' => 'No se pudo eliminar la plantilla'], 500);
        return $this->jsonResponse(['success' => true]);
    }

    public function update_plantilla_lista(){
        $this->verificarAcceso('tablero', 'ver');
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }
        $payload          = $this->getJsonInput();
        $id_tablero       = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id               = isset($payload['id_plantilla_lista']) ? (int)$payload['id_plantilla_lista'] : 0;
        $nombre_plantilla = trim($payload['nombre_plantilla'] ?? '');
        $nombre_lista     = trim($payload['nombre_lista'] ?? '');
        $tareas           = isset($payload['tareas']) && is_array($payload['tareas']) ? $payload['tareas'] : [];

        if($id_tablero <= 0 || !$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }
        if($id <= 0 || $nombre_plantilla === '' || $nombre_lista === ''){
            return $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos'], 400);
        }
        $plantilla = $this->tableroModel->getTareasPlantillaById($id, $id_tablero);
        if(!$plantilla) return $this->jsonResponse(['success' => false, 'error' => 'Plantilla no encontrada'], 404);

        $ok = $this->tableroModel->updateTareasPlantilla($id, $id_tablero, $nombre_plantilla, $nombre_lista);
        if(!$ok) return $this->jsonResponse(['success' => false, 'error' => 'No se pudo actualizar la plantilla'], 500);

        $this->tableroModel->deleteAllTareasPlantillaDetallesByLista($id);
        $orden = 0;
        foreach($tareas as $tarea){
            $desc = trim((string)($tarea['descripcion'] ?? ''));
            if($desc === '') continue;
            $this->tableroModel->addTareasPlantillaDetalle($id, $desc, $orden++);
        }
        return $this->jsonResponse(['success' => true]);
    }

    public function delete_plantilla_lista(){
        $this->verificarAcceso('tablero', 'ver');
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            return $this->jsonResponse(['success' => false, 'error' => 'Metodo no permitido'], 405);
        }
        $payload    = $this->getJsonInput();
        $id_tablero = isset($payload['id_tablero']) ? (int)$payload['id_tablero'] : 0;
        $id         = isset($payload['id_plantilla_lista']) ? (int)$payload['id_plantilla_lista'] : 0;

        if($id_tablero <= 0 || !$this->hasBoardPermission($id_tablero, 'tablero_ver')){
            return $this->jsonResponse(['success' => false, 'error' => 'Sin permiso en este tablero'], 403);
        }
        if($id <= 0) return $this->jsonResponse(['success' => false, 'error' => 'ID invalido'], 400);
        $ok = $this->tableroModel->deleteTareasPlantilla($id, $id_tablero);
        if(!$ok) return $this->jsonResponse(['success' => false, 'error' => 'No se pudo eliminar la plantilla'], 500);
        return $this->jsonResponse(['success' => true]);
    }

    private function getJsonInput(){
        $raw = file_get_contents('php://input');
        if(empty($raw)){
            return $_POST;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : $_POST;
    }

    private function jsonResponse($payload, $status = 200){
        if(ob_get_level()){
            ob_end_clean();
        }
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}

