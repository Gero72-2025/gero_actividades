<?php require APPROOT . '/views/layouts/header.php'; ?>
<?php
$tableroActual = $data['tableroActual'] ?? null;
$idTableroActual = $tableroActual ? (int)$tableroActual->Id_tablero : 0;
$etiquetasTablero = $data['etiquetas'] ?? [];
$prioridadesTablero = $data['prioridades'] ?? [];
$usuariosAsignadosById = [];
if(!empty($data['usuariosAsignados'])){
    foreach($data['usuariosAsignados'] as $ua){
        $usuariosAsignadosById[(int)$ua->Id_usuario] = [
            'tablero_ver' => (int)($ua->Permiso_tablero_ver ?? 0) === 1,
            'tablero_crear' => (int)($ua->Permiso_tablero_crear ?? 0) === 1,
            'tablero_editar' => (int)($ua->Permiso_tablero_editar ?? 0) === 1,
            'tablero_eliminar' => (int)($ua->Permiso_tablero_eliminar ?? 0) === 1,
            'tablero_asignar' => (int)($ua->Permiso_tablero_asignar ?? 0) === 1,
            'tarjeta_ver' => (int)($ua->Permiso_tarjeta_ver ?? 0) === 1,
            'tarjeta_crear' => (int)($ua->Permiso_tarjeta_crear ?? 0) === 1,
            'tarjeta_editar' => (int)($ua->Permiso_tarjeta_editar ?? 0) === 1,
            'tarjeta_eliminar' => (int)($ua->Permiso_tarjeta_eliminar ?? 0) === 1,
            'tarjeta_asignar' => (int)($ua->Permiso_tarjeta_asignar ?? 0) === 1,
            'lista_crear' => (int)($ua->Permiso_lista_crear ?? 0) === 1,
            'lista_editar' => (int)($ua->Permiso_lista_editar ?? 0) === 1,
            'lista_eliminar' => (int)($ua->Permiso_lista_eliminar ?? 0) === 1,
            'tarea_crear' => (int)($ua->Permiso_tarea_crear ?? 0) === 1,
            'tarea_editar' => (int)($ua->Permiso_tarea_editar ?? 0) === 1,
            'tarea_eliminar' => (int)($ua->Permiso_tarea_eliminar ?? 0) === 1,
            'tarea_tiempo_editar' => (int)($ua->Permiso_tarea_tiempo_editar ?? 0) === 1,
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
    'tarjeta_ver' => false,
    'tarjeta_crear' => false,
    'tarjeta_editar' => false,
    'tarjeta_eliminar' => false,
    'tarjeta_asignar' => false,
    'lista_crear' => false,
    'lista_editar' => false,
    'lista_eliminar' => false,
    'tarea_crear' => false,
    'tarea_editar' => false,
    'tarea_eliminar' => false,
    'tarea_tiempo_editar' => false
];

$canCreateBoardGlobal = tienePermiso('tablero.crear');
$canAssignGlobal = tienePermiso('tablero.asignar');
$canCreateColumnGlobal = tienePermiso('tablero.columnas');
$canDeleteColumnGlobal = tienePermiso('tablero.columnas_eliminar');
$canTrackTimeGlobal = tienePermiso('tablero.tiempo');
$canEditTimeGlobal = tienePermiso('tablero.tiempo_editar');
$canEditGlobal = tienePermiso('tablero.editar');
$canDeleteCardGlobal = tienePermiso('tablero.eliminar');

$canEditBoard = $canEditGlobal && !empty($permTablero['tablero_editar']);
$canAssignBoard = $canAssignGlobal && !empty($permTablero['tablero_asignar']);
$canCreateCard = $canCreateBoardGlobal && !empty($permTablero['tarjeta_crear']);
$canEditCard = $canEditGlobal && !empty($permTablero['tarjeta_editar']);
$canMarkDone = !empty($permTablero['tablero_ver']);
$canAssign = $canAssignGlobal && !empty($permTablero['tarjeta_asignar']);
$canTrackTime = $canTrackTimeGlobal && !empty($permTablero['tablero_ver']);
$canEditTime = $canEditTimeGlobal && !empty($permTablero['tarea_tiempo_editar']);
$canCreateList = $canEditGlobal && !empty($permTablero['lista_crear']);
$canEditList = $canEditGlobal && !empty($permTablero['lista_editar']);
$canDeleteList = $canDeleteCardGlobal && !empty($permTablero['lista_eliminar']);
$canCreateTask = $canEditGlobal && !empty($permTablero['tarea_crear']);
$canEditTask = $canEditGlobal && !empty($permTablero['tarea_editar']);
$canDeleteTask = $canDeleteCardGlobal && !empty($permTablero['tarea_eliminar']);
$canCreateColumn = $canCreateColumnGlobal && !empty($permTablero['tablero_editar']);
$canEditColumn = $canCreateColumn;
$canDeleteColumn = $canDeleteColumnGlobal && !empty($permTablero['tablero_eliminar']);
$canDeleteCard = $canDeleteCardGlobal && !empty($permTablero['tarjeta_eliminar']);
$canCreateBoard = $canCreateBoardGlobal && ($idTableroActual <= 0 || !empty($permTablero['tablero_crear']));
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
            <li class="nav-item">
                <a class="nav-link" href="<?php echo URLROOT; ?>/tablero/dashboard<?php echo $tableroParam; ?>">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo URLROOT; ?>/tablero/calendario<?php echo $tableroParam; ?>">
                    <i class="bi bi-calendar3"></i> Calendario
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo URLROOT; ?>/tablero/reporteria<?php echo $tableroParam; ?>">
                    <i class="bi bi-table"></i> Reporte
                </a>
            </li>
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

                        <?php if($canCreateColumn && $idTableroActual > 0): ?>
                            <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#modalCreateColumna">
                                <i class="bi bi-layout-three-columns"></i> Nueva Columna
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
<div class="tablero-wrapper pb-2">
    <div class="d-flex gap-3 tablero-columns" style="overflow-x:auto;">
        <?php foreach($data['columnas'] as $columna): ?>
            <div class="card tablero-columna" style="min-width:320px; max-width:320px;">
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
                            <?php if($canDeleteColumn && $tarjetasEnColumna === 0): ?>
                                <button type="button"
                                    class="btn btn-sm btn-link p-0 lh-1 text-white btn-delete-columna"
                                    data-toggle="modal"
                                    data-target="#modalDeleteColumna"
                                    data-columna-id="<?php echo (int)$columna->Id_columna; ?>"
                                    data-columna-nombre="<?php echo htmlspecialchars($columna->Nombre, ENT_QUOTES); ?>"
                                    title="Eliminar columna">
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
                                class="card mb-2 tablero-tarjeta <?php echo !empty($tarjeta->Completado) ? 'tablero-tarjeta--completada' : ''; ?>"
                                data-tarjeta-id="<?php echo (int)$tarjeta->Id_tarjeta; ?>"
                                data-tarjeta-completado="<?php echo !empty($tarjeta->Completado) ? '1' : '0'; ?>"
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

                        <div class="mb-3">
                            <h6 class="mb-2">Seccion 1: Tablero</h6>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tablero_ver" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tablero_ver" id="modal_perm_tablero_ver" value="1" checked><span>Ver tablero</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tablero_crear" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tablero_crear" id="modal_perm_tablero_crear" value="1"><span>Crear tablero</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tablero_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tablero_editar" id="modal_perm_tablero_editar" value="1"><span>Editar tablero</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tablero_eliminar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tablero_eliminar" id="modal_perm_tablero_eliminar" value="1"><span>Eliminar tablero</span></label></div>
                                <div class="col-12 col-md-6"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tablero_asignar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tablero_asignar" id="modal_perm_tablero_asignar" value="1"><span>Asignar usuarios a tablero</span></label></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="mb-2">Seccion 2: Tarjetas</h6>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarjeta_ver" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarjeta_ver" id="modal_perm_tarjeta_ver" value="1" checked><span>Ver tarjetas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarjeta_crear" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarjeta_crear" id="modal_perm_tarjeta_crear" value="1"><span>Crear tarjetas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarjeta_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarjeta_editar" id="modal_perm_tarjeta_editar" value="1"><span>Editar tarjetas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarjeta_eliminar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarjeta_eliminar" id="modal_perm_tarjeta_eliminar" value="1"><span>Eliminar tarjetas</span></label></div>
                                <div class="col-12 col-md-6"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarjeta_asignar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarjeta_asignar" id="modal_perm_tarjeta_asignar" value="1"><span>Asignar usuario a tarjeta</span></label></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="mb-2">Seccion 3: Lista de tareas</h6>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_lista_crear" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_lista_crear" id="modal_perm_lista_crear" value="1"><span>Crear lista</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_lista_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_lista_editar" id="modal_perm_lista_editar" value="1"><span>Editar lista</span></label></div>
                                <div class="col-12 col-md-6"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_lista_eliminar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_lista_eliminar" id="modal_perm_lista_eliminar" value="1"><span>Eliminar lista</span></label></div>
                            </div>
                        </div>

                        <div>
                            <h6 class="mb-2">Seccion 4: Tareas</h6>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarea_crear" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarea_crear" id="modal_perm_tarea_crear" value="1"><span>Crear tareas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarea_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarea_editar" id="modal_perm_tarea_editar" value="1"><span>Editar tareas</span></label></div>
                                <div class="col-12 col-md-6 mb-2"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarea_eliminar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarea_eliminar" id="modal_perm_tarea_eliminar" value="1"><span>Eliminar tareas</span></label></div>
                                <div class="col-12 col-md-6"><label class="d-flex align-items-center border rounded bg-white px-3 py-2 mb-0" for="modal_perm_tarea_tiempo_editar" style="cursor:pointer;"><input class="mr-2" type="checkbox" name="permiso_tarea_tiempo_editar" id="modal_perm_tarea_tiempo_editar" value="1"><span>Editar tiempo en tareas</span></label></div>
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

                                                <?php if((int)($ua->Permiso_tarjeta_ver ?? 0) === 1): ?><span class="badge bg-success me-1">Tarjetas: Ver</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarjeta_crear ?? 0) === 1): ?><span class="badge bg-success me-1">Tarjetas: Crear</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarjeta_editar ?? 0) === 1): ?><span class="badge bg-success me-1">Tarjetas: Editar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarjeta_eliminar ?? 0) === 1): ?><span class="badge bg-success me-1">Tarjetas: Eliminar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarjeta_asignar ?? 0) === 1): ?><span class="badge bg-success me-1">Tarjetas: Asignar</span><?php endif; ?>

                                                <?php if((int)($ua->Permiso_lista_crear ?? 0) === 1): ?><span class="badge bg-warning text-dark me-1">Listas: Crear</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_lista_editar ?? 0) === 1): ?><span class="badge bg-warning text-dark me-1">Listas: Editar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_lista_eliminar ?? 0) === 1): ?><span class="badge bg-warning text-dark me-1">Listas: Eliminar</span><?php endif; ?>

                                                <?php if((int)($ua->Permiso_tarea_crear ?? 0) === 1): ?><span class="badge bg-danger me-1">Tareas: Crear</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarea_editar ?? 0) === 1): ?><span class="badge bg-danger me-1">Tareas: Editar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarea_eliminar ?? 0) === 1): ?><span class="badge bg-danger me-1">Tareas: Eliminar</span><?php endif; ?>
                                                <?php if((int)($ua->Permiso_tarea_tiempo_editar ?? 0) === 1): ?><span class="badge bg-danger me-1">Tareas: Tiempo</span><?php endif; ?>

                                                <?php if((int)($ua->Permiso_tablero_ver ?? 0) !== 1 && (int)($ua->Permiso_tablero_crear ?? 0) !== 1 && (int)($ua->Permiso_tablero_editar ?? 0) !== 1 && (int)($ua->Permiso_tablero_eliminar ?? 0) !== 1 && (int)($ua->Permiso_tablero_asignar ?? 0) !== 1 && (int)($ua->Permiso_tarjeta_ver ?? 0) !== 1 && (int)($ua->Permiso_tarjeta_crear ?? 0) !== 1 && (int)($ua->Permiso_tarjeta_editar ?? 0) !== 1 && (int)($ua->Permiso_tarjeta_eliminar ?? 0) !== 1 && (int)($ua->Permiso_tarjeta_asignar ?? 0) !== 1 && (int)($ua->Permiso_lista_crear ?? 0) !== 1 && (int)($ua->Permiso_lista_editar ?? 0) !== 1 && (int)($ua->Permiso_lista_eliminar ?? 0) !== 1 && (int)($ua->Permiso_tarea_crear ?? 0) !== 1 && (int)($ua->Permiso_tarea_editar ?? 0) !== 1 && (int)($ua->Permiso_tarea_eliminar ?? 0) !== 1 && (int)($ua->Permiso_tarea_tiempo_editar ?? 0) !== 1): ?>
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

