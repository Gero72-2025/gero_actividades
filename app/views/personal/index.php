<?php require APPROOT . '/views/layouts/header.php'; ?>
<?php 
$canCreate = tienePermiso('personal.crear');
$canEdit   = tienePermiso('personal.editar');
$canDelete = tienePermiso('personal.eliminar');
?>

<div class="row mb-4">
    <div class="col-12 col-md-6 mb-3 mb-md-0">
        <h1><?php echo $data['title']; ?></h1>
    </div>
    <div class="col-12 col-md-6">
        <?php if($canCreate): ?>
            <a href="<?php echo URLROOT; ?>/personal/add" class="btn btn-success w-100">
                <i class="bi bi-plus"></i> Añadir Personal
            </a>
        <?php else: ?>
            <button class="btn btn-success w-100" disabled title="Sin permiso para crear">
                <i class="bi bi-plus"></i> Añadir Personal
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($data['personal'])): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay personal registrado o activo.
            </div>
        <?php else: ?>
            <div class="table-responsive-wrapper">
                <table class="table table-striped mt-3">
                    <thead class="thead-dark">
                        <tr>
                            <th class="hide-on-mobile">ID</th>
                            <th>Nombre</th>
                            <th class="hide-on-mobile">Puesto</th>
                            <th class="hide-on-mobile">Tipo Servicio</th>
                            <th class="hide-on-mobile">División</th>
                            <th class="hide-on-mobile">Contrato</th>
                            <th class="hide-on-mobile">Email</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['personal'] as $p): ?>
                            <tr>
                                <td class="hide-on-mobile" data-label="ID"><?php echo $p->Id_personal; ?></td>
                                <td data-label="Nombre"><?php echo $p->Nombre_Completo . ' ' . $p->Apellido_Completo; ?></td>
                                <td class="hide-on-mobile" data-label="Puesto"><?php echo $p->Puesto; ?></td>
                                <td class="hide-on-mobile" data-label="Tipo Servicio">
                                    <?php if($p->Tipo_servicio == 1): ?>
                                        <span class="badge badge-primary">Profesionales</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Técnicos</span>
                                    <?php endif; ?>
                                </td>
                                <td class="hide-on-mobile" data-label="División"><?php echo $p->division_nombre ?? 'N/A'; ?></td>
                                <td class="hide-on-mobile" data-label="Contrato">
                                    <?php if($p->contrato_expediente): ?>
                                        <span class="badge <?php echo ($p->Contrato_activo == 1) ? 'badge-success' : 'badge-secondary'; ?>">
                                            <?php echo $p->contrato_expediente; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="hide-on-mobile" data-label="Email"><?php echo $p->usuario_email ?? 'N/A'; ?></td> 
                                <td data-label="Acciones" class="text-nowrap">
                                    <?php if($canEdit): ?>
                                        <a href="<?php echo URLROOT; ?>/personal/edit/<?php echo $p->Id_personal; ?>" class="btn btn-sm btn-info mr-1" title="Editar">
                                            <i class="bi bi-pencil-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if($canDelete): ?>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-danger" 
                                            data-toggle="modal" 
                                            data-target="#deletePersonalModal"
                                            data-id="<?php echo $p->Id_personal; ?>" 
                                            data-nombre="<?php echo $p->Nombre_Completo . ' ' . $p->Apellido_Completo; ?>"
                                            title="Eliminar"
                                        >
                                            <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">Eliminar</span>
                                        </button>
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

<div class="modal fade" id="deletePersonalModal" tabindex="-1" aria-labelledby="deletePersonalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deletePersonalModalLabel"><i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="deletePersonalForm" action="" method="post">
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar lógicamente el registro de personal:</p>
            <p><strong><span id="personalNamePlaceholder"></span></strong> (ID: <span id="personalIdPlaceholder"></span>)?</p>
            <p class="text-danger"><small>Esta acción establecerá el estado a 0 y el registro dejará de ser visible.</small></p>
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