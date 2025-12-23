<?php require APPROOT . '/views/layouts/header.php'; ?>

<div class="row mb-4">
    <div class="col-12 col-md-6 mb-3 mb-md-0">
        <h1><?php echo $data['title']; ?></h1>
    </div>
    <div class="col-12 col-md-6">
        <a href="<?php echo URLROOT; ?>/alcances/add" class="btn btn-success w-100">
            <i class="bi bi-plus"></i> Añadir Alcance
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($data['alcances'])): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay alcances activos registrados.
            </div>
        <?php else: ?>
            <div class="table-responsive-wrapper">
                <table class="table table-striped table-hover mt-3">
                    <thead class="thead-dark">
                        <tr>
                            <th class="hide-on-mobile">ID</th>
                            <th>Contrato</th>
                            <th class="hide-on-mobile">Descripción</th>
                            <th class="hide-on-mobile">Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['alcances'] as $alcance): ?>
                            <tr>
                                <td class="hide-on-mobile" data-label="ID"><?php echo $alcance->Id_alcance; ?></td>
                                <td data-label="Contrato"><?php echo $alcance->Expediente; ?></td>
                                <td class="hide-on-mobile" data-label="Descripción"><?php echo substr($alcance->Descripcion, 0, 50) . (strlen($alcance->Descripcion) > 50 ? '...' : ''); ?></td>
                                <td class="hide-on-mobile" data-label="Creación"><?php echo date('d/m/Y', strtotime($alcance->Fecha_creacion)); ?></td>
                                <td data-label="Acciones" class="text-nowrap">
                                    <a href="<?php echo URLROOT; ?>/alcances/edit/<?php echo $alcance->Id_alcance; ?>" class="btn btn-sm btn-info mr-1" title="Editar">
                                        <i class="bi bi-pencil-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger" 
                                        data-toggle="modal" 
                                        data-target="#deleteAlcanceModal"
                                        data-id="<?php echo $alcance->Id_alcance; ?>" 
                                        data-nombre="<?php echo $alcance->Contrato_Descripcion; ?>"
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