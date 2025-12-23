<?php require APPROOT . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12 col-md-8 mx-auto">
        <div class="card card-body bg-light mt-3 mt-md-5">
            <h2><?php echo $data['title']; ?></h2>
            <p class="text-muted">Cree un nuevo role para asignar permisos a usuarios.</p>
            
            <form action="<?php echo URLROOT; ?>/roles/add" method="post">
                
                <div class="form-group">
                    <label for="nombre">Nombre del Role: <sup>*</sup></label>
                    <input 
                        type="text" 
                        name="nombre" 
                        class="form-control form-control-lg <?php echo (!empty($data['nombre_err'])) ? 'is-invalid' : ''; ?>" 
                        value="<?php echo $data['nombre']; ?>"
                        placeholder="Ej: Administrador, Gerente, Supervisor"
                    >
                    <span class="invalid-feedback"><?php echo $data['nombre_err']; ?></span>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción: <sup>*</sup></label>
                    <textarea 
                        name="descripcion" 
                        class="form-control form-control-lg <?php echo (!empty($data['descripcion_err'])) ? 'is-invalid' : ''; ?>" 
                        rows="5"
                        placeholder="Describa el propósito y responsabilidades de este role."
                    ><?php echo $data['descripcion']; ?></textarea>
                    <span class="invalid-feedback"><?php echo $data['descripcion_err']; ?></span>
                    <small class="form-text text-muted">Puede asignar permisos después de crear el role.</small>
                </div>

                <div class="row mt-4 g-2">
                    <div class="col-12 col-md-6">
                        <input type="submit" value="Crear Role" class="btn btn-success btn-block w-100">
                    </div>
                    <div class="col-12 col-md-6">
                         <a href="<?php echo URLROOT; ?>/roles/index" class="btn btn-light btn-block w-100">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/layouts/footer.php'; ?>
