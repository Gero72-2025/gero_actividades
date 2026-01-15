<?php require APPROOT . '/views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-body bg-light mt-5">
            <h2><?php echo $data['title']; ?></h2>
            <p>Edite los detalles del empleado.</p>
            <form action="<?php echo URLROOT; ?>/personal/edit/<?php echo $data['id']; ?>" method="post">
                
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="nombre">Nombre(s): <sup>*</sup></label>
                        <input type="text" name="nombre" class="form-control form-control-lg <?php echo (!empty($data['nombre_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['nombre']; ?>">
                        <span class="invalid-feedback"><?php echo $data['nombre_err']; ?></span>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="apellido">Apellido(s): <sup>*</sup></label>
                        <input type="text" name="apellido" class="form-control form-control-lg <?php echo (!empty($data['apellido_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['apellido']; ?>">
                        <span class="invalid-feedback"><?php echo $data['apellido_err']; ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="puesto">Puesto:</label>
                    <input type="text" name="puesto" class="form-control form-control-lg <?php echo (!empty($data['puesto_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['puesto']; ?>">
                    <span class="invalid-feedback"><?php echo $data['puesto_err']; ?></span>
                </div>

                <div class="form-group">
                    <label for="tipo_servicio">Tipo de Servicio: <sup>*</sup></label>
                    <select name="tipo_servicio" class="form-control form-control-lg <?php echo (!empty($data['tipo_servicio_err'])) ? 'is-invalid' : ''; ?>">
                        <option value="">Seleccione una opción</option>
                        <option value="1" <?php echo ($data['tipo_servicio'] === '1' || $data['tipo_servicio'] === 1) ? 'selected' : ''; ?>>Servicios Profesionales</option>
                        <option value="0" <?php echo ($data['tipo_servicio'] === '0' || $data['tipo_servicio'] === 0) ? 'selected' : ''; ?>>Servicios Técnicos</option>
                    </select>
                    <span class="invalid-feedback"><?php echo $data['tipo_servicio_err']; ?></span>
                </div>
                
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="id_division">División:</label>
                        <select name="id_division" class="form-control form-control-lg">
                            <option value="">Seleccione una división (Opcional)</option>
                            <?php 
                            // Asume que $data['divisiones'] tiene objetos con Id_Division y Nombre
                            if (isset($data['divisiones']) && is_array($data['divisiones'])) :
                                foreach($data['divisiones'] as $division): ?>
                                    <option 
                                        value="<?php echo $division->Id_Division; ?>" 
                                        <?php echo ($data['id_division'] == $division->Id_Division) ? 'selected' : ''; ?>
                                    >
                                        <?php echo $division->Nombre; ?>
                                    </option>
                                <?php endforeach; 
                            endif; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="id_contrato">Contrato:</label>
                        <select name="id_contrato" class="form-control form-control-lg">
                            <option value="">Seleccione un contrato (Opcional)</option>
                            <?php 
                            // Asume que $data['contratos'] tiene objetos con Id_contrato y Nombre_Contrato
                            if (isset($data['contratos']) && is_array($data['contratos'])) :
                                foreach($data['contratos'] as $contrato): ?>
                                    <option 
                                        value="<?php echo $contrato->Id_contrato; ?>" 
                                        <?php echo ($data['id_contrato'] == $contrato->Id_contrato) ? 'selected' : ''; ?>
                                    >
                                        <?php echo $contrato->Expediente; ?>
                                    </option>
                                <?php endforeach; 
                            endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_usuario">Usuario Vinculado: <sup>*</sup></label>
                    <select 
                        name="id_usuario" 
                        id="id_usuario" 
                        class="form-control form-control-lg <?php echo (!empty($data['id_usuario_err'])) ? 'is-invalid' : ''; ?>"
                    >
                        <option value="">Seleccione el Email del Usuario</option>
                        
                        <?php 
                        if (isset($data['usuarios']) && is_array($data['usuarios'])) :
                            foreach($data['usuarios'] as $usuario): ?>
                                <option 
                                    value="<?php echo $usuario->Id_usuario; ?>" 
                                    <?php echo ($data['id_usuario'] == $usuario->Id_usuario) ? 'selected' : ''; ?>
                                >
                                    <?php echo $usuario->email; ?> 
                                </option>
                            <?php endforeach; 
                        endif; ?>
                    </select>
                    <span class="invalid-feedback"><?php echo $data['id_usuario_err']; ?></span>
                    <small class="form-text text-muted">Solo se muestran usuarios no asignados o el usuario actualmente vinculado.</small>
                </div>
                
                <div class="row mt-4">
                    <div class="col">
                        <input type="submit" value="Guardar Cambios" class="btn btn-info btn-block">
                    </div>
                    <div class="col">
                         <a href="<?php echo URLROOT; ?>/personal/index" class="btn btn-light btn-block">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require APPROOT . '/views/layouts/footer.php'; ?>