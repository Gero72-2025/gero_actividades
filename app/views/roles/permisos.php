<?php require APPROOT . '/views/layouts/header.php'; ?>
<?php $isAdminRoleUser = !empty($data['is_admin_role_user']); ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1 class="mb-0"><?php echo $data['title']; ?></h1>
            <div class="d-flex gap-2">
                <?php if($isAdminRoleUser): ?>
                    <button
                        type="button"
                        class="btn btn-warning"
                        data-toggle="modal"
                        data-target="#restaurarPermisosModal"
                        title="Restaura todos los permisos del usuario Administrador"
                    >
                        <i class="bi bi-shield-lock"></i> Restaurar Permisos
                    </button>
                <?php endif; ?>
                <a href="<?php echo URLROOT; ?>/roles/index" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-lock"></i> Asignar Permisos a: <strong><?php echo $data['role']->Nombre; ?></strong>
                </h5>
            </div>
            <div class="card-body">
                <form action="<?php echo URLROOT; ?>/roles/permisos/<?php echo $data['role']->Id_role; ?>" method="post">
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        Seleccione los permisos que desea asignar a este role. Los usuarios con este role tendrán acceso a las acciones correspondientes.
                    </div>

                    <?php foreach ($data['permisos_agrupados'] as $modulo => $permisos): ?>
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <strong><?php echo ucfirst($modulo); ?></strong>
                                    <span class="badge badge-secondary float-right"><?php echo count($permisos); ?> permisos</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($permisos as $permiso): ?>
                                        <div class="col-12 col-md-6 col-lg-4 mb-3">
                                            <div class="custom-control custom-checkbox">
                                                <input 
                                                    type="checkbox" 
                                                    class="custom-control-input" 
                                                    id="permiso_<?php echo $permiso->Id_permiso; ?>"
                                                    name="permisos[]"
                                                    value="<?php echo $permiso->Id_permiso; ?>"
                                                    <?php echo $permiso->asignado ? 'checked' : ''; ?>
                                                >
                                                <label class="custom-control-label" for="permiso_<?php echo $permiso->Id_permiso; ?>">
                                                    <strong><?php echo ucfirst($permiso->Accion); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo $permiso->Nombre; ?></small>
                                                    <?php if (!empty($permiso->Descripcion)): ?>
                                                        <br>
                                                        <small class="text-secondary"><?php echo $permiso->Descripcion; ?></small>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="row mt-4 g-2">
                        <div class="col-12 col-md-6">
                            <input type="submit" value="Guardar Permisos" class="btn btn-success btn-block w-100">
                        </div>
                        <div class="col-12 col-md-6">
                             <a href="<?php echo URLROOT; ?>/roles/index" class="btn btn-light btn-block w-100">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/layouts/footer.php'; ?>

<?php if($isAdminRoleUser): ?>
<div class="modal fade" id="restaurarPermisosModal" tabindex="-1" aria-labelledby="restaurarPermisosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="restaurarPermisosModalLabel">
                    <i class="bi bi-shield-lock"></i> Restaurar Permisos del Administrador
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Esta acción restaurará permisos del usuario <strong>admin@admin.com</strong> (ID 1):</p>
                <ul>
                    <li>Rol Administrador activo</li>
                    <li>Todos los permisos globales activos para Administrador</li>
                    <li>Todos los permisos granulares de tablero activos</li>
                </ul>
                <div id="restaurarPermisosMsg" class="mt-3 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarRestaurar" class="btn btn-warning">
                    <i class="bi bi-arrow-counterclockwise"></i> Si, Restaurar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
        const btnConfirmar = document.getElementById('btnConfirmarRestaurar');
        const msgEl = document.getElementById('restaurarPermisosMsg');
        if(!btnConfirmar || !msgEl) return;

        btnConfirmar.addEventListener('click', async function(){
                btnConfirmar.disabled = true;
                btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Restaurando...';
                msgEl.className = 'mt-3 d-none';

                try {
                        const resp = await fetch('<?php echo URLROOT; ?>/roles/restaurar_permisos_admin', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({})
                        });
                        const data = await resp.json();
                        msgEl.className = 'mt-3 alert ' + (data.success ? 'alert-success' : 'alert-danger');
                        msgEl.textContent = data.success ? data.message : (data.error || 'Error desconocido.');

                        if(data.success){
                            setTimeout(function(){
                                if(window.jQuery && window.jQuery.fn && window.jQuery.fn.modal){
                                    window.jQuery('#restaurarPermisosModal').modal('hide');
                                } else {
                                    const modalEl = document.getElementById('restaurarPermisosModal');
                                    if(modalEl){
                                        modalEl.classList.remove('show');
                                        modalEl.style.display = 'none';
                                    }
                                }
                                window.location.reload();
                            }, 500);
                        }
                } catch(err) {
                        msgEl.className = 'mt-3 alert alert-danger';
                        msgEl.textContent = 'Error de red al intentar restaurar permisos.';
                }

                btnConfirmar.disabled = false;
                btnConfirmar.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> Si, Restaurar';
        });
})();
</script>
<?php endif; ?>
