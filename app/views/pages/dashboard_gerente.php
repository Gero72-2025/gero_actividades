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

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-left-info">
                <div class="card-body">
                    <h5 class="card-title text-info"><i class="bi bi-people"></i> Personal Total</h5>
                    <h2 class="text-info"><?php echo $personalTotal; ?></h2>
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
                                                <a href="<?php echo URLROOT; ?>/divisions/index" class="btn btn-sm btn-primary" title="Ver Detalles">
                                                    <i class="bi bi-eye"></i>
                                                </a>
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
                </div>
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
    if(isset($data['stats_by_week']) && is_array($data['stats_by_week'])):
        foreach($data['stats_by_week'] as $week):
            $label = "Semana " . $week->semana . " - " . $week->Division_Nombre;
            $weeklyLabels[] = $label;
            $weeklyData[] = $week->cantidad ?? 0;
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
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
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
</script>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
