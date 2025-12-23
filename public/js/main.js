$('#deleteContratoModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var contratoId = button.data('id');
    var contratoName = button.data('nombre'); // La descripción truncada

    var modal = $(this);
    modal.find('#contratoNamePlaceholder').text(contratoName + '...');
    modal.find('#contratoIdPlaceholder').text(contratoId);

    var formAction = APP_URL_ROOT + '/contratos/delete/' + contratoId;
    
    modal.find('#deleteContratoForm').attr('action', formAction);
});

$('#deleteDivisionModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var divisionId = button.data('id');
            var divisionName = button.data('nombre');
            var modal = $(this);
            modal.find('#divisionNamePlaceholder').text(divisionName);
            modal.find('#divisionIdPlaceholder').text(divisionId);
            var formAction = APP_URL_ROOT + '/divisions/delete/' + divisionId;
            modal.find('#deleteForm').attr('action', formAction);
        });

// Script para modal de Personal ¡NUEVO!
$('#deletePersonalModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var personalId = button.data('id');
    var personalName = button.data('nombre');

    var modal = $(this);
    modal.find('#personalNamePlaceholder').text(personalName);
    modal.find('#personalIdPlaceholder').text(personalId);

    var formAction = APP_URL_ROOT + '/personal/delete/' + personalId;
    
    modal.find('#deletePersonalForm').attr('action', formAction); // <-- Nota: Asegúrate que el ID del form en el modal sea 'deletePersonalForm'
});

// Script para modal de Usuarios
$('#deleteUsuarioModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var usuarioId = button.data('id');
    var usuarioName = button.data('nombre'); 

    var modal = $(this);
    modal.find('#usuarioNamePlaceholder').text(usuarioName);
    modal.find('#usuarioIdPlaceholder').text(usuarioId);

    var formAction = APP_URL_ROOT + '/usuarios/delete/' + usuarioId;
    
    modal.find('#deleteUsuarioForm').attr('action', formAction);
});

// Script para modal de Alcances
$('#deleteAlcanceModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var alcanceId = button.data('id');
    var alcanceName = button.data('nombre'); // Usamos el nombre del contrato como referencia

    var modal = $(this);
    modal.find('#alcanceNamePlaceholder').text(alcanceName);
    modal.find('#alcanceIdPlaceholder').text(alcanceId);

    var formAction = APP_URL_ROOT + '/alcances/delete/' + alcanceId;
    
    modal.find('#deleteAlcanceForm').attr('action', formAction);
});

// Script para modal de Actividades
$('#deleteActividadModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var actividadId = button.data('id');
    var alcanceDesc = button.data('descripcion'); 

    var modal = $(this);
    modal.find('#alcanceDescPlaceholder').text(alcanceDesc);
    modal.find('#actividadIdPlaceholder').text(actividadId);

    var formAction = APP_URL_ROOT + '/actividades/delete/' + actividadId;
    
    modal.find('#deleteActividadForm').attr('action', formAction);
});

// CALENDARIO JS
let currentYear;
let currentMonth; // 0 = Enero, 11 = Diciembre
let allActivities = []; // Almacenar todas las actividades cargadas para el mes

// Nombres en español
const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
const dayNamesFull = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];

// Función para obtener el nombre del estado para el estilo
function getDotClass(estado) {
    return `dot-${estado.replace(/\s/g, '')}`;
}

