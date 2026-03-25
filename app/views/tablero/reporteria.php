<?php require APPROOT . '/views/layouts/header.php'; ?>
<?php
$tableroActual = $data['tableroActual'] ?? null;
$idTableroActual = $tableroActual ? (int)$tableroActual->Id_tablero : 0;
$reporteAgrupado = $data['reporteAgrupado'] ?? [];
$resumenTiempoUsuarios = $data['resumenTiempoUsuarios'] ?? [];
$tableroParam = $idTableroActual > 0 ? ('?tablero_id=' . $idTableroActual) : '';
$canDashboardGlobal = tienePermiso('tablero.dashboard');
$canCalendarioGlobal = tienePermiso('tablero.calendario');
$formatSegundos = function($total){
    $sec = max(0, (int)$total);
    $hh = str_pad((string)floor($sec / 3600), 2, '0', STR_PAD_LEFT);
    $mm = str_pad((string)floor(($sec % 3600) / 60), 2, '0', STR_PAD_LEFT);
    $ss = str_pad((string)($sec % 60), 2, '0', STR_PAD_LEFT);
    return $hh . ':' . $mm . ':' . $ss;
};
?>

<div class="mb-3">
    <h1 class="m-0"><?php echo $data['title']; ?></h1>
</div>

<?php echo displayFlashMessage('tablero_message'); ?>
<?php echo displayFlashMessage('tablero_error'); ?>

<div class="card mb-3">
    <div class="card-body py-2">
        <ul class="nav nav-pills tablero-nav-tabs flex-wrap gap-2">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo URLROOT; ?>/tablero/index<?php echo $tableroParam; ?>">
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
            <li class="nav-item">
                <a class="nav-link active" href="<?php echo URLROOT; ?>/tablero/reporteria<?php echo $tableroParam; ?>">
                    <i class="bi bi-table"></i> Reporte
                </a>
            </li>
        </ul>
    </div>
</div>

