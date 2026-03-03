<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Título y Bienvenida -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h1 class="mb-0"><i class="bi bi-graph-up"></i> Dashboard - División: <?php echo $data['division']->Nombre; ?></h1>
                    <p class="mb-0 mt-2">Gestione y analice el desempeño de su equipo</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen General de Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary"><i class="bi bi-list-check"></i> Total de Tareas</h5>
                    <h2 class="text-primary"><?php echo $data['stats']->total ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="bi bi-check-circle"></i> Completadas</h5>
                    <h2 class="text-success"><?php echo $data['stats']->completadas ?? 0; ?></h2>
                    <small class="text-muted"><?php echo $data['stats']->total > 0 ? round(($data['stats']->completadas / $data['stats']->total) * 100, 1) : 0; ?>%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning"><i class="bi bi-hourglass"></i> En Progreso</h5>
                    <h2 class="text-warning"><?php echo $data['stats']->en_progreso ?? 0; ?></h2>
                    <small class="text-muted"><?php echo $data['stats']->total > 0 ? round(($data['stats']->en_progreso / $data['stats']->total) * 100, 1) : 0; ?>%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger"><i class="bi bi-exclamation-circle"></i> Pendientes</h5>
                    <h2 class="text-danger"><?php echo $data['stats']->pendientes ?? 0; ?></h2>
                    <small class="text-muted"><?php echo $data['stats']->total > 0 ? round(($data['stats']->pendientes / $data['stats']->total) * 100, 1) : 0; ?>%</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-left-info">
                <div class="card-body">
                    <h5 class="card-title text-info"><i class="bi bi-people"></i> Personal en División</h5>
                    <h2 class="text-info"><?php echo $data['stats']->cantidad_personal ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Gráficos y Tablas -->
    <div class="row">
        <!-- Gráfico de Actividades por Semana -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Actividades por Semana</h5>
                </div>
                <div class="card-body">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Estado General de Actividades -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Distribución de Estados</h5>
                </div>
                <div class="card-body">
                    <canvas id="statesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Personal con Estadísticas -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Estadísticas por Personal</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Personal</th>
                                    <th>Completadas</th>
                                    <th>En Progreso</th>
                                    <th>Pendientes</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if(isset($data['personal_list']) && is_array($data['personal_list'])):
                                    $personalStats = [];
                                    
                                    // Primero, inicializar todos los usuarios del personal_list con 0 en todo
                                    foreach($data['personal_list'] as $personal):
                                        $key = $personal->Id_personal;
                                        $personalStats[$key] = [
                                            'nombre' => $personal->Nombre_Completo . ' ' . $personal->Apellido_Completo,
                                            'completadas' => 0,
                                            'en_progreso' => 0,
                                            'pendientes' => 0
                                        ];
                                    endforeach;
                                    
                                    // Luego, agregar las estadísticas de actividades (si existen)
                                    if(isset($data['stats_by_personal']) && is_array($data['stats_by_personal'])):
                                        foreach($data['stats_by_personal'] as $stat):
                                            $key = $stat->Id_personal;
                                            if(isset($personalStats[$key])):
                                                if($stat->Estado_actividad === 'Completada'):
                                                    $personalStats[$key]['completadas'] = $stat->cantidad;
                                                elseif($stat->Estado_actividad === 'En Progreso'):
                                                    $personalStats[$key]['en_progreso'] = $stat->cantidad;
                                                elseif($stat->Estado_actividad === 'Pendiente'):
                                                    $personalStats[$key]['pendientes'] = $stat->cantidad;
                                                endif;
                                            endif;
                                        endforeach;
                                    endif;
                                    
                                    // Mostrar tabla con todos los usuarios
                                    foreach($personalStats as $stats):
                                        $total = $stats['completadas'] + $stats['en_progreso'] + $stats['pendientes'];
                                        ?>
                                        <tr>
                                            <td><strong><?php echo $stats['nombre']; ?></strong></td>
                                            <td><span class="badge badge-success"><?php echo $stats['completadas']; ?></span></td>
                                            <td><span class="badge badge-warning"><?php echo $stats['en_progreso']; ?></span></td>
                                            <td><span class="badge badge-danger"><?php echo $stats['pendientes']; ?></span></td>
                                            <td><strong><?php echo $total; ?></strong></td>
                                        </tr>
                                        <?php
                                    endforeach;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay personal asignado a esta división</td>
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
</div>

<!-- Scripts para gráficos - Chart.js Local -->
<script src="<?php echo URLROOT; ?>/public/lib/chartjs/chart.min.js"></script>
<script>
    // Datos para gráfico semanal
    const weeklyData = <?php echo json_encode($data['stats_by_week']); ?>;
    const statsData = <?php echo json_encode($data['stats']); ?>;
    
    // Preparar datos para gráfico de semanas
    const weeks = {};
    weeklyData.forEach(item => {
        const weekKey = 'Semana ' + item.week;
        if (!weeks[weekKey]) {
            weeks[weekKey] = { completadas: 0, en_progreso: 0, pendientes: 0 };
        }
        if (item.Estado_actividad === 'Completada') {
            weeks[weekKey].completadas = item.cantidad;
        } else if (item.Estado_actividad === 'En Progreso') {
            weeks[weekKey].en_progreso = item.cantidad;
        } else if (item.Estado_actividad === 'Pendiente') {
            weeks[weekKey].pendientes = item.cantidad;
        }
    });

    // Gráfico de barras - Actividades por semana
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(weeklyCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(weeks),
            datasets: [
                {
                    label: 'Completadas',
                    data: Object.values(weeks).map(w => w.completadas),
                    backgroundColor: '#28a745'
                },
                {
                    label: 'En Progreso',
                    data: Object.values(weeks).map(w => w.en_progreso),
                    backgroundColor: '#ffc107'
                },
                {
                    label: 'Pendientes',
                    data: Object.values(weeks).map(w => w.pendientes),
                    backgroundColor: '#dc3545'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Gráfico de pastel - Distribución de estados
    const statesCtx = document.getElementById('statesChart').getContext('2d');
    new Chart(statesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completadas', 'En Progreso', 'Pendientes'],
            datasets: [{
                data: [
                    statsData.completadas || 0,
                    statsData.en_progreso || 0,
                    statsData.pendientes || 0
                ],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderColor: ['#fff', '#fff', '#fff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
</script>

<style>
    .border-left-primary {
        border-left: 4px solid #007bff !important;
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
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }
</style>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