// --- FUNCIÓN DE DETALLE DIARIO ---
    function updateDetailCard(dateStr) {
        const detailCard = document.getElementById('detailCard');
        const date = new Date(dateStr + 'T00:00:00'); // Añadir T00:00:00 para evitar problemas de zona horaria
        
        const dayNumber = date.getDate();
        const dayOfWeek = dayNamesFull[date.getDay()];
        const monthName = monthNames[date.getMonth()];
        const year = date.getFullYear();

        // Filtrar actividades para el día seleccionado
        const dailyActivities = allActivities.filter(act => act.Fecha_ingreso === dateStr);
        
        let activityListHTML = '';

        if (dailyActivities.length > 0) {
            activityListHTML = `<div class="detail-activity-list">`;
            dailyActivities.forEach(act => {
                // Usamos tu función getDotColor para mantener el estilo
                const dotColor = getDotColor(act.Estado_actividad); 
                const dot = `<span style="display:inline-block; width:8px; height:8px; border-radius:50%; background-color:${dotColor}; margin-right:5px;"></span>`;
                
                const descriptionPreview = (act.Descripcion_realizada || '').substring(0, 30);
                // HACEMOS CADA ACTIVIDAD CLICKEABLE
                activityListHTML += `
                <div 
                    class="detail-activity-item" 
                    onclick="viewActivityDetails(${act.Id_actividad})" 
                    style="cursor: pointer;"
                    title="Clic para ver detalles de: ${descriptionPreview}..."
                >
                    ${dot} ${act.Estado_actividad}
                </div>`;
            });
            activityListHTML += `</div>`;
        } else {
            activityListHTML = '<p class="text-secondary mt-3">No hay actividades registradas para este día.</p>';
        }

        const displayDate = `${dayOfWeek} · ${monthName} · ${year}`;
        // Renderizar el contenido de la tarjeta negra
        detailCard.innerHTML = `
            <h1 class="detail-day-number">${dayNumber}</h1>
            <p class="detail-full-date">${dayOfWeek} · ${monthName} · ${year}</p>
            ${activityListHTML}
            <hr style="width: 80%; border-color: rgba(255,255,255,0.2);">

            <button 
                type="button" 
                class="btn btn-outline-light btn-block mt-3" 
                data-toggle="modal" 
                data-target="#addActivityModal"
                data-date-str="${dateStr}"
                data-date-display="${displayDate}"
                onclick="loadAlcancesForActivity('${dateStr}', '${displayDate}')"
            >
                <i class="fa fa-pencil-alt"></i> Agregar Actividad
            </button>
        `;
    }

// Función auxiliar para obtener color del punto (necesaria para el detalle)
function getDotColor(estado) {
    switch (estado) {
        case 'Completada': return '#28a745';
        case 'En Progreso': return '#ffc107';
        case 'Cancelada': return '#dc3545';
        default: return '#6c757d';
    }
}


