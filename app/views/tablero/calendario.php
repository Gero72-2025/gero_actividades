<?php require APPROOT . '/views/layouts/header.php'; ?>
<?php
$tableroActual = $data['tableroActual'] ?? null;
$idTableroActual = $tableroActual ? (int)$tableroActual->Id_tablero : 0;
$tableroParam = $idTableroActual > 0 ? ('?tablero_id=' . $idTableroActual) : '';
$calendarEvents = $data['calendarEvents'] ?? [];
?>

<div class="mb-3">
    <h1 class="m-0"><?php echo $data['title']; ?></h1>
</div>

<?php echo displayFlashMessage('tablero_message'); ?>
<?php echo displayFlashMessage('tablero_error'); ?>

<div class="card mb-3">
    <div class="card-body py-2">
        <ul class="nav nav-pills tablero-nav-tabs flex-wrap gap-2">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo URLROOT; ?>/tablero/index<?php echo $tableroParam; ?>">
                    <i class="bi bi-kanban"></i> Tablero
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo URLROOT; ?>/tablero/dashboard<?php echo $tableroParam; ?>">
                    <i class="bi bi-graph-up-arrow"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="<?php echo URLROOT; ?>/tablero/calendario<?php echo $tableroParam; ?>">
                    <i class="bi bi-calendar3"></i> Calendario
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo URLROOT; ?>/tablero/reporteria<?php echo $tableroParam; ?>">
                    <i class="bi bi-table"></i> Reporte
                </a>
            </li>
        </ul>
    </div>
</div>

<?php if(!empty($data['tableros'])): ?>
    <form action="<?php echo URLROOT; ?>/tablero/calendario" method="get" class="card card-body mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label">Tablero activo</label>
                <?php if(count($data['tableros']) > 1): ?>
                    <select name="tablero_id" class="form-select tablero-activo-select" onchange="this.form.submit()">
                        <?php foreach($data['tableros'] as $tb): ?>
                            <option value="<?php echo (int)$tb->Id_tablero; ?>" <?php echo ((int)$tb->Id_tablero === $idTableroActual) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tb->Nombre); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['tableros'][0]->Nombre ?? 'Tablero'); ?>" readonly>
                <?php endif; ?>
            </div>
            <div class="col-12 col-md-7">
                <label class="form-label">Descripcion</label>
                <div class="form-control bg-light"><?php echo $tableroActual && !empty($tableroActual->Descripcion) ? htmlspecialchars($tableroActual->Descripcion) : 'Sin descripcion'; ?></div>
            </div>
        </div>
    </form>

    <section class="tablero-calendar-layout">
        <aside class="tablero-calendar-detail card">
            <div class="card-body">
                <div class="tablero-calendar-kicker">Vista de trazabilidad</div>
                <h2 class="tablero-calendar-date-title" id="tableroCalendarSelectedDate">-</h2>
                <p class="tablero-calendar-date-subtitle">Tarjetas con fecha de inicio, fin o rango completo.</p>
                <div class="tablero-calendar-summary" id="tableroCalendarSummary"></div>
                <hr>
                <div class="tablero-calendar-day-events" id="tableroCalendarDayEvents"></div>
            </div>
        </aside>

        <div class="tablero-calendar-main card">
            <div class="card-body">
                <div class="tablero-calendar-header">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="tableroCalendarPrevMonth">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="text-center">
                        <div class="tablero-calendar-month" id="tableroCalendarCurrentMonth">-</div>
                        <div class="tablero-calendar-year" id="tableroCalendarCurrentYear">-</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="tableroCalendarNextMonth">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>

                <div class="tablero-calendar-grid tablero-calendar-daynames">
                    <div>LUN</div><div>MAR</div><div>MIE</div><div>JUE</div><div>VIE</div>
                </div>
                <div class="tablero-calendar-grid tablero-calendar-days" id="tableroCalendarDays"></div>

                <div class="tablero-calendar-legend mt-3">
                    <span class="badge bg-success-subtle border text-success-emphasis">Inicio</span>
                    <span class="badge bg-danger-subtle border text-danger-emphasis">Fin</span>
                    <span class="badge bg-primary-subtle border text-primary-emphasis">Rango</span>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="tableroCalendarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-card-checklist"></i> Detalle de tarjeta</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h4 class="mb-1" id="tableroCalendarModalTitle">-</h4>
                    <p class="text-muted mb-3" id="tableroCalendarModalDescription">-</p>
                    <div class="row g-2">
                        <div class="col-md-6"><strong>Tipo de evento:</strong> <span id="tableroCalendarModalType">-</span></div>
                        <div class="col-md-6"><strong>Columna:</strong> <span id="tableroCalendarModalColumn">-</span></div>
                        <div class="col-md-6"><strong>Inicio:</strong> <span id="tableroCalendarModalStart">-</span></div>
                        <div class="col-md-6"><strong>Fin:</strong> <span id="tableroCalendarModalEnd">-</span></div>
                        <div class="col-md-6"><strong>Prioridad:</strong> <span id="tableroCalendarModalPriority">-</span></div>
                        <div class="col-md-6"><strong>Asignado:</strong> <span id="tableroCalendarModalAssigned">-</span></div>
                        <div class="col-12"><strong>Actividad:</strong> <span id="tableroCalendarModalActivity">-</span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-outline-primary" id="tableroCalendarModalBoardLink">
                        <i class="bi bi-kanban"></i> Ir al tablero
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-warning">No hay tableros asignados para mostrar el calendario.</div>
<?php endif; ?>

