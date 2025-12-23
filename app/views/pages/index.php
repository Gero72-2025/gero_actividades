<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<div class="row">
    <div class="col-md-9 mx-auto">
        <div class="calendar-layout-container">
            
            <div class="calendar-detail-card" id="detailCard">
                </div>

            <div class="calendar-main-view">
                <div class="calendar-header">
                    <h2 id="currentYear"></h2>
                    <div class="month-navigation">
                        <button id="prevMonth" class="btn btn-sm btn-light prev-month"><i class="bi bi-chevron-left"></i></button>
                        <h3 id="currentMonthName">JUNIO</h3>
                        <button id="nextMonth" class="btn btn-sm btn-light next-month"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
                
                <div class="calendar-grid day-names">
                    <div class="day-name">DOM</div> <div class="day-name">LUN</div>
                    <div class="day-name">MAR</div>
                    <div class="day-name">MIÉ</div>
                    <div class="day-name">JUE</div>
                    <div class="day-name">VIE</div>
                    <div class="day-name">SÁB</div>
                </div>
                
                <div class="calendar-grid days-container" id="daysContainer">
                    </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL DE ACTIVIDADES EN CALENDARIO -->
<div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addActivityModalLabel"><i class="bi bi-plus-circle"></i> Registrar Actividades Múltiples</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="multiActivityForm" action="<?php echo URLROOT; ?>/actividades/process_multiple" method="post">
          <div class="modal-body">
              <h4 class="mb-3">Fecha Seleccionada: <span id="modalSelectedDate" class="badge badge-secondary"></span></h4>
              <input type="hidden" name="fecha_ingreso" id="modalFechaIngreso">
              
              <div id="alcancesContainer">
                  <p class="text-info">Cargando alcances disponibles...</p>
              </div>
              
              <div class="alert alert-warning mt-4">
                  <strong>Importante:</strong> Solo se guardarán los registros donde la descripción de la actividad **no esté vacía**.
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Guardar Actividades</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- modal para ver y editar actividad -->
<div class="modal fade" id="viewEditActivityModal" tabindex="-1" aria-labelledby="viewEditActivityModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-secondary text-white">
        <h5 class="modal-title" id="viewEditActivityModalLabel">Detalle de Actividad</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <form id="editActivityForm" action="" method="post">
          <div class="modal-body">
              <input type="hidden" name="id" id="modalActivityId">
              
              <div class="row mb-3">
                  <div class="col-md-6">
                      <label><strong>Fecha de Ingreso:</strong></label>
                      <p id="displayFechaIngreso"></p>
                  </div>
                  <div class="col-md-6">
                      <label><strong>Personal:</strong></label>
                      <p id="displayPersonal"></p>
                  </div>
              </div>
              
              <div class="form-group">
                  <label for="modalIdAlcance"><strong>Alcance / Contrato:</strong></label>
                  <select name="id_alcance" id="modalIdAlcance" class="form-control" disabled required>
                      </select>
              </div>

              <div class="form-group">
                  <label for="modalDescripcionRealizada"><strong>Descripción Realizada:</strong></label>
                  <textarea name="descripcion_realizada" id="modalDescripcionRealizada" class="form-control" rows="5" disabled required></textarea>
              </div>
              
              <div class="form-group">
                  <label for="modalEstadoActividad"><strong>Estado:</strong></label>
                  <select name="estado_actividad" id="modalEstadoActividad" class="form-control" disabled required>
                      <option value="Pendiente">Pendiente</option>
                      <option value="En Progreso">En Progreso</option>
                      <option value="Completada">Completada</option>
                      <option value="Cancelada">Cancelada</option>
                  </select>
              </div>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-warning" id="btnEditMode" onclick="toggleEditMode(true)">
                <i class="bi bi-pencil-square"></i> Editar
            </button>
            <button type="submit" class="btn btn-success d-none" id="btnSaveChanges">
                <i class="bi bi-download"></i> Guardar Cambios
            </button>
            <button type="button" class="btn btn-info d-none" id="btnCancelEdit" onclick="toggleEditMode(false)">
                Cancelar
            </button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>