// --- FUNCIÓN DE RENDERIZADO PRINCIPAL ---
async function renderCalendar() {
    const year = currentYear;
    const month = currentMonth;
    const daysContainer = document.getElementById('daysContainer');
    daysContainer.innerHTML = ''; 

    // 1. Mostrar Mes y Año
    document.getElementById('currentYear').textContent = year;
    document.getElementById('currentMonthName').textContent = monthNames[month].toUpperCase();

    // 2. Cálculo de días del mes
    const firstDayOfMonth = new Date(year, month, 1).getDay(); // 0 (Dom) - 6 (Sáb)
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    // El inicio de la semana es Domingo (0) en JS, igual que el diseño de la imagen.
    const startDay = firstDayOfMonth;

    // 3. Obtener actividades por AJAX
    allActivities = await fetchActivities(year, month + 1); 
    
    const activityMap = allActivities.reduce((map, act) => {
        // Agrupar estados si hay múltiples actividades en un día
        if (!map[act.Fecha_ingreso]) {
            map[act.Fecha_ingreso] = [];
        }
        map[act.Fecha_ingreso].push(act.Estado_actividad);
        return map;
    }, {});

    // 4. Llenar celdas de días del mes anterior (padding)
    for (let i = 0; i < startDay; i++) {
        daysContainer.innerHTML += '<div class="day-cell text-muted"></div>';
    }

    // 5. Llenar celdas de días del mes actual
    let selectedDateStr = '';
    const todayStr = new Date().toISOString().slice(0, 10);

    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day); // Creamos un objeto Date para obtener el día de la semana
        const dayOfWeek = date.getDay(); // 0 = Domingo, 1 = Lunes, ..., 6 = Sábado
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = dateStr === todayStr;
        const hasActivity = activityMap[dateStr] && activityMap[dateStr].length > 0;
        
        let cellClass = 'day-cell';
        let htmlContent = day;
        let dotHTML = '';

        // --- LÓGICA PARA FIN DE SEMANA ---
        if (dayOfWeek === 0 || dayOfWeek === 6) { 
            cellClass += ' weekend';
        }
        // Definir qué día debe seleccionarse por defecto
        if (isToday) {
            cellClass += ' selected';
            selectedDateStr = dateStr;
        }

        if (hasActivity) {
            // Si hay actividades, solo mostrar el color del estado MÁS ALTO (ej: En Progreso > Pendiente)
            const primaryState = activityMap[dateStr][0]; 
            const dotClass = getDotClass(primaryState);
            dotHTML = `<div class="activity-dot ${dotClass}" title="${activityMap[dateStr].join(', ')}"></div>`;
            cellClass += ' has-activity';
        }
        
        daysContainer.innerHTML += `<div 
            class="${cellClass}" 
            data-date="${dateStr}" 
            onclick="handleDayClick(this, '${dateStr}')">
            ${htmlContent}
            ${dotHTML}
        </div>`;
    }
    
    // 6. Llenar celdas de días del mes siguiente (padding)
    const totalCells = startDay + daysInMonth;
    const remainingCells = 42 - totalCells; 

        for (let i = 0; i < remainingCells; i++) {
        daysContainer.innerHTML += '<div class="day-cell text-muted"></div>';
    }
    
    // 7. Cargar la tarjeta de detalle para el día seleccionado (hoy)
    if (selectedDateStr) {
        updateDetailCard(selectedDateStr);
    } else {
        // Si el mes no es el actual, seleccionar el primer día del mes.
        updateDetailCard(`${year}-${String(month + 1).padStart(2, '0')}-01`);
    }
    
}

// --- FUNCIÓN MANEJADORA DE CLIC EN DÍA ---
function handleDayClick(element, dateStr) {
    // 1. Limpiar selección anterior
    document.querySelectorAll('.day-cell').forEach(cell => {
        cell.classList.remove('selected');
    });
    
    // 2. Seleccionar la nueva celda
    if (!element.classList.contains('text-muted')) {
        element.classList.add('selected');
        // 3. Actualizar la tarjeta de detalle
        updateDetailCard(dateStr);
    }
}


// --- FUNCIÓN AJAX (MISMA QUE ANTES) ---
async function fetchActivities(year, month) {
    try {
        const response = await fetch(`${APP_URL_ROOT}/actividades/get_monthly_activities/${year}/${month}`);
        if (!response.ok) {
            const errorData = await response.json();
            console.error('Error al cargar actividades:', errorData.error);
            return []; 
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error de red al consultar actividades:', error);
        return [];
    }
}

// --- LÓGICA DE NAVEGACIÓN Y START ---

// Verificar que los elementos del calendario existan antes de agregar listeners
if (document.getElementById('prevMonth')) {
    document.getElementById('prevMonth').addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        renderCalendar();
    });
}

if (document.getElementById('nextMonth')) {
    document.getElementById('nextMonth').addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        renderCalendar();
    });
}

function initializeCalendar() {
    const today = new Date();
    currentYear = today.getFullYear();
    currentMonth = today.getMonth();
    // Solo renderizar si el contenedor del calendario existe
    if (document.getElementById('daysContainer')) {
        renderCalendar();
    }
}

// Solo inicializar el calendario si existe
if (document.getElementById('daysContainer')) {
    initializeCalendar();
}

async function fetchAllAlcances() {
    try {
        const response = await fetch(`${APP_URL_ROOT}/alcances/get_all_active`); // <-- Necesitamos crear este endpoint
        if (!response.ok) {
            console.error('Error al cargar alcances.');
            return [];
        }
        return await response.json();
    } catch (error) {
        console.error('Error de red al consultar alcances:', error);
        return [];
    }
}


