<?php require APPROOT . '/views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-body bg-light mt-5">
            <h2><?php echo $data['title']; ?></h2>
            <p>Complete el formulario para añadir una nueva división.</p>
            <form action="<?php echo URLROOT; ?>/divisions/add" method="post">
                
                <div class="form-group">
                    <label for="nombre">Nombre: <sup>*</sup></label>
                    <input type="text" name="nombre" class="form-control form-control-lg <?php echo (!empty($data['nombre_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['nombre']; ?>">
                    <span class="invalid-feedback"><?php echo $data['nombre_err']; ?></span>
                </div>
                
                <div class="form-group">
                    <label for="siglas">Siglas: <sup>*</sup></label>
                    <input type="text" name="siglas" class="form-control form-control-lg <?php echo (!empty($data['siglas_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['siglas']; ?>">
                    <span class="invalid-feedback"><?php echo $data['siglas_err']; ?></span>
                </div>

                <div class="form-group">
                    <label for="id_personal_jefe">Jefe de División:</label>
                    <select name="id_personal_jefe" class="form-control form-control-lg">
                        <option value="">Seleccione un jefe (Opcional)</option>
                        <?php 
                        // El bucle asume que $data['personal_list'] tiene objetos con Id_personal y Nombre
                        if (isset($data['personal_list']) && is_array($data['personal_list'])) :
                            foreach($data['personal_list'] as $personal): ?>
                                <option 
                                    value="<?php echo $personal->Id_personal; ?>" 
                                    <?php echo ($data['id_personal_jefe'] == $personal->Id_personal) ? 'selected' : ''; ?>
                                >
                                    <?php echo $personal->Nombre; ?>
                                </option>
                            <?php endforeach; 
                        endif; ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col">
                        <input type="submit" value="Añadir División" class="btn btn-success btn-block">
                    </div>
                    <div class="col">
                         <a href="<?php echo URLROOT; ?>/divisions/index" class="btn btn-light btn-block">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require APPROOT . '/views/layouts/footer.php'; ?>