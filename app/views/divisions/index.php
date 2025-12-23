<?php require APPROOT . '/views/layouts/header.php'; ?>

<div class="row mb-4">
    <div class="col-12 col-md-6 mb-3 mb-md-0">
        <h1><?php echo $data['title']; ?></h1>
    </div>
    <div class="col-12 col-md-6">
        <a href="<?php echo URLROOT; ?>/divisions/add" class="btn btn-success w-100">
            <i class="bi bi-plus"></i> Añadir División
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php if (empty($data['divisions'])): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay divisiones registradas o activas.
            </div>
        <?php else: ?>
            <div class="table-responsive-wrapper">
                <table class="table table-striped mt-3">
                    <thead class="thead-dark">
                        <tr>
                            <th>No.</th>
                            <th>Nombre</th>
                            <th class="hide-on-mobile">Siglas</th>
                            <th class="hide-on-mobile">Jefe</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['divisions'] as $division): ?>
                            <tr>
                                <td data-label="No."><?php echo $division->Id_Division; ?></td>
                                <td data-label="Nombre"><?php echo $division->Nombre; ?></td>
                                <td class="hide-on-mobile" data-label="Siglas"><?php echo $division->Siglas; ?></td>
                                <td class="hide-on-mobile" data-label="Jefe"><?php echo $division->jefe_nombre ?? 'N/A'; ?></td> 
                                <td data-label="Acciones" class="text-nowrap">
                                    <a href="<?php echo URLROOT; ?>/divisions/edit/<?php echo $division->Id_Division; ?>" class="btn btn-sm btn-info mr-1" title="Editar">
                                        <i class="bi bi-pencil-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger" 
                                        data-toggle="modal" 
                                        data-target="#deleteDivisionModal"
                                        data-id="<?php echo $division->Id_Division; ?>" 
                                        data-nombre="<?php echo $division->Nombre; ?>"
                                        title="Eliminar"
                                    >
                                        <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">Eliminar</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteDivisionModal" tabindex="-1" aria-labelledby="deleteDivisionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteDivisionModalLabel"><i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="deleteForm" action="" method="post">
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar lógicamente la división:</p>
            <p><strong><span id="divisionNamePlaceholder"></span></strong> (ID: <span id="divisionIdPlaceholder"></span>)?</p>
            <p class="text-danger"><small>Esta acción establecerá el estado a 0 y la división dejará de ser visible en el listado.</small></p>
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

<script>
    $('#deleteDivisionModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) // Botón que activó el modal
        var divisionId = button.data('id') // Extrae la información 'data-id'
        var divisionName = button.data('nombre') // Extrae la información 'data-nombre'

        var modal = $(this)
        
        // 1. Actualizar el placeholder con el nombre y ID de la división
        modal.find('#divisionNamePlaceholder').text(divisionName)
        modal.find('#divisionIdPlaceholder').text(divisionId)

        // 2. Establecer la acción del formulario al controlador/método correcto
        // URLROOT debe estar disponible como variable global o definida aquí si no lo está.
        // Asumiendo que URLROOT es una constante PHP y la has definido en tu archivo de configuración:
        var urlRoot = "<?php echo URLROOT; ?>"; 
        var formAction = urlRoot + '/divisions/delete/' + divisionId;
        
        modal.find('#deleteForm').attr('action', formAction);
    })
</script>

