<?php require APPROOT . '/views/layouts/header.php'; ?>

<div class="row mb-4">
    <div class="col-12 col-md-7 mb-3 mb-md-0">
        <h1><?php echo $data['title']; ?></h1>
    </div>
    <div class="col-12 col-md-5 d-flex flex-column flex-md-row gap-2">
        <a href="<?php echo URLROOT; ?>/actividades/add" class="btn btn-success flex-grow-1">
            <i class="bi bi-plus"></i> <span class="d-none d-sm-inline">Añadir</span> Actividad
        </a>
        <button type="button" class="btn btn-primary flex-grow-1" data-toggle="modal" data-target="#reporteActividadesModal">
            <i class="bi bi-file-pdf"></i> <span class="d-none d-sm-inline">Generar</span> PDF
        </button>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <form action="<?php echo URLROOT; ?>/actividades/index" method="get" class="form-inline w-100 flex-column flex-md-row">
            <div class="input-group w-100 mb-2 mb-md-0 flex-grow-1 mr-md-2">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Buscar actividades..." 
                    value="<?php echo htmlspecialchars($data['pagination']['search_term']); ?>"
                >
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    <?php if (!empty($data['pagination']['search_term'])): ?>
                        <a href="<?php echo URLROOT; ?>/actividades/index" class="btn btn-outline-danger" title="Limpiar">
                            <i class="bi bi-x"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($data['actividades'])): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay actividades activas registradas.
            </div>
        <?php else: ?>
            <div class="table-responsive-wrapper">
                <table class="table table-striped table-hover mt-3">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Personal</th>
                            <th class="hide-on-mobile">Alcance</th>
                            <th class="hide-on-mobile">Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['actividades'] as $actividad): ?>
                            <tr>
                                <td data-label="Fecha"><?php echo date('d/m/Y', strtotime($actividad->Fecha_ingreso)); ?></td>
                                <td data-label="Personal"><?php echo $actividad->Personal_Nombre . ' ' . $actividad->Personal_Apellido; ?></td>
                                <td class="hide-on-mobile" data-label="Alcance"><?php echo substr($actividad->Alcance_Descripcion, 0, 40) . '...'; ?></td>
                                <td class="hide-on-mobile" data-label="Descripción"><?php echo substr($actividad->Descripcion_realizada, 0, 50) . '...'; ?></td>
                                <td data-label="Estado">
                                    <?php 
                                        $badge_class = '';
                                        switch ($actividad->Estado_actividad) {
                                            case 'Completada':
                                                $badge_class = 'badge-success';
                                                break;
                                            case 'En Progreso':
                                                $badge_class = 'badge-warning';
                                                break;
                                            case 'Cancelada':
                                                $badge_class = 'badge-danger';
                                                break;
                                            default:
                                                $badge_class = 'badge-secondary';
                                                break;
                                        }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $actividad->Estado_actividad; ?></span>
                                </td>
                                <td data-label="Acciones" class="text-nowrap">
                                    <a href="<?php echo URLROOT; ?>/actividades/edit/<?php echo $actividad->Id_actividad; ?>" class="btn btn-sm btn-info" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger" 
                                        data-toggle="modal" 
                                        data-target="#deleteActividadModal"
                                        data-id="<?php echo $actividad->Id_actividad; ?>" 
                                        data-descripcion="<?php echo $actividad->Alcance_Descripcion; ?>"
                                        title="Eliminar"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                <?php endforeach; ?>
            </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($data['pagination']['total_pages'] > 1): ?>
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Paginación">
                <ul class="pagination justify-content-center flex-wrap">
                    
                    <?php 
                        $totalPages = $data['pagination']['total_pages'];
                        $currentPage = $data['pagination']['current_page'];
                        // Prepara la URL base para incluir el término de búsqueda
                        $searchTerm = $data['pagination']['search_term'];
                        $baseUrl = URLROOT . '/actividades/index?search=' . urlencode($searchTerm) . '&page=';
                    ?>

                    <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $baseUrl . ($currentPage - 1); ?>" aria-label="Anterior">
                            <span aria-hidden="true">&laquo; Anterior</span>
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $baseUrl . $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $baseUrl . ($currentPage + 1); ?>" aria-label="Siguiente">
                            <span aria-hidden="true">Siguiente &raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted">
                Mostrando página <?php echo $currentPage; ?> de <?php echo $totalPages; ?>. Total de registros: <?php echo $data['pagination']['total_records']; ?>.
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- moda para crear multiples tareas -->
<div class="modal fade" id="deleteActividadModal" tabindex="-1" aria-labelledby="deleteActividadModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteActividadModalLabel"><i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="deleteActividadForm" action="" method="post">
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar lógicamente la actividad:</p>
            <p>Relacionada con el Alcance: <strong><span id="alcanceDescPlaceholder"></span></strong> (ID: <span id="actividadIdPlaceholder"></span>)?</p>
            <p class="text-danger"><small>Esta acción establecerá el estado a 0 y la actividad dejará de ser visible.</small></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Sí, Eliminar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="reporteActividadesModal" tabindex="-1" aria-labelledby="reporteActividadesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="reporteActividadesModalLabel">Generar Reporte de Actividades</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form id="reporteActividadesForm" action="<?php echo URLROOT; ?>/actividades/generar_reporte_pdf" method="post" target="_blank">
                <div class="modal-body">
                    <p>Seleccione el rango de fechas, mes, año y número de pago para el reporte de actividades **Completadas**.</p>
                    
                    <div class="form-group">
                        <label for="numero_pago">Número de Pago:</label>
                        <select class="form-control" id="numero_pago" name="numero_pago" required>
                            <option value="">-- Seleccione un pago --</option>
                        </select>
                        <small class="form-text text-muted">Se cargarán automáticamente los pagos disponibles del contrato.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mes_reporte">Mes del Reporte:</label>
                                <select class="form-control" id="mes_reporte" name="mes_reporte" required>
                                    <option value="">-- Seleccione un mes --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="anio_reporte">Año del Reporte:</label>
                                <select class="form-control" id="anio_reporte" name="anio_reporte" required>
                                    <option value="">-- Seleccione un año --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio:</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha_fin">Fecha de Fin:</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGenerarReporte">
                        <i class="bi bi-download"></i> Generar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/layouts/footer.php'; ?>