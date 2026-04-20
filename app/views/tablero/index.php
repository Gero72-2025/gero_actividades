<?php require APPROOT . '/views/layouts/header.php'; ?>
<?php
$tableroActual = $data['tableroActual'] ?? null;
$idTableroActual = $tableroActual ? (int)$tableroActual->Id_tablero : 0;
$etiquetasTablero = $data['etiquetas'] ?? [];
$prioridadesTablero = $data['prioridades'] ?? [];
$tableroDeleteSummary = $data['tableroDeleteSummary'] ?? (object)[
    'total_columnas' => 0,
    'total_tarjetas' => 0,
    'total_listas' => 0,
    'total_tareas' => 0
];
$canDeleteTableroData = !empty($data['canDeleteTablero']);
$usuariosAsignadosById = [];
if(!empty($data['usuariosAsignados'])){
    foreach($data['usuariosAsignados'] as $ua){
        $usuariosAsignadosById[(int)$ua->Id_usuario] = [
            'tablero_ver' => (int)($ua->Permiso_tablero_ver ?? 0) === 1,
            'tablero_crear' => (int)($ua->Permiso_tablero_crear ?? 0) === 1,
            'tablero_editar' => (int)($ua->Permiso_tablero_editar ?? 0) === 1,
            'tablero_eliminar' => (int)($ua->Permiso_tablero_eliminar ?? 0) === 1,
            'tablero_asignar' => (int)($ua->Permiso_tablero_asignar ?? 0) === 1,
            'columna_crear' => (int)($ua->Permiso_columna_crear ?? 0) === 1,
            'columna_editar' => (int)($ua->Permiso_columna_editar ?? 0) === 1,
            'columna_eliminar' => (int)($ua->Permiso_columna_eliminar ?? 0) === 1,
            'columna_ordenar' => (int)($ua->Permiso_columna_ordenar ?? 0) === 1,
            'tarjeta_ver' => (int)($ua->Permiso_tarjeta_ver ?? 0) === 1,
            'tarjeta_crear' => (int)($ua->Permiso_tarjeta_crear ?? 0) === 1,
            'tarjeta_editar' => (int)($ua->Permiso_tarjeta_editar ?? 0) === 1,
            'tarjeta_mover' => (int)($ua->Permiso_tarjeta_mover ?? $ua->Permiso_tarjeta_editar ?? 0) === 1,
            'tarjeta_eliminar' => (int)($ua->Permiso_tarjeta_eliminar ?? 0) === 1,
            'tarjeta_asignar' => (int)($ua->Permiso_tarjeta_asignar ?? 0) === 1,
            'lista_crear' => (int)($ua->Permiso_lista_crear ?? 0) === 1,
            'lista_editar' => (int)($ua->Permiso_lista_editar ?? 0) === 1,
            'lista_eliminar' => (int)($ua->Permiso_lista_eliminar ?? 0) === 1,
            'tarea_crear' => (int)($ua->Permiso_tarea_crear ?? 0) === 1,
            'tarea_editar' => (int)($ua->Permiso_tarea_editar ?? 0) === 1,
            'tarea_eliminar' => (int)($ua->Permiso_tarea_eliminar ?? 0) === 1,
            'tarea_tiempo_editar' => (int)($ua->Permiso_tarea_tiempo_editar ?? 0) === 1,
            'plantilla_tarjeta_crear' => (int)($ua->Permiso_plantilla_tarjeta_crear ?? 0) === 1,
            'plantilla_tarjeta_editar' => (int)($ua->Permiso_plantilla_tarjeta_editar ?? 0) === 1,
            'plantilla_tarjeta_eliminar' => (int)($ua->Permiso_plantilla_tarjeta_eliminar ?? 0) === 1,
            'plantilla_tarjeta_asociar' => (int)($ua->Permiso_plantilla_tarjeta_asociar ?? 0) === 1,
            'plantilla_lista_crear' => (int)($ua->Permiso_plantilla_lista_crear ?? 0) === 1,
            'plantilla_lista_editar' => (int)($ua->Permiso_plantilla_lista_editar ?? 0) === 1,
            'plantilla_lista_eliminar' => (int)($ua->Permiso_plantilla_lista_eliminar ?? 0) === 1,
            'legacy_ver' => (int)($ua->Permiso_ver ?? 0) === 1,
            'legacy_crear' => (int)($ua->Permiso_crear ?? 0) === 1,
            'legacy_editar' => (int)($ua->Permiso_editar ?? 0) === 1,
            'legacy_eliminar' => (int)($ua->Permiso_eliminar ?? 0) === 1
        ];
    }
}

$permTablero = $data['permisosTablero'] ?? [
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

$canDashboardGlobal = tienePermiso('tablero.dashboard');
$canCalendarioGlobal = tienePermiso('tablero.calendario');
$canReporteriaGlobal = tienePermiso('tablero.reporteria');

$canEditBoard = !empty($permTablero['tablero_editar']);
$canAssignBoard = !empty($permTablero['tablero_asignar']);
$canCreateCard = !empty($permTablero['tarjeta_crear']);
$canEditCard = !empty($permTablero['tarjeta_editar']);
$canMoveCard = !empty($permTablero['tarjeta_mover']);
$canMarkDone = !empty($permTablero['tablero_ver']);
$canAssign = !empty($permTablero['tarjeta_asignar']);
$canTrackTime = !empty($permTablero['tablero_ver']);
$canEditTime = !empty($permTablero['tarea_tiempo_editar']);
$canCreateList = !empty($permTablero['lista_crear']);
$canEditList = !empty($permTablero['lista_editar']);
$canDeleteList = !empty($permTablero['lista_eliminar']);
$canCreateTask = !empty($permTablero['tarea_crear']);
$canEditTask = !empty($permTablero['tarea_editar']);
$canDeleteTask = !empty($permTablero['tarea_eliminar']);
$canCreateColumn = !empty($permTablero['columna_crear']);
$canEditColumn = !empty($permTablero['columna_editar']);
$canDeleteColumn = !empty($permTablero['columna_eliminar']);
$canOrderColumn = !empty($permTablero['columna_ordenar']);
$canDeleteCard = !empty($permTablero['tarjeta_eliminar']);
$canCreateBoard = !empty($permTablero['tablero_crear']);
$canDeleteBoard = !empty($permTablero['tablero_eliminar']);
$canDeleteBoardFromModal = $canDeleteBoard && $canDeleteTableroData;
$canPlantillaTarjetaCrear    = !empty($permTablero['plantilla_tarjeta_crear']);
$canPlantillaTarjetaEditar   = !empty($permTablero['plantilla_tarjeta_editar']);
$canPlantillaTarjetaEliminar = !empty($permTablero['plantilla_tarjeta_eliminar']);
$canPlantillaTarjetaAsociar  = !empty($permTablero['plantilla_tarjeta_asociar']);
$canPlantillaListaCrear      = !empty($permTablero['plantilla_lista_crear']);
$canPlantillaListaEditar     = !empty($permTablero['plantilla_lista_editar']);
$canPlantillaListaEliminar   = !empty($permTablero['plantilla_lista_eliminar']);
$canGestorPlantillaTarjeta   = $canPlantillaTarjetaCrear || $canPlantillaTarjetaEditar || $canPlantillaTarjetaEliminar;
$canGestorPlantillaLista     = $canPlantillaListaCrear || $canPlantillaListaEditar || $canPlantillaListaEliminar;
?>

<div class="mb-3">
    <h1 class="m-0"><?php echo $data['title']; ?></h1>
</div>

<?php $tableroParam = $idTableroActual > 0 ? ('?tablero_id=' . $idTableroActual) : ''; ?>
<div class="card mb-3">
    <div class="card-body py-2">
        <ul class="nav nav-pills tablero-nav-tabs flex-wrap gap-2">
            <li class="nav-item">
                <a class="nav-link active" href="<?php echo URLROOT; ?>/tablero/index<?php echo $tableroParam; ?>">
                    <i class="bi bi-kanban"></i> Tablero
                </a>
            </li>
            <?php if($canDashboardGlobal): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo URLROOT; ?>/tablero/dashboard<?php echo $tableroParam; ?>">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard
                </a>
            </li>
            <?php endif; ?>
            <?php if($canCalendarioGlobal): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo URLROOT; ?>/tablero/calendario<?php echo $tableroParam; ?>">
                    <i class="bi bi-calendar3"></i> Calendario
                </a>
            </li>
            <?php endif; ?>
            <?php if($canReporteriaGlobal): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo URLROOT; ?>/tablero/reporteria<?php echo $tableroParam; ?>">
                    <i class="bi bi-table"></i> Reporte
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php echo displayFlashMessage('tablero_message'); ?>
<?php echo displayFlashMessage('tablero_error'); ?>

<?php if(!empty($data['tableros'])): ?>
    <div class="accordion mb-3" id="accordionTableroControl">
        <div class="card">
            <div class="card-header py-2 bg-info text-white" id="headingTableroControl">
                <button class="btn btn-link text-decoration-none p-0 w-100 text-left d-flex justify-content-between align-items-center text-white" type="button" data-toggle="collapse" data-target="#collapseTableroControl" aria-expanded="true" aria-controls="collapseTableroControl">
                    <span><i class="bi bi-sliders"></i> Tablero Activo y Controles</span>
                    <span class="small text-white"><i class="bi bi-chevron-down"></i></span>
                </button>
            </div>
            <div id="collapseTableroControl" class="collapse show" aria-labelledby="headingTableroControl" data-parent="#accordionTableroControl">
                <div class="card-body">
                    <div class="mb-3 d-flex flex-wrap gap-2">
                        <?php if($canAssignBoard && $idTableroActual > 0): ?>
                            <button class="btn btn-outline-success" data-toggle="modal" data-target="#modalAsignarUsuarioTablero">
                                <i class="bi bi-person-plus"></i> Asignar Usuario al Tablero
                            </button>
                        <?php endif; ?>

                        <?php if($canCreateBoard): ?>
                            <button class="btn btn-outline-primary" data-toggle="modal" data-target="#modalCreateTablero">
                                <i class="bi bi-kanban"></i> Nuevo Tablero
                            </button>
                        <?php endif; ?>

                        <?php if($canEditBoard && $idTableroActual > 0): ?>
                            <button class="btn btn-outline-info" data-toggle="modal" data-target="#modalEditTablero">
                                <i class="bi bi-pencil-square"></i> Editar Tablero
                            </button>
                        <?php endif; ?>

                        <?php if($canCreateColumn && $idTableroActual > 0): ?>
                            <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#modalCreateColumna">
                                <i class="bi bi-layout-three-columns"></i> Nueva Columna
                            </button>
                        <?php endif; ?>

                        <?php if($canOrderColumn && $idTableroActual > 0): ?>
                            <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#modalOrdenarColumnas">
                                <i class="bi bi-arrow-left-right"></i> Ordenar Columnas
                            </button>
                        <?php endif; ?>

                        <?php if($canEditBoard && $idTableroActual > 0): ?>
                            <button class="btn btn-outline-dark" data-toggle="modal" data-target="#modalEtiquetasTablero">
                                <i class="bi bi-tags"></i> Etiquetas
                            </button>
                        <?php endif; ?>

                        <?php if($canEditBoard && $idTableroActual > 0): ?>
                            <button class="btn btn-outline-warning" data-toggle="modal" data-target="#modalPrioridadesTablero">
                                <i class="bi bi-flag"></i> Prioridades
                            </button>
                        <?php endif; ?>

                        <?php if($canCreateCard && $idTableroActual > 0): ?>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalCreateTarjeta">
                                <i class="bi bi-plus-circle"></i> Nueva Tarjeta
                            </button>
                        <?php endif; ?>

                        <?php if($idTableroActual > 0 && ($canGestorPlantillaTarjeta || $canGestorPlantillaLista)): ?>
                            <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#modalTipoPlantilla">
                                <i class="bi bi-file-earmark-text"></i> Plantillas
                            </button>
                        <?php endif; ?>
                    </div>

                    <form action="<?php echo URLROOT; ?>/tablero/index" method="get" class="row g-2 align-items-end mb-3" id="formTableroActivo">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Tablero activo</label>
                            <?php if(count($data['tableros']) > 1): ?>
                                <select name="tablero_id" class="form-select tablero-activo-select" onchange="this.form.submit()">
                                    <?php foreach($data['tableros'] as $tb): ?>
                                        <option value="<?php echo (int)$tb->Id_tablero; ?>" <?php echo ((int)$tb->Id_tablero === $idTableroActual) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tb->Nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['tableros'][0]->Nombre ?? 'Tablero'); ?>" readonly>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?php if($tableroActual): ?>
                                <label class="form-label">Descripcion</label>
                                <div class="form-control bg-light"><?php echo !empty($tableroActual->Descripcion) ? htmlspecialchars($tableroActual->Descripcion) : 'Sin descripcion'; ?></div>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if($idTableroActual > 0): ?>
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-5">
                                <label class="form-label">Filtrar por nombre de tarjeta</label>
                                <input type="text" class="form-control" id="filtroTarjetaNombre" placeholder="Escriba para buscar...">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">Filtrar por etiqueta</label>
                                <select class="form-select tablero-activo-select" id="filtroTarjetaEtiqueta">
                                    <option value="">Todas</option>
                                    <?php foreach($etiquetasTablero as $etiqueta): ?>
                                        <option value="<?php echo (int)$etiqueta->Id_etiqueta; ?>">
                                            <?php echo !empty($etiqueta->Nombre) ? htmlspecialchars($etiqueta->Nombre) : 'Sin texto'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label">Filtrar por prioridad</label>
                                <select class="form-select tablero-activo-select" id="filtroTarjetaPrioridad">
                                    <option value="">Todas</option>
                                    <?php foreach($prioridadesTablero as $prioridad): ?>
                                        <option value="<?php echo (int)$prioridad->Id_prioridad; ?>">
                                            <?php echo htmlspecialchars($prioridad->Nombre); ?> (<?php echo (int)$prioridad->Valor; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-1 d-grid">
                                <button type="button" class="btn btn-outline-secondary" id="btnLimpiarFiltrosTarjeta" title="Limpiar filtros">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                            <div class="col-12 col-md-auto d-flex align-items-end pb-1">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="switchMostrarArchivadas" role="switch">
                                    <label class="form-check-label text-muted small" for="switchMostrarArchivadas">Mostrar Archivadas</label>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">No tiene tableros asignados todavia.</div>
<?php endif; ?>

<?php if($idTableroActual > 0): ?>
<div class="tablero-interactive-shell" id="tableroInteractiveShell">
    <div class="tablero-scroll-sync tablero-scroll-sync-x tablero-scroll-sync--top js-tablero-scroll-x" aria-label="Barra de desplazamiento horizontal superior">
        <div class="tablero-scroll-sync-inner-x js-tablero-scroll-x-inner"></div>
    </div>

    <div class="tablero-content-band">
        <div class="tablero-wrapper pb-2">
            <div class="tablero-viewport js-tablero-viewport">
                <div class="d-flex gap-3 tablero-columns js-tablero-columns">
                    <?php foreach($data['columnas'] as $columna): ?>
                        <div class="card tablero-columna">
                <div class="card-header text-white" style="background: <?php echo htmlspecialchars($columna->Color); ?>;">
                    <div class="d-flex justify-content-between align-items-center gap-1">
                        <div class="d-flex align-items-center gap-1 flex-grow-1 overflow-hidden">
                            <strong class="text-truncate"><?php echo htmlspecialchars($columna->Nombre); ?></strong>
                            <?php if($canEditColumn): ?>
                                <button type="button"
                                    class="btn btn-sm btn-link p-0 lh-1 text-white btn-rename-columna"
                                    data-toggle="modal"
                                    data-target="#modalRenameColumna"
                                    data-columna-id="<?php echo (int)$columna->Id_columna; ?>"
                                    data-columna-nombre="<?php echo htmlspecialchars($columna->Nombre, ENT_QUOTES); ?>"
                                    data-columna-color="<?php echo htmlspecialchars($columna->Color, ENT_QUOTES); ?>"
                                    title="Renombrar columna">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <?php $tarjetasEnColumna = count($data['tarjetasPorColumna'][$columna->Id_columna] ?? []); ?>
                            <span class="badge bg-light text-dark"><?php echo $tarjetasEnColumna; ?></span>
                            <?php if($canDeleteColumn): ?>
                                <button type="button"
                                    class="btn btn-sm btn-link p-0 lh-1 text-white btn-delete-columna<?php echo $tarjetasEnColumna > 0 ? ' disabled' : ''; ?>"
                                    <?php if($tarjetasEnColumna === 0): ?>
                                        data-toggle="modal"
                                        data-target="#modalDeleteColumna"
                                        data-columna-id="<?php echo (int)$columna->Id_columna; ?>"
                                        data-columna-nombre="<?php echo htmlspecialchars($columna->Nombre, ENT_QUOTES); ?>"
                                        title="Eliminar columna"
                                    <?php else: ?>
                                        aria-disabled="true"
                                        tabindex="-1"
                                        title="No se puede eliminar: la columna tiene tarjetas activas"
                                    <?php endif; ?>>
                                    <i class="bi bi-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2 tablero-card-list" data-columna-id="<?php echo (int)$columna->Id_columna; ?>" data-tablero-id="<?php echo $idTableroActual; ?>">
                    <?php if(!empty($data['tarjetasPorColumna'][$columna->Id_columna])): ?>
                        <?php foreach($data['tarjetasPorColumna'][$columna->Id_columna] as $tarjeta): ?>
                            <div
                                id="tarjeta-<?php echo (int)$tarjeta->Id_tarjeta; ?>"
                                class="card mb-2 tablero-tarjeta <?php echo !empty($tarjeta->Completado) ? 'tablero-tarjeta--completada' : ''; ?> <?php echo !empty($tarjeta->Archivada) ? 'tablero-tarjeta--archivada' : ''; ?>"
                                data-tarjeta-id="<?php echo (int)$tarjeta->Id_tarjeta; ?>"
                                data-tarjeta-completado="<?php echo !empty($tarjeta->Completado) ? '1' : '0'; ?>"
                                data-tarjeta-archivada="<?php echo !empty($tarjeta->Archivada) ? '1' : '0'; ?>"
                                data-tarjeta-estado="<?php echo htmlspecialchars($tarjeta->Estado_tarjeta ?? 'Pendiente', ENT_QUOTES, 'UTF-8'); ?>"
                                data-prioridad-id="<?php echo !empty($tarjeta->Id_prioridad) ? (int)$tarjeta->Id_prioridad : ''; ?>"
                                data-etiqueta-ids="<?php echo htmlspecialchars(implode(',', $tarjeta->EtiquetaIds ?? []), ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <h6 class="card-title mb-0"><?php echo htmlspecialchars($tarjeta->Titulo); ?></h6>
                                        <?php if($canEditCard): ?>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-link p-0 text-primary btn-edit-tarjeta"
                                                data-toggle="modal"
                                                data-target="#modalEditTarjeta"
                                                data-tarjeta-id="<?php echo (int)$tarjeta->Id_tarjeta; ?>"
                                                data-tarjeta-titulo="<?php echo htmlspecialchars($tarjeta->Titulo, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-tarjeta-descripcion="<?php echo htmlspecialchars($tarjeta->Descripcion ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-columna-id="<?php echo (int)$tarjeta->Id_columna; ?>"
                                                data-alcance-id="<?php echo !empty($tarjeta->Id_alcance) ? (int)$tarjeta->Id_alcance : ''; ?>"
                                                data-actividad-id="<?php echo !empty($tarjeta->Id_actividad) ? (int)$tarjeta->Id_actividad : ''; ?>"
                                                data-usuario-asignado-id="<?php echo !empty($tarjeta->Id_usuario_asignado) ? (int)$tarjeta->Id_usuario_asignado : ''; ?>"
                                                data-prioridad-id="<?php echo !empty($tarjeta->Id_prioridad) ? (int)$tarjeta->Id_prioridad : ''; ?>"
                                                data-fecha-inicio="<?php echo !empty($tarjeta->Fecha_inicio) ? htmlspecialchars($tarjeta->Fecha_inicio, ENT_QUOTES, 'UTF-8') : ''; ?>"
                                                data-fecha-fin="<?php echo !empty($tarjeta->Fecha_fin) ? htmlspecialchars($tarjeta->Fecha_fin, ENT_QUOTES, 'UTF-8') : ''; ?>"
                                                data-tarjeta-completado="<?php echo !empty($tarjeta->Completado) ? '1' : '0'; ?>"
                                                data-tarjeta-archivada="<?php echo !empty($tarjeta->Archivada) ? '1' : '0'; ?>"
                                                data-can-delete="<?php echo !empty($tarjeta->Can_Delete) ? '1' : '0'; ?>"
                                                data-etiqueta-ids="<?php echo htmlspecialchars(implode(',', $tarjeta->EtiquetaIds ?? []), ENT_QUOTES, 'UTF-8'); ?>"
                                                title="Editar tarjeta"
                                            >
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php if(!empty($tarjeta->Descripcion)): ?>
                                        <p class="card-text text-muted mb-2"><?php echo nl2br(htmlspecialchars($tarjeta->Descripcion)); ?></p>
                                    <?php endif; ?>

                                    <div class="tarjeta-completado-row mb-2">
                                        <label class="form-check mb-0 d-flex align-items-center gap-2 tarjeta-completado-wrapper">
                                            <input
                                                class="form-check-input tarjeta-completado-toggle"
                                                type="checkbox"
                                                data-tarjeta-id="<?php echo (int)$tarjeta->Id_tarjeta; ?>"
                                                <?php echo !empty($tarjeta->Completado) ? 'checked' : ''; ?>
                                                <?php echo $canMarkDone ? '' : 'disabled'; ?>
                                            >
                                            <span class="small fw-semibold">Finalizada</span>
                                        </label>
                                        <span
                                            class="badge tarjeta-estado-badge <?php echo !empty($tarjeta->Completado) ? 'bg-success' : 'bg-secondary'; ?>"
                                            data-tarjeta-id="<?php echo (int)$tarjeta->Id_tarjeta; ?>"
                                        >
                                            <?php echo !empty($tarjeta->Completado) ? 'Completado' : 'Pendiente'; ?>
                                        </span>
                                    </div>

                                    <?php if(!empty($tarjeta->Fecha_inicio) || !empty($tarjeta->Fecha_fin)): ?>
                                        <div class="mb-2 small text-muted">
                                            <?php if(!empty($tarjeta->Fecha_inicio)): ?>
                                                <span class="badge border text-dark mr-1">
                                                    <i class="bi bi-calendar-event"></i> Inicio: <?php echo htmlspecialchars($tarjeta->Fecha_inicio); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if(!empty($tarjeta->Fecha_fin)): ?>
                                                <span class="badge border text-dark">
                                                    <i class="bi bi-calendar-check"></i> Fin: <?php echo htmlspecialchars($tarjeta->Fecha_fin); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($tarjeta->Prioridad_Nombre)): ?>
                                        <div class="mb-2">
                                            <span class="badge" style="background: <?php echo htmlspecialchars($tarjeta->Prioridad_Color ?? '#6c757d'); ?>; color:#fff;">
                                                <?php echo htmlspecialchars($tarjeta->Prioridad_Nombre); ?> (<?php echo (int)($tarjeta->Prioridad_Valor ?? 0); ?>)
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(!empty($tarjeta->Etiquetas)): ?>
                                        <div class="mb-2 d-flex flex-wrap gap-1">
                                            <?php foreach($tarjeta->Etiquetas as $etiqueta): ?>
                                                <span class="badge border" style="background: <?php echo htmlspecialchars($etiqueta->Color); ?>; color:#fff; min-width:24px;" title="<?php echo htmlspecialchars(!empty($etiqueta->Nombre) ? $etiqueta->Nombre : 'Sin texto'); ?>">
                                                    <?php if(!empty($etiqueta->Nombre)): ?>
                                                        <?php echo htmlspecialchars($etiqueta->Nombre); ?>
                                                    <?php else: ?>
                                                        &nbsp;
                                                    <?php endif; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mb-2">
                                        <button
                                            class="btn btn-sm btn-outline-primary btn-open-tareas"
                                            type="button"
                                            data-toggle="modal"
                                            data-target="#modalTarjetaTareas"
                                            data-tarjeta-id="<?php echo (int)$tarjeta->Id_tarjeta; ?>"
                                            data-tarjeta-titulo="<?php echo htmlspecialchars($tarjeta->Titulo, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-tarjeta-descripcion="<?php echo htmlspecialchars($tarjeta->Descripcion ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                            data-tarjeta-completado="<?php echo !empty($tarjeta->Completado) ? '1' : '0'; ?>"
                                        >
                                            <i class="bi bi-list-check"></i> Tareas
                                            <span class="ms-1 badge bg-primary-subtle text-primary border">
                                                <?php echo (int)($tarjeta->Total_Tareas_Completadas ?? 0); ?>/<?php echo (int)($tarjeta->Total_Tareas ?? 0); ?>
                                            </span>
                                        </button>
                                    </div>

                                    <?php if(!empty($tarjeta->AsignadosDetalle)): ?>
                                    <div class="mb-2">
                                        <div class="fw-bold small mb-1">Asignados</div>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach($tarjeta->AsignadosDetalle as $asignado): ?>
                                                <span class="badge bg-secondary-subtle text-secondary border small"><?php echo htmlspecialchars($asignado->Email, ENT_QUOTES, 'UTF-8'); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php $timerEnCurso = isset($tarjeta->Total_Timers_En_Curso) && (int)$tarjeta->Total_Timers_En_Curso > 0; ?>
                                    <div class="border rounded p-2 tarjeta-tiempo-box <?php echo $timerEnCurso ? 'tarjeta-tiempo-running' : 'tarjeta-tiempo-idle'; ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small fw-bold timer-status-label"><?php echo $timerEnCurso ? 'En Curso' : 'Tiempo total'; ?></span>
                                            <span class="badge timer-display" style="font-weight:700;min-width:78px;" data-tarjeta-id="<?php echo (int)$tarjeta->Id_tarjeta; ?>" data-base-seconds="<?php echo (int)$tarjeta->Tiempo_Total_Segundos; ?>" data-running="<?php echo $timerEnCurso ? '1' : '0'; ?>">
                                                00:00:00
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <small class="text-muted empty-column-msg">Sin tarjetas</small>
                    <?php endif; ?>
                </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="tablero-scroll-sync tablero-scroll-sync-x tablero-scroll-sync--bottom js-tablero-scroll-x" aria-label="Barra de desplazamiento horizontal inferior">
        <div class="tablero-scroll-sync-inner-x js-tablero-scroll-x-inner"></div>
    </div>

    <div class="tablero-edge-zone tablero-edge-zone--left" data-autoscroll-dir="left" aria-hidden="true" title="Mantenga el cursor para desplazarse a la izquierda"></div>
    <div class="tablero-edge-zone tablero-edge-zone--right" data-autoscroll-dir="right" aria-hidden="true" title="Mantenga el cursor para desplazarse a la derecha"></div>

</div>
<?php endif; ?>

<?php if($canCreateBoard): ?>
<div class="modal fade" id="modalCreateTablero" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nuevo Tablero</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="<?php echo URLROOT; ?>/tablero/create_tablero" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" maxlength="150" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripcion</label>
                        <textarea name="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($canEditBoard && $idTableroActual > 0): ?>
<div class="modal fade" id="modalEditTablero" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Tablero</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="<?php echo URLROOT; ?>/tablero/update_tablero/<?php echo $idTableroActual; ?>" method="post">
                <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del tablero</label>
                        <input
                            type="text"
                            name="nombre"
                            class="form-control"
                            maxlength="150"
                            value="<?php echo htmlspecialchars($tableroActual->Nombre ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripcion</label>
                        <textarea name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($tableroActual->Descripcion ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <?php if($canDeleteBoard && !$canDeleteBoardFromModal): ?>
                        <div class="alert alert-warning small mb-0">
                            Para habilitar <strong>Eliminar tablero</strong> debe estar totalmente vacio: sin columnas, sin tarjetas, sin listas y sin tareas.
                            <hr class="my-2">
                            <div>Columnas activas: <strong><?php echo (int)($tableroDeleteSummary->total_columnas ?? 0); ?></strong></div>
                            <div>Tarjetas activas: <strong><?php echo (int)($tableroDeleteSummary->total_tarjetas ?? 0); ?></strong></div>
                            <div>Listas activas: <strong><?php echo (int)($tableroDeleteSummary->total_listas ?? 0); ?></strong></div>
                            <div>Tareas activas: <strong><?php echo (int)($tableroDeleteSummary->total_tareas ?? 0); ?></strong></div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <?php if($canDeleteBoardFromModal): ?>
                        <button
                            type="button"
                            id="btnDeleteTableroModal"
                            class="btn btn-outline-danger mr-auto"
                        >
                            <i class="bi bi-trash"></i> Eliminar tablero
                        </button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($canDeleteBoardFromModal): ?>
<form id="formDeleteTablero" action="<?php echo URLROOT; ?>/tablero/delete_tablero/<?php echo $idTableroActual; ?>" method="post" class="d-none">
    <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
</form>
<?php endif; ?>

<?php if($canCreateColumn && $idTableroActual > 0): ?>
<div class="modal fade" id="modalCreateColumna" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="bi bi-layout-three-columns"></i> Nueva Columna</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="<?php echo URLROOT; ?>/tablero/create_columna" method="post">
                <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre de columna</label>
                        <input type="text" name="nombre" class="form-control" maxlength="120" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Color</label>
                        <input type="color" name="color" class="form-control form-control-color" value="#0d6efd">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Crear Columna</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($canOrderColumn && $idTableroActual > 0): ?>
<div class="modal fade" id="modalOrdenarColumnas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right"></i> Ordenar Columnas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="<?php echo URLROOT; ?>/tablero/reorder_columnas" method="post" id="formReorderColumnas">
                <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
                <input type="hidden" name="orden_columnas" id="inputOrdenColumnas" value="">
                <div class="modal-body">
                    <div class="alert alert-light border small mb-3">
                        Arrastre y suelte las columnas para definir el orden de visualizacion del tablero.
                    </div>
                    <div id="listaOrdenColumnas" class="list-group">
                        <?php foreach($data['columnas'] as $columna): ?>
                            <div class="list-group-item d-flex align-items-center gap-2 reorder-columna-item" data-columna-id="<?php echo (int)$columna->Id_columna; ?>">
                                <span class="text-muted" style="cursor:grab;"><i class="bi bi-grip-vertical"></i></span>
                                <span class="badge" style="background: <?php echo htmlspecialchars($columna->Color); ?>; color:#fff; min-width:14px;">&nbsp;</span>
                                <span class="flex-grow-1"><?php echo htmlspecialchars($columna->Nombre); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar orden</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($canAssignBoard && $idTableroActual > 0): ?>
<div class="modal fade" id="modalAsignarUsuarioTablero" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Asignar Usuario al Tablero</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="<?php echo URLROOT; ?>/tablero/assign_usuario_tablero" method="post">
                <div class="modal-body">
                    <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">

                    <div class="mb-3">
                        <label class="form-label">Tablero Activo</label>
                        <input type="text" class="form-control" value="<?php echo $idTableroActual; ?> - <?php echo $tableroActual ? htmlspecialchars($tableroActual->Nombre) : ''; ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <select name="id_usuario" class="form-select tablero-activo-select" required>
                            <option value="">Seleccione</option>
                            <?php foreach($data['usuarios'] as $usuario): ?>
                                <?php $permPayload = $usuariosAsignadosById[(int)$usuario->Id_usuario] ?? null; ?>
                                <option value="<?php echo (int)$usuario->Id_usuario; ?>" <?php echo $permPayload ? ('data-permisos="' . htmlspecialchars(json_encode($permPayload, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . '"') : ''; ?>><?php echo htmlspecialchars($usuario->email); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="border rounded bg-light p-3">
                        <label class="form-label d-block mb-2">Permisos del usuario en este tablero</label>

                        <div class="mb-3 d-none" id="modalGlobalSelectAllWrap">
                            <label class="mb-0 d-inline-flex align-items-center" style="cursor:pointer;">
                                <input class="mr-2" type="checkbox" id="modal_perm_select_all_global" value="1">
                                <span>Seleccionar todos los permisos</span>
                            </label>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Seccion 1: Tablero</h6>
                                <label class="mb-0 d-inline-flex align-items-center" style="cursor:pointer;">
                                    <input class="mr-2 js-select-all-section" type="checkbox" data-section="tablero" value="1">
                                    <span>Seleccionar todos</span>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tablero_ver" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tablero_ver" id="modal_perm_tablero_ver" data-perm-section="tablero" value="1" checked><span>Ver tablero</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tablero_crear" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tablero_crear" id="modal_perm_tablero_crear" data-perm-section="tablero" value="1"><span>Crear tablero</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tablero_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tablero_editar" id="modal_perm_tablero_editar" data-perm-section="tablero" value="1"><span>Editar tablero</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tablero_eliminar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tablero_eliminar" id="modal_perm_tablero_eliminar" data-perm-section="tablero" value="1"><span>Eliminar tablero</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tablero_asignar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tablero_asignar" id="modal_perm_tablero_asignar" data-perm-section="tablero" value="1"><span>Asignar usuarios a tablero</span></label></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Seccion 2: Columnas</h6>
                                <label class="mb-0 d-inline-flex align-items-center" style="cursor:pointer;">
                                    <input class="mr-2 js-select-all-section" type="checkbox" data-section="columna" value="1">
                                    <span>Seleccionar todos</span>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_columna_crear" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_columna_crear" id="modal_perm_columna_crear" data-perm-section="columna" value="1"><span>Crear columnas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_columna_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_columna_editar" id="modal_perm_columna_editar" data-perm-section="columna" value="1"><span>Editar columnas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_columna_eliminar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_columna_eliminar" id="modal_perm_columna_eliminar" data-perm-section="columna" value="1"><span>Eliminar columnas</span></label></div>
                                <div class="col-12 col-md-6"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_columna_ordenar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_columna_ordenar" id="modal_perm_columna_ordenar" data-perm-section="columna" value="1"><span>Ordenar columnas</span></label></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Seccion 3: Tarjetas</h6>
                                <label class="mb-0 d-inline-flex align-items-center" style="cursor:pointer;">
                                    <input class="mr-2 js-select-all-section" type="checkbox" data-section="tarjeta" value="1">
                                    <span>Seleccionar todos</span>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarjeta_ver" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarjeta_ver" id="modal_perm_tarjeta_ver" data-perm-section="tarjeta" value="1" checked><span>Ver tarjetas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarjeta_crear" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarjeta_crear" id="modal_perm_tarjeta_crear" data-perm-section="tarjeta" value="1"><span>Crear tarjetas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarjeta_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarjeta_editar" id="modal_perm_tarjeta_editar" data-perm-section="tarjeta" value="1"><span>Editar tarjetas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarjeta_mover" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarjeta_mover" id="modal_perm_tarjeta_mover" data-perm-section="tarjeta" value="1"><span>Mover Tarjetas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarjeta_eliminar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarjeta_eliminar" id="modal_perm_tarjeta_eliminar" data-perm-section="tarjeta" value="1"><span>Eliminar tarjetas</span></label></div>
                                <div class="col-12 col-md-6"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarjeta_asignar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarjeta_asignar" id="modal_perm_tarjeta_asignar" data-perm-section="tarjeta" value="1"><span>Asignar usuario a tarjeta</span></label></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Seccion 4: Lista de tareas</h6>
                                <label class="mb-0 d-inline-flex align-items-center" style="cursor:pointer;">
                                    <input class="mr-2 js-select-all-section" type="checkbox" data-section="lista" value="1">
                                    <span>Seleccionar todos</span>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_lista_crear" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_lista_crear" id="modal_perm_lista_crear" data-perm-section="lista" value="1"><span>Crear lista</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_lista_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_lista_editar" id="modal_perm_lista_editar" data-perm-section="lista" value="1"><span>Editar lista</span></label></div>
                                <div class="col-12 col-md-6"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_lista_eliminar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_lista_eliminar" id="modal_perm_lista_eliminar" data-perm-section="lista" value="1"><span>Eliminar lista</span></label></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Seccion 5: Tareas</h6>
                                <label class="mb-0 d-inline-flex align-items-center" style="cursor:pointer;">
                                    <input class="mr-2 js-select-all-section" type="checkbox" data-section="tarea" value="1">
                                    <span>Seleccionar todos</span>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarea_crear" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarea_crear" id="modal_perm_tarea_crear" data-perm-section="tarea" value="1"><span>Crear tareas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarea_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarea_editar" id="modal_perm_tarea_editar" data-perm-section="tarea" value="1"><span>Editar tareas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarea_eliminar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarea_eliminar" id="modal_perm_tarea_eliminar" data-perm-section="tarea" value="1"><span>Eliminar tareas</span></label></div>
                                <div class="col-12 col-md-6"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarea_tiempo_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarea_tiempo_editar" id="modal_perm_tarea_tiempo_editar" data-perm-section="tarea" value="1"><span>Editar tiempo en tareas</span></label></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Seccion 6: Plantillas de Tarjetas</h6>
                                <label class="mb-0 d-inline-flex align-items-center" style="cursor:pointer;">
                                    <input class="mr-2 js-select-all-section" type="checkbox" data-section="plantilla_tarjeta" value="1">
                                    <span>Seleccionar todos</span>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_plantilla_tarjeta_crear" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_plantilla_tarjeta_crear" id="modal_perm_plantilla_tarjeta_crear" data-perm-section="plantilla_tarjeta" value="1"><span>Crear plantillas de tarjeta</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_plantilla_tarjeta_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_plantilla_tarjeta_editar" id="modal_perm_plantilla_tarjeta_editar" data-perm-section="plantilla_tarjeta" value="1"><span>Editar plantillas de tarjeta</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_plantilla_tarjeta_eliminar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_plantilla_tarjeta_eliminar" id="modal_perm_plantilla_tarjeta_eliminar" data-perm-section="plantilla_tarjeta" value="1"><span>Eliminar plantillas de tarjeta</span></label></div>
                                <div class="col-12 col-md-6"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_plantilla_tarjeta_asociar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_plantilla_tarjeta_asociar" id="modal_perm_plantilla_tarjeta_asociar" data-perm-section="plantilla_tarjeta" value="1"><span>Asociar plantillas de tareas</span></label></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Seccion 7: Plantillas de Tareas</h6>
                                <label class="mb-0 d-inline-flex align-items-center" style="cursor:pointer;">
                                    <input class="mr-2 js-select-all-section" type="checkbox" data-section="plantilla_lista" value="1">
                                    <span>Seleccionar todos</span>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_plantilla_lista_crear" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_plantilla_lista_crear" id="modal_perm_plantilla_lista_crear" data-perm-section="plantilla_lista" value="1"><span>Crear plantillas de tareas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_plantilla_lista_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_plantilla_lista_editar" id="modal_perm_plantilla_lista_editar" data-perm-section="plantilla_lista" value="1"><span>Editar plantillas de tareas</span></label></div>
                                <div class="col-12 col-md-6"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_plantilla_lista_eliminar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_plantilla_lista_eliminar" id="modal_perm_plantilla_lista_eliminar" data-perm-section="plantilla_lista" value="1"><span>Eliminar plantillas de tareas</span></label></div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-2">Usuarios Ya Asignados y Permisos Activos</h6>
                    <?php if(!empty($data['usuariosAsignados'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Permisos Activos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($data['usuariosAsignados'] as $ua): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ua->email); ?></td>
                                            <td>
                                                <?php if((int)($ua->Permiso_tablero_ver ?? 0) === 1): ?><span class="badge bg-primary me-1">Tablero: Ver</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tablero_crear ?? 0) === 1): ?><span class="badge bg-primary me-1">Tablero: Crear</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tablero_editar ?? 0) === 1): ?><span class="badge bg-primary me-1">Tablero: Editar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tablero_eliminar ?? 0) === 1): ?><span class="badge bg-primary me-1">Tablero: Eliminar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tablero_asignar ?? 0) === 1): ?><span class="badge bg-primary me-1">Tablero: Asignar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_columna_crear ?? 0) === 1): ?><span class="badge bg-info text-dark me-1">Columnas: Crear</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_columna_editar ?? 0) === 1): ?><span class="badge bg-info text-dark me-1">Columnas: Editar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_columna_eliminar ?? 0) === 1): ?><span class="badge bg-info text-dark me-1">Columnas: Eliminar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_columna_ordenar ?? 0) === 1): ?><span class="badge bg-info text-dark me-1">Columnas: Ordenar</span><?php endif; ?>

                                                <?php if((int)($ua->Permiso_tarjeta_ver ?? 0) === 1): ?><span class="badge bg-success me-1">Tarjetas: Ver</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarjeta_crear ?? 0) === 1): ?><span class="badge bg-success me-1">Tarjetas: Crear</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarjeta_editar ?? 0) === 1): ?><span class="badge bg-success me-1">Tarjetas: Editar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarjeta_mover ?? 0) === 1): ?><span class="badge bg-success me-1">Tarjetas: Mover</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarjeta_eliminar ?? 0) === 1): ?><span class="badge bg-success me-1">Tarjetas: Eliminar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarjeta_asignar ?? 0) === 1): ?><span class="badge bg-success me-1">Tarjetas: Asignar</span><?php endif; ?>

                                                <?php if((int)($ua->Permiso_lista_crear ?? 0) === 1): ?><span class="badge bg-warning text-dark me-1">Listas: Crear</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_lista_editar ?? 0) === 1): ?><span class="badge bg-warning text-dark me-1">Listas: Editar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_lista_eliminar ?? 0) === 1): ?><span class="badge bg-warning text-dark me-1">Listas: Eliminar</span><?php endif; ?>

                                                <?php if((int)($ua->Permiso_tarea_crear ?? 0) === 1): ?><span class="badge bg-danger me-1">Tareas: Crear</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarea_editar ?? 0) === 1): ?><span class="badge bg-danger me-1">Tareas: Editar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarea_eliminar ?? 0) === 1): ?><span class="badge bg-danger me-1">Tareas: Eliminar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarea_tiempo_editar ?? 0) === 1): ?><span class="badge bg-danger me-1">Tareas: Tiempo</span><?php endif; ?>

                                                <?php if((int)($ua->Permiso_plantilla_tarjeta_crear ?? 0) === 1): ?><span class="badge bg-secondary me-1">Plant.Tarjeta: Crear</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_plantilla_tarjeta_editar ?? 0) === 1): ?><span class="badge bg-secondary me-1">Plant.Tarjeta: Editar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_plantilla_tarjeta_eliminar ?? 0) === 1): ?><span class="badge bg-secondary me-1">Plant.Tarjeta: Eliminar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_plantilla_tarjeta_asociar ?? 0) === 1): ?><span class="badge bg-secondary me-1">Plant.Tarjeta: Asociar</span><?php endif; ?>

                                                <?php if((int)($ua->Permiso_plantilla_lista_crear ?? 0) === 1): ?><span class="badge bg-dark me-1">Plant.Tareas: Crear</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_plantilla_lista_editar ?? 0) === 1): ?><span class="badge bg-dark me-1">Plant.Tareas: Editar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_plantilla_lista_eliminar ?? 0) === 1): ?><span class="badge bg-dark me-1">Plant.Tareas: Eliminar</span><?php endif; ?>

                                                <?php if((int)($ua->Permiso_tablero_ver ?? 0) !== 1 && (int)($ua->Permiso_tablero_crear ?? 0) !== 1 && (int)($ua->Permiso_tablero_editar ?? 0) !== 1 && (int)($ua->Permiso_tablero_eliminar ?? 0) !== 1 && (int)($ua->Permiso_tablero_asignar ?? 0) !== 1 && (int)($ua->Permiso_columna_crear ?? 0) !== 1 && (int)($ua->Permiso_columna_editar ?? 0) !== 1 && (int)($ua->Permiso_columna_eliminar ?? 0) !== 1 && (int)($ua->Permiso_columna_ordenar ?? 0) !== 1 && (int)($ua->Permiso_tarjeta_ver ?? 0) !== 1 && (int)($ua->Permiso_tarjeta_crear ?? 0) !== 1 && (int)($ua->Permiso_tarjeta_editar ?? 0) !== 1 && (int)($ua->Permiso_tarjeta_mover ?? 0) !== 1 && (int)($ua->Permiso_tarjeta_eliminar ?? 0) !== 1 && (int)($ua->Permiso_tarjeta_asignar ?? 0) !== 1 && (int)($ua->Permiso_lista_crear ?? 0) !== 1 && (int)($ua->Permiso_lista_editar ?? 0) !== 1 && (int)($ua->Permiso_lista_eliminar ?? 0) !== 1 && (int)($ua->Permiso_tarea_crear ?? 0) !== 1 && (int)($ua->Permiso_tarea_editar ?? 0) !== 1 && (int)($ua->Permiso_tarea_eliminar ?? 0) !== 1 && (int)($ua->Permiso_tarea_tiempo_editar ?? 0) !== 1 && (int)($ua->Permiso_plantilla_tarjeta_crear ?? 0) !== 1 && (int)($ua->Permiso_plantilla_tarjeta_editar ?? 0) !== 1 && (int)($ua->Permiso_plantilla_tarjeta_eliminar ?? 0) !== 1 && (int)($ua->Permiso_plantilla_tarjeta_asociar ?? 0) !== 1 && (int)($ua->Permiso_plantilla_lista_crear ?? 0) !== 1 && (int)($ua->Permiso_plantilla_lista_editar ?? 0) !== 1 && (int)($ua->Permiso_plantilla_lista_eliminar ?? 0) !== 1): ?>
                                                    <span class="text-muted">Sin permisos activos</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border mb-0">No hay usuarios asignados todavia a este tablero.</div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Asignar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($canEditBoard && $idTableroActual > 0): ?>