async function loadAlcancesForActivity(dateStr, dateDisplay) {
    const container = document.getElementById('alcancesContainer');
    const modalSelectedDate = document.getElementById('modalSelectedDate');
    const modalFechaIngreso = document.getElementById('modalFechaIngreso');

    // 1. Establecer la fecha en el modal
    modalSelectedDate.textContent = dateDisplay;
    modalFechaIngreso.value = dateStr;

    // 2. Cargar y renderizar los alcances
    container.innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i> Cargando alcances...</div>';
    
    const alcances = await fetchAllAlcances();
    
    if (alcances.length === 0) {
        container.innerHTML = '<div class="alert alert-danger">No se encontraron alcances de contratos activos para registrar actividades.</div>';
        return;
    }

    // 3. Obtener actividades ya registradas en ese día
    const actividadesDelDia = await fetchActivitiesByDate(dateStr);

    let html = '';
    alcances.forEach(alcance => {
        const id = `actividad_alcance_${alcance.Id_alcance}`;
        const actividadExistente = actividadesDelDia[alcance.Id_alcance];
        
        // Determinar si el campo debe estar deshabilitado
        const isDisabled = actividadExistente ? true : false;
        const descripcion = actividadExistente ? actividadExistente.Descripcion_realizada : '';
        const placeholderText = actividadExistente 
            ? 'Esta actividad ya fue registrada. No puede editarse desde aquí.'
            : 'Escriba la descripción de la actividad realizada para este alcance. Dejar vacío para omitir.';
        
        // Badge con información adicional si existe actividad
        let badgeHTML = '';
        if (actividadExistente) {
            badgeHTML = `<span class="badge badge-info ml-2">Estado: ${actividadExistente.Estado_actividad}</span>`;
        }
        
        html += `
            <div class="form-group border p-3 mb-3 ${isDisabled ? 'bg-light' : ''}">
                <label for="${id}">
                    <strong>Alcance ${alcance.Id_alcance}:</strong> ${alcance.Descripcion}
                    ${badgeHTML}
                    <br> 
                    <small class="text-muted">(Contrato ID ${alcance.Expediente})</small>
                </label>
                <textarea 
                    id="${id}" 
                    name="alcances[${alcance.Id_alcance}]" 
                    class="form-control" 
                    rows="3" 
                    placeholder="${placeholderText}"
                    ${isDisabled ? 'disabled' : ''}
                >${descripcion}</textarea>
                ${isDisabled ? '<small class="text-warning"><i class="fa fa-lock"></i> Campo deshabilitado - Actividad ya registrada</small>' : ''}
            </div>
        `;
    });
    
    container.innerHTML = html;
}

/**
 * Obtiene las actividades registradas en una fecha específica
 */
async function fetchActivitiesByDate(dateStr) {
    try {
        const response = await fetch(`${APP_URL_ROOT}/actividades/get_activities_by_date/${dateStr}`);
        if (!response.ok) {
            console.warn('No se encontraron actividades previas para esta fecha');
            return {};
        }
        return await response.json();
    } catch (error) {
        console.error('Error al obtener actividades del día:', error);
        return {};
    }
}

// Almacena la actividad original en modo edición
let originalActivityData = {};

/**
 * Carga los datos de una actividad en el modal y lo abre en modo Vista.
 * @param {number} activityId ID de la actividad a cargar.
 */
