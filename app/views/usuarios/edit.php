<?php require APPROOT . '/views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-body bg-light mt-5">
            <h2><?php echo $data['title']; ?></h2>
            <p>Edite el email o actualice la contraseña. Deje los campos de contraseña vacíos para mantener la actual.</p>
            <form action="<?php echo URLROOT; ?>/usuarios/edit/<?php echo $data['id']; ?>" method="post">
                
                <div class="form-group">
                    <label for="email">Email: <sup>*</sup></label>
                    <input type="email" name="email" class="form-control form-control-lg <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>">
                    <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
                </div>
                
                <h4 class="mt-4 mb-3">Cambiar Contraseña (Opcional)</h4>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="pass">Nueva Contraseña:</label>
                        <input type="password" name="pass" class="form-control form-control-lg <?php echo (!empty($data['pass_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['pass']; ?>">
                        <span class="invalid-feedback"><?php echo $data['pass_err']; ?></span>
                        <small class="form-text text-muted">Mínimo 6 caracteres.</small>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="confirm_pass">Confirmar Nueva Contraseña:</label>
                        <input type="password" name="confirm_pass" class="form-control form-control-lg <?php echo (!empty($data['confirm_pass_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['confirm_pass']; ?>">
                        <span class="invalid-feedback"><?php echo $data['confirm_pass_err']; ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_role">Rol:</label>
                    <select name="id_role" class="form-control form-control-lg">
                        <option value="">-- Seleccionar Rol --</option>
                        <?php if(!empty($data['roles'])): ?>
                            <?php foreach($data['roles'] as $role): ?>
                                <option value="<?php echo $role->Id_role; ?>" <?php echo ($data['id_role_actual'] == $role->Id_role) ? 'selected' : ''; ?>>
                                    <?php echo $role->Nombre; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="row mt-4">
                    <div class="col">
                        <input type="submit" value="Guardar Cambios" class="btn btn-info btn-block">
                    </div>
                    <div class="col">
                         <a href="<?php echo URLROOT; ?>/usuarios/index" class="btn btn-light btn-block">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require APPROOT . '/views/layouts/footer.php'; ?>