<?php if($canCreateCard && $idTableroActual > 0): ?>
<div class="modal fade" id="modalCreateTarjeta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Crear Tarjeta</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="<?php echo URLROOT; ?>/tablero/create_tarjeta" method="post">
                <input type="hidden" name="id_tablero" value="<?php echo $idTableroActual; ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Columna</label>
                            <select name="id_columna" class="form-select tablero-activo-select" required>
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
                            <select name="id_prioridad" class="form-select tablero-activo-select" required>
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
                    <?php if($canDeleteCard): ?>
                        <button type="button" class="btn btn-outline-danger mr-auto d-none" id="btnDeleteTarjetaModal">
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

                        <?php if($canEditCard): ?>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="inputNuevaListaTareas" maxlength="180" placeholder="Nombre de nueva lista de tareas">
                                <button class="btn btn-primary" type="button" id="btnAgregarListaTareas">Agregar lista</button>
                            </div>
                        <?php endif; ?>
                        <div id="contenedorListasTareas"></div>
                    </div>
                    <div class="col-lg-5">
                        <h6 class="mb-2">Historial</h6>
                        <div id="contenedorHistorialTarjeta" class="border rounded p-2 bg-light" style="max-height: 520px; overflow-y:auto;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
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
                <div class="alert alert-light border small mb-3">
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
    const canDeleteCard = <?php echo $canDeleteCard ? 'true' : 'false'; ?>;
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
                    (int)($ua->Permiso_tarjeta_ver ?? 0),
                    (int)($ua->Permiso_tarjeta_crear ?? 0),
                    (int)($ua->Permiso_tarjeta_editar ?? 0),
                    (int)($ua->Permiso_tarjeta_eliminar ?? 0),
                    (int)($ua->Permiso_tarjeta_asignar ?? 0),
                    (int)($ua->Permiso_lista_crear ?? 0),
                    (int)($ua->Permiso_lista_editar ?? 0),
                    (int)($ua->Permiso_lista_eliminar ?? 0),
                    (int)($ua->Permiso_tarea_crear ?? 0),
                    (int)($ua->Permiso_tarea_editar ?? 0),
                    (int)($ua->Permiso_tarea_eliminar ?? 0),
                    (int)($ua->Permiso_tarea_tiempo_editar ?? 0)
                ];

                $isLegacyOnly = (array_sum($granularFlags) === 0) && ($legacyVer || $legacyCrear || $legacyEditar || $legacyEliminar);

                $mapPermisos[(int)$ua->Id_usuario] = [
                    'tablero_ver' => $isLegacyOnly ? $legacyVer : ((int)($ua->Permiso_tablero_ver ?? 0) === 1),
                    'tablero_crear' => $isLegacyOnly ? $legacyCrear : ((int)($ua->Permiso_tablero_crear ?? 0) === 1),
                    'tablero_editar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tablero_editar ?? 0) === 1),
                    'tablero_eliminar' => $isLegacyOnly ? $legacyEliminar : ((int)($ua->Permiso_tablero_eliminar ?? 0) === 1),
                    'tablero_asignar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tablero_asignar ?? 0) === 1),
                    'tarjeta_ver' => $isLegacyOnly ? $legacyVer : ((int)($ua->Permiso_tarjeta_ver ?? 0) === 1),
                    'tarjeta_crear' => $isLegacyOnly ? $legacyCrear : ((int)($ua->Permiso_tarjeta_crear ?? 0) === 1),
                    'tarjeta_editar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tarjeta_editar ?? 0) === 1),
                    'tarjeta_eliminar' => $isLegacyOnly ? $legacyEliminar : ((int)($ua->Permiso_tarjeta_eliminar ?? 0) === 1),
                    'tarjeta_asignar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tarjeta_asignar ?? 0) === 1),
                    'lista_crear' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_lista_crear ?? 0) === 1),
                    'lista_editar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_lista_editar ?? 0) === 1),
                    'lista_eliminar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_lista_eliminar ?? 0) === 1),
                    'tarea_crear' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tarea_crear ?? 0) === 1),
                    'tarea_editar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tarea_editar ?? 0) === 1),
                    'tarea_eliminar' => $isLegacyOnly ? $legacyEditar : ((int)($ua->Permiso_tarea_eliminar ?? 0) === 1),
                    'tarea_tiempo_editar' => (int)($ua->Permiso_tarea_tiempo_editar ?? 0) === 1
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
    const modalConfirmarAccionEl = document.getElementById('modalConfirmarAccionTablero');
    const modalConfirmarAccionTituloEl = document.getElementById('modalConfirmarAccionTitulo');
    const modalConfirmarAccionMensajeEl = document.getElementById('modalConfirmarAccionMensaje');
    const btnConfirmarAccionTableroEl = document.getElementById('btnConfirmarAccionTablero');
    const btnDeleteTarjetaModalEl = document.getElementById('btnDeleteTarjetaModal');
    const formDeleteTarjetaEl = document.getElementById('formDeleteTarjeta');
    const modalEditarTiempoUsuariosEl = document.getElementById('modalEditarTiempoUsuarios');
    const modalTiempoUsuariosDetalleIdEl = document.getElementById('modalTiempoUsuariosDetalleId');
    const contenedorTiempoUsuariosRowsEl = document.getElementById('contenedorTiempoUsuariosRows');
    const modalTiempoUsuariosAplicarTodosEl = document.getElementById('modalTiempoUsuariosAplicarTodos');
    const modalTiempoUsuariosTotalEl = document.getElementById('modalTiempoUsuariosTotal');
    const btnTiempoUsuariosSeleccionarTodosEl = document.getElementById('btnTiempoUsuariosSeleccionarTodos');
    const btnTiempoUsuariosDeseleccionarTodosEl = document.getElementById('btnTiempoUsuariosDeseleccionarTodos');
    const btnTiempoUsuariosAplicarSeleccionadosEl = document.getElementById('btnTiempoUsuariosAplicarSeleccionados');
    const btnGuardarTiempoUsuariosEl = document.getElementById('btnGuardarTiempoUsuarios');
    const detalleTiempoUsuarioMap = {};
    let pendingConfirmAction = null;
    let tarjetaTareasActualId = null;
    let tarjetaEditandoId = null;

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
        if(items.length < 2){
            return;
        }

        modalTiempoUsuariosDetalleIdEl.value = String(detalleId);

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

    function openConfirmActionModal(config){
        pendingConfirmAction = config && typeof config.onConfirm === 'function' ? config.onConfirm : null;
        if(modalConfirmarAccionTituloEl){
            modalConfirmarAccionTituloEl.innerHTML = config && config.title ? config.title : '<i class="bi bi-exclamation-triangle"></i> Confirmar accion';
        }
        if(modalConfirmarAccionMensajeEl){
            modalConfirmarAccionMensajeEl.textContent = config && config.message ? config.message : '¿Desea continuar?';
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

        document.querySelectorAll('.tablero-tarjeta').forEach(card => {
            const titleEl = card.querySelector('.card-title');
            const titulo = normalizeText(titleEl ? titleEl.textContent : '');
            const cardPrioridadId = String(card.dataset.prioridadId || '');
            const cardEtiquetas = parseIdList(card.dataset.etiquetaIds || '');

            const matchNombre = query === '' || titulo.includes(query);
            const matchEtiqueta = etiquetaId === '' || cardEtiquetas.includes(etiquetaId);
            const matchPrioridad = prioridadId === '' || cardPrioridadId === prioridadId;
            const visible = matchNombre && matchEtiqueta && matchPrioridad;

            card.classList.toggle('d-none', !visible);
        });

        document.querySelectorAll('.tablero-card-list').forEach(listEl => {
            syncEmptyColumnState(listEl);
            syncColumnCounter(listEl);
        });
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

    function renderHistorial(items){
        if(!contenedorHistorialTarjetaEl) return;

        if(!items || !items.length){
            contenedorHistorialTarjetaEl.innerHTML = '<div class="text-muted small">Sin historial registrado.</div>';
            return;
        }

        const html = items.map(item => {
            const usuario = item.Usuario_email ? escapeHtml(item.Usuario_email) : 'Sistema';
            const mensaje = escapeHtml(item.Mensaje || '');
            const fecha = formatDateTime(item.Fecha_creacion || '');
            return `
                <div class="border-bottom pb-2 mb-2">
                    <div class="small"><strong>${usuario}</strong></div>
                    <div class="small">${mensaje}</div>
                    <div class="text-muted" style="font-size:0.78rem;">${fecha}</div>
                </div>
            `;
        }).join('');

        contenedorHistorialTarjetaEl.innerHTML = html;
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
                const timerActionAllowed = !idUsuarioAsignado || idUsuarioAsignado === currentUserId || canTimerAdminOverride;
                let timerActionReason = '';
                if(!timerActionAllowed){
                    timerActionReason = 'title="Solo el usuario asignado puede operar el cronometro"';
                } else if(idUsuarioAsignado && idUsuarioAsignado !== currentUserId && canTimerAdminOverride){
                    timerActionReason = 'title="Modo administrador: operara el cronometro para el usuario asignado"';
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
                                ${canDeleteTask ? `<button class="btn btn-sm btn-outline-danger btn-delete-detalle" type="button" data-detalle-id="${parseInt(det.Id_tarea_detalle || 0, 10)}" title="Eliminar tarea"><i class="bi bi-trash"></i></button>` : ''}
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
                                ? `<select class="form-select form-select-sm detalle-usuario-asignado tablero-select-enhanced" style="max-width:280px;" data-detalle-id="${parseInt(det.Id_tarea_detalle || 0, 10)}" data-prev-value="${idUsuarioAsignado ? String(idUsuarioAsignado) : ''}">${buildDetalleUsuarioOptions(idUsuarioAsignado)}</select>`
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
            applyTarjetaFilters();
        });
    }

    if(canEditCard && window.Sortable){
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

    applyTarjetaFilters();

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
                const idColumna = this.dataset.columnaId || '';
                const idAlcance = this.dataset.alcanceId || '';
                const idActividad = this.dataset.actividadId || '';
                const idUsuarioAsignado = this.dataset.usuarioAsignadoId || '';
                const idPrioridad = this.dataset.prioridadId || '';
                const fechaInicio = this.dataset.fechaInicio || '';
                const fechaFin = this.dataset.fechaFin || '';
                const completado = parseInt(this.dataset.tarjetaCompletado || '0', 10) === 1;
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

    if(contenedorListasTareasEl){
        contenedorListasTareasEl.addEventListener('click', async function(evt){
            const btnEditTarea = evt.target.closest('.btn-edit-tarea');
            if(btnEditTarea && canEditList && tarjetaTareasActualId){
                const idTarea = parseInt(btnEditTarea.dataset.tareaId || '0', 10);
                const nombreActual = btnEditTarea.dataset.tareaNombre || '';
                const nuevoNombre = window.prompt('Editar nombre de la lista', nombreActual);

                if(nuevoNombre === null){
                    return;
                }

                const nombreLimpio = nuevoNombre.trim();
                if(!nombreLimpio){
                    alert('El nombre de la lista es obligatorio.');
                    return;
                }

                try {
                    await postJson(`${APP_URL_ROOT}/tablero/update_tarjeta_tarea`, {
                        id_tablero: idTableroActual,
                        id_tarea: idTarea,
                        nombre_tarea: nombreLimpio
                    });
                    await cargarModalTareas();
                } catch(err){
                    alert(err.message);
                }
                return;
            }

            const btnEditDetalle = evt.target.closest('.btn-edit-detalle');
            if(btnEditDetalle && canEditTask && tarjetaTareasActualId){
                const idDetalle = parseInt(btnEditDetalle.dataset.detalleId || '0', 10);
                const descripcionActual = btnEditDetalle.dataset.detalleDescripcion || '';
                const nuevaDescripcion = window.prompt('Editar descripcion de la tarea', descripcionActual);

                if(nuevaDescripcion === null){
                    return;
                }

                const descripcionLimpia = nuevaDescripcion.trim();
                if(!descripcionLimpia){
                    alert('La descripcion de la tarea es obligatoria.');
                    return;
                }

                try {
                    await postJson(`${APP_URL_ROOT}/tablero/update_tarjeta_tarea_detalle`, {
                        id_tablero: idTableroActual,
                        id_tarea_detalle: idDetalle,
                        descripcion: descripcionLimpia
                    });
                    await cargarModalTareas();
                } catch(err){
                    alert(err.message);
                }
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
                openConfirmActionModal({
                    title: '<i class="bi bi-trash"></i> Eliminar tarea',
                    message: '¿Eliminar esta tarea del listado?',
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

                if(Array.isArray(tiemposUsuarios) && tiemposUsuarios.length >= 2){
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
                tarjeta_ver: modal.querySelector('#modal_perm_tarjeta_ver'),
                tarjeta_crear: modal.querySelector('#modal_perm_tarjeta_crear'),
                tarjeta_editar: modal.querySelector('#modal_perm_tarjeta_editar'),
                tarjeta_eliminar: modal.querySelector('#modal_perm_tarjeta_eliminar'),
                tarjeta_asignar: modal.querySelector('#modal_perm_tarjeta_asignar'),
                lista_crear: modal.querySelector('#modal_perm_lista_crear'),
                lista_editar: modal.querySelector('#modal_perm_lista_editar'),
                lista_eliminar: modal.querySelector('#modal_perm_lista_eliminar'),
                tarea_crear: modal.querySelector('#modal_perm_tarea_crear'),
                tarea_editar: modal.querySelector('#modal_perm_tarea_editar'),
                tarea_eliminar: modal.querySelector('#modal_perm_tarea_eliminar'),
                tarea_tiempo_editar: modal.querySelector('#modal_perm_tarea_tiempo_editar')
            };

            const resetDefaults = () => {
                Object.keys(checkboxes).forEach(key => {
                    if(!checkboxes[key]) return;
                    checkboxes[key].checked = (key === 'tablero_ver' || key === 'tarjeta_ver');
                });
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
                        'tarjeta_ver', 'tarjeta_crear', 'tarjeta_editar', 'tarjeta_eliminar', 'tarjeta_asignar',
                        'lista_crear', 'lista_editar', 'lista_eliminar',
                        'tarea_crear', 'tarea_editar', 'tarea_eliminar', 'tarea_tiempo_editar'
                    ];
                    const granularSum = granularKeys.reduce((acc, key) => acc + (perms[key] ? 1 : 0), 0);
                    const useLegacyFallback = granularSum === 0 && (perms.legacy_ver || perms.legacy_crear || perms.legacy_editar || perms.legacy_eliminar);

                    const normalizedPerms = useLegacyFallback ? {
                        tablero_ver: !!perms.legacy_ver,
                        tablero_crear: !!perms.legacy_crear,
                        tablero_editar: !!perms.legacy_editar,
                        tablero_eliminar: !!perms.legacy_eliminar,
                        tablero_asignar: !!perms.legacy_editar,
                        tarjeta_ver: !!perms.legacy_ver,
                        tarjeta_crear: !!perms.legacy_crear,
                        tarjeta_editar: !!perms.legacy_editar,
                        tarjeta_eliminar: !!perms.legacy_eliminar,
                        tarjeta_asignar: !!perms.legacy_editar,
                        lista_crear: !!perms.legacy_editar,
                        lista_editar: !!perms.legacy_editar,
                        lista_eliminar: !!perms.legacy_editar,
                        tarea_crear: !!perms.legacy_editar,
                        tarea_editar: !!perms.legacy_editar,
                        tarea_eliminar: !!perms.legacy_editar,
                        tarea_tiempo_editar: !!perms.tarea_tiempo_editar
                    } : perms;

                    Object.keys(checkboxes).forEach(key => {
                        if(!checkboxes[key]) return;
                        checkboxes[key].checked = !!normalizedPerms[key];
                    });
                } else {
                    resetDefaults();
                }
            };

            const syncSelectedUserPermissions = async () => {
                const id = parseInt(userSelect.value || '0', 10);
                if(id > 0){
                    await applyPermissions(id);
                } else {
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

<?php require APPROOT . '/views/layouts/footer.php'; ?>
