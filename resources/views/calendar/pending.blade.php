<x-app-layout>
    @section('title', 'My Calendar Submissions')

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('calendar.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-800">My Calendar Submissions</h1>
                <p class="text-sm text-gray-500 mt-0.5">Track approval status of events you submitted</p>
            </div>
        </div>
    </x-slot>

    <div class="py-4">

        @if ($events->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
                <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-400 text-sm">No pending or rejected submissions.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($events as $event)
                    @php
                        $statusConfig = [
                            'pending_manager' => [
                                'label' => 'Awaiting Manager',
                                'bg' => 'bg-yellow-100',
                                'text' => 'text-yellow-800',
                            ],
                            'pending_gm_director' => [
                                'label' => 'Awaiting GM/Director',
                                'bg' => 'bg-blue-100',
                                'text' => 'text-blue-800',
                            ],
                            'rejected' => ['label' => 'Rejected', 'bg' => 'bg-red-100', 'text' => 'text-red-800'],
                        ];
                        $sc = $statusConfig[$event->status] ?? [
                            'label' => $event->status,
                            'bg' => 'bg-gray-100',
                            'text' => 'text-gray-800',
                        ];
                    @endphp
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5" x-data="{ editOpen: false }">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <span class="w-3 h-3 rounded-full flex-shrink-0 mt-1.5"
                                    style="background:{{ $event->color }}"></span>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $event->title }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $event->start_datetime->format('d M Y' . ($event->all_day ? '' : ', H:i')) }}
                                        @if ($event->end_datetime)
                                            →
                                            {{ $event->end_datetime->format('d M Y' . ($event->all_day ? '' : ', H:i')) }}
                                        @endif
                                    </p>
                                    @if ($event->description)
                                        <p class="text-xs text-gray-400 mt-1">{{ Str::limit($event->description, 100) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <span
                                class="flex-shrink-0 text-xs font-medium px-2.5 py-1 rounded-full {{ $sc['bg'] }} {{ $sc['text'] }}">
                                {{ $sc['label'] }}
                            </span>
                        </div>

                        {{-- Rejection reason --}}
                        @if ($event->status === 'rejected' && $event->rejection_reason)
                            <div
                                class="mt-3 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 text-xs text-red-700">
                                <span class="font-semibold">Rejection reason:</span> {{ $event->rejection_reason }}
                            </div>
                        @endif

                        {{-- Approval trail --}}
                        @if ($event->approvals->isNotEmpty())
                            <div class="mt-3 space-y-1">
                                @foreach ($event->approvals as $apv)
                                    <p class="text-xs text-gray-500">
                                        <span
                                            class="font-medium {{ $apv->status === 'approved' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ ucfirst($apv->status) }}
                                        </span>
                                        by {{ $apv->approver?->name }} — {{ $apv->acted_at->format('d M Y, H:i') }}
                                    </p>
                                @endforeach
                            </div>
                        @endif

                        {{-- Resubmit form (rejected only) --}}
                        @if ($event->status === 'rejected')
                            <div class="mt-3">
                                <button @click="editOpen = !editOpen"
                                    class="text-sm text-blue-600 hover:underline font-medium">
                                    Edit &amp; Resubmit
                                </button>
                                <div x-show="editOpen" x-cloak class="mt-3 border-t border-gray-100 pt-3">
                                    <form method="POST" action="{{ route('calendar.update', $event) }}">
                                        @csrf @method('PATCH')
                                        <div class="grid grid-cols-2 gap-3 mb-3">
                                            <div class="col-span-2">
                                                <label
                                                    class="block text-xs font-medium text-gray-700 mb-1">Title</label>
                                                <input type="text" name="title" required
                                                    value="{{ $event->title }}"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-2 focus:ring-[#1D3557]/30">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 mb-1">Category</label>
                                                <select name="category"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-2 focus:ring-[#1D3557]/30">
                                                    @foreach (\App\Models\CalendarEvent::categoryLabels() as $key => $label)
                                                        <option value="{{ $key }}"
                                                            {{ $event->category === $key ? 'selected' : '' }}>
                                                            {{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="flex items-end">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" name="all_day" value="1"
                                                        {{ $event->all_day ? 'checked' : '' }}
                                                        class="w-4 h-4 rounded border-gray-300 text-[#1D3557]">
                                                    <span class="text-sm text-gray-700">All-day</span>
                                                </label>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Start
                                                    Date</label>
                                                <input type="date" name="start_date" required
                                                    value="{{ $event->start_datetime->format('Y-m-d') }}"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-2 focus:ring-[#1D3557]/30">
                                            </div>
                                            @if (!$event->all_day)
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Start
                                                        Time</label>
                                                    <input type="time" name="start_time"
                                                        value="{{ $event->start_datetime->format('H:i') }}"
                                                        class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-2 focus:ring-[#1D3557]/30">
                                                </div>
                                            @endif
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">End
                                                    Date</label>
                                                <input type="date" name="end_date"
                                                    value="{{ $event->end_datetime?->format('Y-m-d') }}"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-2 focus:ring-[#1D3557]/30">
                                            </div>
                                            @if (!$event->all_day)
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">End
                                                        Time</label>
                                                    <input type="time" name="end_time"
                                                        value="{{ $event->end_datetime?->format('H:i') }}"
                                                        class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-2 focus:ring-[#1D3557]/30">
                                                </div>
                                            @endif
                                            <div class="col-span-2">
                                                <label
                                                    class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                                                <textarea name="description" rows="2"
                                                    class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm outline-none resize-none focus:ring-2 focus:ring-[#1D3557]/30">{{ $event->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="submit"
                                                class="px-4 py-1.5 bg-[#1D3557] text-white text-xs font-semibold rounded-lg hover:bg-[#162840]">
                                                Resubmit for Approval
                                            </button>
                                            <button type="button" @click="editOpen = false"
                                                class="px-4 py-1.5 border border-gray-200 text-gray-600 text-xs rounded-lg hover:bg-gray-50">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
