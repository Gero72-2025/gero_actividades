<?php require APPROOT . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-12 col-md-8 mx-auto">
        <div class="card card-body bg-light mt-3 mt-md-5">
            <h2><?php echo $data['title']; ?></h2>
            <p class="text-muted">Edite la información del permiso.</p>
            
            <form action="<?php echo URLROOT; ?>/permisos/edit/<?php echo $data['id']; ?>" method="post">
                
                <div class="form-group">
                    <label for="nombre">Nombre del Permiso: <sup>*</sup></label>
                    <input 
                        type="text" 
                        name="nombre" 
                        class="form-control form-control-lg <?php echo (!empty($data['nombre_err'])) ? 'is-invalid' : ''; ?>"
                        value="<?php echo $data['nombre']; ?>"
                        placeholder="Ej: actividades.crear"
                    >
                    <span class="invalid-feedback"><?php echo $data['nombre_err']; ?></span>
                </div>

                <div class="row">
                    <div class="form-group col-12 col-md-6">
                        <label for="modulo">Módulo: <sup>*</sup></label>
                        <input 
                            type="text" 
                            name="modulo" 
                            class="form-control form-control-lg <?php echo (!empty($data['modulo_err'])) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $data['modulo']; ?>"
                            placeholder="Ej: actividades"
                            list="modulos-list"
                        >
                        <datalist id="modulos-list">
                            <?php foreach ($data['modulos'] as $modulo): ?>
                                <option value="<?php echo $modulo; ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <span class="invalid-feedback"><?php echo $data['modulo_err']; ?></span>
                    </div>

                    <div class="form-group col-12 col-md-6">
                        <label for="accion">Acción: <sup>*</sup></label>
                        <input 
                            type="text" 
                            name="accion" 
                            class="form-control form-control-lg <?php echo (!empty($data['accion_err'])) ? 'is-invalid' : ''; ?>"
                            value="<?php echo $data['accion']; ?>"
                            placeholder="Ej: crear"
                            list="acciones-list"
                        >
                        <datalist id="acciones-list">
                            <?php foreach ($data['acciones'] as $accion): ?>
                                <option value="<?php echo $accion; ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <span class="invalid-feedback"><?php echo $data['accion_err']; ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción: <sup>*</sup></label>
                    <textarea 
                        name="descripcion" 
                        class="form-control form-control-lg <?php echo (!empty($data['descripcion_err'])) ? 'is-invalid' : ''; ?>"
                        rows="4"
                        placeholder="Describa qué acciones permite este permiso."
                    ><?php echo $data['descripcion']; ?></textarea>
                    <span class="invalid-feedback"><?php echo $data['descripcion_err']; ?></span>
                </div>

                <div class="row mt-4 g-2">
                    <div class="col-12 col-md-6">
                        <input type="submit" value="Actualizar Permiso" class="btn btn-success btn-block w-100">
                    </div>
                    <div class="col-12 col-md-6">
                         <a href="<?php echo URLROOT; ?>/permisos/index" class="btn btn-light btn-block w-100">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/layouts/footer.php'; ?>
