<?php require APPROOT . '/views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-body bg-light mt-5">
            <h2><?php echo $data['title']; ?></h2>
            <p>Complete los detalles para registrar un nuevo alcance asociado a un contrato existente.</p>
            <form action="<?php echo URLROOT; ?>/alcances/add" method="post">
                
                <div class="form-group">
                    <label for="id_contrato">Contrato Asociado: <sup>*</sup></label>
                    <select 
                        name="id_contrato" 
                        class="form-control form-control-lg <?php echo (!empty($data['id_contrato_err'])) ? 'is-invalid' : ''; ?>"
                    >
                        <option value="">-- Seleccione un Contrato --</option>
                        
                        <?php 
                        if (isset($data['contratos']) && is_array($data['contratos'])) :
                            foreach($data['contratos'] as $contrato): 
                                // Muestra el ID del contrato y una porción de la descripción.
                                $contrato_display = 'ID ' . $contrato->Id_contrato . ' - ' . $contrato->Expediente;
                            ?>
                                <option 
                                    value="<?php echo $contrato->Id_contrato; ?>" 
                                    <?php echo ($data['id_contrato'] == $contrato->Id_contrato) ? 'selected' : ''; ?>
                                >
                                    <?php echo $contrato_display; ?> 
                                </option>
                            <?php endforeach; 
                        endif; ?>
                    </select>
                    <span class="invalid-feedback"><?php echo $data['id_contrato_err']; ?></span>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción del Alcance: <sup>*</sup></label>
                    <textarea 
                        name="descripcion" 
                        class="form-control form-control-lg <?php echo (!empty($data['descripcion_err'])) ? 'is-invalid' : ''; ?>" 
                        rows="5"
                        placeholder="Detalles sobre lo que abarca este alcance del contrato."
                    ><?php echo $data['descripcion']; ?></textarea>
                    <span class="invalid-feedback"><?php echo $data['descripcion_err']; ?></span>
                </div>

                <div class="row mt-4">
                    <div class="col">
                        <input type="submit" value="Guardar Alcance" class="btn btn-success btn-block">
                    </div>
                    <div class="col">
                         <a href="<?php echo URLROOT; ?>/alcances/index" class="btn btn-light btn-block">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require APPROOT . '/views/layouts/footer.php'; ?>