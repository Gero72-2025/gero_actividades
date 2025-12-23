<?php require APPROOT . '/views/layouts/header.php'; ?>

<?php 
    // Mostrar mensaje flash si existe
    $flashMsg = getFlashMessage('role_message');
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
        <a href="<?php echo URLROOT; ?>/roles/add" class="btn btn-success w-100">
            <i class="bi bi-plus"></i> Crear Nuevo Role
        </a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <form action="<?php echo URLROOT; ?>/roles/index" method="get" class="form-inline w-100 flex-column flex-md-row">
            <div class="input-group w-100 mb-2 mb-md-0 flex-grow-1 mr-md-2">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Buscar roles..." 
                    value="<?php echo htmlspecialchars($data['pagination']['search_term']); ?>"
                >
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    <?php if (!empty($data['pagination']['search_term'])): ?>
                        <a href="<?php echo URLROOT; ?>/roles/index" class="btn btn-outline-danger" title="Limpiar">
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
        <?php if (empty($data['roles'])): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No hay roles registrados o activos.
            </div>
        <?php else: ?>
            <div class="table-responsive-wrapper">
                <table class="table table-striped table-hover mt-3">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th class="hide-on-mobile">Descripción</th>
                            <th class="hide-on-mobile">Permisos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['roles'] as $role): ?>
                            <tr>
                                <td data-label="ID"><?php echo $role->Id_role; ?></td>
                                <td data-label="Nombre"><?php echo $role->Nombre; ?></td>
                                <td class="hide-on-mobile" data-label="Descripción"><?php echo substr($role->Descripcion, 0, 60) . '...'; ?></td>
                                <td class="hide-on-mobile" data-label="Permisos">
                                    <span class="badge badge-primary"><?php echo $role->cantidad_permisos; ?></span>
                                </td>
                                <td data-label="Acciones" class="text-nowrap">
                                    <a href="<?php echo URLROOT; ?>/roles/permisos/<?php echo $role->Id_role; ?>" class="btn btn-sm btn-warning mr-1" title="Permisos">
                                        <i class="bi bi-lock"></i> <span class="d-none d-sm-inline">Permisos</span>
                                    </a>
                                    <a href="<?php echo URLROOT; ?>/roles/edit/<?php echo $role->Id_role; ?>" class="btn btn-sm btn-info mr-1" title="Editar">
                                        <i class="bi bi-pencil-square"></i> <span class="d-none d-sm-inline">Editar</span>
                                    </a>
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-danger" 
                                        data-toggle="modal" 
                                        data-target="#deleteRoleModal"
                                        data-id="<?php echo $role->Id_role; ?>" 
                                        data-nombre="<?php echo $role->Nombre; ?>"
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

<?php if ($data['pagination']['total_pages'] > 1): ?>
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Paginación">
                <ul class="pagination justify-content-center flex-wrap">
                    
                    <?php 
                        $totalPages = $data['pagination']['total_pages'];
                        $currentPage = $data['pagination']['current_page'];
                        $searchTerm = $data['pagination']['search_term'];
                        $baseUrl = URLROOT . '/roles/index?search=' . urlencode($searchTerm) . '&page=';
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

<!-- Modal para eliminar role -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteRoleModalLabel"><i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="deleteRoleForm" action="" method="post">
          <div class="modal-body">
            <p>¿Está seguro de que desea eliminar lógicamente el role:</p>
            <p><strong><span id="roleNamePlaceholder"></span></strong> (ID: <span id="roleIdPlaceholder"></span>)?</p>
            <p class="text-danger"><small>Esta acción establecerá el estado a 0 y el role dejará de ser visible.</small></p>
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
    $('#deleteRoleModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var roleId = button.data('id');
        var roleName = button.data('nombre');

        var modal = $(this);
        modal.find('#roleNamePlaceholder').text(roleName);
        modal.find('#roleIdPlaceholder').text(roleId);

        var urlRoot = "<?php echo URLROOT; ?>";
        $('#deleteRoleForm').attr('action', urlRoot + '/roles/delete');
        $('#deleteRoleForm').append('<input type="hidden" name="id" value="' + roleId + '">');
    });

    // Manejar eliminación
    $('#deleteRoleForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: {
                id: $('#deleteRoleForm').find('input[name="id"]').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#deleteRoleModal').modal('hide');
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
