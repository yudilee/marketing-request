<x-app-layout>
    @section('title', 'Calendar')

    @push('head')
        @vite('resources/js/calendar.js')
        <style>
            /* FullCalendar theme overrides */
            .fc .fc-toolbar-title {
                font-size: 1rem;
                font-weight: 600;
                color: #1D3557;
            }

            .fc .fc-button {
                background: #1D3557 !important;
                border-color: #1D3557 !important;
                font-size: .75rem !important;
                padding: .3rem .75rem !important;
            }

            .fc .fc-button:hover {
                background: #162840 !important;
            }

            .fc .fc-button-active {
                background: #457B9D !important;
                border-color: #457B9D !important;
            }

            .fc .fc-daygrid-event {
                border-radius: 4px;
                font-size: .72rem;
                padding: 1px 4px;
            }

            .fc .fc-daygrid-day-number {
                font-size: .78rem;
                color: #374151;
            }

            .fc .fc-col-header-cell-cushion {
                font-size: .75rem;
                font-weight: 600;
                color: #6B7280;
                text-transform: uppercase;
                letter-spacing: .05em;
            }

            .fc .fc-day-today {
                background: #EFF6FF !important;
            }

            .fc-theme-standard td,
            .fc-theme-standard th {
                border-color: #F3F4F6;
            }
        </style>
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Calendar</h1>
                <p class="text-sm text-gray-500 mt-0.5">Marcom event planner — all approved events shown here</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('calendar.pending') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    My Submissions
                </a>
                <button type="button" onclick="window._calendarModal && window._calendarModal.openCreate(null, false)"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#1D3557] text-white text-sm font-semibold rounded-lg hover:bg-[#162840] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Event
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-4" x-data="calendarModal()" x-init="init()" @keydown.escape.window="closeAll()">

        {{-- Flash messages handled by layout --}}

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-4 mb-4">
            @foreach ($categories as $key => $label)
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full" style="background:{{ $colors[$key] }}"></span>
                    <span class="text-xs text-gray-500">{{ $label }}</span>
                </div>
            @endforeach
        </div>

        {{-- Calendar --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div id="fullcalendar" data-events-url="{{ route('calendar.events') }}"></div>
        </div>

        {{-- ===================== CREATE EVENT MODAL ===================== --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div class="absolute inset-0 bg-black/40" @click="createOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-800">New Calendar Event</h2>
                    <button @click="createOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('calendar.store') }}" class="px-6 py-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Title <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="title" required maxlength="200"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1D3557]/30 focus:border-[#1D3557] outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Category <span
                                    class="text-red-500">*</span></label>
                            <select name="category" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1D3557]/30 focus:border-[#1D3557] outline-none">
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="all_day" value="1" x-model="isAllDay"
                                    class="w-4 h-4 rounded border-gray-300 text-[#1D3557]">
                                <span class="text-sm text-gray-700">All-day event</span>
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Start Date <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="start_date" required :value="createDate"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1D3557]/30 focus:border-[#1D3557] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="end_date"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1D3557]/30 focus:border-[#1D3557] outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3" x-show="!isAllDay">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Start Time <span
                                    class="text-red-500">*</span></label>
                            <input type="time" name="start_time" :required="!isAllDay"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1D3557]/30 focus:border-[#1D3557] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">End Time</label>
                            <input type="time" name="end_time"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1D3557]/30 focus:border-[#1D3557] outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="2" maxlength="2000"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1D3557]/30 focus:border-[#1D3557] outline-none resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Link to Marketing Request <span
                                class="text-gray-400">(optional)</span></label>
                        <select name="marketing_request_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#1D3557]/30 focus:border-[#1D3557] outline-none">
                            <option value="">— none —</option>
                            @foreach ($requests as $mr)
                                <option value="{{ $mr->id }}">#{{ str_pad($mr->id, 4, '0', STR_PAD_LEFT) }}
                                    {{ Str::limit($mr->purpose, 60) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-xs text-blue-700">
                        <strong>Note:</strong> This event will be sent to a Manager for approval, then a GM or Director
                        for final sign-off before appearing on the calendar.
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="createOpen = false"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Cancel</button>
                        <button type="submit"
                            class="px-5 py-2 bg-[#1D3557] text-white text-sm font-semibold rounded-lg hover:bg-[#162840] transition-colors">
                            Submit for Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== EVENT DETAIL MODAL ===================== --}}
        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div class="absolute inset-0 bg-black/40" @click="detailOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full flex-shrink-0" :style="`background:${detail.color}`"></span>
                        <h2 class="text-base font-semibold text-gray-800" x-text="detail.title"></h2>
                    </div>
                    <button @click="detailOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-3">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span x-text="formatDateRange(detail)"></span>
                    </div>
                    <template x-if="detail.description">
                        <div class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2"
                            x-text="detail.description"></div>
                    </template>
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Created by <span class="font-medium text-gray-600"
                                x-text="detail.created_by"></span></span>
                    </div>
                    {{-- Add to calendar buttons --}}
                    <div class="grid grid-cols-2 gap-2">
                        {{-- Google Calendar --}}
                        <a :href="buildGoogleCalendarUrl(detail)" target="_blank" rel="noopener"
                            class="flex items-center justify-center gap-1.5 px-3 py-2.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none">
                                <rect x="3" y="4" width="18" height="18" rx="2" fill="#4285F4"
                                    opacity=".15" />
                                <path d="M3 9h18" stroke="#4285F4" stroke-width="1.5" />
                                <path d="M8 2v4M16 2v4" stroke="#4285F4" stroke-width="1.5" stroke-linecap="round" />
                                <text x="12" y="19" text-anchor="middle" font-size="7" fill="#4285F4"
                                    font-weight="bold">G</text>
                            </svg>
                            Google Calendar
                        </a>
                        {{-- Download .ics --}}
                        <a :href="'/calendar/' + detail.id + '/ical'"
                            class="flex items-center justify-center gap-1.5 px-3 py-2.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0 text-indigo-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Outlook / Apple (.ics)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function calendarModal() {
                return {
                    createOpen: false,
                    detailOpen: false,
                    isAllDay: false,
                    createDate: '',
                    detail: {},

                    init() {
                        // Expose to FullCalendar JS which runs after DOMContentLoaded
                        window._calendarModal = this;
                    },

                    openCreate(dateStr, allDay) {
                        this.createDate = dateStr ?? '';
                        this.isAllDay = allDay ?? false;
                        this.createOpen = true;
                        this.detailOpen = false;
                    },

                    openDetail(event) {
                        this.detail = event;
                        this.detailOpen = true;
                        this.createOpen = false;
                    },

                    closeAll() {
                        this.createOpen = false;
                        this.detailOpen = false;
                    },

                    formatDateRange(d) {
                        if (!d.start) return '';
                        const fmt = (s) => {
                            const dt = new Date(s);
                            return dt.toLocaleString('en-GB', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        };
                        const fmtDay = (s) => {
                            const dt = new Date(s);
                            return dt.toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });
                        };
                        if (d.allDay) return d.end ? `${fmtDay(d.start)} → ${fmtDay(d.end)}` : fmtDay(d.start);
                        return d.end ? `${fmt(d.start)} → ${fmt(d.end)}` : fmt(d.start);
                    },

                    buildGoogleCalendarUrl(d) {
                        if (!d.google_start) return '#';
                        const params = new URLSearchParams({
                            action: 'TEMPLATE',
                            text: d.title ?? '',
                            details: d.description ?? '',
                            dates: d.google_start + '/' + (d.google_end ?? d.google_start),
                        });
                        return 'https://calendar.google.com/calendar/render?' + params.toString();
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
