import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('fullcalendar');
    if (!el) return;

    const eventsUrl = el.dataset.eventsUrl;
    const modalStore = window._calendarModal;

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        height: 'auto',
        editable: false,
        selectable: true,
        events: eventsUrl,

        // Click on empty date → open create modal
        dateClick: function (info) {
            if (!modalStore) return;
            modalStore.openCreate(info.dateStr, info.allDay);
        },

        // Click on existing event → open detail modal
        eventClick: function (info) {
            if (!modalStore) return;
            const p = info.event.extendedProps;
            modalStore.openDetail({
                id: info.event.id,
                title: info.event.title,
                start: info.event.startStr,
                end: info.event.endStr,
                allDay: info.event.allDay,
                description: p.description,
                category: p.category,
                created_by: p.created_by,
                marketing_request_id: p.marketing_request_id,
                google_start: p.google_start,
                google_end: p.google_end,
                color: info.event.backgroundColor,
            });
        },
    });

    calendar.render();

    // Expose so Alpine can call refetchEvents after form submit
    window._fcInstance = calendar;
});
