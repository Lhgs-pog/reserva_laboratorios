/**
 * Inicialização compartilhada do FullCalendar (LabHub).
 */
(function (global) {
    'use strict';

    function statusLabel(estado) {
        if (estado === 'pendente') return 'Aguardando aprovação';
        if (estado === 'rejeitado') return 'Rejeitada';
        if (estado === 'aprovado') return 'Aprovada';
        return '';
    }

    function formatEventContent(arg) {
        var isGrid = arg.view.type === 'dayGridMonth';
        var truncateClass = isGrid ? 'text-truncate-multi' : 'text-truncate-single';
        var props = arg.event.extendedProps || {};
        var timeHtml = arg.timeText
            ? '<div class="lh-cal-event-time">' + arg.timeText + '</div>'
            : '';
        var titleHtml = '<div class="lh-cal-event-title ' + truncateClass + '">' + arg.event.title + '</div>';
        var statusHtml = '';
        var estado = props.estado || '';
        if (estado && estado !== 'aprovado') {
            statusHtml = '<div class="lh-cal-event-status">' + statusLabel(estado) + '</div>';
        }
        var localHtml = '';
        if (!isGrid && props.local) {
            localHtml = '<div class="lh-cal-event-local ' + truncateClass + '">' + props.local + '</div>';
        }
        var content = document.createElement('div');
        content.style.width = '100%';
        content.style.overflow = 'hidden';
        content.innerHTML = timeHtml + titleHtml + statusHtml + localHtml;

        var tooltipText = arg.event.title;
        if (estado) tooltipText += ' (' + statusLabel(estado) + ')';
        if (props.local) {
            tooltipText += ' — ' + String(props.local).replace(/<[^>]*>?/gm, '');
        }
        content.title = tooltipText;
        return { domNodes: [content] };
    }

    function applyDiaEspecial(info, mapaDatas) {
        if (!mapaDatas || !info.date) return;
        var y = info.date.getFullYear();
        var m = String(info.date.getMonth() + 1).padStart(2, '0');
        var d = String(info.date.getDate()).padStart(2, '0');
        var key = y + '-' + m + '-' + d;
        var meta = mapaDatas[key];
        if (!meta) return;
        if (meta.tipo === 'feriado') {
            info.el.classList.add('lh-cal-dia-feriado');
        } else if (meta.tipo === 'facultativo') {
            info.el.classList.add('lh-cal-dia-facultativo');
        }
    }

    function badgeTipoEvento(event) {
        var classes = event.classNames || [];
        if (classes.includes('apple-event-fixa')) {
            return '<span class="lh-badge lh-badge-fixa">Aula fixa</span>';
        }
        if (classes.includes('apple-event-feriado')) {
            return '<span class="lh-badge lh-badge-feriado">Feriado</span>';
        }
        if (classes.includes('apple-event-facultativo')) {
            return '<span class="lh-badge lh-badge-facultativo">Ponto facultativo (DF)</span>';
        }
        if (classes.includes('apple-event-pendente')) {
            return '<span class="lh-badge lh-badge-pendente">Aguardando aprovação</span>';
        }
        if (classes.includes('apple-event-rejeitado')) {
            return '<span class="lh-badge lh-badge-rejeitada">Rejeitada</span>';
        }
        return '<span class="lh-badge lh-badge-avulsa">Reserva avulsa</span>';
    }

    function abrirModalDetalhe(arg) {
        var titulo = document.getElementById('modalDetalheTitulo');
        var corpo = document.getElementById('modalDetalheCorpo');
        var modalEl = document.getElementById('modalDetalheEvento');
        if (!titulo || !corpo || !modalEl) return;

        titulo.innerHTML = '<i class="bi bi-calendar2-event me-2"></i>' + arg.event.title;
        var dataStr = arg.event.start.toLocaleDateString('pt-BR');
        var horaInicio = arg.event.start.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        var horaFim = arg.event.end
            ? arg.event.end.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
            : '';
        var horarioStr = arg.event.allDay ? 'Dia inteiro' : horaInicio + ' às ' + horaFim;
        var localStr = (arg.event.extendedProps && arg.event.extendedProps.local)
            ? arg.event.extendedProps.local
            : '<i class="bi bi-geo-alt me-1"></i> Local não definido';

        corpo.innerHTML =
            '<div class="mb-3"><strong class="text-secondary"><i class="bi bi-clock me-1"></i> Data e horário:</strong><br>' +
            '<span class="fs-6 fw-bold">' + dataStr + '</span> &nbsp;|&nbsp; <span class="fs-6">' + horarioStr + '</span></div>' +
            '<div class="mb-3"><strong class="text-secondary"><i class="bi bi-geo-alt me-1"></i> Localização:</strong><br>' +
            '<span class="fs-6">' + localStr + '</span></div>' +
            '<div class="mb-2"><strong class="text-secondary"><i class="bi bi-tag me-1"></i> Categoria:</strong><br>' +
            badgeTipoEvento(arg.event) + '</div>';

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function initLabhubCalendar(el, options) {
        if (!el || typeof FullCalendar === 'undefined') return null;

        options = options || {};
        var mapaDatas = options.mapaDatas || {};
        var isMobile = window.innerWidth < 768;

        var config = {
            locale: 'pt-br',
            initialView: isMobile ? 'listWeek' : 'dayGridMonth',
            navLinks: true,
            nowIndicator: true,
            dayMaxEvents: true,
            moreLinkText: function (n) { return '+' + n + ' mais'; },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            buttonText: { today: 'Hoje', month: 'Mês', week: 'Semana', day: 'Dia', list: 'Lista' },
            events: options.events || [],
            slotMinTime: '08:00:00',
            slotMaxTime: '23:30:00',
            allDaySlot: true,
            expandRows: true,
            eventOrder: '-allDay,title',
            height: 'auto',
            eventContent: formatEventContent,
            dayCellDidMount: function (info) {
                applyDiaEspecial(info, mapaDatas);
            },
            windowResize: function () {
                var cal = this;
                if (window.innerWidth < 768) {
                    cal.changeView('listWeek');
                } else if (cal.view.type === 'listWeek') {
                    cal.changeView('dayGridMonth');
                }
            }
        };

        if (typeof options.eventClick === 'function') {
            config.eventClick = options.eventClick;
        } else if (options.comModalDetalhe) {
            config.eventClick = function (arg) {
                arg.jsEvent.preventDefault();
                abrirModalDetalhe(arg);
            };
        }

        var cal = new FullCalendar.Calendar(el, config);
        cal.render();
        return cal;
    }

    global.initLabhubCalendar = initLabhubCalendar;
    global.labhubCalendarEventContent = formatEventContent;
})(window);