<script>
(function(){
    const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
    const events = <?php echo json_encode($calendarEvents, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const urlRoot = <?php echo json_encode(URLROOT); ?>;

    const daysContainer = document.getElementById('tableroCalendarDays');
    if(!daysContainer){
        return;
    }

    const elSelectedDate = document.getElementById('tableroCalendarSelectedDate');
    const elDayEvents = document.getElementById('tableroCalendarDayEvents');
    const elSummary = document.getElementById('tableroCalendarSummary');
    const elMonth = document.getElementById('tableroCalendarCurrentMonth');
    const elYear = document.getElementById('tableroCalendarCurrentYear');

    const detailModalEl = document.getElementById('tableroCalendarModal');
    const modalTitle = document.getElementById('tableroCalendarModalTitle');
    const modalDescription = document.getElementById('tableroCalendarModalDescription');
    const modalType = document.getElementById('tableroCalendarModalType');
    const modalColumn = document.getElementById('tableroCalendarModalColumn');
    const modalStart = document.getElementById('tableroCalendarModalStart');
    const modalEnd = document.getElementById('tableroCalendarModalEnd');
    const modalPriority = document.getElementById('tableroCalendarModalPriority');
    const modalAssigned = document.getElementById('tableroCalendarModalAssigned');
    const modalActivity = document.getElementById('tableroCalendarModalActivity');
    const modalBoardLink = document.getElementById('tableroCalendarModalBoardLink');

    let currentDate = new Date();
    let selectedDate = formatDateKey(currentDate);

    function formatDateKey(dateObj){
        const y = dateObj.getFullYear();
        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dateObj.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    function parseDate(dateStr){
        return new Date(dateStr + 'T00:00:00');
    }

    function isWeekday(dateObj){
        const day = dateObj.getDay();
        return day >= 1 && day <= 5;
    }

    function weekdayColumn(dateObj){
        const day = dateObj.getDay();
        if(day < 1 || day > 5) return -1;
        return day - 1;
    }

    function formatDisplayDate(dateStr){
        const dateObj = parseDate(dateStr);
        if(Number.isNaN(dateObj.getTime())) return dateStr;
        return dayNames[dateObj.getDay()] + ', ' + dateObj.getDate() + ' de ' + monthNames[dateObj.getMonth()] + ' de ' + dateObj.getFullYear();
    }

    function escapeHtml(value){
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function eventTypeLabel(type){
        if(type === 'range') return 'Rango completo';
        if(type === 'start') return 'Fecha de inicio';
        if(type === 'end') return 'Fecha de fin';
        return 'Fecha registrada';
    }

    function eventChipLabel(item){
        if(item.dayType === 'start') return 'Inicio';
        if(item.dayType === 'end') return 'Fin';
        return 'Rango';
    }

    function eventLineClass(entry){
        if(entry.segmentType === 'single') return 'line-single';
        if(entry.segmentType === 'start') return 'line-start';
        if(entry.segmentType === 'end') return 'line-end';
        return 'line-middle';
    }

    function assignEventLanes(baseEvents){
        const sorted = (baseEvents || []).slice().sort(function(left, right){
            const leftStart = String(left.start_date || '');
            const rightStart = String(right.start_date || '');
            if(leftStart !== rightStart){
                return leftStart.localeCompare(rightStart);
            }

            const leftEnd = String(left.end_date || leftStart);
            const rightEnd = String(right.end_date || rightStart);
            return rightEnd.localeCompare(leftEnd);
        });

        const laneEnd = [];
        const laneByEventId = {};

        sorted.forEach(function(event){
            const eventId = Number(event.id_tarjeta || 0);
            const start = String(event.start_date || '');
            const end = String(event.end_date || start);

            let assignedLane = -1;
            for(let lane = 0; lane < laneEnd.length; lane++){
                if(start > laneEnd[lane]){
                    assignedLane = lane;
                    break;
                }
            }

            if(assignedLane === -1){
                assignedLane = laneEnd.length;
                laneEnd.push(end);
            } else {
                laneEnd[assignedLane] = end;
            }

            laneByEventId[eventId] = assignedLane;
        });

        return {
            laneByEventId: laneByEventId,
            laneCount: Math.max(1, laneEnd.length)
        };
    }

    function shouldRepeatLabel(dateKey){
        const dateObj = parseDate(dateKey);
        if(Number.isNaN(dateObj.getTime())) return false;

        const dayOfMonth = dateObj.getDate();
        const dayOfWeek = dateObj.getDay();
        return dayOfWeek === 1 || dayOfMonth === 1 || dayOfMonth % 10 === 0;
    }

    function expandEventsToDays(baseEvents, laneByEventId){
        const map = {};

        baseEvents.forEach(function(event){
            const start = event.start_date;
            const end = event.end_date;
            if(!start || !end){
                return;
            }

            let cursor = parseDate(start);
            const finalDate = parseDate(end);
            if(Number.isNaN(cursor.getTime()) || Number.isNaN(finalDate.getTime())){
                return;
            }

            while(cursor <= finalDate){
                const key = formatDateKey(cursor);
                if(!map[key]){
                    map[key] = [];
                }

                let dayType = 'range';
                if(start === end){
                    dayType = event.event_type === 'start' ? 'start' : (event.event_type === 'end' ? 'end' : 'range');
                } else if(key === start){
                    dayType = 'start';
                } else if(key === end){
                    dayType = 'end';
                }

                map[key].push({
                    event: event,
                    dayType: dayType,
                    segmentType: dayType,
                    showLabel: dayType === 'start' || (start === end),
                    lane: Number((laneByEventId || {})[Number(event.id_tarjeta || 0)] || 0)
                });

                cursor.setDate(cursor.getDate() + 1);
            }
        });

        Object.keys(map).forEach(function(key){
            map[key].sort(function(left, right){
                return (right.event.prioridad_valor || 0) - (left.event.prioridad_valor || 0);
            });
        });

        Object.keys(map).forEach(function(key){
            map[key].forEach(function(entry){
                const eventId = Number(entry.event.id_tarjeta || 0);
                const current = key;
                const prevDate = parseDate(current);
                prevDate.setDate(prevDate.getDate() - 1);
                const nextDate = parseDate(current);
                nextDate.setDate(nextDate.getDate() + 1);
                const prevKey = formatDateKey(prevDate);
                const nextKey = formatDateKey(nextDate);

                const hasPrev = (map[prevKey] || []).some(function(prevEntry){
                    return Number(prevEntry.event.id_tarjeta || 0) === eventId;
                });
                const hasNext = (map[nextKey] || []).some(function(nextEntry){
                    return Number(nextEntry.event.id_tarjeta || 0) === eventId;
                });

                if(!hasPrev && !hasNext){
                    entry.segmentType = 'single';
                } else if(!hasPrev && hasNext){
                    entry.segmentType = 'start';
                } else if(hasPrev && !hasNext){
                    entry.segmentType = 'end';
                } else {
                    entry.segmentType = 'middle';
                }

                entry.showLabel =
                    entry.segmentType === 'start' ||
                    entry.segmentType === 'end' ||
                    entry.segmentType === 'single' ||
                    shouldRepeatLabel(key);
            });

            map[key].sort(function(left, right){
                return Number(left.lane || 0) - Number(right.lane || 0);
            });
        });

        return map;
    }

    const laneInfo = assignEventLanes(events);
    const totalLanes = Math.max(1, Number(laneInfo.laneCount || 1));
    const eventsByDay = expandEventsToDays(events, laneInfo.laneByEventId || {});

    function renderSummary(){
        let withRange = 0;
        let withStart = 0;
        let withEnd = 0;

        events.forEach(function(item){
            if(item.fecha_inicio && item.fecha_fin){
                withRange++;
            } else if(item.fecha_inicio){
                withStart++;
            } else if(item.fecha_fin){
                withEnd++;
            }
        });

        elSummary.innerHTML = '' +
            '<div class="small text-muted mb-2">Resumen del tablero</div>' +
            '<div class="d-flex flex-column gap-1">' +
                '<span class="badge bg-primary-subtle text-primary-emphasis border">Tarjetas con trazabilidad: ' + withRange + '</span>' +
                '<span class="badge bg-success-subtle text-success-emphasis border">Solo inicio: ' + withStart + '</span>' +
                '<span class="badge bg-danger-subtle text-danger-emphasis border">Solo fin: ' + withEnd + '</span>' +
            '</div>';
    }

    function renderCalendar(){
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        elMonth.textContent = monthNames[month];
        elYear.textContent = String(year);

        daysContainer.innerHTML = '';
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const weekdaysInMonth = [];
        for(let day = 1; day <= daysInMonth; day++){
            const dateObj = new Date(year, month, day);
            if(isWeekday(dateObj)){
                weekdaysInMonth.push(dateObj);
            }
        }

        if(weekdaysInMonth.length === 0){
            elSelectedDate.textContent = 'Sin dias habiles en este mes';
            elDayEvents.innerHTML = '<div class="alert alert-light border mb-0">No hay dias habiles disponibles en este mes.</div>';
            return;
        }

        const monthWeekdayKeys = weekdaysInMonth.map(function(dateObj){
            return formatDateKey(dateObj);
        });

        if(monthWeekdayKeys.indexOf(selectedDate) === -1){
            selectedDate = monthWeekdayKeys[0];
        }

        const firstWeekday = weekdaysInMonth[0];
        const leadingPads = Math.max(0, weekdayColumn(firstWeekday));

        for(let i = 0; i < leadingPads; i++){
            const pad = document.createElement('div');
            pad.className = 'tablero-calendar-day tablero-calendar-day--pad';
            daysContainer.appendChild(pad);
        }

        weekdaysInMonth.forEach(function(dateObj){
            const dateKey = formatDateKey(dateObj);
            const entries = eventsByDay[dateKey] || [];

            const cell = document.createElement('div');
            cell.className = 'tablero-calendar-day';
            if(dateKey === selectedDate){
                cell.classList.add('is-selected');
            }
            if(entries.length > 0){
                cell.classList.add('has-events');
            }

            const number = document.createElement('div');
            number.className = 'tablero-calendar-day__number';
            number.textContent = String(dateObj.getDate());
            cell.appendChild(number);

            const badges = document.createElement('div');
            badges.className = 'tablero-calendar-day__badges';

            cell.style.setProperty('--lane-count', String(totalLanes));

            const lanes = new Array(totalLanes).fill(null);
            entries.forEach(function(entry){
                const laneIndex = Number(entry.lane || 0);
                if(laneIndex >= 0 && laneIndex < totalLanes){
                    lanes[laneIndex] = entry;
                }
            });

            lanes.forEach(function(entry){
                if(!entry){
                    const emptyLine = document.createElement('div');
                    emptyLine.className = 'tablero-calendar-line tablero-calendar-line--empty';
                    badges.appendChild(emptyLine);
                    return;
                }

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'tablero-calendar-line ' + eventLineClass(entry);
                button.title = (entry.event.titulo || 'Tarjeta') + ' (' + eventTypeLabel(entry.event.event_type) + ')';

                const nameSpan = document.createElement('span');
                nameSpan.className = 'tablero-calendar-line__label';
                nameSpan.textContent = entry.showLabel ? (entry.event.titulo || 'Tarjeta') : '';
                button.appendChild(nameSpan);

                button.addEventListener('click', function(e){
                    e.stopPropagation();
                    openEventModal(entry.event, entry.dayType);
                });
                badges.appendChild(button);
            });

            cell.appendChild(badges);
            cell.addEventListener('click', function(){
                selectedDate = dateKey;
                renderCalendar();
                renderSelectedDay();
            });
            daysContainer.appendChild(cell);

        });

        const totalCells = leadingPads + weekdaysInMonth.length;
        const trailingPads = (5 - (totalCells % 5)) % 5;
        for(let i = 0; i < trailingPads; i++){
            const pad = document.createElement('div');
            pad.className = 'tablero-calendar-day tablero-calendar-day--pad';
            daysContainer.appendChild(pad);
        }
    }

    function renderSelectedDay(){
        const entries = eventsByDay[selectedDate] || [];
        elSelectedDate.textContent = formatDisplayDate(selectedDate);

        if(entries.length === 0){
            elDayEvents.innerHTML = '<div class="alert alert-light border mb-0">No hay tarjetas con fechas registradas para este dia.</div>';
            return;
        }

        let html = '<div class="list-group">';
        entries.forEach(function(entry){
            const item = entry.event;
            html += '' +
                '<button type="button" class="list-group-item list-group-item-action tablero-calendar-day-item" data-card-id="' + item.id_tarjeta + '">' +
                    '<div class="d-flex justify-content-between align-items-start gap-2">' +
                        '<div>' +
                            '<div class="fw-semibold">' + escapeHtml(item.titulo || 'Tarjeta') + '</div>' +
                            '<div class="small text-muted">' + escapeHtml(item.columna || 'Columna') + ' | ' + escapeHtml(eventTypeLabel(item.event_type)) + '</div>' +
                        '</div>' +
                        '<span class="badge text-bg-light border">' + escapeHtml(eventChipLabel(entry)) + '</span>' +
                    '</div>' +
                '</button>';
        });
        html += '</div>';

        elDayEvents.innerHTML = html;
        elDayEvents.querySelectorAll('.tablero-calendar-day-item').forEach(function(button, index){
            button.addEventListener('click', function(){
                openEventModal(entries[index].event, entries[index].dayType);
            });
        });
    }

    function openEventModal(item, dayType){
        modalTitle.textContent = item.titulo || 'Tarjeta';
        modalDescription.textContent = item.descripcion || 'Sin descripcion';
        modalType.textContent = eventTypeLabel(item.event_type) + ' (' + eventChipLabel({ dayType: dayType }) + ')';
        modalColumn.textContent = item.columna || '-';
        modalStart.textContent = item.fecha_inicio || '-';
        modalEnd.textContent = item.fecha_fin || '-';
        modalPriority.textContent = (item.prioridad || 'Sin prioridad') + ' (' + String(item.prioridad_valor || 0) + ')';
        modalAssigned.textContent = item.asignado || 'Sin asignar';
        modalActivity.textContent = item.actividad || 'Sin actividad vinculada';

        modalBoardLink.href = urlRoot + '/tablero/index?tablero_id=' + item.id_tablero + '#tarjeta-' + item.id_tarjeta;

        if(window.jQuery && window.jQuery.fn && window.jQuery.fn.modal){
            window.jQuery(detailModalEl).modal('show');
            return;
        }
        if(window.bootstrap && window.bootstrap.Modal){
            window.bootstrap.Modal.getOrCreateInstance(detailModalEl).show();
        }
    }

    document.getElementById('tableroCalendarPrevMonth').addEventListener('click', function(){
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    document.getElementById('tableroCalendarNextMonth').addEventListener('click', function(){
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    renderSummary();
    renderCalendar();
    renderSelectedDay();
})();
</script>

<?php require APPROOT . '/views/layouts/footer.php'; ?>