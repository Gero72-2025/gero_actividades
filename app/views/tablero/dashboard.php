<?php require APPROOT . '/views/layouts/header.php'; ?>
<?php
$tableroActual = $data['tableroActual'] ?? null;
$idTableroActual = $tableroActual ? (int)$tableroActual->Id_tablero : 0;
$tableroParam = $idTableroActual > 0 ? ('?tablero_id=' . $idTableroActual) : '';
$dashboardMetrics = $data['dashboardMetrics'] ?? [];
$summary = $dashboardMetrics['summary'] ?? [];
$topCards = $dashboardMetrics['top_cards'] ?? [];
$bottleneckCards = $dashboardMetrics['bottleneck_cards'] ?? [];
$criticalColumns = $dashboardMetrics['critical_columns'] ?? [];
$assignedStats = $dashboardMetrics['assigned'] ?? [];
$priorityStats = $dashboardMetrics['priorities'] ?? [];
$chartData = $dashboardMetrics['chart_data'] ?? [
    'columns' => ['labels' => [], 'time_seconds' => [], 'card_counts' => [], 'completion_percent' => [], 'colors' => []],
    'priorities' => ['labels' => [], 'time_seconds' => [], 'card_counts' => [], 'colors' => []],
    'assigned' => ['labels' => [], 'time_seconds' => [], 'card_counts' => [], 'completion_percent' => []]
];
$formatSegundos = function($total){
    $sec = max(0, (int)$total);
    $hh = str_pad((string)floor($sec / 3600), 2, '0', STR_PAD_LEFT);
    $mm = str_pad((string)floor(($sec % 3600) / 60), 2, '0', STR_PAD_LEFT);
    $ss = str_pad((string)($sec % 60), 2, '0', STR_PAD_LEFT);
    return $hh . ':' . $mm . ':' . $ss;
};
$formatPercent = function($value){
    return number_format((float)$value, 1) . '%';
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
            <li class="nav-item">
                <a class="nav-link active" href="<?php echo URLROOT; ?>/tablero/dashboard<?php echo $tableroParam; ?>">
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

<?php if(!empty($data['tableros'])): ?>
    <form action="<?php echo URLROOT; ?>/tablero/dashboard" method="get" class="card card-body mb-4 dashboard-tablero-toolbar">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
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
            <div class="col-12 col-md-7">
                <label class="form-label">Descripcion</label>
                <div class="form-control bg-light dashboard-tablero-description"><?php echo $tableroActual && !empty($tableroActual->Descripcion) ? htmlspecialchars($tableroActual->Descripcion) : 'Sin descripcion'; ?></div>
            </div>
        </div>
    </form>

    <section class="dashboard-tablero-hero mb-4">
        <div class="dashboard-tablero-hero__content">
            <div>
                <span class="dashboard-tablero-kicker">Tablero Activo</span>
                <h2 class="dashboard-tablero-title"><?php echo $tableroActual ? htmlspecialchars($tableroActual->Nombre) : 'Tablero'; ?></h2>
                <p class="dashboard-tablero-subtitle">Visión ejecutiva del flujo de trabajo con foco en tiempo invertido, avance operativo por columna y presión por prioridad.</p>
            </div>
            <div class="dashboard-tablero-hero__meta">
                <div class="dashboard-meta-pill">
                    <span>Columnas activas</span>
                    <strong><?php echo (int)($summary['active_columns'] ?? 0); ?></strong>
                </div>
                <div class="dashboard-meta-pill">
                    <span>Tiempo en curso</span>
                    <strong><?php echo htmlspecialchars($formatSegundos((int)($summary['running_time_seconds'] ?? 0))); ?></strong>
                </div>
                <div class="dashboard-meta-pill">
                    <span>Actualizado</span>
                    <strong><?php echo date('d/m/Y H:i'); ?></strong>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-kpi-grid mb-4">
        <article class="dashboard-kpi-card dashboard-kpi-card--sand">
            <span class="dashboard-kpi-label">Tiempo total acumulado</span>
            <strong class="dashboard-kpi-value"><?php echo htmlspecialchars($formatSegundos((int)($summary['total_time_seconds'] ?? 0))); ?></strong>
            <small class="dashboard-kpi-meta">Promedio por tarjeta: <?php echo htmlspecialchars($formatSegundos((int)($summary['avg_time_per_card_seconds'] ?? 0))); ?></small>
        </article>
        <article class="dashboard-kpi-card dashboard-kpi-card--ink">
            <span class="dashboard-kpi-label">Tarjetas activas</span>
            <strong class="dashboard-kpi-value"><?php echo (int)($summary['total_cards'] ?? 0); ?></strong>
            <small class="dashboard-kpi-meta"><?php echo (int)($summary['cards_running'] ?? 0); ?> tarjeta(s) con tiempo corriendo</small>
        </article>
        <article class="dashboard-kpi-card dashboard-kpi-card--mint">
            <span class="dashboard-kpi-label">Avance de tareas</span>
            <strong class="dashboard-kpi-value"><?php echo $formatPercent((float)($summary['task_completion_percent'] ?? 0)); ?></strong>
            <small class="dashboard-kpi-meta"><?php echo (int)($summary['completed_tasks'] ?? 0); ?> de <?php echo (int)($summary['total_tasks'] ?? 0); ?> completadas</small>
        </article>
        <article class="dashboard-kpi-card dashboard-kpi-card--ember">
            <span class="dashboard-kpi-label">Prioridad acumulada</span>
            <strong class="dashboard-kpi-value"><?php echo number_format((float)($summary['total_priority_points'] ?? 0), 0); ?></strong>
            <small class="dashboard-kpi-meta">Promedio: <?php echo number_format((float)($summary['avg_priority_points'] ?? 0), 1); ?> pts por tarjeta</small>
        </article>
        <article class="dashboard-kpi-card dashboard-kpi-card--sea">
            <span class="dashboard-kpi-label">Tarjetas en columnas finales</span>
            <strong class="dashboard-kpi-value"><?php echo $formatPercent((float)($summary['completed_column_percent'] ?? 0)); ?></strong>
            <small class="dashboard-kpi-meta"><?php echo (int)($summary['completed_column_cards'] ?? 0); ?> tarjeta(s) en etapa de cierre</small>
        </article>
        <article class="dashboard-kpi-card dashboard-kpi-card--plum">
            <span class="dashboard-kpi-label">Tarjetas de presión alta</span>
            <strong class="dashboard-kpi-value"><?php echo (int)($summary['high_priority_cards'] ?? 0); ?></strong>
            <small class="dashboard-kpi-meta">Prioridades con valor mayor o igual a 8</small>
        </article>
    </section>

    <section class="dashboard-kpi-grid dashboard-kpi-grid--summary mb-4">
        <article class="dashboard-kpi-card dashboard-kpi-card--bottleneck">
            <span class="dashboard-kpi-label">Semaforo de cuellos de botella</span>
            <strong class="dashboard-kpi-value"><?php echo $formatPercent((float)($summary['bottleneck_index'] ?? 0)); ?></strong>
            <small class="dashboard-kpi-meta">Indice ponderado: rojo = 1 | amarillo = 0.5 | verde = 0</small>
            <div class="dashboard-semaforo-resumen">
                <span class="dashboard-semaforo-pill dashboard-semaforo-pill--red">
                    <span class="dot"></span>
                    Rojo: <?php echo (int)($summary['bottleneck_red_cards'] ?? 0); ?>
                </span>
                <span class="dashboard-semaforo-pill dashboard-semaforo-pill--yellow">
                    <span class="dot"></span>
                    Amarillo: <?php echo (int)($summary['bottleneck_yellow_cards'] ?? 0); ?>
                </span>
                <span class="dashboard-semaforo-pill dashboard-semaforo-pill--green">
                    <span class="dot"></span>
                    Verde: <?php echo (int)($summary['bottleneck_green_cards'] ?? 0); ?>
                </span>
            </div>
        </article>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card dashboard-panel h-100">
                <div class="card-header dashboard-panel__header">
                    <div>
                        <span class="dashboard-panel__eyebrow">Tiempo por columna</span>
                        <h3 class="dashboard-panel__title">Dónde se concentra el esfuerzo</h3>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="dashboardColumnTimeChart" height="140"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card dashboard-panel h-100">
                <div class="card-header dashboard-panel__header">
                    <div>
                        <span class="dashboard-panel__eyebrow">Carga por prioridad</span>
                        <h3 class="dashboard-panel__title">Presión operativa actual</h3>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="dashboardPriorityChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card dashboard-panel h-100">
                <div class="card-header dashboard-panel__header">
                    <div>
                        <span class="dashboard-panel__eyebrow">Avance por columna</span>
                        <h3 class="dashboard-panel__title">Ritmo de ejecución de tareas</h3>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(!empty($criticalColumns)): ?>
                        <div class="dashboard-progress-list">
                            <?php foreach($criticalColumns as $column): ?>
                                <div class="dashboard-progress-row">
                                    <div class="dashboard-progress-row__head">
                                        <span><?php echo htmlspecialchars($column['nombre']); ?></span>
                                        <strong><?php echo $formatPercent((float)$column['completion_percent']); ?></strong>
                                    </div>
                                    <div class="progress dashboard-progress-bar">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo max(0, min(100, (float)$column['completion_percent'])); ?>%; background: <?php echo htmlspecialchars($column['color']); ?>;"></div>
                                    </div>
                                    <div class="dashboard-progress-row__meta">
                                        <span><?php echo (int)$column['card_count']; ?> tarjeta(s)</span>
                                        <span><?php echo htmlspecialchars($formatSegundos((int)$column['time_seconds'])); ?></span>
                                        <span><?php echo (int)$column['running_cards']; ?> en curso</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border mb-0">No hay columnas con datos para analizar.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card dashboard-panel h-100">
                <div class="card-header dashboard-panel__header">
                    <div>
                        <span class="dashboard-panel__eyebrow">Carga por responsable</span>
                        <h3 class="dashboard-panel__title">Distribución de tiempo por asignado</h3>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="dashboardAssignedChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card dashboard-panel h-100">
                <div class="card-header dashboard-panel__header">
                    <div>
                        <span class="dashboard-panel__eyebrow">Tarjetas con mayor tiempo</span>
                        <h3 class="dashboard-panel__title">Focos de atención inmediata</h3>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if(!empty($topCards)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Tarjeta</th>
                                        <th>Semaforo</th>
                                        <th>Columna</th>
                                        <th>Prioridad</th>
                                        <th>Asignado</th>
                                        <th>Avance</th>
                                        <th>Tiempo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($topCards as $card): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($card['titulo']); ?></div>
                                                <div class="small text-muted"><?php echo !empty($card['labels']) ? htmlspecialchars(implode(' | ', array_map(function($label){ return $label['nombre']; }, $card['labels']))) : 'Sin etiquetas'; ?></div>
                                            </td>
                                            <td>
                                                <?php $light = $card['traffic_light'] ?? ['label' => 'Verde', 'level' => 'green', 'reason' => '']; ?>
                                                <span class="badge dashboard-semaforo-badge dashboard-semaforo-badge--<?php echo htmlspecialchars($light['level']); ?>" title="<?php echo htmlspecialchars((string)($light['reason'] ?? '')); ?>">
                                                    <span class="dot"></span>
                                                    <?php echo htmlspecialchars((string)($light['label'] ?? 'Verde')); ?>
                                                </span>
                                            </td>
                                            <td><span class="badge dashboard-soft-badge" style="background: <?php echo htmlspecialchars($card['columna_color']); ?>20; color: <?php echo htmlspecialchars($card['columna_color']); ?>; border-color: <?php echo htmlspecialchars($card['columna_color']); ?>40;"><?php echo htmlspecialchars($card['columna']); ?></span></td>
                                            <td><span class="badge" style="background: <?php echo htmlspecialchars($card['prioridad_color']); ?>; color: #fff;"><?php echo htmlspecialchars($card['prioridad']); ?> (<?php echo (int)$card['prioridad_valor']; ?>)</span></td>
                                            <td><?php echo htmlspecialchars($card['asignado']); ?></td>
                                            <td><?php echo $formatPercent((float)$card['completion_rate']); ?></td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($formatSegundos((int)$card['total_time_seconds'])); ?></div>
                                                <?php if(!empty($card['has_running'])): ?>
                                                    <div class="small text-danger">En curso: <?php echo htmlspecialchars($formatSegundos((int)$card['running_time_seconds'])); ?></div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-3">
                            <div class="alert alert-light border mb-0">No hay tarjetas activas para mostrar en el dashboard.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card dashboard-panel h-100">
                <div class="card-header dashboard-panel__header">
                    <div>
                        <span class="dashboard-panel__eyebrow">Responsables</span>
                        <h3 class="dashboard-panel__title">Ranking de carga operativa</h3>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if(!empty($assignedStats)): ?>
                        <div class="dashboard-list-group">
                            <?php foreach(array_slice($assignedStats, 0, 6) as $assigned): ?>
                                <div class="dashboard-list-group__item">
                                    <div>
                                        <strong><?php echo htmlspecialchars($assigned['nombre']); ?></strong>
                                        <div class="small text-muted"><?php echo (int)$assigned['card_count']; ?> tarjeta(s) | <?php echo (int)$assigned['running_cards']; ?> en curso</div>
                                    </div>
                                    <div class="text-end">
                                        <strong><?php echo htmlspecialchars($formatSegundos((int)$assigned['time_seconds'])); ?></strong>
                                        <div class="small text-muted"><?php echo $formatPercent((float)$assigned['completion_percent']); ?> avance</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-3">
                            <div class="alert alert-light border mb-0">No hay responsables con información disponible.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card dashboard-panel h-100">
                <div class="card-header dashboard-panel__header">
                    <div>
                        <span class="dashboard-panel__eyebrow">Prioridades</span>
                        <h3 class="dashboard-panel__title">Resumen por nivel</h3>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if(!empty($priorityStats)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Prioridad</th>
                                        <th>Tarjetas</th>
                                        <th>Tiempo</th>
                                        <th>Promedio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($priorityStats as $priority): ?>
                                        <tr>
                                            <td><span class="badge" style="background: <?php echo htmlspecialchars($priority['color']); ?>; color:#fff;"><?php echo htmlspecialchars($priority['nombre']); ?> (<?php echo (int)$priority['valor']; ?>)</span></td>
                                            <td><?php echo (int)$priority['card_count']; ?></td>
                                            <td><?php echo htmlspecialchars($formatSegundos((int)$priority['time_seconds'])); ?></td>
                                            <td><?php echo htmlspecialchars($formatSegundos((int)$priority['avg_time_per_card_seconds'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-3">
                            <div class="alert alert-light border mb-0">No hay prioridades con actividad registrada.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card dashboard-panel h-100">
                <div class="card-header dashboard-panel__header">
                    <div>
                        <span class="dashboard-panel__eyebrow">Columnas críticas</span>
                        <h3 class="dashboard-panel__title">Etapas con mayor tiempo invertido</h3>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if(!empty($criticalColumns)): ?>
                        <div class="dashboard-list-group">
                            <?php foreach($criticalColumns as $column): ?>
                                <div class="dashboard-list-group__item">
                                    <div>
                                        <span class="badge dashboard-soft-badge" style="background: <?php echo htmlspecialchars($column['color']); ?>20; color: <?php echo htmlspecialchars($column['color']); ?>; border-color: <?php echo htmlspecialchars($column['color']); ?>40;"><?php echo htmlspecialchars($column['nombre']); ?></span>
                                        <div class="small text-muted mt-1"><?php echo (int)$column['card_count']; ?> tarjeta(s) | <?php echo (int)$column['completed_tasks']; ?>/<?php echo (int)$column['total_tasks']; ?> tareas</div>
                                    </div>
                                    <div class="text-end">
                                        <strong><?php echo htmlspecialchars($formatSegundos((int)$column['time_seconds'])); ?></strong>
                                        <div class="small text-muted"><?php echo $formatPercent((float)$column['completion_percent']); ?> completado</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-3">
                            <div class="alert alert-light border mb-0">No hay columnas con actividad para ordenar.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-12">
            <div class="card dashboard-panel">
                <div class="card-header dashboard-panel__header">
                    <div>
                        <span class="dashboard-panel__eyebrow">Tarjeta resumen</span>
                        <h3 class="dashboard-panel__title">Cuellos de botella detectados por semaforo</h3>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if(!empty($bottleneckCards)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Tarjeta</th>
                                        <th>Semaforo</th>
                                        <th>Motivo</th>
                                        <th>Columna</th>
                                        <th>Asignado</th>
                                        <th>Prioridad</th>
                                        <th>Tiempo</th>
                                        <th>Avance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($bottleneckCards as $card): ?>
                                        <?php $light = $card['traffic_light'] ?? ['label' => 'Verde', 'level' => 'green', 'reason' => '']; ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($card['titulo']); ?></div>
                                                <div class="small text-muted">#<?php echo (int)$card['id_tarjeta']; ?></div>
                                            </td>
                                            <td>
                                                <span class="badge dashboard-semaforo-badge dashboard-semaforo-badge--<?php echo htmlspecialchars($light['level']); ?>">
                                                    <span class="dot"></span>
                                                    <?php echo htmlspecialchars((string)($light['label'] ?? 'Verde')); ?>
                                                </span>
                                                <div class="small text-muted mt-1">Score: <?php echo (int)($light['score'] ?? 0); ?></div>
                                            </td>
                                            <td class="small"><?php echo htmlspecialchars((string)($light['reason'] ?? 'Sin alertas')); ?></td>
                                            <td><span class="badge dashboard-soft-badge" style="background: <?php echo htmlspecialchars($card['columna_color']); ?>20; color: <?php echo htmlspecialchars($card['columna_color']); ?>; border-color: <?php echo htmlspecialchars($card['columna_color']); ?>40;"><?php echo htmlspecialchars($card['columna']); ?></span></td>
                                            <td><?php echo htmlspecialchars($card['asignado']); ?></td>
                                            <td><span class="badge" style="background: <?php echo htmlspecialchars($card['prioridad_color']); ?>; color:#fff;"><?php echo htmlspecialchars($card['prioridad']); ?> (<?php echo (int)$card['prioridad_valor']; ?>)</span></td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($formatSegundos((int)$card['total_time_seconds'])); ?></div>
                                                <?php if(!empty($card['has_running'])): ?>
                                                    <div class="small text-danger">En curso: <?php echo htmlspecialchars($formatSegundos((int)$card['running_time_seconds'])); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $formatPercent((float)$card['completion_rate']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-3">
                            <div class="alert alert-light border mb-0">Aun no hay tarjetas para evaluar cuellos de botella.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>
    <div class="alert alert-warning">No hay tableros asignados para mostrar el dashboard.</div>
<?php endif; ?>

<script src="<?php echo URLROOT; ?>/public/lib/chartjs/chart.min.js"></script>
<script>
(function(){
    const chartData = <?php echo json_encode($chartData, JSON_UNESCAPED_UNICODE); ?>;
    const secondsToHours = values => (values || []).map(value => Number((Number(value || 0) / 3600).toFixed(2)));
    const assignedPalette = ['#204b57', '#4e7d77', '#d17b49', '#8f5d5d', '#617a9b', '#7c9150', '#9a6b8f', '#59606d'];

    const sharedLegend = {
        labels: {
            boxWidth: 12,
            color: '#44515a',
            font: { size: 11, weight: '600' }
        }
    };

    const columnCtx = document.getElementById('dashboardColumnTimeChart');
    if(columnCtx && chartData.columns.labels.length){
        new Chart(columnCtx, {
            type: 'bar',
            data: {
                labels: chartData.columns.labels,
                datasets: [
                    {
                        label: 'Horas invertidas',
                        data: secondsToHours(chartData.columns.time_seconds),
                        backgroundColor: chartData.columns.colors,
                        borderRadius: 8,
                        maxBarThickness: 42
                    },
                    {
                        label: 'Tarjetas',
                        data: chartData.columns.card_counts,
                        type: 'line',
                        borderColor: '#20262c',
                        backgroundColor: '#20262c',
                        yAxisID: 'yCards',
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: sharedLegend,
                    tooltip: {
                        callbacks: {
                            label: function(context){
                                if(context.dataset.label === 'Horas invertidas'){
                                    return context.dataset.label + ': ' + context.formattedValue + ' h';
                                }
                                return context.dataset.label + ': ' + context.formattedValue;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Horas' },
                        ticks: { color: '#5e6a73' },
                        grid: { color: 'rgba(32, 75, 87, 0.08)' }
                    },
                    yCards: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { display: false },
                        ticks: { color: '#5e6a73', stepSize: 1 }
                    },
                    x: {
                        ticks: { color: '#5e6a73' },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    const priorityCtx = document.getElementById('dashboardPriorityChart');
    if(priorityCtx && chartData.priorities.labels.length){
        new Chart(priorityCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.priorities.labels,
                datasets: [{
                    data: secondsToHours(chartData.priorities.time_seconds),
                    backgroundColor: chartData.priorities.colors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                        ,labels: sharedLegend.labels
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context){
                                return context.label + ': ' + context.formattedValue + ' h';
                            }
                        }
                    }
                },
                cutout: '64%'
            }
        });
    }

    const assignedCtx = document.getElementById('dashboardAssignedChart');
    if(assignedCtx && chartData.assigned.labels.length){
        new Chart(assignedCtx, {
            type: 'bar',
            data: {
                labels: chartData.assigned.labels,
                datasets: [
                    {
                        label: 'Horas invertidas',
                        data: secondsToHours(chartData.assigned.time_seconds),
                        backgroundColor: assignedPalette.slice(0, chartData.assigned.labels.length),
                        borderRadius: 8,
                        maxBarThickness: 24
                    },
                    {
                        label: 'Avance %',
                        data: chartData.assigned.completion_percent,
                        type: 'line',
                        borderColor: '#d17b49',
                        backgroundColor: '#d17b49',
                        yAxisID: 'yPercent',
                        tension: 0.35,
                        pointRadius: 4
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: sharedLegend,
                    tooltip: {
                        callbacks: {
                            label: function(context){
                                if(context.dataset.label === 'Horas invertidas'){
                                    return context.dataset.label + ': ' + context.formattedValue + ' h';
                                }
                                return context.dataset.label + ': ' + context.formattedValue + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { color: '#5e6a73' },
                        grid: { color: 'rgba(32, 75, 87, 0.08)' }
                    },
                    y: {
                        ticks: { color: '#5e6a73' },
                        grid: { display: false }
                    },
                    yPercent: {
                        beginAtZero: true,
                        position: 'right',
                        min: 0,
                        max: 100,
                        ticks: { color: '#5e6a73' },
                        grid: { display: false }
                    }
                }
            }
        });
    }
})();
</script>

<?php require APPROOT . '/views/layouts/footer.php'; ?>