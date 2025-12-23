<?php require APPROOT . '/views/layouts/header.php'; ?>

<div class="row mb-4">
    <div class="col-12 col-md-6 mb-3 mb-md-0">
        <h1><?php echo $data['title']; ?></h1>
    </div>
    <div class="col-12 col-md-6">
        <a href="<?php echo URLROOT; ?>/contratos/add" class="btn btn-success w-100">
            <i class="bi bi-plus"></i> Añadir Contrato
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($data['contratos'])): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay contratos activos registrados.
            </div>
        <?php else: ?>
            <div class="table-responsive-wrapper">
                <table class="table table-striped mt-3">
                    <thead class="thead-dark">
                        <tr>
                            <th class="hide-on-mobile">ID</th>
                            <th>Descripción</th>
                            <th class="hide-on-mobile">Pagos</th>
                            <th class="hide-on-mobile">Expediente</th>
                            <th class="hide-on-mobile">Inicio</th>
                            <th class="hide-on-mobile">Fin</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['contratos'] as $contrato): ?>
                            <tr>
                                <td class="hide-on-mobile" data-label="ID"><?php echo $contrato->Id_contrato; ?></td>
                                <td data-label="Descripción"><?php echo substr($contrato->Descripcion, 0, 40) . '...'; ?></td>
                                <td class="hide-on-mobile" data-label="Pagos"><?php echo $contrato->Numero_pagos; ?></td>
                                <td class="hide-on-mobile" data-label="Expediente"><?php echo $contrato->Expediente ?? 'N/A'; ?></td>
                                <td class="hide-on-mobile" data-label="Inicio"><?php echo date('d/m/Y', strtotime($contrato->Inicio_contrato)); ?></td>
                                <td class="hide-on-mobile" data-label="Fin"><?php echo date('d/m/Y', strtotime($contrato->Fin_contrato)); ?></td>
                                <td data-label="Acciones" class="text-nowrap">
                                    <a href="<?php echo URLROOT; ?>/contratos/edit/<?php echo $contrato->Id_contrato; ?>" class="btn btn-sm btn-info mr-1" title="Editar">
                                        <i class="bi bi-pencil-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger" 
                                        data-toggle="modal" 
                                        data-target="#deleteContratoModal"
                                        data-id="<?php echo $contrato->Id_contrato; ?>" 
                                        data-nombre="<?php echo substr($contrato->Descripcion, 0, 30); ?>"
                                        title="Eliminar"
                                    >
                                        <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">Eliminar</span>
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

<div class="modal fade" id="deleteContratoModal" tabindex="-1" aria-labelledby="deleteContratoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteContratoModalLabel"><i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="deleteContratoForm" action="" method="post">
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar lógicamente el contrato:</p>
            <p><strong><span id="contratoNamePlaceholder"></span></strong> (ID: <span id="contratoIdPlaceholder"></span>)?</p>
            <p class="text-danger"><small>Esta acción establecerá el estado a 0 y el contrato dejará de ser visible.</small></p>
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