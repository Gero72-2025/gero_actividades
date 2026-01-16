<?php require APPROOT . '/views/layouts/header.php'; ?>
<?php 
$canCreate = tienePermiso('usuarios.crear');
$canEdit   = tienePermiso('usuarios.editar');
$canDelete = tienePermiso('usuarios.eliminar');
?>

<div class="row mb-4">
    <div class="col-12 col-md-6 mb-3 mb-md-0">
        <h1><?php echo $data['title']; ?></h1>
    </div>
    <div class="col-12 col-md-6">
        <?php if($canCreate): ?>
            <a href="<?php echo URLROOT; ?>/usuarios/add" class="btn btn-success w-100">
                <i class="bi bi-plus"></i> Añadir Usuario
            </a>
        <?php else: ?>
            <button class="btn btn-success w-100" disabled title="Sin permiso para crear">
                <i class="bi bi-plus"></i> Añadir Usuario
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($data['usuarios'])): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay usuarios activos registrados.
            </div>
        <?php else: ?>
            <div class="table-responsive-wrapper">
                <table class="table table-striped mt-3">
                    <thead class="thead-dark">
                        <tr>
                            <th class="hide-on-mobile">ID</th>
                            <th>Email</th>
                            <th class="hide-on-mobile">Conectado</th>
                            <th class="hide-on-mobile">Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['usuarios'] as $usuario): ?>
                            <tr>
                                <td class="hide-on-mobile" data-label="ID"><?php echo $usuario->Id_usuario; ?></td>
                                <td data-label="Email"><?php echo $usuario->email; ?></td>
                                <td class="hide-on-mobile" data-label="Conectado">
                                    <?php if ($usuario->conectado): ?>
                                        <span class="badge badge-success">Sí</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="hide-on-mobile" data-label="Creación"><?php echo date('d/m/Y', strtotime($usuario->Fecha_creacion)); ?></td>
                                <td data-label="Acciones" class="text-nowrap">
                                    <?php if($canEdit): ?>
                                        <a href="<?php echo URLROOT; ?>/usuarios/edit/<?php echo $usuario->Id_usuario; ?>" class="btn btn-sm btn-info mr-1" title="Editar">
                                            <i class="bi bi-pencil-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if($canDelete): ?>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-danger" 
                                            data-toggle="modal" 
                                            data-target="#deleteUsuarioModal"
                                            data-id="<?php echo $usuario->Id_usuario; ?>" 
                                            data-nombre="<?php echo $usuario->email; ?>"
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

<div class="modal fade" id="deleteUsuarioModal" tabindex="-1" aria-labelledby="deleteUsuarioModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteUsuarioModalLabel"><i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="deleteUsuarioForm" action="" method="post">
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar lógicamente el usuario:</p>
            <p><strong><span id="usuarioNamePlaceholder"></span></strong> (ID: <span id="usuarioIdPlaceholder"></span>)?</p>
            <p class="text-danger"><small>Esta acción establecerá el estado a 0 y el usuario dejará de ser visible.</small></p>
            <p class="text-warning"><small>Si este usuario está asignado a un registro de Personal, ese registro se quedará sin usuario vinculado.</small></p>
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