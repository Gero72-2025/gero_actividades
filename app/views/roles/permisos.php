<?php require APPROOT . '/views/layouts/header.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1><?php echo $data['title']; ?></h1>
            <a href="<?php echo URLROOT; ?>/roles/index" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
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