async function viewActivityDetails(activityId) {
    try {
        // Debes crear este endpoint en tu controlador Actividades.php
        const response = await fetch(`${APP_URL_ROOT}/actividades/get_activity_details/${activityId}`);
        if (!response.ok) throw new Error('Error al cargar la actividad.');
        
        const activity = await response.json();
        if (!activity) throw new Error('Actividad no encontrada.');

        // Guardar copia original para el modo edición
        originalActivityData = activity;
        
        // Cargar los datos en el modal
        $('#modalActivityId').val(activity.Id_actividad);
        $('#displayFechaIngreso').text(activity.Fecha_ingreso);
        $('#displayPersonal').text(`${activity.Personal_Nombre} ${activity.Personal_Apellido}`);
        $('#modalDescripcionRealizada').val(activity.Descripcion_realizada);
        
        // Setear el SELECTs
        await loadAlcancesDropdown(activity.Id_alcance); // Cargar todos los alcances y seleccionar el actual
        $('#modalEstadoActividad').val(activity.Estado_actividad);
        
        // Configurar el formulario para la edición
        $('#editActivityForm').attr('action', `${APP_URL_ROOT}/actividades/edit_calendar_activity`);
        
        // Iniciar en modo VISTA (deshabilitado)
        toggleEditMode(false); 
        
        // Mostrar el modal
        $('#viewEditActivityModal').modal('show');

    } catch (error) {
        console.error('Error al ver detalles de actividad:', error);
        alert('No se pudo cargar la información de la actividad.');
    }
}

/**
 * Activa o desactiva el modo de edición del modal.
 * @param {boolean} enable true para modo Edición, false para modo Vista.
 */
function toggleEditMode(enable) {
    const isEditing = enable;

    // Campos a habilitar/deshabilitar
    $('#modalIdAlcance').prop('disabled', !isEditing);
    $('#modalDescripcionRealizada').prop('disabled', !isEditing);
    $('#modalEstadoActividad').prop('disabled', !isEditing);
    
    // Títulos y botones
    $('#viewEditActivityModalLabel').text(isEditing ? 'Editar Actividad' : 'Detalle de Actividad');

    if (isEditing) {
        // Modo Edición: Mostrar botones de Guardar/Cancelar
        $('#btnEditMode').addClass('d-none');
        $('#btnSaveChanges').removeClass('d-none');
        $('#btnCancelEdit').removeClass('d-none');
    } else {
        // Modo Vista: Mostrar botón de Editar
        $('#btnEditMode').removeClass('d-none');
        $('#btnSaveChanges').addClass('d-none');
        $('#btnCancelEdit').addClass('d-none');

        // Si se cancela la edición, restaurar valores originales
        if (originalActivityData && $('#modalActivityId').val()) {
             // Restaurar valores
            $('#modalIdAlcance').val(originalActivityData.Id_alcance);
            $('#modalDescripcionRealizada').val(originalActivityData.Descripcion_realizada);
            $('#modalEstadoActividad').val(originalActivityData.Estado_actividad);
        }
    }
}

/**
 * Carga todos los alcances en el dropdown y selecciona el Id_alcance dado.
 * Reutiliza el endpoint creado para el modal de Agregar.
 */
async function loadAlcancesDropdown(selectedId = null) {
    const select = $('#modalIdAlcance');
    select.empty();
    select.append('<option value="">Cargando...</option>');
    
    try {
        const alcances = await fetchAllAlcances(); // Reutiliza la función fetchAllAlcances()

        select.empty();
        select.append('<option value="">--- Seleccione un Alcance ---</option>');
        alcances.forEach(alcance => {
            const isSelected = selectedId == alcance.Id_alcance ? 'selected' : '';
            select.append(`<option value="${alcance.Id_alcance}" ${isSelected}>${alcance.Id_alcance} - ${alcance.Descripcion.substring(0, 70)}...</option>`);
        });

    } catch (error) {
        console.error("Error cargando alcances para edición:", error);
        select.empty();
        select.append('<option value="">Error al cargar alcances</option>');
    }
}

