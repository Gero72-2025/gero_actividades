<?php require APPROOT . '/views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-body bg-light mt-5">
            <h2><?php echo $data['title']; ?></h2>
            <p>Complete los detalles del nuevo contrato.</p>
            <form action="<?php echo URLROOT; ?>/contratos/add" method="post">
                
                <div class="form-group">
                    <label for="descripcion">Objeto del Servicio: <sup>*</sup></label>
                    <textarea name="descripcion" class="form-control form-control-lg <?php echo (!empty($data['descripcion_err'])) ? 'is-invalid' : ''; ?>" rows="3"><?php echo $data['descripcion']; ?></textarea>
                    <span class="invalid-feedback"><?php echo $data['descripcion_err']; ?></span>
                </div>
                
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="numero_pagos">Número de Pagos: <sup>*</sup></label>
                        <input type="number" name="numero_pagos" class="form-control form-control-lg <?php echo (!empty($data['numero_pagos_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['numero_pagos']; ?>">
                        <span class="invalid-feedback"><?php echo $data['numero_pagos_err']; ?></span>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="expediente">Expediente (Código):</label>
                        <input type="text" name="expediente" class="form-control form-control-lg" maxlength="20" value="<?php echo $data['expediente']; ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label for="inicio_contrato">Fecha de Inicio: <sup>*</sup></label>
                        <input type="date" name="inicio_contrato" class="form-control form-control-lg <?php echo (!empty($data['inicio_contrato_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['inicio_contrato']; ?>">
                        <span class="invalid-feedback"><?php echo $data['inicio_contrato_err']; ?></span>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="fin_contrato">Fecha de Fin (Opcional):</label>
                        <input type="date" name="fin_contrato" class="form-control form-control-lg" value="<?php echo $data['fin_contrato']; ?>">
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col">
                        <input type="submit" value="Añadir Contrato" class="btn btn-success btn-block">
                    </div>
                    <div class="col">
                         <a href="<?php echo URLROOT; ?>/contratos/index" class="btn btn-light btn-block">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require APPROOT . '/views/layouts/footer.php'; ?>