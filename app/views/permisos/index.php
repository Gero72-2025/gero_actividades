<?php require APPROOT . '/views/layouts/header.php'; ?>
<?php 
$canCreate = tienePermiso('permisos.crear');
$canEdit   = tienePermiso('permisos.editar');
$canDelete = tienePermiso('permisos.eliminar');
?>

<?php 
    // Mostrar mensaje flash si existe
    $flashMsg = getFlashMessage('permiso_message');
    if($flashMsg): 
?>
    <div class="alert alert-<?php echo $flashMsg['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($flashMsg['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-12 col-md-6 mb-3 mb-md-0">
        <h1><?php echo $data['title']; ?></h1>
    </div>
    <div class="col-12 col-md-6">
        <?php if($canCreate): ?>
            <a href="<?php echo URLROOT; ?>/permisos/add" class="btn btn-success w-100">
                <i class="bi bi-plus"></i> Crear Nuevo Permiso
            </a>
        <?php else: ?>
            <button class="btn btn-success w-100" disabled title="Sin permiso para crear">
                <i class="bi bi-plus"></i> Crear Nuevo Permiso
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <form action="<?php echo URLROOT; ?>/permisos/index" method="get" class="form-inline w-100 flex-column flex-md-row">
            <div class="input-group w-100 mb-2 mb-md-0 flex-grow-1 mr-md-2">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Buscar permisos..." 
                    value="<?php echo htmlspecialchars($data['pagination']['search_term']); ?>"
                >
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    <?php if (!empty($data['pagination']['search_term'])): ?>
                        <a href="<?php echo URLROOT; ?>/permisos/index" class="btn btn-outline-danger" title="Limpiar">
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
        <?php if (empty($data['permisos'])): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay permisos registrados o activos.
            </div>
        <?php else: ?>
            <div class="table-responsive-wrapper">
                <table class="table table-striped table-hover mt-3">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th class="hide-on-mobile">Módulo</th>
                            <th class="hide-on-mobile">Acción</th>
                            <th class="hide-on-mobile">Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['permisos'] as $permiso): ?>
                            <tr>
                                <td data-label="ID"><?php echo $permiso->Id_permiso; ?></td>
                                <td data-label="Nombre"><?php echo $permiso->Nombre; ?></td>
                                <td class="hide-on-mobile" data-label="Módulo">
                                    <span class="badge badge-info"><?php echo $permiso->Modulo; ?></span>
                                </td>
                                <td class="hide-on-mobile" data-label="Acción">
                                    <span class="badge badge-secondary"><?php echo $permiso->Accion; ?></span>
                                </td>
                                <td class="hide-on-mobile" data-label="Descripción"><?php echo substr($permiso->Descripcion, 0, 40) . '...'; ?></td>
                                <td data-label="Acciones" class="text-nowrap">
                                    <?php if($canEdit): ?>
                                        <a href="<?php echo URLROOT; ?>/permisos/edit/<?php echo $permiso->Id_permiso; ?>" class="btn btn-sm btn-info mr-1" title="Editar">
                                            <i class="bi bi-pencil-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if($canDelete): ?>
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-danger" 
                                            data-toggle="modal" 
                                            data-target="#deletePermisoModal"
                                            data-id="<?php echo $permiso->Id_permiso; ?>" 
                                            data-nombre="<?php echo $permiso->Nombre; ?>"
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

<?php if ($data['pagination']['total_pages'] > 1): ?>
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Paginación">
                <ul class="pagination justify-content-center flex-wrap">
                    
                    <?php 
                        $totalPages = $data['pagination']['total_pages'];
                        $currentPage = $data['pagination']['current_page'];
                        $searchTerm = $data['pagination']['search_term'];
                        $baseUrl = URLROOT . '/permisos/index?search=' . urlencode($searchTerm) . '&page=';
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

<!-- Modal para eliminar permiso -->
<div class="modal fade" id="deletePermisoModal" tabindex="-1" aria-labelledby="deletePermisoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deletePermisoModalLabel"><i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="deletePermisoForm" action="" method="post">
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar lógicamente el permiso:</p>
            <p><strong><span id="permisoNamePlaceholder"></span></strong> (ID: <span id="permisoIdPlaceholder"></span>)?</p>
            <p class="text-danger"><small>Esta acción establecerá el estado a 0 y el permiso dejará de ser visible.</small></p>
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
    $('#deletePermisoModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var permisoId = button.data('id');
        var permisoName = button.data('nombre');

        var modal = $(this);
        modal.find('#permisoNamePlaceholder').text(permisoName);
        modal.find('#permisoIdPlaceholder').text(permisoId);

        var urlRoot = "<?php echo URLROOT; ?>";
        $('#deletePermisoForm').attr('action', urlRoot + '/permisos/delete');
        $('#deletePermisoForm').append('<input type="hidden" name="id" value="' + permisoId + '">');
    });

    // Manejar eliminación
    $('#deletePermisoForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: {
                id: $('#deletePermisoForm').find('input[name="id"]').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#deletePermisoModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al procesar la solicitud');
            }
        });
    });
</script>
