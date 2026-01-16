<?php require APPROOT . '/views/layouts/header.php'; ?>
<?php 
$canCreate = tienePermiso('alcances.crear');
$canEdit   = tienePermiso('alcances.editar');
$canDelete = tienePermiso('alcances.eliminar');
?>

<div class="row mb-4">
    <div class="col-12 col-md-6 mb-3 mb-md-0">
        <h1><?php echo $data['title']; ?></h1>
    </div>
    <div class="col-12 col-md-6">
        <?php if($canCreate): ?>
            <a href="<?php echo URLROOT; ?>/alcances/add" class="btn btn-success w-100">
                <i class="bi bi-plus"></i> Añadir Alcance
            </a>
        <?php else: ?>
            <button class="btn btn-success w-100" disabled title="Sin permiso para crear">
                <i class="bi bi-plus"></i> Añadir Alcance
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($data['agrupados'])): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay alcances activos registrados para su división.
            </div>
        <?php else: ?>
            <div class="accordion" id="alcancesAccordion">
                <?php foreach($data['agrupados'] as $contratoId => $grupo): ?>
                    <?php $contrato = $grupo['contrato']; ?>
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center" id="heading-<?php echo $contratoId; ?>">
                            <div>
                                <strong>Contrato:</strong> <?php echo $contrato['Expediente'] ?: 'Sin expediente'; ?>
                                <span class="text-muted"> | </span>
                                <small><?php echo substr($contrato['Descripcion'], 0, 60) . (strlen($contrato['Descripcion']) > 60 ? '...' : ''); ?></small>
                            </div>
                            <div class="d-flex align-items-center">
                                <?php if($contrato['Contrato_activo'] == 1): ?>
                                    <span class="badge badge-success mr-2">Contrato Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary mr-2">Contrato Vencido</span>
                                <?php endif; ?>
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse-<?php echo $contratoId; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $contratoId; ?>">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>
                        </div>

                        <div id="collapse-<?php echo $contratoId; ?>" class="collapse" aria-labelledby="heading-<?php echo $contratoId; ?>" data-parent="#alcancesAccordion">
                            <div class="card-body p-0">
                                <?php if (empty($grupo['alcances'])): ?>
                                    <div class="p-3">No hay alcances para este contrato.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table mb-0 table-sm">
                                            <thead>
                                                <tr>
                                                    <th class="hide-on-mobile">ID</th>
                                                    <th>Descripción</th>
                                                    <th class="hide-on-mobile">Creación</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($grupo['alcances'] as $alcance): ?>
                                                    <tr>
                                                        <td class="hide-on-mobile" data-label="ID"><?php echo $alcance->Id_alcance; ?></td>
                                                        <td data-label="Descripción"><?php echo substr($alcance->Descripcion, 0, 80) . (strlen($alcance->Descripcion) > 80 ? '...' : ''); ?></td>
                                                        <td class="hide-on-mobile" data-label="Creación"><?php echo date('d/m/Y', strtotime($alcance->Fecha_creacion)); ?></td>
                                                        <td data-label="Acciones" class="text-nowrap">
                                                            <?php if($canEdit): ?>
                                                                <a href="<?php echo URLROOT; ?>/alcances/edit/<?php echo $alcance->Id_alcance; ?>" class="btn btn-sm btn-info mr-1" title="Editar">
                                                                    <i class="bi bi-pencil-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if($canDelete): ?>
                                                                <?php if($alcance->tiene_actividades): ?>
                                                                    <button type="button" class="btn btn-sm btn-danger disabled" disabled title="No se puede eliminar: tiene actividades">
                                                                        <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">Eliminar</span>
                                                                    </button>
                                                                    <small class="d-block text-danger"><i class="bi bi-info-circle"></i> Tiene actividades</small>
                                                                <?php else: ?>
                                                                    <button 
                                                                        type="button" 
                                                                        class="btn btn-sm btn-danger" 
                                                                        data-toggle="modal" 
                                                                        data-target="#deleteAlcanceModal"
                                                                        data-id="<?php echo $alcance->Id_alcance; ?>" 
                                                                        data-nombre="<?php echo $contrato['Descripcion']; ?>"
                                                                        title="Eliminar"
                                                                    >
                                                                        <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">Eliminar</span>
                                                                    </button>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Paginación -->
            <?php if($data['totalPages'] > 1): ?>
                <nav aria-label="Paginación de contratos" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Botón Anterior -->
                        <li class="page-item <?php echo ($data['currentPage'] <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo URLROOT; ?>/alcances/index/<?php echo $data['currentPage'] - 1; ?>" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <!-- Números de página -->
                        <?php
                        $startPage = max(1, $data['currentPage'] - 2);
                        $endPage = min($data['totalPages'], $data['currentPage'] + 2);
                        
                        if($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo URLROOT; ?>/alcances/index/1">1</a>
                            </li>
                            <?php if($startPage > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?php echo ($i == $data['currentPage']) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo URLROOT; ?>/alcances/index/<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if($endPage < $data['totalPages']): ?>
                            <?php if($endPage < $data['totalPages'] - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo URLROOT; ?>/alcances/index/<?php echo $data['totalPages']; ?>"><?php echo $data['totalPages']; ?></a>
                            </li>
                        <?php endif; ?>

                        <!-- Botón Siguiente -->
                        <li class="page-item <?php echo ($data['currentPage'] >= $data['totalPages']) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo URLROOT; ?>/alcances/index/<?php echo $data['currentPage'] + 1; ?>" aria-label="Siguiente">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                    <p class="text-center text-muted">
                        Mostrando contratos <?php echo $offset = ($data['currentPage'] - 1) * 10 + 1; ?> 
                        - <?php echo min($offset + 9, $data['totalContratos']); ?> 
                        de <?php echo $data['totalContratos']; ?>
                    </p>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteAlcanceModal" tabindex="-1" aria-labelledby="deleteAlcanceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteAlcanceModalLabel"><i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="deleteAlcanceForm" action="" method="post">
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar lógicamente el siguiente alcance del contrato:</p>
            <p><strong><span id="alcanceNamePlaceholder"></span></strong> (ID: <span id="alcanceIdPlaceholder"></span>)?</p>
            <p class="text-danger"><small>Esta acción establecerá el estado a 0 y el alcance dejará de ser visible.</small></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-danger">Sí, Eliminar</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php require APPROOT . '/views/layouts/footer.php'; ?>