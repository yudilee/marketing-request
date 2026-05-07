<x-app-layout>
    @section('title', 'Calendar Event Approvals')

    <x-slot name="header">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">Calendar Event Approvals</h1>
            <p class="text-sm text-gray-500 mt-0.5">Events awaiting your sign-off before they appear on the calendar</p>
        </div>
    </x-slot>

    <div class="py-4">

        @if ($events->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
                <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-400 text-sm">No events pending your approval.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($events as $event)
                    <div x-data="{ panel: null }"
                        class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0 mt-1.5"
                                        style="background:{{ $event->color }}"></span>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $event->title }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ ucfirst(str_replace('_', ' ', \App\Models\CalendarEvent::categoryLabels()[$event->category] ?? $event->category)) }}
                                            &middot;
                                            {{ $event->start_datetime->format('d M Y' . ($event->all_day ? '' : ', H:i')) }}
                                            @if ($event->end_datetime)
                                                →
                                                {{ $event->end_datetime->format('d M Y' . ($event->all_day ? '' : ', H:i')) }}
                                            @endif
                                        </p>
                                        @if ($event->description)
                                            <p class="text-sm text-gray-600 mt-1.5">{{ $event->description }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-2">
                                            Submitted by <span
                                                class="font-medium text-gray-600">{{ $event->creator?->name }}</span>
                                            · {{ $event->created_at->diffForHumans() }}
                                        </p>
                                        @if ($event->marketingRequest)
                                            <p class="text-xs text-blue-600 mt-1">
                                                Linked to: <a
                                                    href="{{ route('requests.show', $event->marketingRequest) }}"
                                                    class="underline hover:text-blue-800">
                                                    #{{ str_pad($event->marketing_request_id, 4, '0', STR_PAD_LEFT) }}
                                                </a>
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Action buttons --}}
                                <div class="flex-shrink-0 flex gap-2">
                                    <button type="button" @click="panel = panel === 'approve' ? null : 'approve'"
                                        :class="panel === 'approve' ? 'bg-green-600 text-white' :
                                            'bg-green-50 border border-green-200 text-green-700 hover:bg-green-100'"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Approve
                                    </button>
                                    <button type="button" @click="panel = panel === 'reject' ? null : 'reject'"
                                        :class="panel === 'reject' ? 'bg-red-600 text-white' :
                                            'bg-red-50 border border-red-200 text-red-700 hover:bg-red-100'"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Reject
                                    </button>
                                </div>
                            </div>

                            {{-- Prior approvals trail --}}
                            @if ($event->approvals->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-3">
                                    @foreach ($event->approvals as $apv)
                                        <span
                                            class="text-xs {{ $apv->status === 'approved' ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50' }} px-2.5 py-1 rounded-full">
                                            ✓ {{ $apv->approver?->name }}
                                            ({{ ucfirst($apv->approver?->role instanceof \App\Enums\Role ? $apv->approver->role->value : $apv->approver?->role) }})
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Approve panel --}}
                        <div x-show="panel === 'approve'" x-cloak
                            class="border-t border-green-200 bg-green-50 px-6 py-4">
                            <form method="POST" action="{{ route('calendar.approvals.decide', $event) }}"
                                class="flex items-end gap-3">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-green-900 mb-1">Comment <span
                                            class="font-normal text-green-700">(optional)</span></label>
                                    <input type="text" name="comment" maxlength="1000"
                                        class="w-full border border-green-300 rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-2 focus:ring-green-300 bg-white">
                                </div>
                                <button type="submit"
                                    class="px-5 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors whitespace-nowrap">
                                    Confirm Approve
                                </button>
                            </form>
                        </div>

                        {{-- Reject panel --}}
                        <div x-show="panel === 'reject'" x-cloak class="border-t border-red-200 bg-red-50 px-6 py-4">
                            <form method="POST" action="{{ route('calendar.approvals.decide', $event) }}"
                                class="flex items-end gap-3">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-red-900 mb-1">Reason <span
                                            class="text-red-700">(required)</span></label>
                                    <input type="text" name="comment" required maxlength="1000"
                                        class="w-full border border-red-300 rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-2 focus:ring-red-300 bg-white">
                                </div>
                                <button type="submit"
                                    class="px-5 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors whitespace-nowrap">
                                    Confirm Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