// Manejador para llenar el dropdown de números de pago cuando se abre el modal
$('#reporteActividadesModal').on('show.bs.modal', function (event) {
    var selectNumeroPago = document.getElementById('numero_pago');
    var selectMes = document.getElementById('mes_reporte');
    var selectAnio = document.getElementById('anio_reporte');
    
    // 1. Llenar el dropdown de meses
    const meses = [
        { valor: 1, nombre: 'Enero' },
        { valor: 2, nombre: 'Febrero' },
        { valor: 3, nombre: 'Marzo' },
        { valor: 4, nombre: 'Abril' },
        { valor: 5, nombre: 'Mayo' },
        { valor: 6, nombre: 'Junio' },
        { valor: 7, nombre: 'Julio' },
        { valor: 8, nombre: 'Agosto' },
        { valor: 9, nombre: 'Septiembre' },
        { valor: 10, nombre: 'Octubre' },
        { valor: 11, nombre: 'Noviembre' },
        { valor: 12, nombre: 'Diciembre' }
    ];
    
    const today = new Date();
    const mesActual = today.getMonth() + 1; // getMonth() retorna 0-11
    const anioActual = today.getFullYear();
    
    // Llenar meses
    selectMes.innerHTML = '<option value="">-- Seleccione un mes --</option>';
    meses.forEach(mes => {
        const option = document.createElement('option');
        option.value = mes.valor;
        option.textContent = mes.nombre;
        if (mes.valor === mesActual) {
            option.selected = true; // Seleccionar el mes actual por defecto
        }
        selectMes.appendChild(option);
    });
    
    // 2. Llenar el dropdown de años (2024 a 2030)
    selectAnio.innerHTML = '<option value="">-- Seleccione un año --</option>';
    for (let year = 2024; year <= 2030; year++) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        if (year === anioActual) {
            option.selected = true; // Seleccionar el año actual por defecto
        }
        selectAnio.appendChild(option);
    }
    
    // 3. Limpiar opciones previas de número de pago
    selectNumeroPago.innerHTML = '<option value="">-- Cargando pagos disponibles... --</option>';
    
    // 4. Obtener el contrato del personal logueado
    var urlFetch = APP_URL_ROOT + '/actividades/get_contrato_pagos';
    console.log('Fetch URL:', urlFetch);
    
    fetch(urlFetch)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('Error HTTP: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Data recibida:', data);
            
            if (data.success && data.numero_pagos && data.numero_pagos > 0) {
                console.log('Generando opciones para:', data.numero_pagos, 'pagos');
                
                // Limpiar opciones previas
                selectNumeroPago.innerHTML = '<option value="">-- Seleccione un pago --</option>';
                
                // Generar opciones con los números de pago
                for (let i = 1; i <= data.numero_pagos; i++) {
                    let texto = convertirNumeroPagoTexto(i);
                    let option = document.createElement('option');
                    option.value = i;
                    option.textContent = texto + ' (' + i + ')';
                    selectNumeroPago.appendChild(option);
                    console.log('Opción agregada:', texto + ' (' + i + ')');
                }
            } else {
                console.warn('No hay pagos disponibles. Data:', data);
                selectNumeroPago.innerHTML = '<option value="">No hay contrato activo</option>';
            }
        })
        .catch(error => {
            console.error('Error al cargar los números de pago:', error);
            selectNumeroPago.innerHTML = '<option value="">Error al cargar pagos - ' + error.message + '</option>';
        });
});

// Función auxiliar para convertir números a texto ordinal
function convertirNumeroPagoTexto(numeroPago) {
    const conversion = {
        1: 'Primer',
        2: 'Segundo',
        3: 'Tercero',
        4: 'Cuarto',
        5: 'Quinto',
        6: 'Sexto',
        7: 'Séptimo',
        8: 'Octavo',
        9: 'Noveno',
        10: 'Décimo',
        11: 'Décimo Primero',
        12: 'Décimo Segundo'
    };
    return conversion[numeroPago] || numeroPago + 'º';
}

$('#reporteActividadesForm').submit(function(e) {
    // Aquí puedes añadir validación de fechas si es necesario (ej: inicio < fin)

    // El target="_blank" ya manejará la descarga.
    
    // Ocultar el modal después de enviar el formulario para mejor UX
    $('#reporteActividadesModal').modal('hide');
    
    // El formulario se enviará de forma estándar (POST)
});