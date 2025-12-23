<?php require APPROOT . '/views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-body bg-light mt-5">
            <h2><?php echo $data['title']; ?> (ID: <?php echo $data['id']; ?>)</h2>
            <p>Edite los detalles de la actividad diaria.</p>
            
            <form action="<?php echo URLROOT; ?>/actividades/edit/<?php echo $data['id']; ?>" method="post">
                
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="fecha_ingreso">Fecha de la Actividad: <sup>*</sup></label>
                        <input 
                            type="date" 
                            name="fecha_ingreso" 
                            class="form-control form-control-lg <?php echo (!empty($data['fecha_ingreso_err'])) ? 'is-invalid' : ''; ?>" 
                            value="<?php echo $data['fecha_ingreso']; ?>"
                        >
                        <span class="invalid-feedback"><?php echo $data['fecha_ingreso_err']; ?></span>
                    </div>

                    <div class="form-group col-md-8">
                        <label for="estado_actividad">Estado: <sup>*</sup></label>
                        <select 
                            name="estado_actividad" 
                            class="form-control form-control-lg <?php echo (!empty($data['estado_actividad_err'])) ? 'is-invalid' : ''; ?>"
                        >
                            <?php 
                            if (isset($data['estados']) && is_array($data['estados'])) :
                                foreach($data['estados'] as $estado): ?>
                                    <option 
                                        value="<?php echo $estado; ?>" 
                                        <?php echo ($data['estado_actividad'] == $estado) ? 'selected' : ''; ?>
                                    >
                                        <?php echo $estado; ?> 
                                    </option>
                                <?php endforeach; 
                            endif; ?>
                        </select>
                        <span class="invalid-feedback"><?php echo $data['estado_actividad_err']; ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_alcance">Alcance del Contrato: <sup>*</sup></label>
                    <select 
                        name="id_alcance" 
                        class="form-control form-control-lg <?php echo (!empty($data['id_alcance_err'])) ? 'is-invalid' : ''; ?>"
                    >
                        <option value="">-- Seleccione el Alcance --</option>
                        
                        <?php 
                        if (isset($data['alcances']) && is_array($data['alcances'])) :
                            foreach($data['alcances'] as $alcance): 
                                // Muestra el ID del alcance y una porción de la descripción.
                                $alcance_display = 'ID ' . $alcance->Id_alcance . ' (' . $alcance->Contrato_Descripcion . ') - ' . substr($alcance->Descripcion, 0, 40) . '...';
                            ?>
                                <option 
                                    value="<?php echo $alcance->Id_alcance; ?>" 
                                    <?php echo ($data['id_alcance'] == $alcance->Id_alcance) ? 'selected' : ''; ?>
                                >
                                    <?php echo $alcance_display; ?> 
                                </option>
                            <?php endforeach; 
                        endif; ?>
                    </select>
                    <span class="invalid-feedback"><?php echo $data['id_alcance_err']; ?></span>
                </div>

                <div class="form-group">
                    <label for="id_personal">Personal Responsable: <sup>*</sup></label>
                    <select 
                        name="id_personal_display" // Usamos otro nombre para evitar que el disabled se envíe
                        class="form-control form-control-lg <?php echo (!empty($data['id_personal_err'])) ? 'is-invalid' : ''; ?>"
                        disabled 
                    >
                        <option value="">-- Seleccione el Personal --</option>
                        
                        <?php 
                        if (isset($data['personal']) && is_array($data['personal'])) :
                            foreach($data['personal'] as $persona): 
                                $personal_display = $persona->Nombre_Completo . ' ' . $persona->Apellido_Completo . ' (' . $persona->Puesto . ')';
                            ?>
                                <option 
                                    value="<?php echo $persona->Id_personal; ?>" 
                                    <?php echo ($data['id_personal'] == $persona->Id_personal) ? 'selected' : ''; ?>
                                >
                                    <?php echo $personal_display; ?> 
                                </option>
                            <?php endforeach; 
                        endif; ?>
                    </select>
                    <input type="hidden" name="id_personal" value="<?php echo $data['id_personal']; ?>">
                    <span class="invalid-feedback"><?php echo $data['id_personal_err']; ?></span>
                    <?php if (empty($data['id_personal_err'])): ?>
                        <small class="form-text text-info">Solo usted, como usuario logueado, puede ser asignado a esta actividad.</small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="descripcion_realizada">Descripción del Trabajo/Actividad: <sup>*</sup></label>
                    <textarea 
                        name="descripcion_realizada" 
                        class="form-control form-control-lg <?php echo (!empty($data['descripcion_realizada_err'])) ? 'is-invalid' : ''; ?>" 
                        rows="5"
                        placeholder="Detalles sobre el trabajo a realizar o realizado en la fecha indicada."
                    ><?php echo $data['descripcion_realizada']; ?></textarea>
                    <span class="invalid-feedback"><?php echo $data['descripcion_realizada_err']; ?></span>
                </div>

                <div class="row mt-4">
                    <div class="col">
                        <input type="submit" value="Guardar Cambios" class="btn btn-info btn-block">
                    </div>
                    <div class="col">
                         <a href="<?php echo URLROOT; ?>/actividades/index" class="btn btn-light btn-block">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require APPROOT . '/views/layouts/footer.php'; ?>