<?php if(!empty($data['tableros'])): ?>
    <form action="<?php echo URLROOT; ?>/tablero/reporteria" method="get" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
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
            <?php if($tableroActual): ?>
                <div class="col-12">
                    <label class="form-label">Descargas</label>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-success" href="<?php echo URLROOT; ?>/tablero/export_reporteria?tablero_id=<?php echo $idTableroActual; ?>&format=xlsx">
                            <i class="bi bi-file-earmark-excel"></i> Descargar XLSX
                        </a>
                        <a class="btn btn-danger" href="<?php echo URLROOT; ?>/tablero/export_reporteria?tablero_id=<?php echo $idTableroActual; ?>&format=pdf">
                            <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                        </a>
                        <a class="btn btn-secondary" href="<?php echo URLROOT; ?>/tablero/export_reporteria?tablero_id=<?php echo $idTableroActual; ?>&format=csv">
                            <i class="bi bi-filetype-csv"></i> Descargar CSV
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <?php if(empty($reporteAgrupado)): ?>
        <div class="alert alert-light border">No hay tarjetas activas para el tablero seleccionado.</div>
    <?php else: ?>
        <div class="card mb-3">
            <div class="card-header bg-dark text-white">
                <strong><i class="bi bi-clock-history"></i> Resumen de Tiempo por Usuario</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th style="min-width:240px;">Usuario</th>
                                <th style="min-width:140px;">Total Tarjetas</th>
                                <th style="min-width:180px;">Tiempo Acumulado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($resumenTiempoUsuarios)): ?>
                                <tr>
                                    <td colspan="3" class="text-muted">Sin tiempos por usuario para el tablero seleccionado.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($resumenTiempoUsuarios as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars((string)($item['usuario'] ?? 'Usuario sin nombre')); ?></td>
                                        <td><?php echo (int)($item['total_tarjetas'] ?? 0); ?></td>
                                        <td><strong><?php echo htmlspecialchars($formatSegundos((int)($item['total_segundos'] ?? 0))); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 align-middle reporte-tabla-sticky">
                        <thead class="table-dark">
                            <tr>
                                <th style="min-width:260px;">Descripcion</th>
                                <th style="min-width:130px;">Etapa</th>
                                <th style="min-width:120px;">Prioridad</th>
                                <th style="min-width:130px;">Puntos Prioridad</th>
                                <th style="min-width:180px;">Etiqueta</th>
                                <th style="min-width:220px;">Listado Tareas</th>
                                <th style="min-width:320px;">Tareas</th>
                                <th style="min-width:260px;">Tiempo</th>
                                <th style="min-width:320px;">Tiempo por Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reporteAgrupado as $asignado => $rows): ?>
                                <?php
                                    $sumPrioridad = 0;
                                    $sumTareas = 0;
                                    $sumTiempoSegundos = 0;
                                    $sumTiempoEnCursoSegundos = 0;
                                    foreach($rows as $r){
                                        $sumPrioridad += isset($r['puntos_prioridad']) ? (int)$r['puntos_prioridad'] : 0;
                                        $sumTareas += isset($r['total_tareas']) ? (int)$r['total_tareas'] : 0;
                                        $sumTiempoSegundos += isset($r['total_tiempo_segundos']) ? (int)$r['total_tiempo_segundos'] : 0;
                                        $sumTiempoEnCursoSegundos += isset($r['total_tiempo_en_curso_segundos']) ? (int)$r['total_tiempo_en_curso_segundos'] : 0;
                                    }
                                ?>
                                <tr class="table-secondary">
                                    <td colspan="9">
                                        <strong>Asignado:</strong> <?php echo htmlspecialchars($asignado); ?>
                                        <span class="badge bg-light text-dark border border-secondary ms-2"><?php echo count($rows); ?> tarjeta(s)</span>
                                        <span class="badge bg-primary ms-2">Prioridad: <?php echo (int)$sumPrioridad; ?></span>
                                        <span class="badge bg-info text-dark ms-2">Tareas: <?php echo (int)$sumTareas; ?></span>
                                        <span class="badge bg-warning text-dark border ms-2">Tiempo: <?php echo htmlspecialchars($formatSegundos($sumTiempoSegundos)); ?></span>
                                        <span class="badge bg-danger ms-2">Tiempo en curso: <?php echo htmlspecialchars($formatSegundos($sumTiempoEnCursoSegundos)); ?></span>
                                    </td>
                                </tr>
                                <?php foreach($rows as $row): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['descripcion']); ?></div>
                                            <div class="text-muted small"><?php echo $row['descripcion_detalle'] !== '' ? nl2br(htmlspecialchars($row['descripcion_detalle'])) : 'Sin descripcion'; ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['etapa']); ?></td>
                                        <td><?php echo htmlspecialchars($row['prioridad']); ?></td>
                                        <td><?php echo (int)$row['puntos_prioridad']; ?></td>
                                        <td>
                                            <?php if(!empty($row['etiquetas'])): ?>
                                                <?php echo htmlspecialchars(implode(' | ', $row['etiquetas'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin etiquetas</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!empty($row['listas_tareas'])): ?>
                                                <?php echo htmlspecialchars(implode(' | ', $row['listas_tareas'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin listas</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!empty($row['tareas'])): ?>
                                                <ul class="mb-0 ps-3">
                                                    <?php foreach($row['tareas'] as $t): ?>
                                                        <li class="small"><?php echo htmlspecialchars($t); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted">Sin tareas</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!empty($row['tiempos'])): ?>
                                                <ul class="mb-0 ps-3">
                                                    <?php foreach($row['tiempos'] as $ti): ?>
                                                        <li class="small"><?php echo htmlspecialchars($ti); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted">Sin tiempos</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!empty($row['tiempo_por_usuario'])): ?>
                                                <ul class="mb-0 ps-3">
                                                    <?php foreach($row['tiempo_por_usuario'] as $tu): ?>
                                                        <li class="small"><?php echo htmlspecialchars($tu); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted">Sin tiempos por usuario</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="alert alert-warning">
        No hay tableros asignados para mostrar reportes.
    </div>
<?php endif; ?>

<?php require APPROOT . '/views/layouts/footer.php'; ?>
