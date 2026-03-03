<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Título y Bienvenida -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h1 class="mb-0"><i class="bi bi-graph-up"></i> Dashboard Ejecutivo - Resumen Divisional</h1>
                    <p class="mb-0 mt-2">Monitore el desempeño de todas las divisiones</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen General de Estadísticas (Total de todas las divisiones) -->
    <div class="row mb-4">
        <?php 
        $totalGeneral = 0;
        $completadasTotal = 0;
        $enProgresoTotal = 0;
        $pendientesTotal = 0;
        $personalTotal = 0;
        
        if(isset($data['stats_by_division']) && is_array($data['stats_by_division'])):
            foreach($data['stats_by_division'] as $stat):
                $totalGeneral += ($stat->total ?? 0);
                $completadasTotal += ($stat->completadas ?? 0);
                $enProgresoTotal += ($stat->en_progreso ?? 0);
                $pendientesTotal += ($stat->pendientes ?? 0);
                $personalTotal += ($stat->cantidad_personal ?? 0);
            endforeach;
        endif;
        ?>
        
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary"><i class="bi bi-list-check"></i> Total de Tareas</h5>
                    <h2 class="text-primary"><?php echo $totalGeneral; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="bi bi-check-circle"></i> Completadas</h5>
                    <h2 class="text-success"><?php echo $completadasTotal; ?></h2>
                    <small class="text-muted"><?php echo $totalGeneral > 0 ? round(($completadasTotal / $totalGeneral) * 100, 1) : 0; ?>%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning"><i class="bi bi-hourglass"></i> En Progreso</h5>
                    <h2 class="text-warning"><?php echo $enProgresoTotal; ?></h2>
                    <small class="text-muted"><?php echo $totalGeneral > 0 ? round(($enProgresoTotal / $totalGeneral) * 100, 1) : 0; ?>%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger"><i class="bi bi-exclamation-circle"></i> Pendientes</h5>
                    <h2 class="text-danger"><?php echo $pendientesTotal; ?></h2>
                    <small class="text-muted"><?php echo $totalGeneral > 0 ? round(($pendientesTotal / $totalGeneral) * 100, 1) : 0; ?>%</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4" id="carouselStats">
        <div class="col-md-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-collection"></i> Estadísticas en Tiempo Real por División</h5>
                </div>
                <div class="card-body">
                    <div style="overflow-x: auto; padding: 10px 0; scrollbar-width: none; -ms-overflow-style: none;">
                        <div class="carousel-container" style="display: flex; gap: 15px; animation: scroll 55s linear infinite; width: fit-content;">
                            <?php 
                            if(isset($data['detailed_stats']) && is_array($data['detailed_stats'])):
                                foreach($data['detailed_stats'] as $stat):
                                    // Tarjeta de Completadas
                                    echo '
                                    <div class="stat-card" style="flex: 0 0 280px;">
                                        <div class="card h-100 border-left-success shadow-sm">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                                                </div>
                                                <h6 class="text-muted mb-2">Completadas</h6>
                                                <h3 class="text-success mb-2">' . ($stat->completadas ?? 0) . '</h3>
                                                <small class="text-muted">' . htmlspecialchars($stat->Division_Nombre, ENT_QUOTES, 'UTF-8') . '</small>
                                            </div>
                                        </div>
                                    </div>';
                                    
                                    // Tarjeta de En Progreso
                                    echo '
                                    <div class="stat-card" style="flex: 0 0 280px;">
                                        <div class="card h-100 border-left-warning shadow-sm">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
                                                </div>
                                                <h6 class="text-muted mb-2">En Progreso</h6>
                                                <h3 class="text-warning mb-2">' . ($stat->en_progreso ?? 0) . '</h3>
                                                <small class="text-muted">' . htmlspecialchars($stat->Division_Nombre, ENT_QUOTES, 'UTF-8') . '</small>
                                            </div>
                                        </div>
                                    </div>';
                                    
                                    // Tarjeta de Pendientes
                                    echo '
                                    <div class="stat-card" style="flex: 0 0 280px;">
                                        <div class="card h-100 border-left-danger shadow-sm">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 2rem;"></i>
                                                </div>
                                                <h6 class="text-muted mb-2">Pendientes</h6>
                                                <h3 class="text-danger mb-2">' . ($stat->pendientes ?? 0) . '</h3>
                                                <small class="text-muted">' . htmlspecialchars($stat->Division_Nombre, ENT_QUOTES, 'UTF-8') . '</small>
                                            </div>
                                        </div>
                                    </div>';
                                    
                                    // Tarjeta de Contratos
                                    echo '
                                    <div class="stat-card" style="flex: 0 0 280px;">
                                        <div class="card h-100 border-left-primary shadow-sm">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bi bi-file-text text-primary" style="font-size: 2rem;"></i>
                                                </div>
                                                <h6 class="text-muted mb-2">Contratos</h6>
                                                <h3 class="text-primary mb-2">' . ($stat->total_contratos ?? 0) . '</h3>
                                            </div>
                                        </div>
                                    </div>';
                                    
                                    // Tarjeta de Alcances
                                    echo '
                                    <div class="stat-card" style="flex: 0 0 280px;">
                                        <div class="card h-100 border-left-secondary shadow-sm">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bi bi-diagram-3 text-secondary" style="font-size: 2rem;"></i>
                                                </div>
                                                <h6 class="text-muted mb-2">Alcances</h6>
                                                <h3 class="text-secondary mb-2">' . ($stat->total_alcances ?? 0) . '</h3>
                                            </div>
                                        </div>
                                    </div>';
                                    
                                    // Tarjeta de Personal
                                    echo '
                                    <div class="stat-card" style="flex: 0 0 280px;">
                                        <div class="card h-100 border-left-info shadow-sm">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bi bi-people text-info" style="font-size: 2rem;"></i>
                                                </div>
                                                <h6 class="text-muted mb-2">Personal</h6>
                                                <h3 class="text-info mb-2">' . ($stat->total_personal ?? 0) . '</h3>
                                            </div>
                                        </div>
                                    </div>';
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Divisiones con Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Resumen por División</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>División</th>
                                    <th>Total de Tareas</th>
                                    <th>Completadas</th>
                                    <th>En Progreso</th>
                                    <th>Pendientes</th>
                                    <th>% Completadas</th>
                                    <th>Personal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if(isset($data['stats_by_division']) && is_array($data['stats_by_division'])):
                                    foreach($data['stats_by_division'] as $stat):
                                        $totalDiv = ($stat->total ?? 0);
                                        $porcentaje = $totalDiv > 0 ? round(($stat->completadas ?? 0) / $totalDiv * 100, 1) : 0;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo $stat->Division_Nombre; ?></strong></td>
                                            <td><?php echo $totalDiv; ?></td>
                                            <td><span class="badge badge-success"><?php echo $stat->completadas ?? 0; ?></span></td>
                                            <td><span class="badge badge-warning"><?php echo $stat->en_progreso ?? 0; ?></span></td>
                                            <td><span class="badge badge-danger"><?php echo $stat->pendientes ?? 0; ?></span></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $porcentaje; ?>%" aria-valuenow="<?php echo $porcentaje; ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?php echo $porcentaje; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge badge-info"><?php echo $stat->cantidad_personal ?? 0; ?></span></td>
                                            <td>
                                                <button 
                                                    class="btn btn-sm btn-primary btn-division-detail" 
                                                    data-division-id="<?php echo $stat->Id_Division; ?>" 
                                                    data-division-name="<?php echo htmlspecialchars($stat->Division_Nombre, ENT_QUOTES, 'UTF-8'); ?>" 
                                                    title="Ver Detalles">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php
                                    endforeach;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No hay divisiones registradas</td>
                                    </tr>
                                    <?php
                                endif;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Actividades por Semana (todas las divisiones) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Actividades Registradas por Semana</h5>
                </div>
                <div class="card-body">
                    <canvas id="weeklyChart"></canvas>
                    <?php if(!empty($divisionColorMap)): ?>
                        <div class="mt-3 d-flex flex-wrap align-items-center" style="gap:10px;">
                            <span class="text-muted">Leyenda:</span>
                            <?php foreach($divisionColorMap as $divId => $color): ?>
                                <span class="badge" style="background: <?php echo $color; ?>; color: #fff;">División <?php echo htmlspecialchars($divId, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Actividades por Mes (todas las divisiones) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-calendar3"></i> Actividades Registradas por Mes</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart"></canvas>
                    <?php if(!empty($monthlyDivisionColorMap)): ?>
                        <div class="mt-3 d-flex flex-wrap align-items-center" style="gap:10px;">
                            <span class="text-muted">Leyenda:</span>
                            <?php foreach($monthlyDivisionColorMap as $divId => $color): ?>
                                <span class="badge" style="background: <?php echo $color; ?>; color: #fff;">División <?php echo htmlspecialchars($divId, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Detalle por División -->
<div class="modal fade" id="divisionDetailModal" tabindex="-1" role="dialog" aria-labelledby="divisionDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="divisionDetailTitle">Detalle de División</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="divisionDetailLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="mt-2 mb-0">Cargando información de la división...</p>
                </div>

                <div id="divisionDetailError" class="alert alert-danger d-none" role="alert"></div>
                <div id="divisionDetailEmpty" class="alert alert-warning d-none" role="alert">No se encontraron actividades para esta división.</div>

                <div id="divisionDetailContent" class="d-none">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                <span class="badge badge-success p-2">Completadas: <strong id="divisionTotalCompletadas">0</strong></span>
                                <span class="badge badge-warning p-2">En Progreso: <strong id="divisionTotalEnProgreso">0</strong></span>
                                <span class="badge badge-danger p-2">Pendientes: <strong id="divisionTotalPendientes">0</strong></span>
                                <span class="badge badge-info p-2">Total: <strong id="divisionTotalActividades">0</strong></span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Personal</th>
                                    <th class="text-center">Completadas</th>
                                    <th class="text-center">En Progreso</th>
                                    <th class="text-center">Pendientes</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody id="divisionDetailTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para gráficos -->
<script src="<?php echo URLROOT; ?>/public/lib/chartjs/chart.min.js"></script>
<script>
    // Gráfico semanal
    <?php 
    $weeklyData = [];
    $weeklyLabels = [];
    $weeklyColors = [];
    $weeklyBorderColors = [];
    $divisionColorMap = [];
    $palette = [
        '#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f',
        '#edc948', '#b07aa1', '#ff9da7', '#9c755f', '#bab0ab'
    ];
    $paletteCount = count($palette);

    if(isset($data['stats_by_week']) && is_array($data['stats_by_week'])):
        $colorIndex = 0;
        foreach($data['stats_by_week'] as $week):
            if(!isset($divisionColorMap[$week->Id_Division])){
                $divisionColorMap[$week->Id_Division] = $palette[$colorIndex % $paletteCount];
                $colorIndex++;
            }

            $label = "Semana " . $week->semana . " - " . $week->Division_Nombre;
            $weeklyLabels[] = $label;
            $weeklyData[] = $week->cantidad ?? 0;
            $color = $divisionColorMap[$week->Id_Division];
            $weeklyColors[] = $color;
            $weeklyBorderColors[] = $color;
        endforeach;
    endif;
    ?>

    <?php 
    $monthlyData = [];
    $monthlyLabels = [];
    $monthlyColors = [];
    $monthlyBorderColors = [];
    $monthlyDivisionColorMap = [];
    $monthlyPalette = [
        '#2e86de', '#ff6b6b', '#10ac84', '#f6b93b', '#5f27cd',
        '#1dd1a1', '#ff9f43', '#576574', '#54a0ff', '#c44569'
    ];
    $monthlyPaletteCount = count($monthlyPalette);

    if(isset($data['stats_by_month']) && is_array($data['stats_by_month'])):
        $monthlyColorIndex = 0;
        foreach($data['stats_by_month'] as $month):
            if(!isset($monthlyDivisionColorMap[$month->Id_Division])){
                $monthlyDivisionColorMap[$month->Id_Division] = $monthlyPalette[$monthlyColorIndex % $monthlyPaletteCount];
                $monthlyColorIndex++;
            }

            $label = "Mes " . str_pad($month->mes, 2, '0', STR_PAD_LEFT) . " / " . $month->anio . " - " . $month->Division_Nombre;
            $monthlyLabels[] = $label;
            $monthlyData[] = $month->cantidad ?? 0;
            $colorM = $monthlyDivisionColorMap[$month->Id_Division];
            $monthlyColors[] = $colorM;
            $monthlyBorderColors[] = $colorM;
        endforeach;
    endif;
    ?>
    
    const weeklyCtx = document.getElementById('weeklyChart');
    if(weeklyCtx){
        const weeklyChartConfig = {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($weeklyLabels); ?>,
                datasets: [{
                    label: 'Actividades Registradas',
                    data: <?php echo json_encode($weeklyData); ?>,
                        backgroundColor: <?php echo json_encode($weeklyColors); ?>,
                        borderColor: <?php echo json_encode($weeklyBorderColors); ?>,
                        borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        };
        new Chart(weeklyCtx, weeklyChartConfig);
    }

    // Gráfico mensual
    const monthlyCtx = document.getElementById('monthlyChart');
    if(monthlyCtx){
        const monthlyChartConfig = {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($monthlyLabels); ?>,
                datasets: [{
                    label: 'Actividades Registradas',
                    data: <?php echo json_encode($monthlyData); ?>,
                    backgroundColor: <?php echo json_encode($monthlyColors); ?>,
                    borderColor: <?php echo json_encode($monthlyBorderColors); ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        };
        new Chart(monthlyCtx, monthlyChartConfig);
    }

    // Modal de detalle de división (espera a que cargue jQuery del footer)
    (function waitForjQuery(callback){
        if(window.jQuery){
            callback(window.jQuery);
        } else {
            setTimeout(function(){ waitForjQuery(callback); }, 50);
        }
    })(function($){
        $(function(){
            const $modal = $('#divisionDetailModal');
            const $title = $('#divisionDetailTitle');
            const $loading = $('#divisionDetailLoading');
            const $content = $('#divisionDetailContent');
            const $error = $('#divisionDetailError');
            const $empty = $('#divisionDetailEmpty');
            const $tableBody = $('#divisionDetailTableBody');
            const $totalComp = $('#divisionTotalCompletadas');
            const $totalProg = $('#divisionTotalEnProgreso');
            const $totalPend = $('#divisionTotalPendientes');
            const $totalAll = $('#divisionTotalActividades');

            const escapeHtml = (str) => $('<div>').text(str ?? '').html();

            const resetState = () => {
                $loading.removeClass('d-none');
                $content.addClass('d-none');
                $error.addClass('d-none').text('');
                $empty.addClass('d-none');
                $tableBody.empty();
                $totalComp.text('0');
                $totalProg.text('0');
                $totalPend.text('0');
                $totalAll.text('0');
            };

            const renderData = (resp) => {
                const rows = resp?.data || [];
                const totals = resp?.totals || {};

                if(!rows.length){
                    $empty.removeClass('d-none');
                    return;
                }

                const htmlRows = rows.map(item => `
                    <tr>
                        <td>${escapeHtml(item.nombre)}</td>
                        <td class="text-center text-success font-weight-bold">${item.completadas || 0}</td>
                        <td class="text-center text-warning font-weight-bold">${item.en_progreso || 0}</td>
                        <td class="text-center text-danger font-weight-bold">${item.pendientes || 0}</td>
                        <td class="text-center font-weight-bold">${item.total || 0}</td>
                    </tr>
                `).join('');

                $tableBody.html(htmlRows);
                $totalComp.text(totals.completadas || 0);
                $totalProg.text(totals.en_progreso || 0);
                $totalPend.text(totals.pendientes || 0);
                $totalAll.text(totals.total || 0);
            };

            $(document).on('click', '.btn-division-detail', function(e){
                e.preventDefault();

                const divisionId = $(this).data('division-id');
                const divisionName = $(this).data('division-name');

                $title.text(`Detalle de ${divisionName}`);
                resetState();
                $modal.modal('show');

                $.getJSON(`${APP_URL_ROOT}/pages/divisionStats/${divisionId}`)
                    .done(function(resp){
                        $loading.addClass('d-none');

                        if(resp && resp.success){
                            $content.removeClass('d-none');
                            renderData(resp);
                        } else {
                            $error.text(resp?.message || 'No se pudo obtener el detalle.').removeClass('d-none');
                        }
                    })
                    .fail(function(){
                        $loading.addClass('d-none');
                        $error.text('No se pudo obtener el detalle. Intente nuevamente.').removeClass('d-none');
                    });
            });
        });
    });

    // Animación de scroll infinito para el carousel
    document.addEventListener('DOMContentLoaded', function(){
        const carouselContainer = document.querySelector('.carousel-container');
        if(carouselContainer){
            // Clonar las tarjetas para crear un efecto infinito
            const cards = Array.from(carouselContainer.querySelectorAll('.stat-card'));
            cards.forEach(card => {
                const clonedCard = card.cloneNode(true);
                carouselContainer.appendChild(clonedCard);
            });
        }
    });
</script>

<style>
    @keyframes scroll {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-50%);
        }
    }

    .carousel-container {
        white-space: nowrap;
    }

    /* Ocultar scrollbar en todos los navegadores */
    div[style*="overflow-x"] {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE y Edge */
    }

    div[style*="overflow-x"]::-webkit-scrollbar {
        display: none; /* Chrome, Safari y Opera */
    }

    .stat-card {
        display: inline-block;
        white-space: normal;
    }

    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }

    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }

    .border-left-secondary {
        border-left: 4px solid #6c757d !important;
    }

    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }
</style>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