<div class="modal fade" id="modalEtiquetasTablero" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-tags"></i> Etiquetas del Tablero</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formEtiquetaTablero" action="<?php echo URLROOT; ?>/tablero/create_etiqueta" method="post" class="mb-4">
                    <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Texto de etiqueta (opcional)</label>
                            <input type="text" name="nombre" id="inputEtiquetaNombre" class="form-control" maxlength="120" placeholder="Ej. Prioridad alta">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Color</label>
                            <input type="color" name="color" id="inputEtiquetaColor" class="form-control form-control-color" value="#0d6efd" required>
                        </div>
                        <div class="col-12 col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1" id="btnGuardarEtiquetaTablero">Guardar</button>
                            <button type="button" class="btn btn-outline-secondary d-none" id="btnCancelarEdicionEtiqueta">Cancelar</button>
                        </div>
                    </div>
                </form>

                <?php if(!empty($etiquetasTablero)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:90px;">Vista</th>
                                    <th>Texto</th>
                                    <th style="width:130px;">Tarjetas</th>
                                    <th class="text-end" style="width:180px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($etiquetasTablero as $etiqueta): ?>
                                    <tr>
                                        <td>
                                            <span class="badge border" style="background: <?php echo htmlspecialchars($etiqueta->Color); ?>; color:#fff; min-width:48px;">
                                                <?php echo !empty($etiqueta->Nombre) ? htmlspecialchars($etiqueta->Nombre) : '&nbsp;'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo !empty($etiqueta->Nombre) ? htmlspecialchars($etiqueta->Nombre) : '<span class="text-muted">Sin texto</span>'; ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo (int)($etiqueta->Total_tarjetas ?? 0); ?></span></td>
                                        <td class="text-end">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary btn-edit-etiqueta"
                                                data-id="<?php echo (int)$etiqueta->Id_etiqueta; ?>"
                                                data-nombre="<?php echo htmlspecialchars($etiqueta->Nombre ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-color="<?php echo htmlspecialchars($etiqueta->Color, ENT_QUOTES, 'UTF-8'); ?>"
                                            >
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger btn-delete-etiqueta"
                                                data-id="<?php echo (int)$etiqueta->Id_etiqueta; ?>"
                                                data-nombre="<?php echo htmlspecialchars(!empty($etiqueta->Nombre) ? $etiqueta->Nombre : 'Sin texto', ENT_QUOTES, 'UTF-8'); ?>"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border mb-0">Este tablero todavia no tiene etiquetas creadas.</div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<form id="formDeleteEtiqueta" action="" method="post" class="d-none">
    <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
</form>
<?php endif; ?>

<?php if($canEditBoard && $idTableroActual > 0): ?>
<div class="modal fade" id="modalPrioridadesTablero" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-flag"></i> Prioridades del Tablero</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="formPrioridadTablero" action="<?php echo URLROOT; ?>/tablero/create_prioridad" method="post" class="mb-4">
                    <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="inputPrioridadNombre" class="form-control" maxlength="80" placeholder="ALTA" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label">Valor</label>
                            <input type="number" name="valor" id="inputPrioridadValor" class="form-control" min="1" step="1" value="1" required>
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label">Color</label>
                            <input type="color" name="color" id="inputPrioridadColor" class="form-control form-control-color" value="#6c757d" required>
                        </div>
                        <div class="col-12 col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1" id="btnGuardarPrioridadTablero">Guardar</button>
                            <button type="button" class="btn btn-outline-secondary d-none" id="btnCancelarEdicionPrioridad">Cancelar</button>
                        </div>
                    </div>
                </form>

                <?php if(!empty($prioridadesTablero)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th style="width:120px;">Valor</th>
                                    <th style="width:90px;">Color</th>
                                    <th style="width:130px;">Tarjetas</th>
                                    <th class="text-end" style="width:180px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($prioridadesTablero as $prioridad): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($prioridad->Nombre); ?></strong></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo (int)$prioridad->Valor; ?></span></td>
                                        <td><span class="badge" style="background:<?php echo htmlspecialchars($prioridad->Color); ?>;color:#fff;">&nbsp;&nbsp;&nbsp;</span></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo (int)($prioridad->Total_tarjetas ?? 0); ?></span></td>
                                        <td class="text-end">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary btn-edit-prioridad"
                                                data-id="<?php echo (int)$prioridad->Id_prioridad; ?>"
                                                data-nombre="<?php echo htmlspecialchars($prioridad->Nombre, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-valor="<?php echo (int)$prioridad->Valor; ?>"
                                                data-color="<?php echo htmlspecialchars($prioridad->Color, ENT_QUOTES, 'UTF-8'); ?>"
                                            >
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger btn-delete-prioridad"
                                                data-id="<?php echo (int)$prioridad->Id_prioridad; ?>"
                                                data-nombre="<?php echo htmlspecialchars($prioridad->Nombre, ENT_QUOTES, 'UTF-8'); ?>"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border mb-0">Este tablero todavia no tiene prioridades creadas.</div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<form id="formDeletePrioridad" action="" method="post" class="d-none">
    <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
</form>
<?php endif; ?>

<?php if($idTableroActual > 0): ?>
<!-- ===== MODAL: Seleccionar tipo de plantilla ===== -->
<div class="modal fade" id="modalTipoPlantilla" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text"></i> Plantillas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <?php if($canPlantillaTarjetaCrear || $canPlantillaListaCrear): ?>
                <p class="mb-3 text-muted">Seleccione el tipo de plantilla que desea crear:</p>
                <div class="d-flex flex-column gap-3">
                    <?php if($canPlantillaTarjetaCrear): ?>
                    <button type="button" class="btn btn-outline-primary btn-lg" id="btnAbrirPlantillaTarjeta" data-dismiss="modal">
                        <i class="bi bi-kanban"></i> Plantilla de Tarjeta
                        <small class="d-block text-muted mt-1" style="font-size:0.8em;">Guarda titulo y descripcion predeterminados para crear tarjetas rapidamente</small>
                    </button>
                    <?php endif; ?>
                    <?php if($canPlantillaListaCrear): ?>
                    <button type="button" class="btn btn-outline-success btn-lg" id="btnAbrirPlantillaLista" data-dismiss="modal">
                        <i class="bi bi-list-task"></i> Plantilla de Listado de Tareas
                        <small class="d-block text-muted mt-1" style="font-size:0.8em;">Guarda un listado de tareas sin asignar para aplicarlo a cualquier tarjeta</small>
                    </button>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-3">
                    <i class="bi bi-lock text-muted" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0 text-muted">No tiene permisos para crear plantillas.</p>
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: Crear plantilla de tarjeta ===== -->
<div class="modal fade" id="modalCrearPlantillaTarjeta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-kanban"></i> Nueva plantilla de tarjeta</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nombre de la plantilla <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputPlantillaTarjetaNombre" maxlength="150" placeholder="Ej: Tarjeta de revision semanal">
                        <small class="text-muted">Identificador de la plantilla en los desplegables.</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Titulo predeterminado <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputPlantillaTarjetaTitulo" maxlength="180" placeholder="Titulo que se pre-rellenara al usar la plantilla">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripcion predeterminada</label>
                        <textarea class="form-control" id="inputPlantillaTarjetaDescripcion" rows="4" placeholder="Descripcion opcional..."></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Columna predeterminada <small class="text-muted">(opcional)</small></label>
                        <select class="form-control" id="selectPlantillaTarjetaColumna">
                            <option value="">Sin columna predeterminada</option>
                            <?php foreach($data['columnas'] as $col): ?>
                                <option value="<?php echo (int)$col->Id_columna; ?>"><?php echo htmlspecialchars($col->Nombre); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Al aplicar la plantilla se pre-seleccionara esta columna.</small>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Prioridad predeterminada <small class="text-muted">(opcional)</small></label>
                        <select class="form-control" id="selectPlantillaTarjetaPrioridad">
                            <option value="">Sin prioridad predeterminada</option>
                            <?php foreach($prioridadesTablero as $p): ?>
                                <option value="<?php echo (int)$p->Id_prioridad; ?>" style="color:<?php echo htmlspecialchars($p->Color); ?>"><?php echo htmlspecialchars($p->Nombre); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Al aplicar la plantilla se pre-seleccionara esta prioridad.</small>
                    </div>
                    <?php if($canPlantillaTarjetaAsociar): ?>
                    <div class="col-12">
                        <label class="form-label"><i class="bi bi-list-task mr-1"></i>Plantillas de tareas asociadas <small class="text-muted">(opcional)</small></label>
                        <div id="checkboxListasAsociarCrear" class="border rounded p-2" style="max-height:160px;overflow-y:auto;background:#f8f9fa;">
                            <span class="text-muted small">Sin plantillas de listado disponibles para este tablero.</span>
                        </div>
                        <small class="text-muted d-block mt-1">Al usar esta plantilla, se crear&aacute;n autom&aacute;ticamente los listados seleccionados.</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarPlantillaTarjeta">
                    <i class="bi bi-save"></i> Guardar plantilla
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: Crear plantilla de listado de tareas ===== -->
<div class="modal fade" id="modalCrearPlantillaLista" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-list-task"></i> Nueva plantilla de listado de tareas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nombre de la plantilla <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputPlantillaListaNombrePlantilla" maxlength="150" placeholder="Ej: Checklist de entrega">
                        <small class="text-muted">Identificador de la plantilla en los desplegables.</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nombre del listado <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputPlantillaListaNombreLista" maxlength="180" placeholder="Ej: Pasos de revision">
                        <small class="text-muted">Nombre que tendra la lista al aplicarse a una tarjeta.</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Tareas del listado (sin asignar)</label>
                        <div id="contenedorTareasPlantillaLista" class="mb-2"></div>
                        <div class="input-group">
                            <input type="text" class="form-control" id="inputNuevaTareaPlantillaLista" maxlength="255" placeholder="Descripcion de la tarea...">
                            <button class="btn btn-outline-success" type="button" id="btnAgregarTareaPlantillaLista">
                                <i class="bi bi-plus"></i> Agregar
                            </button>
                        </div>
                        <small class="text-muted mt-1 d-block">Agregue todas las tareas que debe contener este listado.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarPlantillaLista">
                    <i class="bi bi-save"></i> Guardar plantilla
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: Confirmacion eliminar plantilla ===== -->
<div class="modal fade" id="modalConfirmarEliminarPlantilla" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash mr-1"></i> Eliminar plantilla</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Está seguro que desea eliminar la plantilla <strong id="confirmarEliminarPlantillaNombre"></strong>? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminarPlantilla">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: Gestor de plantillas de tarjeta ===== -->
<div class="modal fade" id="modalGestorPlantillasTarjeta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-kanban"></i> Plantillas de Tarjeta</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="gestorListaPlantillasTarjeta"></div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <?php if($canPlantillaTarjetaCrear): ?>
                <button type="button" class="btn btn-primary" id="btnNuevaPlantillaTarjeta">
                    <i class="bi bi-plus-lg"></i> Nueva plantilla
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: Gestor de plantillas de listado ===== -->
<div class="modal fade" id="modalGestorPlantillasLista" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-list-task"></i> Plantillas de Listado de Tareas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="gestorListaPlantillasLista"></div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <?php if($canPlantillaListaCrear): ?>
                <button type="button" class="btn btn-success" id="btnNuevaPlantillaLista">
                    <i class="bi bi-plus-lg"></i> Nueva plantilla
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: Editar plantilla de tarjeta ===== -->
<div class="modal fade" id="modalEditarPlantillaTarjeta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-fill"></i> Editar plantilla de tarjeta</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="inputEditarPlantillaTarjetaId">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nombre de la plantilla <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputEditarPlantillaTarjetaNombre" maxlength="150">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Titulo predeterminado <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputEditarPlantillaTarjetaTitulo" maxlength="180">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descripcion predeterminada</label>
                        <textarea class="form-control" id="inputEditarPlantillaTarjetaDescripcion" rows="4"></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Columna predeterminada <small class="text-muted">(opcional)</small></label>
                        <select class="form-control" id="selectEditarPlantillaTarjetaColumna">
                            <option value="">Sin columna predeterminada</option>
                            <?php foreach($data['columnas'] as $col): ?>
                                <option value="<?php echo (int)$col->Id_columna; ?>"><?php echo htmlspecialchars($col->Nombre); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Al aplicar la plantilla se pre-seleccionara esta columna.</small>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Prioridad predeterminada <small class="text-muted">(opcional)</small></label>
                        <select class="form-control" id="selectEditarPlantillaTarjetaPrioridad">
                            <option value="">Sin prioridad predeterminada</option>
                            <?php foreach($prioridadesTablero as $p): ?>
                                <option value="<?php echo (int)$p->Id_prioridad; ?>" style="color:<?php echo htmlspecialchars($p->Color); ?>"><?php echo htmlspecialchars($p->Nombre); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Al aplicar la plantilla se pre-seleccionara esta prioridad.</small>
                    </div>
                    <?php if($canPlantillaTarjetaAsociar): ?>
                    <div class="col-12">
                        <label class="form-label"><i class="bi bi-list-task mr-1"></i>Plantillas de tareas asociadas <small class="text-muted">(opcional)</small></label>
                        <div id="checkboxListasAsociarEditar" class="border rounded p-2" style="max-height:160px;overflow-y:auto;background:#f8f9fa;">
                            <span class="text-muted small">Sin plantillas de listado disponibles.</span>
                        </div>
                        <small class="text-muted d-block mt-1">Al usar esta plantilla, se crear&aacute;n autom&aacute;ticamente los listados seleccionados.</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarEditarPlantillaTarjeta">
                    <i class="bi bi-save"></i> Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL: Editar plantilla de listado ===== -->
<div class="modal fade" id="modalEditarPlantillaLista" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-fill"></i> Editar plantilla de listado</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="inputEditarPlantillaListaId">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nombre de la plantilla <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputEditarPlantillaListaNombrePlantilla" maxlength="150">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nombre del listado <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputEditarPlantillaListaNombreLista" maxlength="180">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Tareas del listado</label>
                        <div id="contenedorTareasEditarPlantillaLista" class="mb-2"></div>
                        <div class="input-group">
                            <input type="text" class="form-control" id="inputNuevaTareaEditarPlantillaLista" maxlength="255" placeholder="Descripcion de la tarea...">
                            <button class="btn btn-outline-success" type="button" id="btnAgregarTareaEditarPlantillaLista">
                                <i class="bi bi-plus"></i> Agregar
                            </button>
                        </div>
                        <small class="text-muted mt-1 d-block">Las tareas existentes se reemplazaran con esta lista al guardar.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarEditarPlantillaLista">
                    <i class="bi bi-save"></i> Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($canCreateCard && $idTableroActual > 0): ?>
<div class="modal fade" id="modalCreateTarjeta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white" style="position:relative;min-height:54px;">
                <h5 class="modal-title mb-0"><i class="bi bi-kanban mr-1"></i> Crear Tarjeta</h5>
                <div class="dropdown" style="position:absolute;left:50%;transform:translateX(-50%);z-index:1;">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="dropdownBtnPlantillasTarjeta" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-file-earmark-text"></i> Desde Plantilla
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" id="dropdownMenuPlantillasTarjeta" aria-labelledby="dropdownBtnPlantillasTarjeta">
                        <span class="dropdown-item text-muted">Cargando...</span>
                    </div>
                </div>
                <button type="button" onclick="$('#modalCreateTarjeta').modal('hide')"
                    style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);z-index:20;background:none;border:none;color:#fff;font-size:1.5rem;line-height:1;cursor:pointer;padding:.25rem .5rem;pointer-events:auto;">
                    &times;
                </button>
            </div>
            <form action="<?php echo URLROOT; ?>/tablero/create_tarjeta" method="post">
                <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
                <input type="hidden" name="plantilla_listas_ids" id="hiddenPlantillaListasIds" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Columna</label>
                            <select name="id_columna" id="createTarjetaColumna" class="form-select tablero-activo-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach($data['columnas'] as $col): ?>
                                    <option value="<?php echo (int)$col->Id_columna; ?>"><?php echo htmlspecialchars($col->Nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Alcance (opcional)</label>
                            <select name="id_alcance" id="createTarjetaAlcance" class="form-select tablero-activo-select">
                                <option value="">Sin alcance</option>
                                <?php if(!empty($data['alcancesAgrupados'])): ?>
                                    <?php foreach($data['alcancesAgrupados'] as $grupo): ?>
                                        <optgroup label="<?php echo htmlspecialchars($grupo['label']); ?>">
                                            <?php foreach(($grupo['items'] ?? []) as $alc): ?>
                                                <option value="<?php echo (int)$alc->Id_alcance; ?>" data-contrato-id="<?php echo !empty($alc->Id_contrato) ? (int)$alc->Id_contrato : ''; ?>"><?php echo htmlspecialchars(substr($alc->Descripcion, 0, 90)); ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay alcances disponibles</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Actividad del alcance (opcional)</label>
                            <select name="id_actividad" id="createTarjetaActividad" class="form-select tablero-activo-select">
                                <option value="">Sin vincular</option>
                                <?php if(!empty($data['actividadesAgrupadas'])): ?>
                                    <?php foreach($data['actividadesAgrupadas'] as $grupo): ?>
                                        <optgroup label="<?php echo htmlspecialchars($grupo['label']); ?>">
                                            <?php foreach(($grupo['items'] ?? []) as $act): ?>
                                                <option value="<?php echo (int)$act->Id_actividad; ?>" data-alcance-id="<?php echo !empty($act->Id_alcance) ? (int)$act->Id_alcance : ''; ?>" data-contrato-id="<?php echo !empty($act->Id_contrato) ? (int)$act->Id_contrato : ''; ?>">#<?php echo (int)$act->Id_actividad; ?> - <?php echo htmlspecialchars(substr($act->Descripcion_realizada, 0, 90)); ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay actividades disponibles</option>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted">Primero seleccione usuario y alcance para filtrar actividades.</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Prioridad</label>
                            <select name="id_prioridad" id="createTarjetaPrioridad" class="form-select tablero-activo-select" required>
                                <option value="">Seleccione prioridad</option>
                                <?php foreach($prioridadesTablero as $prioridad): ?>
                                    <option value="<?php echo (int)$prioridad->Id_prioridad; ?>">
                                        <?php echo htmlspecialchars($prioridad->Nombre); ?> (<?php echo (int)$prioridad->Valor; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12"><label class="form-label">Titulo</label><input type="text" name="titulo" class="form-control" maxlength="180" required></div>
                        <div class="col-12"><label class="form-label">Descripcion</label><textarea name="descripcion" class="form-control" rows="3"></textarea></div>
                        <div class="col-12">
                            <div class="tarjeta-modal-config-grid">
                                <section class="tarjeta-modal-panel tarjeta-modal-panel--schedule">
                                    <div class="tarjeta-modal-panel__head">
                                        <div>
                                            <h6 class="tarjeta-modal-panel__title mb-1">Trazabilidad</h6>
                                            <p class="tarjeta-modal-panel__hint mb-0">Defina el rango visible de la tarjeta dentro del tablero y calendario.</p>
                                        </div>
                                    </div>
                                    <div class="form-check tarjeta-modal-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="createTarjetaUsarFechas" name="usar_fechas" value="1">
                                        <label class="form-check-label" for="createTarjetaUsarFechas">
                                            Definir fechas de inicio y fin
                                        </label>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Fecha inicio (opcional)</label>
                                            <input type="date" name="fecha_inicio" id="createTarjetaFechaInicio" class="form-control" disabled>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Fecha fin (opcional)</label>
                                            <input type="date" name="fecha_fin" id="createTarjetaFechaFin" class="form-control" disabled>
                                        </div>
                                    </div>
                                </section>
                                <section class="tarjeta-modal-panel tarjeta-modal-panel--status">
                                    <div class="tarjeta-modal-panel__head">
                                        <div>
                                            <h6 class="tarjeta-modal-panel__title mb-1">Estado de ciclo</h6>
                                            <p class="tarjeta-modal-panel__hint mb-0">Marque la tarjeta cuando ya haya terminado completamente.</p>
                                        </div>
                                    </div>
                                    <label class="d-flex align-items-center justify-content-between border rounded px-3 py-3 mb-0 tarjeta-completado-modal-control" for="createTarjetaCompletado" style="cursor:pointer;">
                                        <span>
                                            <span class="d-block fw-semibold">Tarjeta finalizada</span>
                                            <small class="text-muted">Esto dejara su ciclo marcado como completado desde la creacion.</small>
                                        </span>
                                        <input class="form-check-input ms-3" type="checkbox" id="createTarjetaCompletado" name="completado" value="1">
                                    </label>
                                </section>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Etiquetas</label>
                            <?php if(!empty($etiquetasTablero)): ?>
                                <div class="tarjeta-etiquetas-grid">
                                    <?php foreach($etiquetasTablero as $etiqueta): ?>
                                        <label class="tarjeta-etiqueta-option" style="cursor:pointer;">
                                            <input type="checkbox" name="etiquetas[]" value="<?php echo (int)$etiqueta->Id_etiqueta; ?>">
                                            <span class="rounded-circle" style="width:14px;height:14px;background:<?php echo htmlspecialchars($etiqueta->Color); ?>;display:inline-block;"></span>
                                            <span><?php echo !empty($etiqueta->Nombre) ? htmlspecialchars($etiqueta->Nombre) : 'Sin texto'; ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small">No hay etiquetas creadas en este tablero.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Tarjeta</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($canEditCard && $idTableroActual > 0): ?>
<div class="modal fade" id="modalEditTarjeta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Tarjeta</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="formEditTarjeta" action="" method="post">
                <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Columna</label>
                            <select name="id_columna" id="editTarjetaColumna" class="form-select tablero-activo-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach($data['columnas'] as $col): ?>
                                    <option value="<?php echo (int)$col->Id_columna; ?>"><?php echo htmlspecialchars($col->Nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Alcance (opcional)</label>
                            <select name="id_alcance" id="editTarjetaAlcance" class="form-select tablero-activo-select">
                                <option value="">Sin alcance</option>
                                <?php if(!empty($data['alcancesAgrupados'])): ?>
                                    <?php foreach($data['alcancesAgrupados'] as $grupo): ?>
                                        <optgroup label="<?php echo htmlspecialchars($grupo['label']); ?>">
                                            <?php foreach(($grupo['items'] ?? []) as $alc): ?>
                                                <option value="<?php echo (int)$alc->Id_alcance; ?>" data-contrato-id="<?php echo !empty($alc->Id_contrato) ? (int)$alc->Id_contrato : ''; ?>"><?php echo htmlspecialchars(substr($alc->Descripcion, 0, 90)); ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay alcances disponibles</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Actividad del alcance (opcional)</label>
                            <select name="id_actividad" id="editTarjetaActividad" class="form-select tablero-activo-select">
                                <option value="">Sin vincular</option>
                                <?php if(!empty($data['actividadesAgrupadas'])): ?>
                                    <?php foreach($data['actividadesAgrupadas'] as $grupo): ?>
                                        <optgroup label="<?php echo htmlspecialchars($grupo['label']); ?>">
                                            <?php foreach(($grupo['items'] ?? []) as $act): ?>
                                                <option value="<?php echo (int)$act->Id_actividad; ?>" data-alcance-id="<?php echo !empty($act->Id_alcance) ? (int)$act->Id_alcance : ''; ?>" data-contrato-id="<?php echo !empty($act->Id_contrato) ? (int)$act->Id_contrato : ''; ?>">#<?php echo (int)$act->Id_actividad; ?> - <?php echo htmlspecialchars(substr($act->Descripcion_realizada, 0, 90)); ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No hay actividades disponibles</option>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted">Primero seleccione usuario y alcance para filtrar actividades.</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Prioridad</label>
                            <select name="id_prioridad" id="editTarjetaPrioridad" class="form-select tablero-activo-select" required>
                                <option value="">Seleccione prioridad</option>
                                <?php foreach($prioridadesTablero as $prioridad): ?>
                                    <option value="<?php echo (int)$prioridad->Id_prioridad; ?>">
                                        <?php echo htmlspecialchars($prioridad->Nombre); ?> (<?php echo (int)$prioridad->Valor; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Titulo</label>
                            <input type="text" name="titulo" id="editTarjetaTitulo" class="form-control" maxlength="180" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripcion</label>
                            <textarea name="descripcion" id="editTarjetaDescripcion" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="tarjeta-modal-config-grid">
                                <section class="tarjeta-modal-panel tarjeta-modal-panel--schedule">
                                    <div class="tarjeta-modal-panel__head">
                                        <div>
                                            <h6 class="tarjeta-modal-panel__title mb-1">Trazabilidad</h6>
                                            <p class="tarjeta-modal-panel__hint mb-0">Ajuste el rango temporal que se mostrara en el tablero y en el calendario.</p>
                                        </div>
                                    </div>
                                    <div class="form-check tarjeta-modal-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="editTarjetaUsarFechas" name="usar_fechas" value="1">
                                        <label class="form-check-label" for="editTarjetaUsarFechas">
                                            Definir fechas de inicio y fin
                                        </label>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Fecha inicio (opcional)</label>
                                            <input type="date" name="fecha_inicio" id="editTarjetaFechaInicio" class="form-control" disabled>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Fecha fin (opcional)</label>
                                            <input type="date" name="fecha_fin" id="editTarjetaFechaFin" class="form-control" disabled>
                                        </div>
                                    </div>
                                </section>
                                <section class="tarjeta-modal-panel tarjeta-modal-panel--status">
                                    <div class="tarjeta-modal-panel__head">
                                        <div>
                                            <h6 class="tarjeta-modal-panel__title mb-1">Estado de ciclo</h6>
                                            <p class="tarjeta-modal-panel__hint mb-0">Use esta marca para indicar que la tarjeta ya completo su ciclo.</p>
                                        </div>
                                    </div>
                                    <label class="d-flex align-items-center justify-content-between border rounded px-3 py-3 mb-0 tarjeta-completado-modal-control" for="editTarjetaCompletado" style="cursor:pointer;">
                                        <span>
                                            <span class="d-block fw-semibold">Tarjeta finalizada</span>
                                            <small class="text-muted">El tablero la identificara como completada aunque permanezca en una columna intermedia.</small>
                                        </span>
                                        <input class="form-check-input ms-3" type="checkbox" id="editTarjetaCompletado" name="completado" value="1">
                                    </label>
                                </section>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Etiquetas</label>
                            <?php if(!empty($etiquetasTablero)): ?>
                                <div class="tarjeta-etiquetas-grid">
                                    <?php foreach($etiquetasTablero as $etiqueta): ?>
                                        <label class="tarjeta-etiqueta-option" style="cursor:pointer;">
                                            <input type="checkbox" name="etiquetas[]" value="<?php echo (int)$etiqueta->Id_etiqueta; ?>" class="edit-etiqueta-checkbox">
                                            <span class="rounded-circle" style="width:14px;height:14px;background:<?php echo htmlspecialchars($etiqueta->Color); ?>;display:inline-block;"></span>
                                            <span><?php echo !empty($etiqueta->Nombre) ? htmlspecialchars($etiqueta->Nombre) : 'Sin texto'; ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small">No hay etiquetas creadas en este tablero.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php if($canEditCard): ?>
                        <button type="button" class="btn btn-outline-warning mr-auto d-none" id="btnArchivarTarjetaModal">
                            <i class="bi bi-archive"></i> <span id="lblArchivarTarjeta">Archivar Tarjeta</span>
                        </button>
                    <?php endif; ?>
                    <?php if($canDeleteCard): ?>
                        <button type="button" class="btn btn-outline-danger d-none" id="btnDeleteTarjetaModal">
                            <i class="bi bi-trash"></i> Eliminar Tarjeta
                        </button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if($canDeleteCard): ?>
<form id="formDeleteTarjeta" action="" method="post" class="d-none">
    <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
</form>
<?php endif; ?>
<?php endif; ?>

<?php if($canEditColumn): ?>
<div class="modal fade" id="modalRenameColumna" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Columna</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="formRenameColumna" action="" method="post">
                <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nombre de la columna</label><input type="text" name="nombre" id="inputRenameNombre" class="form-control" maxlength="120" required></div>
                    <div class="mb-3"><label class="form-label">Color</label><input type="color" name="color" id="inputRenameColor" class="form-control form-control-color" value="#0d6efd"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($canDeleteColumn): ?>
<div class="modal fade" id="modalDeleteColumna" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar Eliminacion</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="formDeleteColumna" action="" method="post">
                <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
                <div class="modal-body">
                    <p>¿Esta seguro de eliminar la columna <strong><span id="deleteColumnaName"></span></strong>?</p>
                    <p class="text-muted small">Solo es posible eliminar columnas sin tarjetas activas.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Si, Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modalTarjetaTareas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-list-task"></i> Tareas de Tarjeta</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="small text-muted">Tarjeta</div>
                    <div id="modalTarjetaTareasTitulo" class="fw-bold">-</div>
                    <div id="modalTarjetaTareasDescripcion" class="small text-muted mt-1"></div>
                    <div class="modal-tarjeta-meta-row mt-2">
                        <div class="modal-tarjeta-meta-item">
                            <span class="modal-tarjeta-meta-label">Actividad</span>
                            <div id="modalTarjetaTareasActividad" class="small"></div>
                        </div>
                        <div class="modal-tarjeta-meta-item">
                            <span class="modal-tarjeta-meta-label">Prioridad</span>
                            <div id="modalTarjetaTareasPrioridad" class="small"></div>
                        </div>
                        <div class="modal-tarjeta-meta-item">
                            <span class="modal-tarjeta-meta-label">Estado</span>
                            <div id="modalTarjetaTareasEstado" class="small mb-2"></div>
                            <label class="form-check mb-0 d-flex align-items-center gap-2 tarjeta-completado-wrapper" for="modalTarjetaCompletado">
                                <input class="form-check-input" type="checkbox" id="modalTarjetaCompletado" value="1" <?php echo $canMarkDone ? '' : 'disabled'; ?>>
                                <span class="small fw-semibold">Finalizada</span>
                            </label>
                        </div>
                        <div class="modal-tarjeta-meta-item modal-tarjeta-meta-item-etiquetas">
                            <span class="modal-tarjeta-meta-label">Etiquetas</span>
                            <div id="modalTarjetaTareasEtiquetas" class="small d-flex flex-wrap gap-1"></div>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label mb-1">Filtro por usuario asignado</label>
                                <select class="form-select form-select-sm tablero-select-enhanced" id="filtroDetalleUsuario">
                                    <option value="">Todos</option>
                                    <option value="__me__">Mis tareas asignadas</option>
                                    <option value="__none__">Sin asignar</option>
                                    <?php foreach($data['usuariosAsignados'] as $usuario): ?>
                                        <option value="<?php echo (int)$usuario->Id_usuario; ?>"><?php echo htmlspecialchars($usuario->email); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div id="notifPlantillaLista" class="alert mb-3 d-none" role="alert" style="padding:.5rem .85rem;font-size:.875rem;"></div>

                        <?php if($canCreateList): ?>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="inputNuevaListaTareas" maxlength="180" placeholder="Nombre de nueva lista de tareas">
                                <button class="btn btn-primary" type="button" id="btnAgregarListaTareas">Agregar lista</button>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownBtnPlantillasLista" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="bi bi-file-earmark-text"></i> Desde Plantilla
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" id="dropdownMenuPlantillasLista" aria-labelledby="dropdownBtnPlantillasLista">
                                        <span class="dropdown-item text-muted">Cargando...</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div id="contenedorListasTareas"></div>
                    </div>
                    <div class="col-lg-5">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="mb-0"><i class="bi bi-clock-history mr-1"></i>Historial</h6>
                            <small id="historialContador" class="text-muted"></small>
                        </div>
                        <div class="input-group input-group-sm mb-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                            </div>
                            <input type="text" class="form-control" id="inputFiltroHistorial" placeholder="Buscar en historial..." autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiarFiltroHistorial" title="Limpiar filtros"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <select class="form-select tablero-activo-select mb-2" id="selectFiltroHistorialCategoria" style="font-size:0.8rem;">
                            <option value="">Todas las categorías</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="lista">Lista de tareas</option>
                            <option value="tarea">Tarea (ítem)</option>
                            <option value="tiempo">Tiempo</option>
                        </select>
                        <div id="contenedorHistorialTarjeta" class="border rounded p-2 bg-light" style="max-height: 440px; overflow-y:auto;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarListaTarea" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar lista</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="formEditarListaTarea" action="#" method="post">
                <div class="modal-body">
                    <label class="form-label" for="inputEditarListaTareaNombre">Nombre de la lista</label>
                    <input type="text" class="form-control" id="inputEditarListaTareaNombre" maxlength="180" required>
                    <div class="form-text">Actualice el nombre de la lista de tareas de esta tarjeta.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarEditarListaTarea">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarDetalleTarea" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar descripcion de la tarea</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="formEditarDetalleTarea" action="#" method="post">
                <div class="modal-body">
                    <label class="form-label" for="inputEditarDetalleTareaDescripcion">Descripcion</label>
                    <textarea class="form-control" id="inputEditarDetalleTareaDescripcion" rows="4" required></textarea>
                    <div class="form-text">Actualice la descripcion del item seleccionado.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarEditarDetalleTarea">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarTiempoUsuarios" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-clock-history"></i> Editar tiempo por usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalTiempoUsuariosDetalleId" value="">
                <div class="alert alert-light border small mb-3" id="modalTiempoUsuariosInfo">
                    Este detalle tiene varios usuarios con tiempo acumulado. Puede editar uno o varios usuarios sin perder los demas registros.
                </div>
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label mb-1">Aplicar mismo tiempo</label>
                        <input type="text" id="modalTiempoUsuariosAplicarTodos" class="form-control form-control-sm" placeholder="hh:mm:ss">
                    </div>
                    <div class="col-12 col-md-8 d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnTiempoUsuariosSeleccionarTodos">Seleccionar todos</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnTiempoUsuariosDeseleccionarTodos">Deseleccionar todos</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnTiempoUsuariosAplicarSeleccionados">Aplicar a seleccionados</button>
                    </div>
                </div>
                <div id="contenedorTiempoUsuariosRows" class="border rounded p-2 bg-light"></div>
                <div class="mt-3 small">
                    <strong>Total resultante:</strong> <span id="modalTiempoUsuariosTotal">00:00:00</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarTiempoUsuarios">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmarAccionTablero" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalConfirmarAccionTitulo"><i class="bi bi-exclamation-triangle"></i> Confirmar accion</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="modalConfirmarAccionMensaje">¿Desea continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarAccionTablero">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function(){
    const APP_URL_ROOT = <?php echo json_encode(URLROOT); ?>;
    const idTableroActual = <?php echo (int)$idTableroActual; ?>;
    const canEditBoard = <?php echo $canEditBoard ? 'true' : 'false'; ?>;
    const canEditCard = <?php echo $canEditCard ? 'true' : 'false'; ?>;
    const canMoveCard = <?php echo $canMoveCard ? 'true' : 'false'; ?>;
    const canMarkDone = <?php echo $canMarkDone ? 'true' : 'false'; ?>;
    const canAssignBoard = <?php echo $canAssignBoard ? 'true' : 'false'; ?>;
    const canCreateList = <?php echo $canCreateList ? 'true' : 'false'; ?>;
    const canEditList = <?php echo $canEditList ? 'true' : 'false'; ?>;
    const canDeleteList = <?php echo $canDeleteList ? 'true' : 'false'; ?>;
    const canCreateTask = <?php echo $canCreateTask ? 'true' : 'false'; ?>;
    const canEditTask = <?php echo $canEditTask ? 'true' : 'false'; ?>;
    const canDeleteTask = <?php echo $canDeleteTask ? 'true' : 'false'; ?>;
    const canTrackTime = <?php echo $canTrackTime ? 'true' : 'false'; ?>;
    const canEditTime = <?php echo $canEditTime ? 'true' : 'false'; ?>;
    const canAssignTaskUser = <?php echo $canAssign ? 'true' : 'false'; ?>;
    const canTimerAdminOverride = <?php echo isAdministradorRol() ? 'true' : 'false'; ?>;
    const canEditColumn = <?php echo $canEditColumn ? 'true' : 'false'; ?>;
    const canDeleteColumn = <?php echo $canDeleteColumn ? 'true' : 'false'; ?>;
    const canOrderColumn = <?php echo $canOrderColumn ? 'true' : 'false'; ?>;
    const canDeleteCard = <?php echo $canDeleteCard ? 'true' : 'false'; ?>;
    const canPlantillaTarjetaCrear    = <?php echo $canPlantillaTarjetaCrear ? 'true' : 'false'; ?>;
    const canPlantillaTarjetaEditar   = <?php echo $canPlantillaTarjetaEditar ? 'true' : 'false'; ?>;
    const canPlantillaTarjetaEliminar = <?php echo $canPlantillaTarjetaEliminar ? 'true' : 'false'; ?>;
    const canPlantillaListaCrear      = <?php echo $canPlantillaListaCrear ? 'true' : 'false'; ?>;
    const canPlantillaListaEditar     = <?php echo $canPlantillaListaEditar ? 'true' : 'false'; ?>;
    const canPlantillaListaEliminar   = <?php echo $canPlantillaListaEliminar ? 'true' : 'false'; ?>;
    const currentUserId = <?php echo (int)$_SESSION['user_id']; ?>;
    const usuariosAsignadosTarea = <?php
        $usuariosDetalle = [];
        if(!empty($data['usuariosAsignados'])){
            foreach($data['usuariosAsignados'] as $ua){
                $usuariosDetalle[] = [
                    'id' => (int)$ua->Id_usuario,
                    'email' => (string)$ua->email
                ];
            }
        }
        echo json_encode($usuariosDetalle, JSON_UNESCAPED_UNICODE);
    ?>;
    const timerIntervals = {};
    const usuariosAsignadosPermisos = <?php
        $mapPermisos = [];
        if(!empty($data['usuariosAsignados'])){
            foreach($data['usuariosAsignados'] as $ua){
                $legacyVer = (int)($ua->Permiso_ver ?? 0) === 1;
                $legacyCrear = (int)($ua->Permiso_crear ?? 0) === 1;
                $legacyEditar = (int)($ua->Permiso_editar ?? 0) === 1;
                $legacyEliminar = (int)($ua->Permiso_eliminar ?? 0) === 1;

                $granularFlags = [
                    (int)($ua->Permiso_tablero_ver ?? 0),
                    (int)($ua->Permiso_tablero_crear ?? 0),
                    (int)($ua->Permiso_tablero_editar ?? 0),
                    (int)($ua->Permiso_tablero_eliminar ?? 0),
                    (int)($ua->Permiso_tablero_asignar ?? 0),
                    (int)($ua->Permiso_columna_crear ?? 0),
                    (int)($ua->Permiso_columna_editar ?? 0),
                    (int)($ua->Permiso_columna_eliminar ?? 0),
                    (int)($ua->Permiso_columna_ordenar ?? 0),
                    (int)($ua->Permiso_tarjeta_ver ?? 0),
                    (int)($ua->Permiso_tarjeta_crear ?? 0),
                    (int)($ua->Permiso_tarjeta_editar ?? 0),
                    (int)($ua->Permiso_tarjeta_mover ?? 0),
                    (int)($ua->Permiso_tarjeta_eliminar ?? 0),
                    (int)($ua->Permiso_tarjeta_asignar ?? 0),
                    (int)($ua->Permiso_lista_crear ?? 0),
                    (int)($ua->Permiso_lista_editar ?? 0),
                    (int)($ua->Permiso_lista_eliminar ?? 0),
                    (int)($ua->Permiso_tarea_crear ?? 0),
                    (int)($ua->Permiso_tarea_editar ?? 0),
                    (int)($ua->Permiso_tarea_eliminar ?? 0),
                    (int)($ua->Permiso_tarea_tiempo_editar ?? 0),
                    (int)($ua->Permiso_plantilla_tarjeta_crear ?? 0),
                    (int)($ua->Permiso_plantilla_tarjeta_editar ?? 0),
                    (int)($ua->Permiso_plantilla_tarjeta_eliminar ?? 0),
                    (int)($ua->Permiso_plantilla_tarjeta_asociar ?? 0),
                    (int)($ua->Permiso_plantilla_lista_crear ?? 0),
                    (int)($ua->Permiso_plantilla_lista_editar ?? 0),
                    (int)($ua->Permiso_plantilla_lista_eliminar ?? 0)
                ];

                $isLegacyOnly = (array_sum($granularFlags) === 0) && ($legacyVer || $legacyCrear || $legacyEditar || $legacyEliminar);

                $mapPermisos[(int)$ua->Id_usuario] = [
                    'tablero_ver' => $isLegacyOnly ? $legacyVer : ((int)($ua->Permiso_tablero_ver ?? 0) === 1),
                    'tablero_crear' => $isLegacyOnly ? $legacyCrear : ((int)($ua->Permiso_tablero_crear ?? 0) === 1),
                    'tablero_editar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tablero_editar ?? 0) === 1),
                    'tablero_eliminar' => $isLegacyOnly ? $legacyEliminar : ((int)($ua->Permiso_tablero_eliminar ?? 0) === 1),
                    'tablero_asignar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tablero_asignar ?? 0) === 1),
                    'columna_crear' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_columna_crear ?? 0) === 1),
                    'columna_editar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_columna_editar ?? 0) === 1),
                    'columna_eliminar' => $isLegacyOnly ? $legacyEliminar : ((int)($ua->Permiso_columna_eliminar ?? 0) === 1),
                    'columna_ordenar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_columna_ordenar ?? 0) === 1),
                    'tarjeta_ver' => $isLegacyOnly ? $legacyVer : ((int)($ua->Permiso_tarjeta_ver ?? 0) === 1),
                    'tarjeta_crear' => $isLegacyOnly ? $legacyCrear : ((int)($ua->Permiso_tarjeta_crear ?? 0) === 1),
                    'tarjeta_editar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tarjeta_editar ?? 0) === 1),
                    'tarjeta_mover' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tarjeta_mover ?? $ua->Permiso_tarjeta_editar ?? 0) === 1),
                    'tarjeta_eliminar' => $isLegacyOnly ? $legacyEliminar : ((int)($ua->Permiso_tarjeta_eliminar ?? 0) === 1),
                    'tarjeta_asignar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tarjeta_asignar ?? 0) === 1),
                    'lista_crear' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_lista_crear ?? 0) === 1),
                    'lista_editar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_lista_editar ?? 0) === 1),
                    'lista_eliminar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_lista_eliminar ?? 0) === 1),
                    'tarea_crear' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tarea_crear ?? 0) === 1),
                    'tarea_editar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tarea_editar ?? 0) === 1),
                    'tarea_eliminar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tarea_eliminar ?? 0) === 1),
                    'tarea_tiempo_editar' => (int)($ua->Permiso_tarea_tiempo_editar ?? 0) === 1,
                    'plantilla_tarjeta_crear' => (int)($ua->Permiso_plantilla_tarjeta_crear ?? 0) === 1,
                    'plantilla_tarjeta_editar' => (int)($ua->Permiso_plantilla_tarjeta_editar ?? 0) === 1,
                    'plantilla_tarjeta_eliminar' => (int)($ua->Permiso_plantilla_tarjeta_eliminar ?? 0) === 1,
                    'plantilla_tarjeta_asociar' => (int)($ua->Permiso_plantilla_tarjeta_asociar ?? 0) === 1,
                    'plantilla_lista_crear' => (int)($ua->Permiso_plantilla_lista_crear ?? 0) === 1,
                    'plantilla_lista_editar' => (int)($ua->Permiso_plantilla_lista_editar ?? 0) === 1,
                    'plantilla_lista_eliminar' => (int)($ua->Permiso_plantilla_lista_eliminar ?? 0) === 1
                ];
            }
        }
        echo json_encode($mapPermisos);
    ?>;
    const modalTarjetaTareasEl = document.getElementById('modalTarjetaTareas');
    const modalTarjetaTareasTituloEl = document.getElementById('modalTarjetaTareasTitulo');
    const modalTarjetaTareasDescripcionEl = document.getElementById('modalTarjetaTareasDescripcion');
    const modalTarjetaTareasActividadEl = document.getElementById('modalTarjetaTareasActividad');
    const modalTarjetaTareasPrioridadEl = document.getElementById('modalTarjetaTareasPrioridad');
    const modalTarjetaTareasEstadoEl = document.getElementById('modalTarjetaTareasEstado');
    const modalTarjetaTareasEtiquetasEl = document.getElementById('modalTarjetaTareasEtiquetas');
    const modalEditarListaTareaEl = document.getElementById('modalEditarListaTarea');
    const formEditarListaTareaEl = document.getElementById('formEditarListaTarea');
    const inputEditarListaTareaNombreEl = document.getElementById('inputEditarListaTareaNombre');
    const btnGuardarEditarListaTareaEl = document.getElementById('btnGuardarEditarListaTarea');
    const modalEditarDetalleTareaEl = document.getElementById('modalEditarDetalleTarea');
    const formEditarDetalleTareaEl = document.getElementById('formEditarDetalleTarea');
    const inputEditarDetalleTareaDescripcionEl = document.getElementById('inputEditarDetalleTareaDescripcion');
    const btnGuardarEditarDetalleTareaEl = document.getElementById('btnGuardarEditarDetalleTarea');
    const modalTarjetaCompletadoEl = document.getElementById('modalTarjetaCompletado');
    const filtroDetalleUsuarioEl = document.getElementById('filtroDetalleUsuario');
    const contenedorListasTareasEl = document.getElementById('contenedorListasTareas');
    const contenedorHistorialTarjetaEl = document.getElementById('contenedorHistorialTarjeta');
    const inputNuevaListaTareasEl = document.getElementById('inputNuevaListaTareas');
    const btnAgregarListaTareasEl = document.getElementById('btnAgregarListaTareas');
    const formEditTarjetaEl = document.getElementById('formEditTarjeta');
    const createTarjetaAlcanceEl = document.getElementById('createTarjetaAlcance');
    const createTarjetaActividadEl = document.getElementById('createTarjetaActividad');
    const createTarjetaAsignadoEl = document.getElementById('createTarjetaAsignado');
    const editTarjetaColumnaEl = document.getElementById('editTarjetaColumna');
    const editTarjetaAlcanceEl = document.getElementById('editTarjetaAlcance');
    const editTarjetaActividadEl = document.getElementById('editTarjetaActividad');
    const editTarjetaPrioridadEl = document.getElementById('editTarjetaPrioridad');
    const editTarjetaTituloEl = document.getElementById('editTarjetaTitulo');
    const editTarjetaDescripcionEl = document.getElementById('editTarjetaDescripcion');
    const editTarjetaAsignadoEl = document.getElementById('editTarjetaAsignado');
    const createTarjetaUsarFechasEl = document.getElementById('createTarjetaUsarFechas');
    const createTarjetaFechaInicioEl = document.getElementById('createTarjetaFechaInicio');
    const createTarjetaFechaFinEl = document.getElementById('createTarjetaFechaFin');
    const editTarjetaUsarFechasEl = document.getElementById('editTarjetaUsarFechas');
    const editTarjetaFechaInicioEl = document.getElementById('editTarjetaFechaInicio');
    const editTarjetaFechaFinEl = document.getElementById('editTarjetaFechaFin');
    const editTarjetaCompletadoEl = document.getElementById('editTarjetaCompletado');
    const formEtiquetaTableroEl = document.getElementById('formEtiquetaTablero');
    const inputEtiquetaNombreEl = document.getElementById('inputEtiquetaNombre');
    const inputEtiquetaColorEl = document.getElementById('inputEtiquetaColor');
    const btnGuardarEtiquetaTableroEl = document.getElementById('btnGuardarEtiquetaTablero');
    const btnCancelarEdicionEtiquetaEl = document.getElementById('btnCancelarEdicionEtiqueta');
    const formDeleteEtiquetaEl = document.getElementById('formDeleteEtiqueta');
    const formPrioridadTableroEl = document.getElementById('formPrioridadTablero');
    const inputPrioridadNombreEl = document.getElementById('inputPrioridadNombre');
    const inputPrioridadValorEl = document.getElementById('inputPrioridadValor');
    const inputPrioridadColorEl = document.getElementById('inputPrioridadColor');
    const btnGuardarPrioridadTableroEl = document.getElementById('btnGuardarPrioridadTablero');
    const btnCancelarEdicionPrioridadEl = document.getElementById('btnCancelarEdicionPrioridad');
    const formDeletePrioridadEl = document.getElementById('formDeletePrioridad');
    const filtroTarjetaNombreEl = document.getElementById('filtroTarjetaNombre');
    const filtroTarjetaEtiquetaEl = document.getElementById('filtroTarjetaEtiqueta');
    const filtroTarjetaPrioridadEl = document.getElementById('filtroTarjetaPrioridad');
    const btnLimpiarFiltrosTarjetaEl = document.getElementById('btnLimpiarFiltrosTarjeta');
    const switchMostrarArchivadas    = document.getElementById('switchMostrarArchivadas');
    const modalConfirmarAccionEl = document.getElementById('modalConfirmarAccionTablero');
    const modalConfirmarAccionTituloEl = document.getElementById('modalConfirmarAccionTitulo');
    const modalConfirmarAccionMensajeEl = document.getElementById('modalConfirmarAccionMensaje');
    const btnConfirmarAccionTableroEl = document.getElementById('btnConfirmarAccionTablero');
    const confirmActionDefaultLabel = btnConfirmarAccionTableroEl ? String(btnConfirmarAccionTableroEl.textContent || 'Eliminar').trim() : 'Eliminar';
    const btnDeleteTarjetaModalEl = document.getElementById('btnDeleteTarjetaModal');
    const btnArchivarTarjetaModalEl = document.getElementById('btnArchivarTarjetaModal');
    const lblArchivarTarjetaEl      = document.getElementById('lblArchivarTarjeta');
    const formDeleteTarjetaEl = document.getElementById('formDeleteTarjeta');
    const btnDeleteTableroModalEl = document.getElementById('btnDeleteTableroModal');
    const formDeleteTableroEl = document.getElementById('formDeleteTablero');
    const modalEditarTiempoUsuariosEl = document.getElementById('modalEditarTiempoUsuarios');
    const modalTiempoUsuariosDetalleIdEl = document.getElementById('modalTiempoUsuariosDetalleId');
    const modalTiempoUsuariosInfoEl = document.getElementById('modalTiempoUsuariosInfo');
    const contenedorTiempoUsuariosRowsEl = document.getElementById('contenedorTiempoUsuariosRows');
    const modalTiempoUsuariosAplicarTodosEl = document.getElementById('modalTiempoUsuariosAplicarTodos');
    const modalTiempoUsuariosTotalEl = document.getElementById('modalTiempoUsuariosTotal');
    const btnTiempoUsuariosSeleccionarTodosEl = document.getElementById('btnTiempoUsuariosSeleccionarTodos');
    const btnTiempoUsuariosDeseleccionarTodosEl = document.getElementById('btnTiempoUsuariosDeseleccionarTodos');
    const btnTiempoUsuariosAplicarSeleccionadosEl = document.getElementById('btnTiempoUsuariosAplicarSeleccionados');
    const btnGuardarTiempoUsuariosEl = document.getElementById('btnGuardarTiempoUsuarios');
    const tableroInteractiveShellEl = document.getElementById('tableroInteractiveShell');
    const tableroViewportEl = document.querySelector('.js-tablero-viewport');
    const tableroColumnsEl = document.querySelector('.js-tablero-columns');
    const tableroSyncXEls = Array.from(document.querySelectorAll('.js-tablero-scroll-x'));
    const tableroSyncYEls = Array.from(document.querySelectorAll('.js-tablero-scroll-y'));
    const tableroSyncXInnerEls = Array.from(document.querySelectorAll('.js-tablero-scroll-x-inner'));
    const tableroSyncYInnerEls = Array.from(document.querySelectorAll('.js-tablero-scroll-y-inner'));
    const tableroEdgeZonesEls = Array.from(document.querySelectorAll('.tablero-edge-zone[data-autoscroll-dir]'));
    const TABLERO_AUTOSCROLL_DELAY_MS = 480;
    const TABLERO_AUTOSCROLL_STEP_PX = 18;
    const detalleTiempoUsuarioMap = {};
    let pendingConfirmAction = null;
    let tarjetaTareasActualId = null;
    let tarjetaEditandoId = null;
    let tareaEditandoId = 0;
    let detalleTareaEditandoId = 0;
    let tableroSyncLocked = false;
    let tableroAutoScrollIntervalId = null;
    let tableroAutoScrollDelayId = null;

    // ------------------------------------------------------------------
    // PLANTILLAS: estado
    // ------------------------------------------------------------------
    let tarjetasPlantillas = [];
    let listasPlantillas   = [];

    function escapeHtmlPl(str){
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    async function cargarPlantillas(){
        if(idTableroActual <= 0) return;
        try {
            const [r1, r2] = await Promise.all([
                fetch(`${APP_URL_ROOT}/tablero/get_plantillas_tarjeta`, {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify({id_tablero: idTableroActual})
                }),
                fetch(`${APP_URL_ROOT}/tablero/get_plantillas_lista`, {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify({id_tablero: idTableroActual})
                })
            ]);
            const d1 = await r1.json();
            const d2 = await r2.json();
            if(d1.success) tarjetasPlantillas = d1.plantillas || [];
            if(d2.success) listasPlantillas   = d2.plantillas  || [];
        } catch(e){ /* fallo silencioso */ }
        renderDropdownPlantillasTarjeta();
        renderDropdownPlantillasLista();
    }

    function renderCheckboxListasAsociar(containerId, selectedIds){
        const cont = document.getElementById(containerId);
        if(!cont) return;
        if(!listasPlantillas || listasPlantillas.length === 0){
            cont.innerHTML = '<span class="text-muted small">Sin plantillas de listado disponibles para este tablero.</span>';
            return;
        }
        const sel = new Set((selectedIds || []).map(Number));
        cont.innerHTML = listasPlantillas.map(p => {
            const id = parseInt(p.Id_plantilla_lista, 10);
            const checked = sel.has(id) ? 'checked' : '';
            return `<div class="form-check mb-1">
                <input class="form-check-input chk-lista-asociar" type="checkbox" value="${id}" id="chkLista_${containerId}_${id}" ${checked}>
                <label class="form-check-label small" for="chkLista_${containerId}_${id}">
                    <strong>${escapeHtmlPl(p.Nombre_plantilla)}</strong>
                    <span class="text-muted"> &mdash; ${escapeHtmlPl(p.Nombre_lista)}</span>
                </label>
            </div>`;
        }).join('');
    }

    function getCheckedListaIds(containerId){
        const cont = document.getElementById(containerId);
        if(!cont) return [];
        return Array.from(cont.querySelectorAll('.chk-lista-asociar:checked')).map(el => parseInt(el.value, 10));
    }

    function renderDropdownPlantillasTarjeta(){
        const menu = document.getElementById('dropdownMenuPlantillasTarjeta');
        if(!menu) return;
        if(tarjetasPlantillas.length === 0){
            menu.innerHTML = '<span class="dropdown-item text-muted">Sin plantillas guardadas</span>';
            return;
        }
        menu.innerHTML = tarjetasPlantillas.map(p =>
            `<a class="dropdown-item plantilla-tarjeta-item" href="#" data-id="${p.Id_plantilla_tarjeta}">${escapeHtmlPl(p.Nombre_plantilla)}</a>`
        ).join('');
    }

    function renderDropdownPlantillasLista(){
        const menu = document.getElementById('dropdownMenuPlantillasLista');
        if(!menu) return;
        if(listasPlantillas.length === 0){
            menu.innerHTML = '<span class="dropdown-item text-muted">Sin plantillas guardadas</span>';
            return;
        }
        menu.innerHTML = listasPlantillas.map(p =>
            `<a class="dropdown-item plantilla-lista-item" href="#" data-id="${p.Id_plantilla_lista}">${escapeHtmlPl(p.Nombre_plantilla)}</a>`
        ).join('');
    }

    // Aplicar plantilla de tarjeta -> pre-rellenar modal crear tarjeta
    document.addEventListener('click', function(e){
        const itemTarjeta = e.target.closest('.plantilla-tarjeta-item');
        if(itemTarjeta){
            e.preventDefault();
            const id = parseInt(itemTarjeta.dataset.id || '0', 10);
            const p  = tarjetasPlantillas.find(x => parseInt(x.Id_plantilla_tarjeta, 10) === id);
            if(p){
                const tituloEl = document.querySelector('#modalCreateTarjeta [name="titulo"]');
                const descEl   = document.querySelector('#modalCreateTarjeta [name="descripcion"]');
                if(tituloEl) tituloEl.value = p.Titulo || '';
                if(descEl)   descEl.value   = p.Descripcion || '';
                const hiddenListas = document.getElementById('hiddenPlantillaListasIds');
                if(hiddenListas) hiddenListas.value = (p.lista_ids || []).join(',');
                // Pre-rellenar columna y prioridad si la plantilla tiene valores defecto
                const colEl = document.getElementById('createTarjetaColumna');
                const priEl = document.getElementById('createTarjetaPrioridad');
                if(colEl && p.Id_columna_defecto) colEl.value = p.Id_columna_defecto;
                if(priEl && p.Id_prioridad_defecto) priEl.value = p.Id_prioridad_defecto;
            }
            return;
        }

        const itemLista = e.target.closest('.plantilla-lista-item');
        if(itemLista){
            e.preventDefault();
            const id = parseInt(itemLista.dataset.id || '0', 10);
            aplicarPlantillaLista(id);
            return;
        }
    });

    function showTareasNotif(mensaje, tipo){
        const el = document.getElementById('notifPlantillaLista');
        if(!el) return;
        el.className = 'alert mb-3' + (tipo === 'success' ? ' alert-success' : ' alert-danger');
        el.textContent = mensaje;
        clearTimeout(el._notifTimer);
        el._notifTimer = setTimeout(() => { el.classList.add('d-none'); }, 4000);
    }

    async function aplicarPlantillaLista(idPlantilla){
        if(!tarjetaTareasActualId){
            showTareasNotif('Primero abra una tarjeta.', 'error');
            return;
        }
        let plantilla;
        try {
            const resp = await fetch(`${APP_URL_ROOT}/tablero/get_plantilla_lista_detalle`, {
                method: 'POST', headers: {'Content-Type':'application/json'},
                credentials: 'same-origin',
                body: JSON.stringify({id_tablero: idTableroActual, id_plantilla_lista: idPlantilla})
            });
            const data = await resp.json();
            if(!data.success){ showTareasNotif(data.error || 'Error al cargar la plantilla', 'error'); return; }
            plantilla = data.plantilla;
        } catch(e){ showTareasNotif('Error de conexion al cargar la plantilla.', 'error'); return; }

        try {
            const rLista = await fetch(`${APP_URL_ROOT}/tablero/create_tarjeta_tarea`, {
                method: 'POST', headers: {'Content-Type':'application/json'},
                credentials: 'same-origin',
                body: JSON.stringify({
                    id_tablero: idTableroActual,
                    id_tarjeta: tarjetaTareasActualId,
                    nombre_tarea: plantilla.Nombre_lista
                })
            });
            const dLista = await rLista.json();
            if(!dLista.success){ showTareasNotif(dLista.error || 'No se pudo crear la lista', 'error'); return; }

            const idTarea = dLista.id_tarea;
            for(const det of (plantilla.detalles || [])){
                await fetch(`${APP_URL_ROOT}/tablero/create_tarjeta_tarea_detalle`, {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        id_tablero: idTableroActual,
                        id_tarea: idTarea,
                        descripcion: det.Descripcion
                    })
                });
            }
        } catch(e){ showTareasNotif('Error al aplicar la plantilla.', 'error'); return; }

        showTareasNotif('Listado "' + (plantilla.Nombre_lista || '') + '" creado desde plantilla correctamente.', 'success');
        if(typeof cargarModalTareas === 'function') await cargarModalTareas();
    }

    // -- Gestor: render listas en el modal gestor
    function renderGestorPlantillasTarjeta(){
        const contenedor = document.getElementById('gestorListaPlantillasTarjeta');
        if(!contenedor) return;
        if(!tarjetasPlantillas.length){
            contenedor.innerHTML = '<div class="text-muted text-center py-4"><i class="bi bi-inbox" style="font-size:2rem;"></i><div class="mt-2">No hay plantillas de tarjeta creadas aun.</div></div>';
            return;
        }
        contenedor.innerHTML = tarjetasPlantillas.map(p => `
            <div class="d-flex align-items-center border rounded p-2 mb-2 bg-light" style="gap:.5rem;">
                <i class="bi bi-kanban text-primary flex-shrink-0"></i>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="font-weight-semibold text-truncate">${escapeHtmlPl(p.Nombre_plantilla)}</div>
                    <div class="small text-muted text-truncate">Titulo: ${escapeHtmlPl(p.Titulo)}</div>
                </div>
                ${canPlantillaTarjetaEditar ? `<button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0 btn-editar-plantilla-tarjeta" data-id="${p.Id_plantilla_tarjeta}" title="Editar"><i class="bi bi-pencil"></i></button>` : ''}
                ${canPlantillaTarjetaEliminar ? `<button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0 btn-eliminar-plantilla-tarjeta" data-id="${p.Id_plantilla_tarjeta}" data-nombre="${escapeHtmlPl(p.Nombre_plantilla)}" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
            </div>
        `).join('');
    }

    function renderGestorPlantillasLista(){
        const contenedor = document.getElementById('gestorListaPlantillasLista');
        if(!contenedor) return;
        if(!listasPlantillas.length){
            contenedor.innerHTML = '<div class="text-muted text-center py-4"><i class="bi bi-inbox" style="font-size:2rem;"></i><div class="mt-2">No hay plantillas de listado creadas aun.</div></div>';
            return;
        }
        contenedor.innerHTML = listasPlantillas.map(p => `
            <div class="d-flex align-items-center border rounded p-2 mb-2 bg-light" style="gap:.5rem;">
                <i class="bi bi-list-task text-success flex-shrink-0"></i>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="font-weight-semibold text-truncate">${escapeHtmlPl(p.Nombre_plantilla)}</div>
                    <div class="small text-muted text-truncate">Listado: ${escapeHtmlPl(p.Nombre_lista)}</div>
                </div>
                ${canPlantillaListaEditar ? `<button type="button" class="btn btn-sm btn-outline-success flex-shrink-0 btn-editar-plantilla-lista" data-id="${p.Id_plantilla_lista}" title="Editar"><i class="bi bi-pencil"></i></button>` : ''}
                ${canPlantillaListaEliminar ? `<button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0 btn-eliminar-plantilla-lista" data-id="${p.Id_plantilla_lista}" data-nombre="${escapeHtmlPl(p.Nombre_plantilla)}" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
            </div>
        `).join('');
    }

    // -- Selector tipo plantilla -> abrir gestor correspondiente
    const btnAbrirPlantillaTarjetaEl = document.getElementById('btnAbrirPlantillaTarjeta');
    const btnAbrirPlantillaListaEl   = document.getElementById('btnAbrirPlantillaLista');
    if(btnAbrirPlantillaTarjetaEl){
        btnAbrirPlantillaTarjetaEl.addEventListener('click', function(){
            renderGestorPlantillasTarjeta();
            $('#modalGestorPlantillasTarjeta').modal('show');
        });
    }
    if(btnAbrirPlantillaListaEl){
        btnAbrirPlantillaListaEl.addEventListener('click', function(){
            renderGestorPlantillasLista();
            $('#modalGestorPlantillasLista').modal('show');
        });
    }

    // -- Gestor: boton "Nueva plantilla" en cada gestor
    const btnNuevaPlantillaTarjetaEl = document.getElementById('btnNuevaPlantillaTarjeta');
    if(btnNuevaPlantillaTarjetaEl){
        btnNuevaPlantillaTarjetaEl.addEventListener('click', function(){
            $('#modalGestorPlantillasTarjeta').modal('hide');
            setTimeout(() => {
                renderCheckboxListasAsociar('checkboxListasAsociarCrear', []);
                $('#modalCrearPlantillaTarjeta').modal('show');
            }, 320);
        });
    }
    const btnNuevaPlantillaListaEl = document.getElementById('btnNuevaPlantillaLista');
    if(btnNuevaPlantillaListaEl){
        btnNuevaPlantillaListaEl.addEventListener('click', function(){
            document.getElementById('contenedorTareasPlantillaLista').innerHTML = '';
            document.getElementById('inputNuevaTareaPlantillaLista').value = '';
            $('#modalGestorPlantillasLista').modal('hide');
            setTimeout(() => { $('#modalCrearPlantillaLista').modal('show'); }, 320);
        });
    }

    function showPlantillaToast(mensaje, tipo){
        const toastEl     = document.getElementById('toastPlantilla');
        const headerEl    = document.getElementById('toastPlantillaHeader');
        const iconoEl     = document.getElementById('toastPlantillaIcono');
        const tituloEl    = document.getElementById('toastPlantillaTitulo');
        const mensajeEl   = document.getElementById('toastPlantillaMensaje');
        if(!toastEl) return;

        const esError = tipo === 'error';
        headerEl.className  = 'toast-header ' + (esError ? 'bg-danger text-white' : 'bg-success text-white');
        iconoEl.className   = 'bi mr-2 ' + (esError ? 'bi-x-circle-fill' : 'bi-check-circle-fill');
        tituloEl.textContent = esError ? 'Error' : 'Exito';
        mensajeEl.textContent = mensaje;
        $(toastEl).toast('show');
    }

    // -- Guardar plantilla de tarjeta
    const btnGuardarPlantillaTarjetaEl = document.getElementById('btnGuardarPlantillaTarjeta');
    if(btnGuardarPlantillaTarjetaEl){
        btnGuardarPlantillaTarjetaEl.addEventListener('click', async function(){
            const nombrePlantilla = (document.getElementById('inputPlantillaTarjetaNombre') || {}).value || '';
            const titulo          = (document.getElementById('inputPlantillaTarjetaTitulo') || {}).value || '';
            const descripcion     = (document.getElementById('inputPlantillaTarjetaDescripcion') || {}).value || '';

            if(!nombrePlantilla.trim()){ showPlantillaToast('El nombre de la plantilla es obligatorio.', 'error'); return; }
            if(!titulo.trim())         { showPlantillaToast('El titulo predeterminado es obligatorio.', 'error'); return; }

            const listaIds = getCheckedListaIds('checkboxListasAsociarCrear');
            const idColumnaDefecto   = parseInt((document.getElementById('selectPlantillaTarjetaColumna') || {}).value || '0', 10) || null;
            const idPrioridadDefecto = parseInt((document.getElementById('selectPlantillaTarjetaPrioridad') || {}).value || '0', 10) || null;

            btnGuardarPlantillaTarjetaEl.disabled = true;
            try {
                const resp = await fetch(`${APP_URL_ROOT}/tablero/create_plantilla_tarjeta`, {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify({id_tablero: idTableroActual, nombre_plantilla: nombrePlantilla.trim(), titulo: titulo.trim(), descripcion: descripcion.trim(), lista_ids: listaIds, id_columna_defecto: idColumnaDefecto, id_prioridad_defecto: idPrioridadDefecto})
                });
                const data = await resp.json();
                if(data.success){
                    $('#modalCrearPlantillaTarjeta').modal('hide');
                    document.getElementById('inputPlantillaTarjetaNombre').value = '';
                    document.getElementById('inputPlantillaTarjetaTitulo').value = '';
                    document.getElementById('inputPlantillaTarjetaDescripcion').value = '';
                    const selCol = document.getElementById('selectPlantillaTarjetaColumna');
                    const selPri = document.getElementById('selectPlantillaTarjetaPrioridad');
                    if(selCol) selCol.value = '';
                    if(selPri) selPri.value = '';
                    await cargarPlantillas();
                    renderGestorPlantillasTarjeta();
                    showPlantillaToast('Plantilla de tarjeta guardada correctamente.', 'success');
                    setTimeout(() => { $('#modalGestorPlantillasTarjeta').modal('show'); }, 320);
                } else {
                    showPlantillaToast(data.error || 'No se pudo guardar la plantilla', 'error');
                }
            } catch(e){ showPlantillaToast('Error de conexion al guardar la plantilla.', 'error'); }
            finally { btnGuardarPlantillaTarjetaEl.disabled = false; }
        });
    }

    // -- Agregar tarea al listado de la plantilla de lista (UI local)
    const btnAgregarTareaPlantillaListaEl = document.getElementById('btnAgregarTareaPlantillaLista');
    if(btnAgregarTareaPlantillaListaEl){
        btnAgregarTareaPlantillaListaEl.addEventListener('click', function(){
            const input = document.getElementById('inputNuevaTareaPlantillaLista');
            const desc  = (input ? input.value : '').trim();
            if(!desc) return;
            const contenedor = document.getElementById('contenedorTareasPlantillaLista');
            const idx = contenedor.querySelectorAll('.plantilla-tarea-item').length;
            const item = document.createElement('div');
            item.className = 'plantilla-tarea-item d-flex align-items-center gap-2 mb-1 border rounded px-2 py-1 bg-light';
            item.dataset.desc = desc;
            item.innerHTML = `<span class="flex-grow-1 small">${escapeHtmlPl(desc)}</span>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-rm-plantilla-tarea"><i class="bi bi-x"></i></button>`;
            contenedor.appendChild(item);
            if(input) input.value = '';
            input.focus();
        });
        document.getElementById('inputNuevaTareaPlantillaLista').addEventListener('keydown', function(e){
            if(e.key === 'Enter'){ e.preventDefault(); btnAgregarTareaPlantillaListaEl.click(); }
        });
    }

    document.addEventListener('click', function(e){
        if(e.target.closest('.btn-rm-plantilla-tarea')){
            e.target.closest('.plantilla-tarea-item').remove();
        }
    });

    // -- Guardar plantilla de lista de tareas
    const btnGuardarPlantillaListaEl = document.getElementById('btnGuardarPlantillaLista');
    if(btnGuardarPlantillaListaEl){
        btnGuardarPlantillaListaEl.addEventListener('click', async function(){
            const nombrePlantilla = (document.getElementById('inputPlantillaListaNombrePlantilla') || {}).value || '';
            const nombreLista     = (document.getElementById('inputPlantillaListaNombreLista') || {}).value || '';
            const contenedor      = document.getElementById('contenedorTareasPlantillaLista');
            const items           = contenedor ? Array.from(contenedor.querySelectorAll('.plantilla-tarea-item')) : [];
            const tareas          = items.map(el => ({ descripcion: el.dataset.desc || '' })).filter(t => t.descripcion !== '');

            if(!nombrePlantilla.trim()){ showPlantillaToast('El nombre de la plantilla es obligatorio.', 'error'); return; }
            if(!nombreLista.trim())    { showPlantillaToast('El nombre del listado es obligatorio.', 'error'); return; }
            if(tareas.length === 0)    { showPlantillaToast('Agregue al menos una tarea al listado.', 'error'); return; }

            btnGuardarPlantillaListaEl.disabled = true;
            try {
                const resp = await fetch(`${APP_URL_ROOT}/tablero/create_plantilla_lista`, {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify({id_tablero: idTableroActual, nombre_plantilla: nombrePlantilla.trim(), nombre_lista: nombreLista.trim(), tareas})
                });
                const data = await resp.json();
                if(data.success){
                    $('#modalCrearPlantillaLista').modal('hide');
                    document.getElementById('inputPlantillaListaNombrePlantilla').value = '';
                    document.getElementById('inputPlantillaListaNombreLista').value = '';
                    if(contenedor) contenedor.innerHTML = '';
                    await cargarPlantillas();
                    renderGestorPlantillasLista();
                    showPlantillaToast('Plantilla de listado guardada correctamente.', 'success');
                    setTimeout(() => { $('#modalGestorPlantillasLista').modal('show'); }, 320);
                } else {
                    showPlantillaToast(data.error || 'No se pudo guardar la plantilla de lista', 'error');
                }
            } catch(e){ showPlantillaToast('Error de conexion al guardar la plantilla.', 'error'); }
            finally { btnGuardarPlantillaListaEl.disabled = false; }
        });
    }

    // -- Modal de confirmacion para eliminar plantilla
    let _confirmarEliminarCallback = null;
    function confirmarEliminarPlantilla(nombre, callback){
        document.getElementById('confirmarEliminarPlantillaNombre').textContent = nombre;
        _confirmarEliminarCallback = callback;
        const $m = $('#modalConfirmarEliminarPlantilla');
        $m.modal('show');
        // Asegurar que queda por encima de cualquier otro modal abierto
        $m.on('shown.bs.modal.stacking', function(){
            const zTop = Math.max(1050, ...Array.from(document.querySelectorAll('.modal.show')).map(el => parseInt(window.getComputedStyle(el).zIndex) || 1050)) + 10;
            $(this).css('z-index', zTop);
            $('.modal-backdrop').last().css('z-index', zTop - 1);
            $m.off('shown.bs.modal.stacking');
        });
    }
    const btnConfirmarEliminarPlantillaEl = document.getElementById('btnConfirmarEliminarPlantilla');
    if(btnConfirmarEliminarPlantillaEl){
        btnConfirmarEliminarPlantillaEl.addEventListener('click', async function(){
            $('#modalConfirmarEliminarPlantilla').modal('hide');
            if(typeof _confirmarEliminarCallback === 'function'){
                await _confirmarEliminarCallback();
                _confirmarEliminarCallback = null;
            }
        });
    }
    // Limpiar callback si el modal se cierra sin confirmar
    const modalConfirmarEliminarEl = document.getElementById('modalConfirmarEliminarPlantilla');
    if(modalConfirmarEliminarEl){
        modalConfirmarEliminarEl.addEventListener('click', function(e){
            if(e.target.closest('[data-dismiss="modal"]') && !e.target.closest('#btnConfirmarEliminarPlantilla')){
                _confirmarEliminarCallback = null;
            }
        });
    }

    // Limpiar listas ids cuando se abre el modal sin plantilla
    const modalCreateTarjetaEl = document.getElementById('modalCreateTarjeta');
    if(modalCreateTarjetaEl){
        modalCreateTarjetaEl.addEventListener('show.bs.modal', function(){
            // Solo limpiar si NO se acaba de seleccionar una plantilla (lo gestiona el click handler del dropdown)
            // Se limpia al cerrar para la proxima apertura "en blanco"
        });
        modalCreateTarjetaEl.addEventListener('hidden.bs.modal', function(){
            const h = document.getElementById('hiddenPlantillaListasIds');
            if(h) h.value = '';
        });
    }

    // Cargar plantillas al inicio
    cargarPlantillas();

    // -- Gestor: editar / eliminar plantillas (delegated) --
    document.addEventListener('click', async function(e){

        // Editar plantilla de tarjeta
        const btnEditTarjeta = e.target.closest('.btn-editar-plantilla-tarjeta');
        if(btnEditTarjeta){
            const id = parseInt(btnEditTarjeta.dataset.id || '0', 10);
            const p  = tarjetasPlantillas.find(x => parseInt(x.Id_plantilla_tarjeta, 10) === id);
            if(!p) return;
            document.getElementById('inputEditarPlantillaTarjetaId').value        = id;
            document.getElementById('inputEditarPlantillaTarjetaNombre').value    = p.Nombre_plantilla || '';
            document.getElementById('inputEditarPlantillaTarjetaTitulo').value    = p.Titulo || '';
            document.getElementById('inputEditarPlantillaTarjetaDescripcion').value = p.Descripcion || '';
            const selEditCol = document.getElementById('selectEditarPlantillaTarjetaColumna');
            const selEditPri = document.getElementById('selectEditarPlantillaTarjetaPrioridad');
            if(selEditCol) selEditCol.value = p.Id_columna_defecto || '';
            if(selEditPri) selEditPri.value = p.Id_prioridad_defecto || '';
            $('#modalGestorPlantillasTarjeta').modal('hide');
            setTimeout(() => {
                renderCheckboxListasAsociar('checkboxListasAsociarEditar', p.lista_ids || []);
                $('#modalEditarPlantillaTarjeta').modal('show');
            }, 320);
            return;
        }

        // Eliminar plantilla de tarjeta
        const btnDelTarjeta = e.target.closest('.btn-eliminar-plantilla-tarjeta');
        if(btnDelTarjeta){
            const id     = parseInt(btnDelTarjeta.dataset.id || '0', 10);
            const nombre = btnDelTarjeta.dataset.nombre || '';
            confirmarEliminarPlantilla(nombre, async () => {
                try {
                    const resp = await fetch(`${APP_URL_ROOT}/tablero/delete_plantilla_tarjeta`, {
                        method: 'POST', headers: {'Content-Type':'application/json'},
                        credentials: 'same-origin',
                        body: JSON.stringify({id_tablero: idTableroActual, id_plantilla_tarjeta: id})
                    });
                    const data = await resp.json();
                    if(data.success){
                        await cargarPlantillas();
                        renderGestorPlantillasTarjeta();
                        showPlantillaToast('Plantilla eliminada correctamente.', 'success');
                        setTimeout(() => { $('#modalGestorPlantillasTarjeta').modal('show'); }, 320);
                    } else { showPlantillaToast(data.error || 'No se pudo eliminar la plantilla.', 'error'); }
                } catch(ex){ showPlantillaToast('Error de conexion.', 'error'); }
            });
            return;
        }

        // Editar plantilla de listado (carga detalles via API)
        const btnEditLista = e.target.closest('.btn-editar-plantilla-lista');
        if(btnEditLista){
            const id = parseInt(btnEditLista.dataset.id || '0', 10);
            try {
                const resp = await fetch(`${APP_URL_ROOT}/tablero/get_plantilla_lista_detalle`, {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify({id_tablero: idTableroActual, id_plantilla_lista: id})
                });
                const data = await resp.json();
                if(!data.success){ showPlantillaToast(data.error || 'Error al cargar plantilla.', 'error'); return; }
                const p = data.plantilla;
                document.getElementById('inputEditarPlantillaListaId').value                  = id;
                document.getElementById('inputEditarPlantillaListaNombrePlantilla').value     = p.Nombre_plantilla || '';
                document.getElementById('inputEditarPlantillaListaNombreLista').value         = p.Nombre_lista || '';
                document.getElementById('inputNuevaTareaEditarPlantillaLista').value          = '';
                const cont = document.getElementById('contenedorTareasEditarPlantillaLista');
                cont.innerHTML = '';
                (p.detalles || []).forEach(det => {
                    const item = document.createElement('div');
                    item.className = 'plantilla-tarea-item d-flex align-items-center gap-2 mb-1 border rounded px-2 py-1 bg-light';
                    item.dataset.desc = det.Descripcion || '';
                    item.innerHTML = `<span class="flex-grow-1 small">${escapeHtmlPl(det.Descripcion || '')}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-rm-plantilla-tarea"><i class="bi bi-x"></i></button>`;
                    cont.appendChild(item);
                });
                $('#modalGestorPlantillasLista').modal('hide');
                setTimeout(() => { $('#modalEditarPlantillaLista').modal('show'); }, 320);
            } catch(ex){ showPlantillaToast('Error de conexion.', 'error'); }
            return;
        }

        // Eliminar plantilla de listado
        const btnDelLista = e.target.closest('.btn-eliminar-plantilla-lista');
        if(btnDelLista){
            const id     = parseInt(btnDelLista.dataset.id || '0', 10);
            const nombre = btnDelLista.dataset.nombre || '';
            confirmarEliminarPlantilla(nombre, async () => {
                try {
                    const resp = await fetch(`${APP_URL_ROOT}/tablero/delete_plantilla_lista`, {
                        method: 'POST', headers: {'Content-Type':'application/json'},
                        credentials: 'same-origin',
                        body: JSON.stringify({id_tablero: idTableroActual, id_plantilla_lista: id})
                    });
                    const data = await resp.json();
                    if(data.success){
                        await cargarPlantillas();
                        renderGestorPlantillasLista();
                        showPlantillaToast('Plantilla eliminada correctamente.', 'success');
                        setTimeout(() => { $('#modalGestorPlantillasLista').modal('show'); }, 320);
                    } else { showPlantillaToast(data.error || 'No se pudo eliminar la plantilla.', 'error'); }
                } catch(ex){ showPlantillaToast('Error de conexion.', 'error'); }
            });
            return;
        }
    });

    // -- Guardar edicion de plantilla de tarjeta
    const btnGuardarEditarPlantillaTarjetaEl = document.getElementById('btnGuardarEditarPlantillaTarjeta');
    if(btnGuardarEditarPlantillaTarjetaEl){
        btnGuardarEditarPlantillaTarjetaEl.addEventListener('click', async function(){
            const id              = parseInt((document.getElementById('inputEditarPlantillaTarjetaId') || {}).value || '0', 10);
            const nombrePlantilla = ((document.getElementById('inputEditarPlantillaTarjetaNombre') || {}).value || '').trim();
            const titulo          = ((document.getElementById('inputEditarPlantillaTarjetaTitulo') || {}).value || '').trim();
            const descripcion     = ((document.getElementById('inputEditarPlantillaTarjetaDescripcion') || {}).value || '').trim();
            if(!nombrePlantilla){ showPlantillaToast('El nombre de la plantilla es obligatorio.', 'error'); return; }
            if(!titulo)          { showPlantillaToast('El titulo predeterminado es obligatorio.', 'error'); return; }
            const listaIds = getCheckedListaIds('checkboxListasAsociarEditar');
            const idColumnaDefecto   = parseInt((document.getElementById('selectEditarPlantillaTarjetaColumna') || {}).value || '0', 10) || null;
            const idPrioridadDefecto = parseInt((document.getElementById('selectEditarPlantillaTarjetaPrioridad') || {}).value || '0', 10) || null;
            btnGuardarEditarPlantillaTarjetaEl.disabled = true;
            try {
                const resp = await fetch(`${APP_URL_ROOT}/tablero/update_plantilla_tarjeta`, {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify({id_tablero: idTableroActual, id_plantilla_tarjeta: id, nombre_plantilla: nombrePlantilla, titulo, descripcion, lista_ids: listaIds, id_columna_defecto: idColumnaDefecto, id_prioridad_defecto: idPrioridadDefecto})
                });
                const data = await resp.json();
                if(data.success){
                    $('#modalEditarPlantillaTarjeta').modal('hide');
                    await cargarPlantillas();
                    renderGestorPlantillasTarjeta();
                    showPlantillaToast('Plantilla de tarjeta actualizada correctamente.', 'success');
                    setTimeout(() => { $('#modalGestorPlantillasTarjeta').modal('show'); }, 320);
                } else { showPlantillaToast(data.error || 'No se pudo actualizar la plantilla.', 'error'); }
            } catch(e){ showPlantillaToast('Error de conexion.', 'error'); }
            finally { btnGuardarEditarPlantillaTarjetaEl.disabled = false; }
        });
    }

    // -- Agregar tarea en modal editar lista
    const btnAgregarTareaEditarListaEl = document.getElementById('btnAgregarTareaEditarPlantillaLista');
    if(btnAgregarTareaEditarListaEl){
        btnAgregarTareaEditarListaEl.addEventListener('click', function(){
            const input = document.getElementById('inputNuevaTareaEditarPlantillaLista');
            const desc  = (input ? input.value : '').trim();
            if(!desc) return;
            const cont = document.getElementById('contenedorTareasEditarPlantillaLista');
            const item = document.createElement('div');
            item.className = 'plantilla-tarea-item d-flex align-items-center gap-2 mb-1 border rounded px-2 py-1 bg-light';
            item.dataset.desc = desc;
            item.innerHTML = `<span class="flex-grow-1 small">${escapeHtmlPl(desc)}</span>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btn-rm-plantilla-tarea"><i class="bi bi-x"></i></button>`;
            cont.appendChild(item);
            if(input) input.value = '';
            input.focus();
        });
        document.getElementById('inputNuevaTareaEditarPlantillaLista').addEventListener('keydown', function(e){
            if(e.key === 'Enter'){ e.preventDefault(); btnAgregarTareaEditarListaEl.click(); }
        });
    }

    // -- Guardar edicion de plantilla de listado
    const btnGuardarEditarPlantillaListaEl = document.getElementById('btnGuardarEditarPlantillaLista');
    if(btnGuardarEditarPlantillaListaEl){
        btnGuardarEditarPlantillaListaEl.addEventListener('click', async function(){
            const id              = parseInt((document.getElementById('inputEditarPlantillaListaId') || {}).value || '0', 10);
            const nombrePlantilla = ((document.getElementById('inputEditarPlantillaListaNombrePlantilla') || {}).value || '').trim();
            const nombreLista     = ((document.getElementById('inputEditarPlantillaListaNombreLista') || {}).value || '').trim();
            const cont            = document.getElementById('contenedorTareasEditarPlantillaLista');
            const tareas          = Array.from(cont ? cont.querySelectorAll('.plantilla-tarea-item') : [])
                                        .map(el => ({ descripcion: el.dataset.desc || '' }))
                                        .filter(t => t.descripcion !== '');
            if(!nombrePlantilla){ showPlantillaToast('El nombre de la plantilla es obligatorio.', 'error'); return; }
            if(!nombreLista)    { showPlantillaToast('El nombre del listado es obligatorio.', 'error'); return; }
            if(!tareas.length)  { showPlantillaToast('Agregue al menos una tarea al listado.', 'error'); return; }
            btnGuardarEditarPlantillaListaEl.disabled = true;
            try {
                const resp = await fetch(`${APP_URL_ROOT}/tablero/update_plantilla_lista`, {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify({id_tablero: idTableroActual, id_plantilla_lista: id, nombre_plantilla: nombrePlantilla, nombre_lista: nombreLista, tareas})
                });
                const data = await resp.json();
                if(data.success){
                    $('#modalEditarPlantillaLista').modal('hide');
                    await cargarPlantillas();
                    renderGestorPlantillasLista();
                    showPlantillaToast('Plantilla de listado actualizada correctamente.', 'success');
                    setTimeout(() => { $('#modalGestorPlantillasLista').modal('show'); }, 320);
                } else { showPlantillaToast(data.error || 'No se pudo actualizar la plantilla.', 'error'); }
            } catch(e){ showPlantillaToast('Error de conexion.', 'error'); }
            finally { btnGuardarEditarPlantillaListaEl.disabled = false; }
        });
    }

    function syncTableroScrollMetrics(){
        if(!tableroViewportEl){
            return;
        }

        const contentWidth = Math.max(
            tableroViewportEl.scrollWidth,
            tableroColumnsEl ? tableroColumnsEl.scrollWidth : 0
        );
        const contentHeight = Math.max(
            tableroViewportEl.scrollHeight,
            tableroColumnsEl ? tableroColumnsEl.scrollHeight : 0
        );

        tableroSyncXInnerEls.forEach(inner => {
            inner.style.width = `${contentWidth}px`;
        });

        tableroSyncYInnerEls.forEach(inner => {
            inner.style.height = `${contentHeight}px`;
        });

        const canScrollX = contentWidth > (tableroViewportEl.clientWidth + 1);
        const canScrollY = contentHeight > (tableroViewportEl.clientHeight + 1);

        tableroSyncXEls.forEach(el => {
            el.style.visibility = canScrollX ? 'visible' : 'hidden';
            el.style.pointerEvents = canScrollX ? 'auto' : 'none';
        });

        tableroSyncYEls.forEach(el => {
            el.style.visibility = canScrollY ? 'visible' : 'hidden';
            el.style.pointerEvents = canScrollY ? 'auto' : 'none';
        });

        tableroEdgeZonesEls.forEach(zone => {
            const dir = zone.dataset.autoscrollDir || '';
            const needsX = dir === 'left' || dir === 'right';
            const needsY = dir === 'up' || dir === 'down';
            const enabled = (needsX && canScrollX) || (needsY && canScrollY);
            zone.style.display = enabled ? 'flex' : 'none';
        });
    }

    function syncTableroFromViewport(){
        if(!tableroViewportEl || tableroSyncLocked){
            return;
        }

        tableroSyncLocked = true;
        const left = tableroViewportEl.scrollLeft;
        const top = tableroViewportEl.scrollTop;

        tableroSyncXEls.forEach(el => {
            if(el.scrollLeft !== left){
                el.scrollLeft = left;
            }
        });

        tableroSyncYEls.forEach(el => {
            if(el.scrollTop !== top){
                el.scrollTop = top;
            }
        });

        requestAnimationFrame(function(){
            tableroSyncLocked = false;
        });
    }

    function syncTableroFromHorizontalBar(sourceEl){
        if(!tableroViewportEl || !sourceEl || tableroSyncLocked){
            return;
        }

        tableroSyncLocked = true;
        const left = sourceEl.scrollLeft;
        tableroViewportEl.scrollLeft = left;

        tableroSyncXEls.forEach(el => {
            if(el !== sourceEl && el.scrollLeft !== left){
                el.scrollLeft = left;
            }
        });

        requestAnimationFrame(function(){
            tableroSyncLocked = false;
        });
    }

    function syncTableroFromVerticalBar(sourceEl){
        if(!tableroViewportEl || !sourceEl || tableroSyncLocked){
            return;
        }

        tableroSyncLocked = true;
        const top = sourceEl.scrollTop;
        tableroViewportEl.scrollTop = top;

        tableroSyncYEls.forEach(el => {
            if(el !== sourceEl && el.scrollTop !== top){
                el.scrollTop = top;
            }
        });

        requestAnimationFrame(function(){
            tableroSyncLocked = false;
        });
    }

    function stopTableroAutoScroll(){
        if(tableroAutoScrollDelayId){
            clearTimeout(tableroAutoScrollDelayId);
            tableroAutoScrollDelayId = null;
        }

        if(tableroAutoScrollIntervalId){
            clearInterval(tableroAutoScrollIntervalId);
            tableroAutoScrollIntervalId = null;
        }
    }

    function beginTableroAutoScroll(direction){
        if(!tableroViewportEl || !direction){
            return;
        }

        stopTableroAutoScroll();
        tableroAutoScrollDelayId = setTimeout(function(){
            tableroAutoScrollIntervalId = setInterval(function(){
                if(direction === 'left'){
                    tableroViewportEl.scrollLeft -= TABLERO_AUTOSCROLL_STEP_PX;
                } else if(direction === 'right'){
                    tableroViewportEl.scrollLeft += TABLERO_AUTOSCROLL_STEP_PX;
                } else if(direction === 'up'){
                    tableroViewportEl.scrollTop -= TABLERO_AUTOSCROLL_STEP_PX;
                } else if(direction === 'down'){
                    tableroViewportEl.scrollTop += TABLERO_AUTOSCROLL_STEP_PX;
                }
            }, 16);
        }, TABLERO_AUTOSCROLL_DELAY_MS);
    }

    function initTableroInteractiveScroll(){
        if(!tableroViewportEl || !tableroInteractiveShellEl){
            return;
        }

        if(tableroViewportEl.dataset.interactiveScrollInit === '1'){
            syncTableroScrollMetrics();
            syncTableroFromViewport();
            return;
        }
        tableroViewportEl.dataset.interactiveScrollInit = '1';

        tableroViewportEl.addEventListener('scroll', syncTableroFromViewport, { passive: true });

        tableroViewportEl.addEventListener('wheel', function(evt){
            const canScrollX = tableroViewportEl.scrollWidth > (tableroViewportEl.clientWidth + 1);
            if(!canScrollX){
                return;
            }

            const listEl = evt.target instanceof Element
                ? evt.target.closest('.tablero-card-list')
                : null;

            if(listEl && Math.abs(evt.deltaY) > 0){
                const maxTop = Math.max(0, listEl.scrollHeight - listEl.clientHeight);
                if(maxTop > 1){
                    const goingDown = evt.deltaY > 0;
                    const atTop = listEl.scrollTop <= 0;
                    const atBottom = listEl.scrollTop >= (maxTop - 1);
                    const canContinueVertical = (goingDown && !atBottom) || (!goingDown && !atTop);

                    // Si la columna aun puede desplazarse, no convertimos el gesto a horizontal.
                    if(canContinueVertical){
                        return;
                    }
                }
            }

            const hasHorizontalGesture = Math.abs(evt.deltaX) > 0;
            const shouldMapVerticalToHorizontal = !hasHorizontalGesture && Math.abs(evt.deltaY) > 0;

            if(shouldMapVerticalToHorizontal){
                tableroViewportEl.scrollLeft += evt.deltaY;
                evt.preventDefault();
            }
        }, { passive: false });

        tableroSyncXEls.forEach(el => {
            el.addEventListener('scroll', function(){
                syncTableroFromHorizontalBar(el);
            }, { passive: true });
        });

        tableroSyncYEls.forEach(el => {
            el.addEventListener('scroll', function(){
                syncTableroFromVerticalBar(el);
            }, { passive: true });
        });

        tableroEdgeZonesEls.forEach(zone => {
            zone.addEventListener('mouseenter', function(){
                beginTableroAutoScroll(zone.dataset.autoscrollDir || '');
            });
            zone.addEventListener('mouseleave', stopTableroAutoScroll);
        });

        tableroInteractiveShellEl.addEventListener('mouseleave', stopTableroAutoScroll);
        tableroViewportEl.addEventListener('pointerdown', stopTableroAutoScroll);
        window.addEventListener('resize', syncTableroScrollMetrics);

        if(window.ResizeObserver){
            const observer = new ResizeObserver(function(){
                syncTableroScrollMetrics();
                syncTableroFromViewport();
            });
            observer.observe(tableroViewportEl);
            if(tableroColumnsEl){
                observer.observe(tableroColumnsEl);
            }
        }

        syncTableroScrollMetrics();
        syncTableroFromViewport();
    }

    function formatSeconds(total){
        const sec = Math.max(0, parseInt(total || 0, 10));
        const hh = String(Math.floor(sec / 3600)).padStart(2, '0');
        const mm = String(Math.floor((sec % 3600) / 60)).padStart(2, '0');
        const ss = String(sec % 60).padStart(2, '0');
        return `${hh}:${mm}:${ss}`;
    }

    function parseDateToTimestamp(dateStr){
        if(!dateStr) return null;
        const dt = new Date(dateStr.replace(' ', 'T'));
        return isNaN(dt.getTime()) ? null : Math.floor(dt.getTime() / 1000);
    }

    function parseDurationToSeconds(value){
        const text = String(value || '').trim();
        const match = text.match(/^(\d{1,3}):([0-5]\d):([0-5]\d)$/);
        if(!match){
            return null;
        }

        const hh = parseInt(match[1], 10);
        const mm = parseInt(match[2], 10);
        const ss = parseInt(match[3], 10);

        if(Number.isNaN(hh) || Number.isNaN(mm) || Number.isNaN(ss)){
            return null;
        }

        return (hh * 3600) + (mm * 60) + ss;
    }

    function recalculateModalTiempoUsuariosTotal(){
        if(!contenedorTiempoUsuariosRowsEl || !modalTiempoUsuariosTotalEl) return;

        let total = 0;
        contenedorTiempoUsuariosRowsEl.querySelectorAll('.tiempo-usuario-hms').forEach(input => {
            const seconds = parseDurationToSeconds(input.value || '');
            if(seconds !== null){
                total += seconds;
            }
        });

        modalTiempoUsuariosTotalEl.textContent = formatSeconds(total);
    }

    function setRowsCheckedInTiempoUsuarios(checked){
        if(!contenedorTiempoUsuariosRowsEl) return;

        contenedorTiempoUsuariosRowsEl.querySelectorAll('.tiempo-usuario-check').forEach(chk => {
            chk.checked = !!checked;
        });
    }

    function applySameTimeToSelectedRows(){
        if(!contenedorTiempoUsuariosRowsEl || !modalTiempoUsuariosAplicarTodosEl) return;

        const value = String(modalTiempoUsuariosAplicarTodosEl.value || '').trim();
        const seconds = parseDurationToSeconds(value);
        if(seconds === null){
            alert('Formato invalido. Use hh:mm:ss, por ejemplo 01:25:30.');
            return;
        }

        const checkedRows = contenedorTiempoUsuariosRowsEl.querySelectorAll('.tiempo-usuario-check:checked');
        if(!checkedRows.length){
            alert('Seleccione al menos un usuario.');
            return;
        }

        checkedRows.forEach(chk => {
            const row = chk.closest('.tiempo-usuario-row');
            const input = row ? row.querySelector('.tiempo-usuario-hms') : null;
            if(input){
                input.value = value;
            }
        });

        recalculateModalTiempoUsuariosTotal();
    }

    function openModalTiempoUsuarios(detalleId, tiempos){
        if(!modalEditarTiempoUsuariosEl || !contenedorTiempoUsuariosRowsEl || !modalTiempoUsuariosDetalleIdEl){
            return;
        }

        const items = Array.isArray(tiempos) ? tiempos : [];

        modalTiempoUsuariosDetalleIdEl.value = String(detalleId);
        if(modalTiempoUsuariosAplicarTodosEl){
            modalTiempoUsuariosAplicarTodosEl.value = '';
        }

        if(modalTiempoUsuariosInfoEl){
            modalTiempoUsuariosInfoEl.textContent = items.length > 1
                ? 'Este detalle tiene varios usuarios con tiempo acumulado. Puede editar uno o varios usuarios sin perder los demas registros.'
                : 'Edite manualmente el tiempo del usuario asociado a esta tarea.';
        }

        const rowsHtml = items.map(item => {
            const userId = parseInt(item.Id_usuario || 0, 10) || 0;
            const email = String(item.email || '').trim();
            const userLabel = email !== '' ? email : ('Usuario #' + userId);
            const base = parseInt(item.Tiempo_total_segundos || 0, 10) || 0;
            const running = parseInt(item.Tiempo_en_curso_segundos || 0, 10) || 0;
            const total = base + running;

            return `
                <div class="d-flex align-items-center gap-2 mb-2 tiempo-usuario-row" data-user-id="${userId}">
                    <input class="form-check-input tiempo-usuario-check" type="checkbox" checked>
                    <div class="small flex-grow-1">${escapeHtml(userLabel)}</div>
                    <input type="text" class="form-control form-control-sm tiempo-usuario-hms" value="${formatSeconds(total)}" style="max-width:130px;">
                </div>
            `;
        }).join('');

        contenedorTiempoUsuariosRowsEl.innerHTML = rowsHtml || '<div class="text-muted small">Sin tiempos para editar.</div>';

        contenedorTiempoUsuariosRowsEl.querySelectorAll('.tiempo-usuario-hms').forEach(input => {
            input.addEventListener('input', recalculateModalTiempoUsuariosTotal);
            input.addEventListener('change', recalculateModalTiempoUsuariosTotal);
        });

        recalculateModalTiempoUsuariosTotal();
        showModal(modalEditarTiempoUsuariosEl);
    }

    async function postJson(url, payload){
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if(!response.ok || !data.success){
            throw new Error(data.error || 'Error en la solicitud');
        }
        return data;
    }

    const BOARD_SYNC_POLL_MS = 6000;
    let boardSyncLastHistorialId = 0;
    let boardSyncTimerId = null;
    let boardSyncPollInFlight = false;
    let boardSyncReloadScheduled = false;
    let boardSyncPendingReload = false;
    let boardSyncPendingNoticeEl = null;
    let boardSyncPendingNoticeTimerId = null;
    let boardSyncLastNoticeHistorialId = 0;

    function closeBoardSyncPendingNotice(){
        if(boardSyncPendingNoticeTimerId){
            window.clearTimeout(boardSyncPendingNoticeTimerId);
            boardSyncPendingNoticeTimerId = null;
        }
        if(boardSyncPendingNoticeEl){
            boardSyncPendingNoticeEl.remove();
            boardSyncPendingNoticeEl = null;
        }
    }

    function reloadBoardPreservingOpenTasksModal(){
        try {
            const tasksModalOpen = !!tarjetaTareasActualId
                && modalTarjetaTareasEl
                && modalTarjetaTareasEl.classList.contains('show');

            if(tasksModalOpen){
                sessionStorage.setItem('tablero_sync_resume_modal', JSON.stringify({
                    id_tablero: idTableroActual,
                    modal: 'tarjeta_tareas',
                    id_tarjeta: parseInt(tarjetaTareasActualId || '0', 10) || 0
                }));
            }
        } catch(_err){
            // Silencio intencional: si sessionStorage falla, continuamos con recarga normal.
        }

        window.location.reload();
    }

    function restoreBoardModalAfterSyncReload(){
        try {
            const raw = sessionStorage.getItem('tablero_sync_resume_modal');
            if(!raw){
                return;
            }

            sessionStorage.removeItem('tablero_sync_resume_modal');
            const payload = JSON.parse(raw);
            if(!payload || String(payload.modal || '') !== 'tarjeta_tareas'){
                return;
            }

            const tableroPayload = parseInt(payload.id_tablero || '0', 10) || 0;
            const tarjetaPayload = parseInt(payload.id_tarjeta || '0', 10) || 0;
            if(tableroPayload !== idTableroActual || tarjetaPayload <= 0){
                return;
            }

            const openBtn = document.querySelector(`.btn-open-tareas[data-tarjeta-id="${tarjetaPayload}"]`);
            if(openBtn){
                window.setTimeout(() => {
                    openBtn.click();
                }, 140);
            }
        } catch(_err){
            // Silencio intencional: no bloquea la carga del tablero.
        }
    }

    function notifyBoardExternalChangesAndReload(latestHistorialId){
        if(boardSyncReloadScheduled){
            return;
        }

        const anyModalOpen = !!document.querySelector('.modal.show');
        const tasksModalOpen = anyModalOpen && !!tarjetaTareasActualId
            && modalTarjetaTareasEl && modalTarjetaTareasEl.classList.contains('show');

        if(anyModalOpen){
            boardSyncPendingReload = true;

            if(tasksModalOpen){
                cargarModalTareas().catch(() => {});
            }

            const latestId = parseInt(latestHistorialId || '0', 10) || 0;
            if(!boardSyncPendingNoticeEl && latestId > boardSyncLastNoticeHistorialId){
                boardSyncLastNoticeHistorialId = latestId;

                boardSyncPendingNoticeEl = document.createElement('div');
                boardSyncPendingNoticeEl.className = 'alert alert-warning shadow-sm small mb-0';
                boardSyncPendingNoticeEl.setAttribute('role', 'alert');
                boardSyncPendingNoticeEl.style.position = 'fixed';
                boardSyncPendingNoticeEl.style.bottom = '16px';
                boardSyncPendingNoticeEl.style.right = '16px';
                boardSyncPendingNoticeEl.style.zIndex = '1090';
                boardSyncPendingNoticeEl.style.maxWidth = '430px';
                boardSyncPendingNoticeEl.innerHTML = `
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-grow-1">
                            <i class="bi bi-clock-history"></i> Hay cambios en el tablero. Se actualizara al cerrar este panel.
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary js-sync-notice-dismiss" style="min-width:132px;white-space:nowrap;">Cerrar</button>
                            <button type="button" class="btn btn-sm btn-primary js-sync-notice-refresh" style="min-width:132px;white-space:nowrap;">Actualizar ahora</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(boardSyncPendingNoticeEl);

                const refreshBtn = boardSyncPendingNoticeEl.querySelector('.js-sync-notice-refresh');
                if(refreshBtn){
                    refreshBtn.addEventListener('click', function(){
                        closeBoardSyncPendingNotice();
                        reloadBoardPreservingOpenTasksModal();
                    });
                }

                const dismissBtn = boardSyncPendingNoticeEl.querySelector('.js-sync-notice-dismiss');
                if(dismissBtn){
                    dismissBtn.addEventListener('click', closeBoardSyncPendingNotice);
                }

                boardSyncPendingNoticeTimerId = window.setTimeout(() => {
                    closeBoardSyncPendingNotice();
                }, 10000);
            }

            return;
        }

        boardSyncReloadScheduled = true;

        const notice = document.createElement('div');
        notice.className = 'alert alert-info shadow-sm';
        notice.setAttribute('role', 'alert');
        notice.style.position = 'fixed';
        notice.style.top = '16px';
        notice.style.right = '16px';
        notice.style.zIndex = '1080';
        notice.style.maxWidth = '420px';
        notice.innerHTML = '<i class="bi bi-arrow-repeat"></i> Se detectaron cambios en el tablero por otro usuario. Sincronizando...';
        document.body.appendChild(notice);

        window.setTimeout(() => {
            window.location.reload();
        }, 1100);
    }

    async function pollBoardSyncStatus(){
        if(idTableroActual <= 0 || boardSyncReloadScheduled || boardSyncPollInFlight){
            return;
        }
        if(document.visibilityState === 'hidden'){
            return;
        }

        boardSyncPollInFlight = true;
        try {
            const url = `${APP_URL_ROOT}/tablero/get_tablero_sync_status?id_tablero=${encodeURIComponent(idTableroActual)}&since_historial=${encodeURIComponent(boardSyncLastHistorialId)}`;
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });

            if(!response.ok){
                return;
            }

            const data = await response.json();
            if(!data || !data.success){
                return;
            }

            const latestHistorialId = parseInt(data.latest_historial_id || '0', 10) || 0;
            const hasChanges = !!data.has_changes;

            if(boardSyncLastHistorialId === 0){
                boardSyncLastHistorialId = latestHistorialId;
                return;
            }

            if(hasChanges && latestHistorialId > boardSyncLastHistorialId){
                notifyBoardExternalChangesAndReload(latestHistorialId);
                boardSyncLastHistorialId = latestHistorialId;
                return;
            }

            if(latestHistorialId > boardSyncLastHistorialId){
                boardSyncLastHistorialId = latestHistorialId;
            }
        } catch(_err) {
            // Silencio intencional: si falla momentaneamente, el siguiente ciclo vuelve a intentar.
        } finally {
            boardSyncPollInFlight = false;
        }
    }

    function startBoardSyncPolling(){
        if(idTableroActual <= 0){
            return;
        }

        pollBoardSyncStatus();

        if(boardSyncTimerId){
            window.clearInterval(boardSyncTimerId);
        }

        boardSyncTimerId = window.setInterval(() => {
            pollBoardSyncStatus();
        }, BOARD_SYNC_POLL_MS);

        document.addEventListener('visibilitychange', () => {
            if(document.visibilityState === 'visible'){
                pollBoardSyncStatus();
            }
        });

        window.addEventListener('beforeunload', () => {
            if(boardSyncTimerId){
                window.clearInterval(boardSyncTimerId);
            }
        }, { once: true });

        function _doBoardSyncReloadIfModalsClosed(){
            if(!boardSyncPendingReload) return;
            if(document.querySelector('.modal.show')) return;
            boardSyncPendingReload = false;
            closeBoardSyncPendingNotice();
            window.location.reload();
        }

        // Escucha via jQuery (close button, ESC, hide programatico)
        if(window.jQuery){
            window.jQuery(document).on('hidden.bs.modal', function(){
                window.setTimeout(_doBoardSyncReloadIfModalsClosed, 50);
            });
        }

        // MutationObserver en cada modal: captura clic en backdrop y cualquier otro cierre
        // que no burbujee correctamente el evento jQuery
        if(window.MutationObserver){
            const _boardSyncObserver = new MutationObserver(function(){
                window.setTimeout(_doBoardSyncReloadIfModalsClosed, 50);
            });
            document.querySelectorAll('.modal').forEach(function(modalEl){
                _boardSyncObserver.observe(modalEl, { attributes: true, attributeFilter: ['class'] });
            });
        }
    }

    function showModal(modalEl){
        if(!modalEl) return;
        if(window.jQuery && window.jQuery.fn && window.jQuery.fn.modal){
            window.jQuery(modalEl).modal('show');
            return;
        }
        if(window.bootstrap && window.bootstrap.Modal){
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
            return;
        }
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        modalEl.removeAttribute('aria-hidden');
    }

    function hideModal(modalEl){
        if(!modalEl) return;
        if(window.jQuery && window.jQuery.fn && window.jQuery.fn.modal){
            window.jQuery(modalEl).modal('hide');
            return;
        }
        if(window.bootstrap && window.bootstrap.Modal){
            window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            return;
        }
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden', 'true');
    }

    function openEditarListaTareaModal(idTarea, nombreActual){
        if(!modalEditarListaTareaEl || !inputEditarListaTareaNombreEl){
            return;
        }

        tareaEditandoId = parseInt(idTarea || '0', 10) || 0;
        inputEditarListaTareaNombreEl.value = nombreActual || '';
        showModal(modalEditarListaTareaEl);

        window.setTimeout(() => {
            inputEditarListaTareaNombreEl.focus();
            inputEditarListaTareaNombreEl.select();
        }, 80);
    }

    function closeEditarListaTareaModal(){
        if(!modalEditarListaTareaEl){
            return;
        }
        hideModal(modalEditarListaTareaEl);
    }

    function openEditarDetalleTareaModal(idDetalle, descripcionActual){
        if(!modalEditarDetalleTareaEl || !inputEditarDetalleTareaDescripcionEl){
            return;
        }

        detalleTareaEditandoId = parseInt(idDetalle || '0', 10) || 0;
        inputEditarDetalleTareaDescripcionEl.value = descripcionActual || '';
        showModal(modalEditarDetalleTareaEl);

        window.setTimeout(() => {
            inputEditarDetalleTareaDescripcionEl.focus();
            inputEditarDetalleTareaDescripcionEl.select();
        }, 80);
    }

    function closeEditarDetalleTareaModal(){
        if(!modalEditarDetalleTareaEl){
            return;
        }
        hideModal(modalEditarDetalleTareaEl);
    }

    function openConfirmActionModal(config){
        pendingConfirmAction = config && typeof config.onConfirm === 'function' ? config.onConfirm : null;
        if(modalConfirmarAccionTituloEl){
            modalConfirmarAccionTituloEl.innerHTML = config && config.title ? config.title : '<i class="bi bi-exclamation-triangle"></i> Confirmar accion';
        }
        if(modalConfirmarAccionMensajeEl){
            modalConfirmarAccionMensajeEl.textContent = config && config.message ? config.message : '¿Desea continuar?';
        }
        if(btnConfirmarAccionTableroEl){
            btnConfirmarAccionTableroEl.textContent = config && config.confirmText
                ? String(config.confirmText)
                : confirmActionDefaultLabel;
        }
        showModal(modalConfirmarAccionEl);
    }

    function escapeHtml(text){
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function formatDateTime(dateStr){
        if(!dateStr) return '-';
        const dt = new Date(dateStr.replace(' ', 'T'));
        if(isNaN(dt.getTime())) return dateStr;
        return dt.toLocaleString();
    }

    function updateTarjetaTiempoDisplay(tarjetaId, totalSegundos, enCurso = null){
        const display = document.querySelector(`.timer-display[data-tarjeta-id="${tarjetaId}"]`);
        if(!display) return;
        const safeTotal = parseInt(totalSegundos || 0, 10) || 0;
        display.dataset.baseSeconds = String(safeTotal);

        if(enCurso !== null){
            display.dataset.running = enCurso ? '1' : '0';
        }

        const isRunning = String(display.dataset.running || '0') === '1';
        display.dataset.renderedAtMs = isRunning ? String(Date.now()) : '';

        updateTarjetaTiempoState(display.closest('.tarjeta-tiempo-box'));

        if(isRunning){
            startLocalTimer(tarjetaId);
        } else {
            stopLocalTimer(tarjetaId, safeTotal);
        }
    }

    function updateTarjetaTiempoState(boxEl){
        if(!boxEl) return;

        const display = boxEl.querySelector('.timer-display');
        const statusLabel = boxEl.querySelector('.timer-status-label');
        const isRunning = display && String(display.dataset.running || '0') === '1';

        boxEl.classList.toggle('tarjeta-tiempo-running', !!isRunning);
        boxEl.classList.toggle('tarjeta-tiempo-idle', !isRunning);

        if(statusLabel){
            statusLabel.textContent = isRunning ? 'En Curso' : 'Tiempo total';
        }
    }

    function clearDetailTimerIntervals(){
        Object.keys(timerIntervals).forEach(key => {
            if(String(key).indexOf('detalle-') === 0){
                clearInterval(timerIntervals[key]);
                delete timerIntervals[key];
            }
        });
    }

    function refreshDetailTimerDisplay(display){
        if(!display) return;

        const baseSeconds = parseInt(display.dataset.baseSeconds || '0', 10);
        const runningStart = parseDateToTimestamp(display.dataset.runningStart || '');

        let total = baseSeconds;
        if(runningStart){
            const now = Math.floor(Date.now() / 1000);
            total += Math.max(0, now - runningStart);
        }

        display.textContent = formatSeconds(total);
    }

    function refreshTiempoUsuarioBadge(badge){
        if(!badge) return;

        const baseSeconds = parseInt(badge.dataset.baseSeconds || '0', 10) || 0;
        const isRunning = String(badge.dataset.running || '0') === '1';
        const renderedAtMs = parseInt(badge.dataset.renderedAtMs || '0', 10) || 0;

        let total = baseSeconds;
        if(isRunning && renderedAtMs > 0){
            const elapsed = Math.floor((Date.now() - renderedAtMs) / 1000);
            total += Math.max(0, elapsed);
        }

        const label = badge.dataset.userLabel || 'Usuario';
        badge.textContent = `${label}: ${formatSeconds(total)}${isRunning ? ' (en curso)' : ''}`;
    }

    function refreshTiempoUsuarioBadgesForDetalle(detailContainer){
        if(!detailContainer) return;
        detailContainer.querySelectorAll('.tiempo-usuario-badge').forEach(refreshTiempoUsuarioBadge);
    }

    function initDetailTimers(){
        clearDetailTimerIntervals();

        document.querySelectorAll('.detalle-timer-display').forEach(display => {
            const detailContainer = display.closest('.tarea-detalle-item');
            refreshDetailTimerDisplay(display);
            refreshTiempoUsuarioBadgesForDetalle(detailContainer);

            if(display.dataset.runningStart){
                const intervalKey = `detalle-${display.dataset.detalleId}`;
                timerIntervals[intervalKey] = setInterval(() => {
                    refreshDetailTimerDisplay(display);
                    refreshTiempoUsuarioBadgesForDetalle(detailContainer);
                }, 1000);
            }
        });
    }

    function syncEmptyColumnState(listEl){
        if(!listEl) return;

        const hasCards = !!listEl.querySelector('.tablero-tarjeta:not(.d-none)');
        const emptyMsg = listEl.querySelector('.empty-column-msg');

        if(hasCards){
            if(emptyMsg) emptyMsg.remove();
            return;
        }

        if(!emptyMsg){
            const small = document.createElement('small');
            small.className = 'text-muted empty-column-msg';
            small.textContent = 'Sin tarjetas';
            listEl.appendChild(small);
        }
    }

    function syncColumnCounter(listEl){
        if(!listEl) return;

        const columnaCard = listEl.closest('.tablero-columna');
        if(!columnaCard) return;

        const counterBadge = columnaCard.querySelector('.card-header .badge');
        if(!counterBadge) return;

        const totalTarjetas = listEl.querySelectorAll('.tablero-tarjeta:not(.d-none)').length;
        counterBadge.textContent = String(totalTarjetas);
    }

    function normalizeText(text){
        return String(text || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function applyTarjetaFilters(){
        const query = normalizeText(filtroTarjetaNombreEl ? filtroTarjetaNombreEl.value : '');
        const etiquetaId = filtroTarjetaEtiquetaEl ? String(filtroTarjetaEtiquetaEl.value || '') : '';
        const prioridadId = filtroTarjetaPrioridadEl ? String(filtroTarjetaPrioridadEl.value || '') : '';
        const mostrarArchivadas = switchMostrarArchivadas ? switchMostrarArchivadas.checked : false;

        document.querySelectorAll('.tablero-tarjeta').forEach(card => {
            const titleEl = card.querySelector('.card-title');
            const titulo = normalizeText(titleEl ? titleEl.textContent : '');
            const cardPrioridadId = String(card.dataset.prioridadId || '');
            const cardEtiquetas = parseIdList(card.dataset.etiquetaIds || '');
            const esArchivada = card.dataset.tarjetaArchivada === '1';

            const matchNombre = query === '' || titulo.includes(query);
            const matchEtiqueta = etiquetaId === '' || cardEtiquetas.includes(etiquetaId);
            const matchPrioridad = prioridadId === '' || cardPrioridadId === prioridadId;
            const matchArchivada = mostrarArchivadas || !esArchivada;
            const visible = matchNombre && matchEtiqueta && matchPrioridad && matchArchivada;

            card.classList.toggle('d-none', !visible);
        });

        document.querySelectorAll('.tablero-card-list').forEach(listEl => {
            syncEmptyColumnState(listEl);
            syncColumnCounter(listEl);
        });

        syncTableroScrollMetrics();
    }

    function parseIdList(value){
        return String(value || '')
            .split(',')
            .map(item => item.trim())
            .filter(item => item !== '');
    }

    function getContratoIdFromUserSelect(userSelectEl){
        if(!userSelectEl) return '';

        const selectedOption = userSelectEl.options[userSelectEl.selectedIndex];
        if(selectedOption && selectedOption.dataset && selectedOption.dataset.contratoId){
            return String(selectedOption.dataset.contratoId || '');
        }

        return '';
    }

    function filterAlcanceOptionsByUser(alcanceSelectEl, userSelectEl){
        if(!alcanceSelectEl) return;

        if(!userSelectEl){
            alcanceSelectEl.disabled = false;
            Array.from(alcanceSelectEl.options).forEach(opt => { opt.hidden = false; });
            return;
        }

        const contratoId = getContratoIdFromUserSelect(userSelectEl);
        let selectedStillValid = false;
        const hasSelectedUser = !!(userSelectEl && String(userSelectEl.value || '') !== '');

        Array.from(alcanceSelectEl.options).forEach(opt => {
            if(opt.value === ''){
                opt.hidden = false;
                return;
            }

            const optionContratoId = String((opt.dataset && opt.dataset.contratoId) || '');
            const visible = hasSelectedUser && contratoId !== '' && optionContratoId === contratoId;
            opt.hidden = !visible;

            if(visible && String(opt.value) === String(alcanceSelectEl.value || '')){
                selectedStillValid = true;
            }
        });

        if(String(alcanceSelectEl.value || '') !== '' && !selectedStillValid){
            alcanceSelectEl.value = '';
        }

        alcanceSelectEl.disabled = !hasSelectedUser;
    }

    function filterActividadOptions(actividadSelectEl, alcanceSelectEl){
        if(!actividadSelectEl) return;

        const selectedAlcanceId = String(alcanceSelectEl ? (alcanceSelectEl.value || '') : '');
        let selectedStillValid = false;

        Array.from(actividadSelectEl.options).forEach(opt => {
            if(opt.value === ''){
                opt.hidden = false;
                return;
            }

            const optionAlcanceId = String((opt.dataset && opt.dataset.alcanceId) || '');
            const visible = selectedAlcanceId === '' ? true : (optionAlcanceId === selectedAlcanceId);
            opt.hidden = !visible;

            if(visible && String(opt.value) === String(actividadSelectEl.value || '')){
                selectedStillValid = true;
            }
        });

        if(String(actividadSelectEl.value || '') !== '' && !selectedStillValid){
            actividadSelectEl.value = '';
        }

        actividadSelectEl.disabled = selectedAlcanceId === '';
    }

    function syncCreateTarjetaActivityFilters(){
        filterAlcanceOptionsByUser(createTarjetaAlcanceEl, createTarjetaAsignadoEl);
        filterActividadOptions(createTarjetaActividadEl, createTarjetaAlcanceEl);
    }

    function syncEditTarjetaActivityFilters(){
        filterAlcanceOptionsByUser(editTarjetaAlcanceEl, editTarjetaAsignadoEl);
        filterActividadOptions(editTarjetaActividadEl, editTarjetaAlcanceEl);
    }

    function setCheckedValues(selector, values){
        const normalized = new Set((values || []).map(value => String(value)));
        document.querySelectorAll(selector).forEach(checkbox => {
            checkbox.checked = normalized.has(String(checkbox.value));
        });
    }

    function toggleFechaInputs(checkboxEl, fechaInicioEl, fechaFinEl){
        const enabled = !!(checkboxEl && checkboxEl.checked);

        if(fechaInicioEl){
            fechaInicioEl.disabled = !enabled;
            if(!enabled) fechaInicioEl.value = '';
        }
        if(fechaFinEl){
            fechaFinEl.disabled = !enabled;
            if(!enabled) fechaFinEl.value = '';
        }
    }

    function getTarjetaEstadoMeta(completado){
        return completado
            ? { label: 'Completado', badgeClass: 'bg-success' }
            : { label: 'Pendiente', badgeClass: 'bg-secondary' };
    }

    function renderTarjetaEstadoBadge(completado){
        const meta = getTarjetaEstadoMeta(!!completado);
        return `<span class="badge ${meta.badgeClass}">${meta.label}</span>`;
    }

    function updateModalTarjetaEstado(completado){
        if(modalTarjetaTareasEstadoEl){
            modalTarjetaTareasEstadoEl.innerHTML = renderTarjetaEstadoBadge(!!completado);
        }

        if(modalTarjetaCompletadoEl){
            modalTarjetaCompletadoEl.checked = !!completado;
        }
    }

    function updateTarjetaCompletionUI(tarjetaId, completado){
        const completed = !!completado;
        const estado = getTarjetaEstadoMeta(completed).label;
        const tarjetaEl = document.querySelector(`.tablero-tarjeta[data-tarjeta-id="${tarjetaId}"]`);

        if(tarjetaEl){
            tarjetaEl.dataset.tarjetaCompletado = completed ? '1' : '0';
            tarjetaEl.dataset.tarjetaEstado = estado;
            tarjetaEl.classList.toggle('tablero-tarjeta--completada', completed);
        }

        document.querySelectorAll(`.tarjeta-completado-toggle[data-tarjeta-id="${tarjetaId}"]`).forEach(input => {
            input.checked = completed;
        });

        document.querySelectorAll(`.tarjeta-estado-badge[data-tarjeta-id="${tarjetaId}"]`).forEach(badge => {
            badge.classList.remove('bg-success', 'bg-secondary');
            badge.classList.add(completed ? 'bg-success' : 'bg-secondary');
            badge.textContent = estado;
        });

        document.querySelectorAll(`.btn-edit-tarjeta[data-tarjeta-id="${tarjetaId}"]`).forEach(btn => {
            btn.dataset.tarjetaCompletado = completed ? '1' : '0';
        });

        document.querySelectorAll(`.btn-open-tareas[data-tarjeta-id="${tarjetaId}"]`).forEach(btn => {
            btn.dataset.tarjetaCompletado = completed ? '1' : '0';
        });

        if(tarjetaTareasActualId === tarjetaId){
            updateModalTarjetaEstado(completed);
        }
    }

    async function toggleTarjetaCompletado(tarjetaId, completado){
        const data = await postJson(`${APP_URL_ROOT}/tablero/toggle_tarjeta_completado`, {
            id_tablero: idTableroActual,
            id_tarjeta: tarjetaId,
            completado: completado ? 1 : 0
        });

        updateTarjetaCompletionUI(tarjetaId, !!data.completado);

        if(tarjetaTareasActualId === tarjetaId){
            await cargarModalTareas();
        }

        return data;
    }

    function resetEtiquetaForm(){
        if(!formEtiquetaTableroEl) return;

        formEtiquetaTableroEl.action = `${APP_URL_ROOT}/tablero/create_etiqueta`;
        if(inputEtiquetaNombreEl) inputEtiquetaNombreEl.value = '';
        if(inputEtiquetaColorEl) inputEtiquetaColorEl.value = '#0d6efd';
        if(btnGuardarEtiquetaTableroEl) btnGuardarEtiquetaTableroEl.textContent = 'Guardar';
        if(btnCancelarEdicionEtiquetaEl) btnCancelarEdicionEtiquetaEl.classList.add('d-none');
    }

    function resetPrioridadForm(){
        if(!formPrioridadTableroEl) return;

        formPrioridadTableroEl.action = `${APP_URL_ROOT}/tablero/create_prioridad`;
        if(inputPrioridadNombreEl) inputPrioridadNombreEl.value = '';
        if(inputPrioridadValorEl) inputPrioridadValorEl.value = '1';
        if(inputPrioridadColorEl) inputPrioridadColorEl.value = '#6c757d';
        if(btnGuardarPrioridadTableroEl) btnGuardarPrioridadTableroEl.textContent = 'Guardar';
        if(btnCancelarEdicionPrioridadEl) btnCancelarEdicionPrioridadEl.classList.add('d-none');
    }

    let historialData = [];

    function getHistorialMeta(tipo){
        const map = {
            // --- Tarjeta ---
            'tarjeta_creada':        { cat:'tarjeta', icon:'bi-plus-circle-fill',   color:'text-primary',  label:'Tarjeta creada' },
            'tarjeta_editada':       { cat:'tarjeta', icon:'bi-pencil-fill',         color:'text-primary',  label:'Tarjeta editada' },
            'tarjeta_eliminada':     { cat:'tarjeta', icon:'bi-trash-fill',          color:'text-danger',   label:'Tarjeta eliminada' },
            'tarjeta_estado':        { cat:'tarjeta', icon:'bi-toggle-on',           color:'text-primary',  label:'Estado de tarjeta' },
            'tarjeta_movida':        { cat:'tarjeta', icon:'bi-arrows-move',         color:'text-primary',  label:'Tarjeta movida' },
            'tarjeta_asignacion':    { cat:'tarjeta', icon:'bi-person-fill',         color:'text-primary',  label:'Asignación de tarjeta' },
            // --- Lista de tareas ---
            'tarea_creada':          { cat:'lista',   icon:'bi-list-task',           color:'text-info',     label:'Lista creada' },
            'tarea_lista_editada':   { cat:'lista',   icon:'bi-pencil-fill',         color:'text-info',     label:'Lista editada' },
            'tarea_lista_eliminada': { cat:'lista',   icon:'bi-trash-fill',          color:'text-danger',   label:'Lista eliminada' },
            // --- Tarea (ítem) ---
            'tarea_detalle_creado':      { cat:'tarea', icon:'bi-plus-square-fill',   color:'text-success',  label:'Ítem creado' },
            'tarea_detalle_editado':     { cat:'tarea', icon:'bi-pencil-square',      color:'text-success',  label:'Ítem editado' },
            'tarea_detalle_eliminado':   { cat:'tarea', icon:'bi-trash-fill',         color:'text-danger',   label:'Ítem eliminado' },
            'tarea_detalle_estado':      { cat:'tarea', icon:'bi-check2-square',      color:'text-success',  label:'Estado de ítem' },
            'tarea_detalle_asignacion':  { cat:'tarea', icon:'bi-person-check-fill',  color:'text-success',  label:'Asignación de ítem' },
            // --- Tiempo ---
            'timer_inicio':                  { cat:'tiempo', icon:'bi-play-circle-fill',  color:'text-warning',  label:'Tiempo iniciado' },
            'timer_fin':                     { cat:'tiempo', icon:'bi-stop-circle-fill',  color:'text-warning',  label:'Tiempo detenido' },
            'timer_detalle_inicio':          { cat:'tiempo', icon:'bi-play-fill',         color:'text-warning',  label:'Tiempo ítem iniciado' },
            'timer_detalle_fin':             { cat:'tiempo', icon:'bi-stop-fill',         color:'text-warning',  label:'Tiempo ítem detenido' },
            'timer_detalle_manual':          { cat:'tiempo', icon:'bi-clock-fill',        color:'text-warning',  label:'Tiempo manual' },
            'timer_detalle_manual_usuarios': { cat:'tiempo', icon:'bi-clock-history',     color:'text-warning',  label:'Tiempo manual (usuarios)' },
        };
        return map[tipo] || { cat:'otro', icon:'bi-info-circle-fill', color:'text-secondary', label: tipo || 'Evento' };
    }

    function filterAndRenderHistorial(){
        if(!contenedorHistorialTarjetaEl) return;
        const query   = (document.getElementById('inputFiltroHistorial')?.value || '').toLowerCase().trim();
        const catFilt = (document.getElementById('selectFiltroHistorialCategoria')?.value || '');
        const contadorEl = document.getElementById('historialContador');

        const filtered = historialData.filter(item => {
            const meta = getHistorialMeta(item.Tipo_evento || '');
            if(catFilt && meta.cat !== catFilt) return false;
            if(query){
                const text = ((item.Mensaje || '') + ' ' + (item.Usuario_email || '') + ' ' + (meta.label || '')).toLowerCase();
                if(!text.includes(query)) return false;
            }
            return true;
        });

        if(contadorEl){
            contadorEl.textContent = filtered.length + ' de ' + historialData.length + ' registros';
        }

        if(!filtered.length){
            contenedorHistorialTarjetaEl.innerHTML = '<div class="text-muted small p-1">' + (historialData.length ? 'Sin resultados para el filtro aplicado.' : 'Sin historial registrado.') + '</div>';
            return;
        }

        const html = filtered.map(item => {
            const meta    = getHistorialMeta(item.Tipo_evento || '');
            const usuario = item.Usuario_email ? escapeHtml(item.Usuario_email) : 'Sistema';
            const mensaje = escapeHtml(item.Mensaje || '');
            const fecha   = formatDateTime(item.Fecha_creacion || '');
            const badgeCat = (meta.cat === 'tiempo')
                ? 'badge-warning'
                : (/elimina/i.test(item.Tipo_evento || '') ? 'badge-danger' : {
                    tarjeta: 'badge-primary',
                    lista:   'badge-info',
                    tarea:   'badge-success',
                    otro:    'badge-secondary',
                }[meta.cat] || 'badge-secondary');
            return `
                <div class="border-bottom pb-2 mb-2 historial-item" data-categoria="${meta.cat}">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi ${meta.icon} ${meta.color} mt-1 flex-shrink-0" style="font-size:1rem;"></i>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
                                <span class="badge ${badgeCat}" style="font-size:0.72rem;">${escapeHtml(meta.label)}</span>
                                <span class="small font-weight-bold text-truncate">${escapeHtml(usuario)}</span>
                            </div>
                            <div class="small text-break">${mensaje}</div>
                            <div class="text-muted" style="font-size:0.75rem;">${fecha}</div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        contenedorHistorialTarjetaEl.innerHTML = html;
    }

    function renderHistorial(items){
        historialData = Array.isArray(items) ? items : [];
        filterAndRenderHistorial();
    }

    function buildDetalleUsuarioOptions(selectedId){
        const selected = selectedId !== null && selectedId !== undefined ? String(selectedId) : '';
        let html = '<option value="">Sin asignar</option>';

        (usuariosAsignadosTarea || []).forEach(usuario => {
            const id = String(usuario.id || '');
            if(id === '') return;

            const selectedAttr = id === selected ? ' selected' : '';
            html += `<option value="${escapeHtml(id)}"${selectedAttr}>${escapeHtml(usuario.email || ('Usuario #' + id))}</option>`;
        });

        return html;
    }

    function renderTiempoPorUsuarioBadges(tiempos, runningUserId = null){
        const items = Array.isArray(tiempos) ? tiempos : [];
        if(!items.length){
            return '<span class="text-muted">Sin tiempo por usuario</span>';
        }

        return items.map(item => {
            const email = String(item.email || '').trim();
            const usuarioLabel = email !== '' ? email : `Usuario #${parseInt(item.Id_usuario || 0, 10)}`;
            const userId = parseInt(item.Id_usuario || 0, 10) || 0;
            const totalBase = parseInt(item.Tiempo_total_segundos || 0, 10) || 0;
            const totalRunning = parseInt(item.Tiempo_en_curso_segundos || 0, 10) || 0;
            const total = totalBase + totalRunning;
            const isRunning = runningUserId
                ? (userId > 0 && userId === parseInt(runningUserId, 10))
                : (totalRunning > 0);

            return `<span class="badge bg-light text-dark border tiempo-usuario-badge" data-user-id="${userId}" data-user-label="${escapeHtml(usuarioLabel)}" data-base-seconds="${total}" data-running="${isRunning ? '1' : '0'}" data-rendered-at-ms="${Date.now()}">${escapeHtml(usuarioLabel)}: ${formatSeconds(total)}${isRunning ? ' (en curso)' : ''}</span>`;
        }).join(' ');
    }

    function getDetalleUsuarioFiltroValue(){
        return filtroDetalleUsuarioEl ? String(filtroDetalleUsuarioEl.value || '') : '';
    }

    function resolveAssignedUserForNewDetail(){
        const filtro = getDetalleUsuarioFiltroValue();
        if(filtro === '__me__'){
            return currentUserId;
        }
        if(filtro === '__none__' || filtro === ''){
            return null;
        }

        const parsed = parseInt(filtro, 10);
        return Number.isNaN(parsed) || parsed <= 0 ? null : parsed;
    }

    function filterDetallesByUsuario(detalles){
        const filtro = getDetalleUsuarioFiltroValue();
        const rows = Array.isArray(detalles) ? detalles : [];
        if(filtro === ''){
            return rows;
        }

        if(filtro === '__me__'){
            return rows.filter(det => parseInt(det.Id_usuario_asignado || 0, 10) === currentUserId);
        }

        if(filtro === '__none__'){
            return rows.filter(det => !det.Id_usuario_asignado || parseInt(det.Id_usuario_asignado, 10) <= 0);
        }

        const target = parseInt(filtro, 10);
        if(Number.isNaN(target) || target <= 0){
            return rows;
        }

        return rows.filter(det => parseInt(det.Id_usuario_asignado || 0, 10) === target);
    }

    function renderTareas(tareas){
        if(!contenedorListasTareasEl) return;

        Object.keys(detalleTiempoUsuarioMap).forEach(key => {
            delete detalleTiempoUsuarioMap[key];
        });

        if(!tareas || !tareas.length){
            contenedorListasTareasEl.innerHTML = '<div class="alert alert-light border">Sin listas de tareas en esta tarjeta.</div>';
            return;
        }

        const html = tareas.map(tarea => {
            const total = parseInt(tarea.Total_detalles || 0, 10);
            const done = parseInt(tarea.Total_detalles_completados || 0, 10);
            const detalles = filterDetallesByUsuario(Array.isArray(tarea.detalles) ? tarea.detalles : []);

            const detallesHtml = detalles.map(det => {
                const checked = parseInt(det.Completado || 0, 10) === 1;
                const detText = escapeHtml(det.Descripcion || '');
                const totalDetalle = parseInt(det.Tiempo_Total_Segundos || 0, 10);
                const runningInicio = det.Running_inicio || '';
                const hasRunning = !!(det.Running_tiempo_detalle_id && runningInicio);
                const editableTimer = canEditTime && !hasRunning;
                const runningUserId = parseInt(det.Running_usuario_id || 0, 10) || 0;
                const idUsuarioAsignado = det.Id_usuario_asignado ? parseInt(det.Id_usuario_asignado, 10) : null;
                const usuarioAsignadoEmail = String(det.Usuario_asignado_email || '').trim();
                const tiempoPorUsuario = Array.isArray(det.Tiempo_por_usuario) ? det.Tiempo_por_usuario : [];
                detalleTiempoUsuarioMap[String(parseInt(det.Id_tarea_detalle || 0, 10))] = tiempoPorUsuario;
                const timerActionAllowed = idUsuarioAsignado !== null && idUsuarioAsignado === currentUserId;
                let timerActionReason = '';
                if(idUsuarioAsignado === null){
                    timerActionReason = 'title="Asigne un usuario a la tarea para operar el cronometro"';
                } else if(!timerActionAllowed){
                    timerActionReason = 'title="Solo el usuario asignado puede operar el cronometro"';
                }
                return `
                    <div class="border rounded px-2 py-2 mb-2 bg-white tarea-detalle-item">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="form-check mb-0 flex-grow-1">
                                <input class="form-check-input tarea-detalle-toggle" type="checkbox" data-detalle-id="${parseInt(det.Id_tarea_detalle || 0, 10)}" ${checked ? 'checked' : ''} ${canMarkDone ? '' : 'disabled'}>
                                <label class="form-check-label small ${checked ? 'text-decoration-line-through text-muted' : ''}">${detText}</label>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                ${canEditTask ? `<button class="btn btn-sm btn-outline-primary btn-edit-detalle" type="button" data-detalle-id="${parseInt(det.Id_tarea_detalle || 0, 10)}" data-detalle-descripcion="${detText}" title="Editar tarea"><i class="bi bi-pencil-square"></i></button>` : ''}
                                ${canDeleteTask ? `<button class="btn btn-sm btn-outline-danger btn-delete-detalle" type="button" data-detalle-id="${parseInt(det.Id_tarea_detalle || 0, 10)}" data-detalle-descripcion="${detText}" data-detalle-tiempo-segundos="${totalDetalle}" data-detalle-running="${hasRunning ? '1' : '0'}" title="Eliminar tarea"><i class="bi bi-trash"></i></button>` : ''}
                                <span class="badge detalle-timer-display ${editableTimer ? 'detalle-timer-display--editable' : ''}" style="background:#fff3cd;color:#7a4b00;border:1px solid #f1d58a;font-weight:700;min-width:78px;" data-detalle-id="${parseInt(det.Id_tarea_detalle || 0, 10)}" data-base-seconds="${totalDetalle}" data-running-start="${escapeHtml(runningInicio)}" data-manual-editable="${editableTimer ? '1' : '0'}" title="${editableTimer ? 'Click para editar (hh:mm:ss)' : (hasRunning ? 'Cronometro en curso' : 'Sin permiso para editar tiempo')}">00:00:00</span>
                                ${canTrackTime ? `
                                    <button class="btn btn-sm btn-success btn-start-detalle-timer" type="button" data-detalle-id="${parseInt(det.Id_tarea_detalle || 0, 10)}" ${hasRunning || !timerActionAllowed ? 'disabled' : ''} ${timerActionReason}><i class="bi bi-play-fill"></i></button>
                                    <button class="btn btn-sm btn-danger btn-stop-detalle-timer" type="button" data-detalle-id="${parseInt(det.Id_tarea_detalle || 0, 10)}" ${hasRunning && timerActionAllowed ? '' : 'disabled'} ${timerActionReason}><i class="bi bi-stop-fill"></i></button>
                                ` : ''}
                            </div>
                        </div>
                        <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                            <span class="small text-muted">Asignado:</span>
                            ${canAssignTaskUser
                                ? `<select class="form-select form-select-sm detalle-usuario-asignado tablero-select-enhanced" style="max-width:280px;" data-detalle-id="${parseInt(det.Id_tarea_detalle || 0, 10)}" data-prev-value="${idUsuarioAsignado ? String(idUsuarioAsignado) : ''}" ${hasRunning ? 'disabled title="No se puede reasignar mientras existe un cronometro en curso."' : ''}>${buildDetalleUsuarioOptions(idUsuarioAsignado)}</select>`
                                : `<span class="badge bg-light text-dark border">${usuarioAsignadoEmail !== '' ? escapeHtml(usuarioAsignadoEmail) : 'Sin asignar'}</span>`}
                        </div>
                        <div class="mt-2 d-flex flex-wrap gap-1 small">
                            ${renderTiempoPorUsuarioBadges(tiempoPorUsuario, runningUserId)}
                        </div>
                    </div>
                `;
            }).join('');

            return `
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="fw-bold small">${escapeHtml(tarea.Nombre_tarea || '')}</div>
                        <div class="d-flex align-items-center gap-2">
                            ${total > 0 && done === total ? '<span class="text-success" title="Lista completada"><i class="bi bi-check-circle-fill"></i></span>' : ''}
                            <span class="badge ${total > 0 && done === total ? 'bg-success' : 'bg-secondary'}">${done}/${total}</span>
                            ${canEditList ? `<button class="btn btn-sm btn-outline-primary btn-edit-tarea" type="button" data-tarea-id="${parseInt(tarea.Id_tarea || 0, 10)}" data-tarea-nombre="${escapeHtml(tarea.Nombre_tarea || '')}" title="Editar lista"><i class="bi bi-pencil-square"></i></button>` : ''}
                            ${canDeleteList && total === 0 ? `<button class="btn btn-sm btn-outline-danger btn-delete-tarea" type="button" data-tarea-id="${parseInt(tarea.Id_tarea || 0, 10)}" title="Eliminar lista"><i class="bi bi-trash"></i></button>` : ''}
                        </div>
                    </div>
                    <div class="card-body">
                        ${detallesHtml || '<small class="text-muted">Sin items.</small>'}
                        ${canCreateTask ? `
                            <div class="input-group input-group-sm mt-2">
                                <input type="text" class="form-control input-nuevo-detalle" maxlength="255" placeholder="Nuevo item" data-tarea-id="${parseInt(tarea.Id_tarea || 0, 10)}">
                                <button class="btn btn-outline-primary btn-agregar-detalle" type="button" data-tarea-id="${parseInt(tarea.Id_tarea || 0, 10)}">Agregar</button>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');

        contenedorListasTareasEl.innerHTML = html;
        initDetailTimers();
    }

    function updateTarjetaTareasCounter(tarjetaId, tareas){
        const btn = document.querySelector(`.btn-open-tareas[data-tarjeta-id="${tarjetaId}"]`);
        if(!btn) return;

        const badge = btn.querySelector('.badge');
        if(!badge) return;

        const listas = Array.isArray(tareas) ? tareas : [];
        let total = 0;
        let completadas = 0;

        listas.forEach(tarea => {
            total += parseInt(tarea.Total_detalles || 0, 10) || 0;
            completadas += parseInt(tarea.Total_detalles_completados || 0, 10) || 0;
        });

        badge.textContent = `${completadas}/${total}`;
    }

    if(btnConfirmarAccionTableroEl){
        btnConfirmarAccionTableroEl.addEventListener('click', async function(){
            if(!pendingConfirmAction){
                hideModal(modalConfirmarAccionEl);
                return;
            }

            const action = pendingConfirmAction;
            pendingConfirmAction = null;
            btnConfirmarAccionTableroEl.disabled = true;

            try {
                await action();
                hideModal(modalConfirmarAccionEl);
            } catch(err){
                alert(err.message);
            } finally {
                btnConfirmarAccionTableroEl.disabled = false;
            }
        });
    }

    async function cargarModalTareas(){
        if(!tarjetaTareasActualId) return;

        try {
            const data = await postJson(`${APP_URL_ROOT}/tablero/get_tarjeta_tareas`, {
                id_tablero: idTableroActual,
                id_tarjeta: tarjetaTareasActualId
            });
            renderTareas(data.tareas || []);
            updateTarjetaTareasCounter(tarjetaTareasActualId, data.tareas || []);
            renderHistorial(data.historial || []);
            updateTarjetaTiempoDisplay(tarjetaTareasActualId, data.total_tarjeta_segundos || 0, !!data.en_curso_tiempo);
            if(modalTarjetaTareasTituloEl){
                modalTarjetaTareasTituloEl.textContent = data.tarjeta_titulo || modalTarjetaTareasTituloEl.textContent;
            }
            if(modalTarjetaTareasDescripcionEl){
                modalTarjetaTareasDescripcionEl.textContent = data.tarjeta_descripcion ? data.tarjeta_descripcion : 'Sin descripcion';
            }
            if(modalTarjetaTareasActividadEl){
                if(data.tarjeta_actividad_id){
                    const desc = String(data.tarjeta_actividad_descripcion || '').trim();
                    modalTarjetaTareasActividadEl.innerHTML = desc
                        ? `<span class="badge bg-secondary">Actividad #${data.tarjeta_actividad_id}</span><span class="text-muted ms-2">${escapeHtml(desc)}</span>`
                        : `<span class="badge bg-secondary">Actividad #${data.tarjeta_actividad_id}</span>`;
                } else {
                    modalTarjetaTareasActividadEl.innerHTML = '<span class="text-muted">Sin actividad vinculada</span>';
                }
            }
            if(modalTarjetaTareasPrioridadEl){
                if(data.tarjeta_prioridad_nombre){
                    const valor = data.tarjeta_prioridad_valor !== null && data.tarjeta_prioridad_valor !== undefined
                        ? ` (${parseInt(data.tarjeta_prioridad_valor, 10)})`
                        : '';
                    modalTarjetaTareasPrioridadEl.innerHTML = `<span class="badge" style="background:${escapeHtml(data.tarjeta_prioridad_color || '#6c757d')};color:#fff;">${escapeHtml(data.tarjeta_prioridad_nombre)}${escapeHtml(valor)}</span>`;
                } else {
                    modalTarjetaTareasPrioridadEl.innerHTML = '<span class="text-muted">Sin prioridad asignada</span>';
                }
            }
            updateModalTarjetaEstado(parseInt(data.tarjeta_completado || 0, 10) === 1);
            if(modalTarjetaTareasEtiquetasEl){
                const etiquetas = Array.isArray(data.tarjeta_etiquetas) ? data.tarjeta_etiquetas : [];
                if(etiquetas.length){
                    modalTarjetaTareasEtiquetasEl.innerHTML = etiquetas.map(et => {
                        const nombre = String(et.nombre || '').trim();
                        const color = String(et.color || '#6c757d');
                        return `<span class="badge border" style="background:${escapeHtml(color)};color:#fff;min-width:24px;">${nombre ? escapeHtml(nombre) : '&nbsp;'}</span>`;
                    }).join('');
                } else {
                    modalTarjetaTareasEtiquetasEl.innerHTML = '<span class="text-muted">Sin etiquetas asignadas</span>';
                }
            }
        } catch(err){
            if(contenedorListasTareasEl) contenedorListasTareasEl.innerHTML = `<div class="alert alert-danger">${escapeHtml(err.message)}</div>`;
        }
    }

    function refreshTimerDisplayForCard(tarjetaEl){
        const display = tarjetaEl.querySelector('.timer-display');
        if(!display) return;

        const baseSeconds = parseInt(display.dataset.baseSeconds || '0', 10);
        const isRunning = String(display.dataset.running || '0') === '1';
        const renderedAtMs = parseInt(display.dataset.renderedAtMs || '0', 10) || 0;

        let total = baseSeconds;
        if(isRunning && renderedAtMs > 0){
            const elapsed = Math.floor((Date.now() - renderedAtMs) / 1000);
            total += Math.max(0, elapsed);
        }

        display.textContent = formatSeconds(total);
    }

    function startLocalTimer(tarjetaId){
        const tarjetaEl = document.querySelector(`.tablero-tarjeta[data-tarjeta-id="${tarjetaId}"]`);
        if(!tarjetaEl) return;

        const display = tarjetaEl.querySelector('.timer-display');
        if(display){
            display.dataset.running = '1';
            if(!display.dataset.renderedAtMs){
                display.dataset.renderedAtMs = String(Date.now());
            }
        }

        if(timerIntervals[tarjetaId]) clearInterval(timerIntervals[tarjetaId]);
        refreshTimerDisplayForCard(tarjetaEl);
        timerIntervals[tarjetaId] = setInterval(() => refreshTimerDisplayForCard(tarjetaEl), 1000);
    }

    function stopLocalTimer(tarjetaId, baseSeconds){
        const tarjetaEl = document.querySelector(`.tablero-tarjeta[data-tarjeta-id="${tarjetaId}"]`);
        if(!tarjetaEl) return;

        const display = tarjetaEl.querySelector('.timer-display');
        if(display){
            display.dataset.baseSeconds = String(baseSeconds || 0);
            display.dataset.running = '0';
            display.dataset.renderedAtMs = '';
            display.textContent = formatSeconds(baseSeconds || 0);
        }

        if(timerIntervals[tarjetaId]){
            clearInterval(timerIntervals[tarjetaId]);
            delete timerIntervals[tarjetaId];
        }
    }

    document.querySelectorAll('.timer-display').forEach(display => {
        const tarjetaEl = display.closest('.tablero-tarjeta');
        if(!tarjetaEl) return;
        updateTarjetaTiempoState(display.closest('.tarjeta-tiempo-box'));
        if(String(display.dataset.running || '0') === '1' && !display.dataset.renderedAtMs){
            display.dataset.renderedAtMs = String(Date.now());
        }
        refreshTimerDisplayForCard(tarjetaEl);
        if(String(display.dataset.running || '0') === '1') startLocalTimer(display.dataset.tarjetaId);
    });

    if(filtroTarjetaNombreEl){
        filtroTarjetaNombreEl.addEventListener('input', applyTarjetaFilters);
    }
    if(filtroTarjetaEtiquetaEl){
        filtroTarjetaEtiquetaEl.addEventListener('change', applyTarjetaFilters);
    }
    if(filtroTarjetaPrioridadEl){
        filtroTarjetaPrioridadEl.addEventListener('change', applyTarjetaFilters);
    }
    if(btnLimpiarFiltrosTarjetaEl){
        btnLimpiarFiltrosTarjetaEl.addEventListener('click', function(){
            if(filtroTarjetaNombreEl) filtroTarjetaNombreEl.value = '';
            if(filtroTarjetaEtiquetaEl) filtroTarjetaEtiquetaEl.value = '';
            if(filtroTarjetaPrioridadEl) filtroTarjetaPrioridadEl.value = '';
            if(switchMostrarArchivadas) switchMostrarArchivadas.checked = false;
            applyTarjetaFilters();
        });
    }

    if(canMoveCard && window.Sortable){
        document.querySelectorAll('.tablero-card-list').forEach(listEl => {
            syncEmptyColumnState(listEl);
            syncColumnCounter(listEl);
            Sortable.create(listEl, {
                group: 'tablero-board',
                animation: 150,
                onEnd: async function(evt){
                    const tarjetaEl = evt.item;
                    const idTarjeta = parseInt(tarjetaEl.dataset.tarjetaId || '0', 10);
                    const idColumna = parseInt(evt.to.dataset.columnaId || '0', 10);
                    const posicion = evt.newIndex;

                    try {
                        await postJson(`${APP_URL_ROOT}/tablero/move_tarjeta`, {
                            id_tablero: idTableroActual,
                            id_tarjeta: idTarjeta,
                            id_columna: idColumna,
                            posicion: posicion
                        });

                        // Mantener sincronizado el estado local para que el modal de editar
                        // refleje la columna real luego de mover por drag & drop.
                        tarjetaEl.dataset.columnaId = String(idColumna);
                        tarjetaEl.querySelectorAll('.btn-edit-tarjeta').forEach(btn => {
                            btn.dataset.columnaId = String(idColumna);
                        });

                        syncEmptyColumnState(evt.from);
                        syncEmptyColumnState(evt.to);
                        syncColumnCounter(evt.from);
                        syncColumnCounter(evt.to);
                    } catch(err){
                        alert(err.message);
                        window.location.reload();
                    }
                }
            });
        });
    } else {
        document.querySelectorAll('.tablero-card-list').forEach(listEl => {
            syncEmptyColumnState(listEl);
            syncColumnCounter(listEl);
        });
    }

    initTableroInteractiveScroll();
    applyTarjetaFilters();
    startBoardSyncPolling();

    if(canMarkDone){
        document.querySelectorAll('.tarjeta-completado-toggle').forEach(input => {
            input.addEventListener('change', async function(){
                const tarjetaId = parseInt(this.dataset.tarjetaId || '0', 10);
                const checked = this.checked;

                try {
                    await toggleTarjetaCompletado(tarjetaId, checked);
                } catch(err){
                    this.checked = !checked;
                    alert(err.message);
                }
            });
        });

        if(modalTarjetaCompletadoEl){
            modalTarjetaCompletadoEl.addEventListener('change', async function(){
                if(!tarjetaTareasActualId) return;

                const checked = this.checked;

                try {
                    await toggleTarjetaCompletado(tarjetaTareasActualId, checked);
                } catch(err){
                    this.checked = !checked;
                    alert(err.message);
                }
            });
        }
    }

    if(canEditCard){
        document.querySelectorAll('.checklist-toggle').forEach(input => {
            input.addEventListener('change', async function(){
                const tarjetaEl = this.closest('.tablero-tarjeta');
                if(!tarjetaEl) return;

                const idTarjeta = parseInt(tarjetaEl.dataset.tarjetaId || '0', 10);
                const checklistItems = [];

                tarjetaEl.querySelectorAll('.checklist-toggle').forEach(toggle => {
                    const labelEl = toggle.parentElement.querySelector('label');
                    const text = labelEl ? labelEl.textContent.trim() : '';
                    checklistItems.push({ text, done: !!toggle.checked });

                    if(labelEl){
                        if(toggle.checked) labelEl.classList.add('text-decoration-line-through', 'text-muted');
                        else labelEl.classList.remove('text-decoration-line-through', 'text-muted');
                    }
                });

                try {
                    await postJson(`${APP_URL_ROOT}/tablero/update_checklist`, {
                        id_tablero: idTableroActual,
                        id_tarjeta: idTarjeta,
                        checklist: checklistItems
                    });
                } catch(err){
                    alert(err.message);
                }
            });
        });
    }

    if(canTrackTime){
        document.querySelectorAll('.btn-start-timer').forEach(btn => {
            btn.addEventListener('click', async function(){
                const idTarjeta = parseInt(this.dataset.tarjetaId || '0', 10);
                const tarjetaEl = this.closest('.tablero-tarjeta');
                const display = tarjetaEl ? tarjetaEl.querySelector('.timer-display') : null;
                const btnStop = tarjetaEl ? tarjetaEl.querySelector('.btn-stop-timer') : null;

                try {
                    const data = await postJson(`${APP_URL_ROOT}/tablero/start_timer`, {
                        id_tablero: idTableroActual,
                        id_tarjeta: idTarjeta
                    });
                    if(display){
                        display.dataset.baseSeconds = String(data.total_segundos || 0);
                        display.dataset.running = '1';
                        display.dataset.renderedAtMs = String(Date.now());
                    }
                    startLocalTimer(idTarjeta);
                    this.disabled = true;
                    if(btnStop) btnStop.disabled = false;
                } catch(err){
                    alert(err.message);
                }
            });
        });

        document.querySelectorAll('.btn-stop-timer').forEach(btn => {
            btn.addEventListener('click', async function(){
                const idTarjeta = parseInt(this.dataset.tarjetaId || '0', 10);
                const tarjetaEl = this.closest('.tablero-tarjeta');
                const btnStart = tarjetaEl ? tarjetaEl.querySelector('.btn-start-timer') : null;

                try {
                    const data = await postJson(`${APP_URL_ROOT}/tablero/stop_timer`, {
                        id_tablero: idTableroActual,
                        id_tarjeta: idTarjeta
                    });
                    stopLocalTimer(idTarjeta, data.total_segundos || 0);
                    this.disabled = true;
                    if(btnStart) btnStart.disabled = false;
                } catch(err){
                    alert(err.message);
                }
            });
        });
    }

    if(btnTiempoUsuariosSeleccionarTodosEl){
        btnTiempoUsuariosSeleccionarTodosEl.addEventListener('click', function(){
            setRowsCheckedInTiempoUsuarios(true);
        });
    }

    if(btnTiempoUsuariosDeseleccionarTodosEl){
        btnTiempoUsuariosDeseleccionarTodosEl.addEventListener('click', function(){
            setRowsCheckedInTiempoUsuarios(false);
        });
    }

    if(btnTiempoUsuariosAplicarSeleccionadosEl){
        btnTiempoUsuariosAplicarSeleccionadosEl.addEventListener('click', function(){
            applySameTimeToSelectedRows();
        });
    }

    if(btnGuardarTiempoUsuariosEl){
        btnGuardarTiempoUsuariosEl.addEventListener('click', async function(){
            const idDetalle = parseInt(modalTiempoUsuariosDetalleIdEl ? (modalTiempoUsuariosDetalleIdEl.value || '0') : '0', 10);
            if(!idDetalle || !contenedorTiempoUsuariosRowsEl || !tarjetaTareasActualId){
                return;
            }

            const updates = [];
            const checkedRows = contenedorTiempoUsuariosRowsEl.querySelectorAll('.tiempo-usuario-check:checked');
            if(!checkedRows.length){
                alert('Seleccione al menos un usuario para editar.');
                return;
            }

            for(const chk of checkedRows){
                const row = chk.closest('.tiempo-usuario-row');
                if(!row) continue;

                const userId = parseInt(row.dataset.userId || '0', 10);
                const input = row.querySelector('.tiempo-usuario-hms');
                const value = input ? String(input.value || '').trim() : '';
                const parsed = parseDurationToSeconds(value);

                if(userId <= 0){
                    continue;
                }

                if(parsed === null){
                    alert('Formato invalido en uno de los tiempos. Use hh:mm:ss.');
                    return;
                }

                updates.push({ id_usuario: userId, tiempo_hms: value });
            }

            if(!updates.length){
                alert('No hay cambios validos para guardar.');
                return;
            }

            openConfirmActionModal({
                title: '<i class="bi bi-exclamation-triangle"></i> Confirmar reemplazo de tiempo',
                message: 'El tiempo manual reemplazara el acumulado actual de los usuarios seleccionados. No se sumara al valor anterior. ¿Desea continuar?',
                confirmText: 'Si, de acuerdo',
                onConfirm: async function(){
                    try {
                        await postJson(`${APP_URL_ROOT}/tablero/update_tarea_detalle_tiempo_manual_usuarios`, {
                            id_tablero: idTableroActual,
                            id_tarea_detalle: idDetalle,
                            updates: updates
                        });
                        hideModal(modalEditarTiempoUsuariosEl);
                        await cargarModalTareas();
                    } catch(err){
                        alert(err.message);
                    }
                }
            });
        });
    }

    if(createTarjetaActividadEl || createTarjetaAlcanceEl || createTarjetaAsignadoEl){
        if(createTarjetaAsignadoEl){
            createTarjetaAsignadoEl.addEventListener('change', syncCreateTarjetaActivityFilters);
        }
        if(createTarjetaAlcanceEl){
            createTarjetaAlcanceEl.addEventListener('change', syncCreateTarjetaActivityFilters);
        }
        syncCreateTarjetaActivityFilters();
    }

    if(createTarjetaUsarFechasEl){
        createTarjetaUsarFechasEl.addEventListener('change', function(){
            toggleFechaInputs(createTarjetaUsarFechasEl, createTarjetaFechaInicioEl, createTarjetaFechaFinEl);
        });
        toggleFechaInputs(createTarjetaUsarFechasEl, createTarjetaFechaInicioEl, createTarjetaFechaFinEl);
    }

    if(canEditCard && formEditTarjetaEl){
        if(editTarjetaAsignadoEl){
            editTarjetaAsignadoEl.addEventListener('change', syncEditTarjetaActivityFilters);
        }
        if(editTarjetaAlcanceEl){
            editTarjetaAlcanceEl.addEventListener('change', syncEditTarjetaActivityFilters);
        }

        document.querySelectorAll('.btn-edit-tarjeta').forEach(btn => {
            btn.addEventListener('click', function(){
                const idTarjeta = this.dataset.tarjetaId || '';
                const titulo = this.dataset.tarjetaTitulo || '';
                const descripcion = this.dataset.tarjetaDescripcion || '';
                const tarjetaNode = this.closest('.tablero-tarjeta');
                const listaNode = tarjetaNode ? tarjetaNode.closest('.tablero-card-list') : null;
                const idColumna = (listaNode && listaNode.dataset && listaNode.dataset.columnaId)
                    ? (listaNode.dataset.columnaId || '')
                    : (this.dataset.columnaId || '');
                const idAlcance = this.dataset.alcanceId || '';
                const idActividad = this.dataset.actividadId || '';
                const idUsuarioAsignado = this.dataset.usuarioAsignadoId || '';
                const idPrioridad = this.dataset.prioridadId || '';
                const fechaInicio = this.dataset.fechaInicio || '';
                const fechaFin = this.dataset.fechaFin || '';
                const completado = parseInt(this.dataset.tarjetaCompletado || '0', 10) === 1;
                const archivada  = parseInt(this.dataset.tarjetaArchivada || '0', 10) === 1;
                const canDeleteThisCard = parseInt(this.dataset.canDelete || '0', 10) === 1;
                const etiquetaIds = parseIdList(this.dataset.etiquetaIds || '');

                tarjetaEditandoId = idTarjeta;
                formEditTarjetaEl.action = `${APP_URL_ROOT}/tablero/update_tarjeta/${idTarjeta}`;
                if(editTarjetaTituloEl) editTarjetaTituloEl.value = titulo;
                if(editTarjetaDescripcionEl) editTarjetaDescripcionEl.value = descripcion;
                if(editTarjetaColumnaEl) editTarjetaColumnaEl.value = idColumna;
                if(editTarjetaAsignadoEl) editTarjetaAsignadoEl.value = idUsuarioAsignado;
                syncEditTarjetaActivityFilters();
                if(editTarjetaAlcanceEl) editTarjetaAlcanceEl.value = idAlcance;
                filterActividadOptions(editTarjetaActividadEl, editTarjetaAlcanceEl);
                if(editTarjetaActividadEl) editTarjetaActividadEl.value = idActividad;
                if(editTarjetaPrioridadEl) editTarjetaPrioridadEl.value = idPrioridad;
                if(editTarjetaUsarFechasEl){
                    editTarjetaUsarFechasEl.checked = fechaInicio !== '' || fechaFin !== '';
                }
                if(editTarjetaFechaInicioEl) editTarjetaFechaInicioEl.value = fechaInicio;
                if(editTarjetaFechaFinEl) editTarjetaFechaFinEl.value = fechaFin;
                if(editTarjetaCompletadoEl) editTarjetaCompletadoEl.checked = completado;
                if(canDeleteCard && btnDeleteTarjetaModalEl){
                    btnDeleteTarjetaModalEl.classList.toggle('d-none', !canDeleteThisCard);
                }
                if(btnArchivarTarjetaModalEl){
                    btnArchivarTarjetaModalEl.classList.remove('d-none');
                    btnArchivarTarjetaModalEl.dataset.archivada = archivada ? '1' : '0';
                    if(lblArchivarTarjetaEl) lblArchivarTarjetaEl.textContent = archivada ? 'Desarchivar Tarjeta' : 'Archivar Tarjeta';
                    btnArchivarTarjetaModalEl.classList.toggle('btn-outline-warning', !archivada);
                    btnArchivarTarjetaModalEl.classList.toggle('btn-warning', archivada);
                }
                toggleFechaInputs(editTarjetaUsarFechasEl, editTarjetaFechaInicioEl, editTarjetaFechaFinEl);
                setCheckedValues('.edit-etiqueta-checkbox', etiquetaIds);
            });
        });

        if(editTarjetaUsarFechasEl){
            editTarjetaUsarFechasEl.addEventListener('change', function(){
                toggleFechaInputs(editTarjetaUsarFechasEl, editTarjetaFechaInicioEl, editTarjetaFechaFinEl);
            });
            toggleFechaInputs(editTarjetaUsarFechasEl, editTarjetaFechaInicioEl, editTarjetaFechaFinEl);
        }

        if(canDeleteCard && btnDeleteTarjetaModalEl && formDeleteTarjetaEl){
            btnDeleteTarjetaModalEl.addEventListener('click', function(){
                if(!tarjetaEditandoId){
                    return;
                }
                openConfirmActionModal({
                    title: '<i class="bi bi-trash"></i> Eliminar tarjeta',
                    message: '¿Eliminar logicamente esta tarjeta? Dejara de mostrarse en el tablero actual.',
                    onConfirm: function(){
                        formDeleteTarjetaEl.action = `${APP_URL_ROOT}/tablero/delete_tarjeta/${tarjetaEditandoId}`;
                        formDeleteTarjetaEl.submit();
                    }
                });
            });
        }

        // -- Botón Archivar / Desarchivar tarjeta
        if(btnArchivarTarjetaModalEl){
            btnArchivarTarjetaModalEl.addEventListener('click', async function(){
                if(!tarjetaEditandoId) return;
                const archivar = this.dataset.archivada !== '1';
                const label = archivar ? 'Archivar Tarjeta' : 'Desarchivar Tarjeta';
                openConfirmActionModal({
                    title: `<i class="bi bi-archive"></i> ${label}`,
                    message: archivar
                        ? '¿Archivar esta tarjeta? Se ocultará del tablero hasta que active "Mostrar Archivadas".'
                        : '¿Desarchivar esta tarjeta? Volverá a mostrarse normalmente en el tablero.',
                    confirmText: label,
                    onConfirm: async () => {
                        try {
                            const resp = await fetch(`${APP_URL_ROOT}/tablero/archivar_tarjeta`, {
                                method: 'POST', headers: {'Content-Type':'application/json'},
                                credentials: 'same-origin',
                                body: JSON.stringify({id_tablero: idTableroActual, id_tarjeta: parseInt(tarjetaEditandoId, 10), archivar})
                            });
                            const data = await resp.json();
                            if(data.success){
                                $('#modalEditTarjeta').modal('hide');
                                const cardEl = document.getElementById(`tarjeta-${tarjetaEditandoId}`);
                                if(cardEl){
                                    cardEl.dataset.tarjetaArchivada = archivar ? '1' : '0';
                                    cardEl.classList.toggle('tablero-tarjeta--archivada', archivar);
                                    // actualizar data en el botón editar de esta tarjeta
                                    const btnEdit = cardEl.querySelector('.btn-edit-tarjeta');
                                    if(btnEdit) btnEdit.dataset.tarjetaArchivada = archivar ? '1' : '0';
                                }
                                applyTarjetaFilters();
                                document.querySelectorAll('.tablero-card-list').forEach(l => {
                                    syncEmptyColumnState(l); syncColumnCounter(l);
                                });
                            } else {
                                alert(data.error || 'No se pudo completar la acción.');
                            }
                        } catch(e){ alert('Error de conexión.'); }
                    }
                });
            });
        }
    }

    // Switch "Mostrar Archivadas"
    if(switchMostrarArchivadas){
        switchMostrarArchivadas.addEventListener('change', applyTarjetaFilters);
    }

    if(btnDeleteTableroModalEl && formDeleteTableroEl){
        btnDeleteTableroModalEl.addEventListener('click', function(){
            openConfirmActionModal({
                title: '<i class="bi bi-trash"></i> Eliminar tablero',
                message: '¿Esta seguro de eliminar este tablero? Esta accion no se puede deshacer.',
                onConfirm: function(){
                    formDeleteTableroEl.submit();
                }
            });
        });
    }

    if(formEtiquetaTableroEl){
        resetEtiquetaForm();

        document.querySelectorAll('.btn-edit-etiqueta').forEach(btn => {
            btn.addEventListener('click', function(){
                const id = this.dataset.id || '';
                const nombre = this.dataset.nombre || '';
                const color = this.dataset.color || '#0d6efd';

                formEtiquetaTableroEl.action = `${APP_URL_ROOT}/tablero/update_etiqueta/${id}`;
                if(inputEtiquetaNombreEl) inputEtiquetaNombreEl.value = nombre;
                if(inputEtiquetaColorEl) inputEtiquetaColorEl.value = color;
                if(btnGuardarEtiquetaTableroEl) btnGuardarEtiquetaTableroEl.textContent = 'Guardar Cambios';
                if(btnCancelarEdicionEtiquetaEl) btnCancelarEdicionEtiquetaEl.classList.remove('d-none');
            });
        });

        document.querySelectorAll('.btn-delete-etiqueta').forEach(btn => {
            btn.addEventListener('click', function(){
                const id = this.dataset.id || '';
                const nombre = this.dataset.nombre || 'Sin texto';

                openConfirmActionModal({
                    title: '<i class="bi bi-trash"></i> Eliminar etiqueta',
                    message: `¿Eliminar la etiqueta "${nombre}"? Solo es posible si no esta asignada a tarjetas activas.`,
                    onConfirm: function(){
                        if(formDeleteEtiquetaEl){
                            formDeleteEtiquetaEl.action = `${APP_URL_ROOT}/tablero/delete_etiqueta/${id}`;
                            formDeleteEtiquetaEl.submit();
                        }
                    }
                });
            });
        });

        if(btnCancelarEdicionEtiquetaEl){
            btnCancelarEdicionEtiquetaEl.addEventListener('click', resetEtiquetaForm);
        }
    }

    if(formPrioridadTableroEl){
        resetPrioridadForm();

        document.querySelectorAll('.btn-edit-prioridad').forEach(btn => {
            btn.addEventListener('click', function(){
                const id = this.dataset.id || '';
                const nombre = this.dataset.nombre || '';
                const valor = this.dataset.valor || '1';
                const color = this.dataset.color || '#6c757d';

                formPrioridadTableroEl.action = `${APP_URL_ROOT}/tablero/update_prioridad/${id}`;
                if(inputPrioridadNombreEl) inputPrioridadNombreEl.value = nombre;
                if(inputPrioridadValorEl) inputPrioridadValorEl.value = valor;
                if(inputPrioridadColorEl) inputPrioridadColorEl.value = color;
                if(btnGuardarPrioridadTableroEl) btnGuardarPrioridadTableroEl.textContent = 'Guardar Cambios';
                if(btnCancelarEdicionPrioridadEl) btnCancelarEdicionPrioridadEl.classList.remove('d-none');
            });
        });

        document.querySelectorAll('.btn-delete-prioridad').forEach(btn => {
            btn.addEventListener('click', function(){
                const id = this.dataset.id || '';
                const nombre = this.dataset.nombre || '';

                openConfirmActionModal({
                    title: '<i class="bi bi-trash"></i> Eliminar prioridad',
                    message: `¿Eliminar la prioridad "${nombre}"? Solo es posible si no esta asignada a tarjetas activas.`,
                    onConfirm: function(){
                        if(formDeletePrioridadEl){
                            formDeletePrioridadEl.action = `${APP_URL_ROOT}/tablero/delete_prioridad/${id}`;
                            formDeletePrioridadEl.submit();
                        }
                    }
                });
            });
        });

        if(btnCancelarEdicionPrioridadEl){
            btnCancelarEdicionPrioridadEl.addEventListener('click', resetPrioridadForm);
        }
    }

    document.querySelectorAll('.btn-open-tareas').forEach(btn => {
        btn.addEventListener('click', function(){
            tarjetaTareasActualId = parseInt(this.dataset.tarjetaId || '0', 10);
            const titulo = this.dataset.tarjetaTitulo || '';
            const descripcion = this.dataset.tarjetaDescripcion || '';
            const completado = parseInt(this.dataset.tarjetaCompletado || '0', 10) === 1;

            if(modalTarjetaTareasTituloEl){
                modalTarjetaTareasTituloEl.textContent = titulo || `Tarjeta #${tarjetaTareasActualId}`;
            }
            if(modalTarjetaTareasDescripcionEl){
                modalTarjetaTareasDescripcionEl.textContent = descripcion || 'Sin descripcion';
            }
            if(modalTarjetaTareasActividadEl){
                modalTarjetaTareasActividadEl.innerHTML = '<span class="text-muted">Cargando actividad...</span>';
            }
            if(modalTarjetaTareasPrioridadEl){
                modalTarjetaTareasPrioridadEl.innerHTML = '<span class="text-muted">Cargando prioridad...</span>';
            }
            updateModalTarjetaEstado(completado);
            if(modalTarjetaTareasEtiquetasEl){
                modalTarjetaTareasEtiquetasEl.innerHTML = '<span class="text-muted">Cargando etiquetas...</span>';
            }

            if(contenedorListasTareasEl){
                contenedorListasTareasEl.innerHTML = '<div class="text-muted small">Cargando tareas...</div>';
            }
            if(contenedorHistorialTarjetaEl){
                contenedorHistorialTarjetaEl.innerHTML = '<div class="text-muted small">Cargando historial...</div>';
            }

            cargarModalTareas();
        });
    });

    restoreBoardModalAfterSyncReload();

    document.querySelectorAll('.tablero-tarjeta').forEach(card => {
        card.addEventListener('click', function(evt){
            const ignoreClick = evt.target.closest('button, a, input, select, textarea, label, .timer-display');
            if(ignoreClick){
                return;
            }

            const openBtn = card.querySelector('.btn-open-tareas');
            if(openBtn){
                openBtn.click();
            }
        });
    });

    if(btnAgregarListaTareasEl){
        btnAgregarListaTareasEl.addEventListener('click', async function(){
            if(!canCreateList || !tarjetaTareasActualId) return;

            const nombre = (inputNuevaListaTareasEl ? inputNuevaListaTareasEl.value : '').trim();
            if(!nombre){
                alert('Ingrese el nombre de la lista de tareas.');
                return;
            }

            try {
                await postJson(`${APP_URL_ROOT}/tablero/create_tarjeta_tarea`, {
                    id_tablero: idTableroActual,
                    id_tarjeta: tarjetaTareasActualId,
                    nombre_tarea: nombre
                });
                if(inputNuevaListaTareasEl) inputNuevaListaTareasEl.value = '';
                await cargarModalTareas();
            } catch(err){
                alert(err.message);
            }
        });
    }

    if(formEditarListaTareaEl){
        formEditarListaTareaEl.addEventListener('submit', async function(evt){
            evt.preventDefault();

            if(!canEditList || !tarjetaTareasActualId || !tareaEditandoId){
                return;
            }

            const nombreLimpio = (inputEditarListaTareaNombreEl ? inputEditarListaTareaNombreEl.value : '').trim();
            if(!nombreLimpio){
                alert('El nombre de la lista es obligatorio.');
                return;
            }

            if(btnGuardarEditarListaTareaEl){
                btnGuardarEditarListaTareaEl.disabled = true;
            }

            try {
                await postJson(`${APP_URL_ROOT}/tablero/update_tarjeta_tarea`, {
                    id_tablero: idTableroActual,
                    id_tarea: tareaEditandoId,
                    nombre_tarea: nombreLimpio
                });

                closeEditarListaTareaModal();
                await cargarModalTareas();
            } catch(err){
                alert(err.message);
            } finally {
                if(btnGuardarEditarListaTareaEl){
                    btnGuardarEditarListaTareaEl.disabled = false;
                }
            }
        });

        const resetEditarListaState = function(){
            tareaEditandoId = 0;
            if(inputEditarListaTareaNombreEl){
                inputEditarListaTareaNombreEl.value = '';
            }
            if(btnGuardarEditarListaTareaEl){
                btnGuardarEditarListaTareaEl.disabled = false;
            }
        };

        if(window.jQuery && modalEditarListaTareaEl){
            window.jQuery(modalEditarListaTareaEl).on('hidden.bs.modal', resetEditarListaState);
        }
    }

    if(formEditarDetalleTareaEl){
        formEditarDetalleTareaEl.addEventListener('submit', async function(evt){
            evt.preventDefault();

            if(!canEditTask || !tarjetaTareasActualId || !detalleTareaEditandoId){
                return;
            }

            const descripcionLimpia = (inputEditarDetalleTareaDescripcionEl ? inputEditarDetalleTareaDescripcionEl.value : '').trim();
            if(!descripcionLimpia){
                alert('La descripcion de la tarea es obligatoria.');
                return;
            }

            if(btnGuardarEditarDetalleTareaEl){
                btnGuardarEditarDetalleTareaEl.disabled = true;
            }

            try {
                await postJson(`${APP_URL_ROOT}/tablero/update_tarjeta_tarea_detalle`, {
                    id_tablero: idTableroActual,
                    id_tarea_detalle: detalleTareaEditandoId,
                    descripcion: descripcionLimpia
                });

                closeEditarDetalleTareaModal();
                await cargarModalTareas();
            } catch(err){
                alert(err.message);
            } finally {
                if(btnGuardarEditarDetalleTareaEl){
                    btnGuardarEditarDetalleTareaEl.disabled = false;
                }
            }
        });

        const resetEditarDetalleState = function(){
            detalleTareaEditandoId = 0;
            if(inputEditarDetalleTareaDescripcionEl){
                inputEditarDetalleTareaDescripcionEl.value = '';
            }
            if(btnGuardarEditarDetalleTareaEl){
                btnGuardarEditarDetalleTareaEl.disabled = false;
            }
        };

        if(window.jQuery && modalEditarDetalleTareaEl){
            window.jQuery(modalEditarDetalleTareaEl).on('hidden.bs.modal', resetEditarDetalleState);
        }
    }

    if(contenedorListasTareasEl){
        contenedorListasTareasEl.addEventListener('click', async function(evt){
            const btnEditTarea = evt.target.closest('.btn-edit-tarea');
            if(btnEditTarea && canEditList && tarjetaTareasActualId){
                const idTarea = parseInt(btnEditTarea.dataset.tareaId || '0', 10);
                const nombreActual = btnEditTarea.dataset.tareaNombre || '';
                openEditarListaTareaModal(idTarea, nombreActual);
                return;
            }

            const btnEditDetalle = evt.target.closest('.btn-edit-detalle');
            if(btnEditDetalle && canEditTask && tarjetaTareasActualId){
                const idDetalle = parseInt(btnEditDetalle.dataset.detalleId || '0', 10);
                const descripcionActual = btnEditDetalle.dataset.detalleDescripcion || '';
                openEditarDetalleTareaModal(idDetalle, descripcionActual);
                return;
            }

            const btnDeleteTarea = evt.target.closest('.btn-delete-tarea');
            if(btnDeleteTarea && canDeleteList && tarjetaTareasActualId){
                const idTarea = parseInt(btnDeleteTarea.dataset.tareaId || '0', 10);
                openConfirmActionModal({
                    title: '<i class="bi bi-trash"></i> Eliminar lista',
                    message: '¿Eliminar esta lista de tareas? Solo es posible si no tiene tareas creadas.',
                    onConfirm: async function(){
                        await postJson(`${APP_URL_ROOT}/tablero/delete_tarjeta_tarea`, {
                            id_tablero: idTableroActual,
                            id_tarea: idTarea
                        });
                        await cargarModalTareas();
                    }
                });
                return;
            }

            const btnDeleteDetalle = evt.target.closest('.btn-delete-detalle');
            if(btnDeleteDetalle && canDeleteTask && tarjetaTareasActualId){
                const idDetalle = parseInt(btnDeleteDetalle.dataset.detalleId || '0', 10);
                const descripcion = String(btnDeleteDetalle.dataset.detalleDescripcion || '').trim() || 'esta tarea';
                const tiempoSegundos = parseInt(btnDeleteDetalle.dataset.detalleTiempoSegundos || '0', 10) || 0;
                const enCurso = String(btnDeleteDetalle.dataset.detalleRunning || '0') === '1';
                const tieneTiempo = tiempoSegundos > 0 || enCurso;

                const mensajeConfirmacion = tieneTiempo
                    ? `La tarea "${descripcion}" tiene tiempo registrado (${formatSeconds(Math.max(0, tiempoSegundos))}${enCurso ? ', en curso' : ''}). ¿Confirma que desea eliminarla? Esta accion no se puede deshacer.`
                    : `¿Eliminar la tarea "${descripcion}" del listado?`;

                openConfirmActionModal({
                    title: '<i class="bi bi-trash"></i> Eliminar tarea',
                    message: mensajeConfirmacion,
                    onConfirm: async function(){
                        await postJson(`${APP_URL_ROOT}/tablero/delete_tarjeta_tarea_detalle`, {
                            id_tablero: idTableroActual,
                            id_tarea_detalle: idDetalle
                        });
                        await cargarModalTareas();
                    }
                });
                return;
            }

            const timerBadge = evt.target.closest('.detalle-timer-display');
            if(timerBadge && canEditTime && tarjetaTareasActualId){
                const editable = String(timerBadge.dataset.manualEditable || '0') === '1';
                const runningStart = String(timerBadge.dataset.runningStart || '').trim();
                if(!editable || runningStart !== ''){
                    return;
                }

                const idDetalle = parseInt(timerBadge.dataset.detalleId || '0', 10);
                const tiemposUsuarios = detalleTiempoUsuarioMap[String(idDetalle)] || [];

                if(Array.isArray(tiemposUsuarios) && tiemposUsuarios.length >= 1){
                    openModalTiempoUsuarios(idDetalle, tiemposUsuarios);
                    return;
                }

                const actual = String(timerBadge.textContent || '').trim();
                const input = window.prompt('Ingrese el tiempo en formato hh:mm:ss', actual);

                if(input === null){
                    return;
                }

                const cleaned = String(input).trim();
                const parsed = parseDurationToSeconds(cleaned);
                if(parsed === null){
                    alert('Formato invalido. Use hh:mm:ss, por ejemplo 01:25:30.');
                    return;
                }

                openConfirmActionModal({
                    title: '<i class="bi bi-exclamation-triangle"></i> Confirmar reemplazo de tiempo',
                    message: 'El tiempo manual reemplazara el acumulado actual del detalle. No se sumara al valor anterior. ¿Desea continuar?',
                    confirmText: 'Si, de acuerdo',
                    onConfirm: async function(){
                        try {
                            await postJson(`${APP_URL_ROOT}/tablero/update_tarea_detalle_tiempo_manual`, {
                                id_tablero: idTableroActual,
                                id_tarea_detalle: idDetalle,
                                tiempo_hms: cleaned
                            });
                            await cargarModalTareas();
                        } catch(err){
                            alert(err.message);
                        }
                    }
                });
                return;
            }

            const btnStartDetalle = evt.target.closest('.btn-start-detalle-timer');
            if(btnStartDetalle && canTrackTime && tarjetaTareasActualId){
                const idDetalle = parseInt(btnStartDetalle.dataset.detalleId || '0', 10);

                try {
                    const data = await postJson(`${APP_URL_ROOT}/tablero/start_tarea_detalle_timer`, {
                        id_tablero: idTableroActual,
                        id_tarea_detalle: idDetalle
                    });
                    updateTarjetaTiempoDisplay(tarjetaTareasActualId, data.total_tarjeta_segundos || 0, !!data.en_curso_tiempo);
                    await cargarModalTareas();
                } catch(err){
                    alert(err.message);
                }
                return;
            }

            const btnStopDetalle = evt.target.closest('.btn-stop-detalle-timer');
            if(btnStopDetalle && canTrackTime && tarjetaTareasActualId){
                const idDetalle = parseInt(btnStopDetalle.dataset.detalleId || '0', 10);

                try {
                    const data = await postJson(`${APP_URL_ROOT}/tablero/stop_tarea_detalle_timer`, {
                        id_tablero: idTableroActual,
                        id_tarea_detalle: idDetalle
                    });
                    updateTarjetaTiempoDisplay(tarjetaTareasActualId, data.total_tarjeta_segundos || 0, !!data.en_curso_tiempo);
                    await cargarModalTareas();
                } catch(err){
                    alert(err.message);
                }
                return;
            }

            const btn = evt.target.closest('.btn-agregar-detalle');
            if(!btn || !canCreateTask || !tarjetaTareasActualId) return;

            const idTarea = parseInt(btn.dataset.tareaId || '0', 10);
            const input = contenedorListasTareasEl.querySelector(`.input-nuevo-detalle[data-tarea-id="${idTarea}"]`);
            const descripcion = input ? input.value.trim() : '';

            if(!descripcion){
                alert('Ingrese una descripcion para el item.');
                return;
            }

            try {
                await postJson(`${APP_URL_ROOT}/tablero/create_tarjeta_tarea_detalle`, {
                    id_tablero: idTableroActual,
                    id_tarea: idTarea,
                    descripcion: descripcion,
                    id_usuario_asignado: resolveAssignedUserForNewDetail()
                });
                if(input) input.value = '';
                await cargarModalTareas();
            } catch(err){
                alert(err.message);
            }
        });

        contenedorListasTareasEl.addEventListener('change', async function(evt){
            const selectAsignado = evt.target.closest('.detalle-usuario-asignado');
            if(selectAsignado && canAssignTaskUser && tarjetaTareasActualId){
                const idDetalle = parseInt(selectAsignado.dataset.detalleId || '0', 10);
                const prevValue = String(selectAsignado.dataset.prevValue || '');
                const nuevoValor = String(selectAsignado.value || '');

                try {
                    await postJson(`${APP_URL_ROOT}/tablero/assign_tarea_detalle_usuario`, {
                        id_tablero: idTableroActual,
                        id_tarea_detalle: idDetalle,
                        id_usuario_asignado: nuevoValor
                    });
                    selectAsignado.dataset.prevValue = nuevoValor;
                    await cargarModalTareas();
                } catch(err){
                    selectAsignado.value = prevValue;
                    alert(err.message);
                }
                return;
            }

            const checkbox = evt.target.closest('.tarea-detalle-toggle');
            if(!checkbox || !canMarkDone || !tarjetaTareasActualId) return;

            const idDetalle = parseInt(checkbox.dataset.detalleId || '0', 10);
            try {
                await postJson(`${APP_URL_ROOT}/tablero/toggle_tarjeta_tarea_detalle`, {
                    id_tablero: idTableroActual,
                    id_tarea_detalle: idDetalle,
                    completado: checkbox.checked ? 1 : 0
                });
                await cargarModalTareas();
            } catch(err){
                alert(err.message);
            }
        });
    }

    if(filtroDetalleUsuarioEl){
        filtroDetalleUsuarioEl.addEventListener('change', function(){
            if(tarjetaTareasActualId){
                cargarModalTareas();
            }
        });
    }

    // -- Historial: búsqueda y filtro por categoría --
    const inputFiltroHistorialEl         = document.getElementById('inputFiltroHistorial');
    const selectFiltroHistorialCatEl     = document.getElementById('selectFiltroHistorialCategoria');
    const btnLimpiarFiltroHistorialEl    = document.getElementById('btnLimpiarFiltroHistorial');

    if(inputFiltroHistorialEl){
        inputFiltroHistorialEl.addEventListener('input', filterAndRenderHistorial);
    }
    if(selectFiltroHistorialCatEl){
        selectFiltroHistorialCatEl.addEventListener('change', filterAndRenderHistorial);
    }
    if(btnLimpiarFiltroHistorialEl){
        btnLimpiarFiltroHistorialEl.addEventListener('click', function(){
            if(inputFiltroHistorialEl) inputFiltroHistorialEl.value = '';
            if(selectFiltroHistorialCatEl) selectFiltroHistorialCatEl.value = '';
            filterAndRenderHistorial();
        });
    }

    if(canEditColumn){
        document.querySelectorAll('.btn-rename-columna').forEach(btn => {
            btn.addEventListener('click', function(){
                const id = this.dataset.columnaId;
                const nombre = this.dataset.columnaNombre;
                const color = this.dataset.columnaColor;

                document.getElementById('inputRenameNombre').value = nombre;
                document.getElementById('inputRenameColor').value = color;
                document.getElementById('formRenameColumna').action = `${APP_URL_ROOT}/tablero/update_columna/${id}`;
            });
        });
    }

    if(canDeleteColumn){
        document.querySelectorAll('.btn-delete-columna').forEach(btn => {
            btn.addEventListener('click', function(){
                const id = this.dataset.columnaId;
                const nombre = this.dataset.columnaNombre;

                document.getElementById('deleteColumnaName').textContent = nombre;
                document.getElementById('formDeleteColumna').action = `${APP_URL_ROOT}/tablero/delete_columna/${id}`;
            });
        });
    }

    if(canOrderColumn){
        const modalOrdenarColumnasEl = document.getElementById('modalOrdenarColumnas');
        const listaOrdenColumnasEl = document.getElementById('listaOrdenColumnas');
        const inputOrdenColumnasEl = document.getElementById('inputOrdenColumnas');
        const formReorderColumnasEl = document.getElementById('formReorderColumnas');

        const syncOrdenColumnasInput = () => {
            if(!listaOrdenColumnasEl || !inputOrdenColumnasEl) return;
            const ids = Array.from(listaOrdenColumnasEl.querySelectorAll('.reorder-columna-item'))
                .map(el => parseInt(el.dataset.columnaId || '0', 10))
                .filter(id => id > 0);
            inputOrdenColumnasEl.value = ids.join(',');
        };

        if(listaOrdenColumnasEl && typeof Sortable !== 'undefined'){
            Sortable.create(listaOrdenColumnasEl, {
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: syncOrdenColumnasInput
            });
        }

        if(modalOrdenarColumnasEl){
            if(window.jQuery){
                window.jQuery(modalOrdenarColumnasEl).on('shown.bs.modal', syncOrdenColumnasInput);
            } else {
                modalOrdenarColumnasEl.addEventListener('shown.bs.modal', syncOrdenColumnasInput);
            }
        }

        if(formReorderColumnasEl){
            formReorderColumnasEl.addEventListener('submit', syncOrdenColumnasInput);
        }
    }

    // Modal: al seleccionar usuario, cargar permisos ya asignados en los checkboxes.
    if(canAssignBoard){
        const modal = document.getElementById('modalAsignarUsuarioTablero');
        if(modal){
            const userSelect = modal.querySelector('select[name="id_usuario"]');
            const checkboxes = {
                tablero_ver: modal.querySelector('#modal_perm_tablero_ver'),
                tablero_crear: modal.querySelector('#modal_perm_tablero_crear'),
                tablero_editar: modal.querySelector('#modal_perm_tablero_editar'),
                tablero_eliminar: modal.querySelector('#modal_perm_tablero_eliminar'),
                tablero_asignar: modal.querySelector('#modal_perm_tablero_asignar'),
                columna_crear: modal.querySelector('#modal_perm_columna_crear'),
                columna_editar: modal.querySelector('#modal_perm_columna_editar'),
                columna_eliminar: modal.querySelector('#modal_perm_columna_eliminar'),
                columna_ordenar: modal.querySelector('#modal_perm_columna_ordenar'),
                tarjeta_ver: modal.querySelector('#modal_perm_tarjeta_ver'),
                tarjeta_crear: modal.querySelector('#modal_perm_tarjeta_crear'),
                tarjeta_editar: modal.querySelector('#modal_perm_tarjeta_editar'),
                tarjeta_mover: modal.querySelector('#modal_perm_tarjeta_mover'),
                tarjeta_eliminar: modal.querySelector('#modal_perm_tarjeta_eliminar'),
                tarjeta_asignar: modal.querySelector('#modal_perm_tarjeta_asignar'),
                lista_crear: modal.querySelector('#modal_perm_lista_crear'),
                lista_editar: modal.querySelector('#modal_perm_lista_editar'),
                lista_eliminar: modal.querySelector('#modal_perm_lista_eliminar'),
                tarea_crear: modal.querySelector('#modal_perm_tarea_crear'),
                tarea_editar: modal.querySelector('#modal_perm_tarea_editar'),
                tarea_eliminar: modal.querySelector('#modal_perm_tarea_eliminar'),
                tarea_tiempo_editar: modal.querySelector('#modal_perm_tarea_tiempo_editar'),
                plantilla_tarjeta_crear: modal.querySelector('#modal_perm_plantilla_tarjeta_crear'),
                plantilla_tarjeta_editar: modal.querySelector('#modal_perm_plantilla_tarjeta_editar'),
                plantilla_tarjeta_eliminar: modal.querySelector('#modal_perm_plantilla_tarjeta_eliminar'),
                plantilla_tarjeta_asociar: modal.querySelector('#modal_perm_plantilla_tarjeta_asociar'),
                plantilla_lista_crear: modal.querySelector('#modal_perm_plantilla_lista_crear'),
                plantilla_lista_editar: modal.querySelector('#modal_perm_plantilla_lista_editar'),
                plantilla_lista_eliminar: modal.querySelector('#modal_perm_plantilla_lista_eliminar')
            };

            const sectionSelectAll = {
                tablero: modal.querySelector('.js-select-all-section[data-section="tablero"]'),
                columna: modal.querySelector('.js-select-all-section[data-section="columna"]'),
                tarjeta: modal.querySelector('.js-select-all-section[data-section="tarjeta"]'),
                lista: modal.querySelector('.js-select-all-section[data-section="lista"]'),
                tarea: modal.querySelector('.js-select-all-section[data-section="tarea"]'),
                plantilla_tarjeta: modal.querySelector('.js-select-all-section[data-section="plantilla_tarjeta"]'),
                plantilla_lista: modal.querySelector('.js-select-all-section[data-section="plantilla_lista"]')
            };

            const sectionPermissionKeys = {
                tablero: ['tablero_ver', 'tablero_crear', 'tablero_editar', 'tablero_eliminar', 'tablero_asignar'],
                columna: ['columna_crear', 'columna_editar', 'columna_eliminar', 'columna_ordenar'],
                tarjeta: ['tarjeta_ver', 'tarjeta_crear', 'tarjeta_editar', 'tarjeta_mover', 'tarjeta_eliminar', 'tarjeta_asignar'],
                lista: ['lista_crear', 'lista_editar', 'lista_eliminar'],
                tarea: ['tarea_crear', 'tarea_editar', 'tarea_eliminar', 'tarea_tiempo_editar'],
                plantilla_tarjeta: ['plantilla_tarjeta_crear', 'plantilla_tarjeta_editar', 'plantilla_tarjeta_eliminar', 'plantilla_tarjeta_asociar'],
                plantilla_lista: ['plantilla_lista_crear', 'plantilla_lista_editar', 'plantilla_lista_eliminar']
            };

            const globalSelectAllWrap = modal.querySelector('#modalGlobalSelectAllWrap');
            const globalSelectAll = modal.querySelector('#modal_perm_select_all_global');

            const allPermissionKeys = Object.keys(checkboxes);

            const updateGlobalSelectAllState = () => {
                if(!globalSelectAll) return;

                const allBoxes = allPermissionKeys
                    .map(key => checkboxes[key])
                    .filter(Boolean);

                if(!allBoxes.length){
                    globalSelectAll.checked = false;
                    globalSelectAll.indeterminate = false;
                    return;
                }

                const checkedCount = allBoxes.reduce((acc, box) => acc + (box.checked ? 1 : 0), 0);
                globalSelectAll.checked = checkedCount === allBoxes.length;
                globalSelectAll.indeterminate = checkedCount > 0 && checkedCount < allBoxes.length;
            };

            const toggleGlobalSelectAllVisibility = (show) => {
                if(!globalSelectAllWrap || !globalSelectAll) return;
                if(show){
                    globalSelectAllWrap.classList.remove('d-none');
                } else {
                    globalSelectAllWrap.classList.add('d-none');
                    globalSelectAll.checked = false;
                    globalSelectAll.indeterminate = false;
                }
            };

            const updateSelectAllStateForSection = (sectionName) => {
                const selectAll = sectionSelectAll[sectionName];
                const keys = sectionPermissionKeys[sectionName] || [];
                if(!selectAll || !keys.length) return;

                const sectionBoxes = keys
                    .map(key => checkboxes[key])
                    .filter(Boolean);

                if(!sectionBoxes.length){
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                    return;
                }

                const checkedCount = sectionBoxes.reduce((acc, box) => acc + (box.checked ? 1 : 0), 0);
                selectAll.checked = checkedCount === sectionBoxes.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < sectionBoxes.length;
            };

            const updateAllSelectAllStates = () => {
                Object.keys(sectionSelectAll).forEach(updateSelectAllStateForSection);
                updateGlobalSelectAllState();
            };

            const bindSelectAllSectionEvents = () => {
                Object.keys(sectionSelectAll).forEach(sectionName => {
                    const selectAll = sectionSelectAll[sectionName];
                    const keys = sectionPermissionKeys[sectionName] || [];
                    if(!selectAll || !keys.length) return;

                    selectAll.addEventListener('change', function(){
                        keys.forEach(key => {
                            if(checkboxes[key]){
                                checkboxes[key].checked = this.checked;
                            }
                        });
                        updateSelectAllStateForSection(sectionName);
                        updateGlobalSelectAllState();
                    });

                    keys.forEach(key => {
                        if(checkboxes[key]){
                            checkboxes[key].addEventListener('change', function(){
                                updateSelectAllStateForSection(sectionName);
                                updateGlobalSelectAllState();
                            });
                        }
                    });
                });

                if(globalSelectAll){
                    globalSelectAll.addEventListener('change', function(){
                        allPermissionKeys.forEach(key => {
                            if(checkboxes[key]){
                                checkboxes[key].checked = this.checked;
                            }
                        });
                        updateAllSelectAllStates();
                    });
                }
            };

            const resetDefaults = () => {
                Object.keys(checkboxes).forEach(key => {
                    if(!checkboxes[key]) return;
                    checkboxes[key].checked = (key === 'tablero_ver' || key === 'tarjeta_ver');
                });
                updateAllSelectAllStates();
            };

            const loadPermissionsFromServer = async (userId) => {
                try {
                    const url = `${APP_URL_ROOT}/tablero/get_usuario_permiso_tablero?id_tablero=${idTableroActual}&id_usuario=${userId}`;
                    const res = await fetch(url, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' }
                    });

                    if(!res.ok){
                        return null;
                    }

                    const data = await res.json();
                    if(data && data.success && data.permisos){
                        return data.permisos;
                    }
                } catch(err){
                    // Silent fallback to client-side mapping.
                }

                return null;
            };

            const applyPermissions = async (userId) => {
                let perms = await loadPermissionsFromServer(userId);
                const selectedOption = userSelect.options[userSelect.selectedIndex] || null;

                if(!perms && selectedOption && selectedOption.dataset && selectedOption.dataset.permisos){
                    try {
                        perms = JSON.parse(selectedOption.dataset.permisos);
                    } catch(err){
                        perms = null;
                    }
                }

                if(!perms){
                    perms = usuariosAsignadosPermisos[String(userId)] || usuariosAsignadosPermisos[userId] || null;
                }

                if(perms){
                    const granularKeys = [
                        'tablero_ver', 'tablero_crear', 'tablero_editar', 'tablero_eliminar', 'tablero_asignar',
                        'columna_crear', 'columna_editar', 'columna_eliminar', 'columna_ordenar',
                        'tarjeta_ver', 'tarjeta_crear', 'tarjeta_editar', 'tarjeta_mover', 'tarjeta_eliminar', 'tarjeta_asignar',
                        'lista_crear', 'lista_editar', 'lista_eliminar',
                        'tarea_crear', 'tarea_editar', 'tarea_eliminar', 'tarea_tiempo_editar',
                        'plantilla_tarjeta_crear', 'plantilla_tarjeta_editar', 'plantilla_tarjeta_eliminar', 'plantilla_tarjeta_asociar',
                        'plantilla_lista_crear', 'plantilla_lista_editar', 'plantilla_lista_eliminar'
                    ];
                    const granularSum = granularKeys.reduce((acc, key) => acc + (perms[key] ? 1 : 0), 0);
                    const useLegacyFallback = granularSum === 0 && (perms.legacy_ver || perms.legacy_crear || perms.legacy_editar || perms.legacy_eliminar);

                    const normalizedPerms = useLegacyFallback ? {
                        tablero_ver: !!perms.legacy_ver,
                        tablero_crear: !!perms.legacy_crear,
                        tablero_editar: !!perms.legacy_editar,
                        tablero_eliminar: !!perms.legacy_eliminar,
                        tablero_asignar: !!perms.legacy_editar,
                        columna_crear: !!perms.legacy_editar,
                        columna_editar: !!perms.legacy_editar,
                        columna_eliminar: !!perms.legacy_eliminar,
                        columna_ordenar: !!perms.legacy_editar,
                        tarjeta_ver: !!perms.legacy_ver,
                        tarjeta_crear: !!perms.legacy_crear,
                        tarjeta_editar: !!perms.legacy_editar,
                        tarjeta_mover: !!perms.legacy_editar,
                        tarjeta_eliminar: !!perms.legacy_eliminar,
                        tarjeta_asignar: !!perms.legacy_editar,
                        lista_crear: !!perms.legacy_editar,
                        lista_editar: !!perms.legacy_editar,
                        lista_eliminar: !!perms.legacy_editar,
                        tarea_crear: !!perms.legacy_editar,
                        tarea_editar: !!perms.legacy_editar,
                        tarea_eliminar: !!perms.legacy_editar,
                        tarea_tiempo_editar: !!perms.tarea_tiempo_editar,
                        plantilla_tarjeta_crear: false,
                        plantilla_tarjeta_editar: false,
                        plantilla_tarjeta_eliminar: false,
                        plantilla_tarjeta_asociar: false,
                        plantilla_lista_crear: false,
                        plantilla_lista_editar: false,
                        plantilla_lista_eliminar: false
                    } : perms;

                    Object.keys(checkboxes).forEach(key => {
                        if(!checkboxes[key]) return;
                        checkboxes[key].checked = !!normalizedPerms[key];
                    });
                    updateAllSelectAllStates();
                } else {
                    resetDefaults();
                }
            };

            const syncSelectedUserPermissions = async () => {
                const id = parseInt(userSelect.value || '0', 10);
                if(id > 0){
                    toggleGlobalSelectAllVisibility(true);
                    await applyPermissions(id);
                } else {
                    toggleGlobalSelectAllVisibility(false);
                    resetDefaults();
                }
            };

            userSelect.addEventListener('change', function(){
                syncSelectedUserPermissions();
            });

            document.querySelectorAll('[data-target="#modalAsignarUsuarioTablero"]').forEach(trigger => {
                trigger.addEventListener('click', function(){
                    syncSelectedUserPermissions();
                });
            });

            bindSelectAllSectionEvents();

            if(window.jQuery){
                window.jQuery(modal).on('shown.bs.modal', function(){
                    syncSelectedUserPermissions();
                });
            }

            syncSelectedUserPermissions();
        }
    }
})();
</script>

<!-- ===== TOAST: Notificaciones plantillas ===== -->
<div aria-live="polite" aria-atomic="true" style="position:fixed;bottom:1.5rem;right:1.2rem;z-index:9999;min-width:280px;max-width:400px;">
    <div id="toastPlantilla" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3500">
        <div class="toast-header" id="toastPlantillaHeader">
            <i id="toastPlantillaIcono" class="mr-2"></i>
            <strong class="mr-auto" id="toastPlantillaTitulo">Plantilla</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body" id="toastPlantillaMensaje"></div>
    </div>
</div>

<?php require APPROOT . '/views/layouts/footer.php'; ?>
