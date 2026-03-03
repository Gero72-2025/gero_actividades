<?php require APPROOT . '/views/layouts/header.php'; ?>
<div class="row">
    <div class="col-12 col-md-8 mx-auto">
        <div class="card card-body bg-light mt-3 mt-md-5">
            <h2><?php echo $data['title']; ?></h2>
            <p class="text-muted">Registre una nueva actividad diaria.</p>
            
            <form action="<?php echo URLROOT; ?>/actividades/add" method="post">
                
                <div class="row">
                    <div class="form-group col-12 col-md-4">
                        <label for="fecha_ingreso">Fecha de la Actividad: <sup>*</sup></label>
                        <input 
                            type="date" 
                            name="fecha_ingreso" 
                            class="form-control form-control-lg <?php echo (!empty($data['fecha_ingreso_err'])) ? 'is-invalid' : ''; ?>" 
                            value="<?php echo $data['fecha_ingreso']; ?>"
                            <?php if(!empty($data['fecha_inicio_contrato'])): ?>min="<?php echo $data['fecha_inicio_contrato']; ?>"<?php endif; ?>
                            <?php if(!empty($data['fecha_fin_contrato'])): ?>max="<?php echo $data['fecha_fin_contrato']; ?>"<?php endif; ?>
                        >
                        <span class="invalid-feedback"><?php echo $data['fecha_ingreso_err']; ?></span>
                        <?php if(!empty($data['fecha_inicio_contrato']) && !empty($data['fecha_fin_contrato'])): ?>
                            <small class="form-text text-muted">Rango permitido: <?php echo date('d/m/Y', strtotime($data['fecha_inicio_contrato'])); ?> - <?php echo date('d/m/Y', strtotime($data['fecha_fin_contrato'])); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group col-12 col-md-8">
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
                                $alcance_display = 'ID ' . $alcance->Id_alcance . ' - '.  substr($alcance->Descripcion, 0, 70) . '...';
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
                        name="id_personal_display"
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
                
                <div id="descripcion_container" class="form-group">
                    <label for="descripcion_realizada">Descripción del Trabajo/Actividad: <sup>*</sup></label>
                    <textarea 
                        name="descripcion_realizada" 
                        class="form-control form-control-lg <?php echo (!empty($data['descripcion_realizada_err'])) ? 'is-invalid' : ''; ?>" 
                        rows="5"
                        placeholder="Detalles sobre el trabajo a realizar o realizado en la fecha indicada."
                    ><?php echo $data['descripcion_realizada']; ?></textarea>
                    <span class="invalid-feedback"><?php echo $data['descripcion_realizada_err']; ?></span>
                </div>

                <!-- Campo para cantidad si es recurrente -->
                <div id="cantidad_container" class="form-group" style="display: none;">
                    <label for="cantidad_realizada">Cantidad Realizada: <sup>*</sup></label>
                    <input 
                        type="number" 
                        name="cantidad_realizada" 
                        class="form-control form-control-lg <?php echo (!empty($data['cantidad_realizada_err'])) ? 'is-invalid' : ''; ?>" 
                        min="1"
                        value="<?php echo $data['cantidad_realizada'] ?? ''; ?>"
                        placeholder="Ingrese la cantidad de repeticiones (ej: número de escaneos, impresiones, etc.)"
                    >
                    <span class="invalid-feedback"><?php echo $data['cantidad_realizada_err'] ?? ''; ?></span>
                    <small class="form-text text-muted">Este campo solo es necesario si el alcance es recurrente.</small>
                </div>

                <div class="row mt-4 g-2">
                    <div class="col-12 col-md-6">
                        <input type="submit" value="Crear Actividad" class="btn btn-success btn-block w-100">
                    </div>
                    <div class="col-12 col-md-6">
                         <a href="<?php echo URLROOT; ?>/actividades/index" class="btn btn-light btn-block w-100">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de actividad duplicada -->
<div class="modal fade" id="duplicateModal" tabindex="-1" role="dialog" aria-labelledby="duplicateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="duplicateModalLabel"><i class="bi bi-exclamation-triangle-fill"></i> Actividad duplicada</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="duplicate-message mb-0">Ya existe una actividad para este alcance en la fecha seleccionada.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const alcanceSelect = document.querySelector('select[name="id_alcance"]');
    const cantidadContainer = document.getElementById('cantidad_container');
    const cantidadInput = document.querySelector('input[name="cantidad_realizada"]');
    const descripcionContainer = document.getElementById('descripcion_container');
    const descripcionTextarea = document.querySelector('textarea[name="descripcion_realizada"]');
    
    // Datos de alcances con información de recurrencia
    const alcancesData = <?php echo json_encode(array_map(function($a) { 
        return ['id' => $a->Id_alcance, 'es_recurrente' => $a->es_recurrente ?? 0]; 
    }, $data['alcances'] ?? [])); ?>;
    
    function checkAlcanceRecurrence() {
        const selectedId = parseInt(alcanceSelect.value);
        const alcance = alcancesData.find(a => a.id === selectedId);
        
        if (alcance && alcance.es_recurrente) {
            // Mostrar cantidad y ocultar descripción
            cantidadContainer.style.display = 'block';
            cantidadInput.required = true;

            descripcionContainer.style.display = 'none';
            descripcionTextarea.required = false;
            descripcionTextarea.value = 'Alcance Recurrente';
        } else {
            // Ocultar cantidad y mostrar descripción
            cantidadContainer.style.display = 'none';
            cantidadInput.required = false;
            cantidadInput.value = '';

            descripcionContainer.style.display = 'block';
            descripcionTextarea.required = false; // validación del servidor se encarga
            if (descripcionTextarea.value === 'Alcance Recurrente') {
                descripcionTextarea.value = '';
            }
        }
    }
    
    alcanceSelect.addEventListener('change', checkAlcanceRecurrence);
    
    // Verificar al cargar si hay un alcance seleccionado
    checkAlcanceRecurrence();

    // Mostrar modal de duplicado si el servidor detectó uno
    const duplicateError = <?php echo json_encode($data['duplicate_error'] ?? ''); ?>;
    if (duplicateError) {
        const $modal = typeof $ === 'function' ? $('#duplicateModal') : null;
        if ($modal && $modal.modal) {
            $modal.find('.duplicate-message').text(duplicateError);
            $modal.modal('show');
        } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalEl = document.getElementById('duplicateModal');
            modalEl.querySelector('.duplicate-message').textContent = duplicateError;
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        } else {
            alert(duplicateError);
        }
    }
});
</script>

<?php require APPROOT . '/views/layouts/footer.php'; ?>