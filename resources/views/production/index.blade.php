<x-app-layout>
    @section('title', 'Production Queue')

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Production Queue</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage production for all approved requests</p>
            </div>
        </div>
    </x-slot>

    <div class="py-4">

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @php
                $summaries = [
                    [
                        'label' => 'Pending',
                        'key' => 'pending',
                        'color' => 'yellow',
                        'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    ],
                    [
                        'label' => 'On Process',
                        'key' => 'on_process',
                        'color' => 'blue',
                        'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                    ],
                    [
                        'label' => 'Revision',
                        'key' => 'revision',
                        'color' => 'red',
                        'icon' =>
                            'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                    ],
                    [
                        'label' => 'Completed',
                        'key' => 'completed',
                        'color' => 'green',
                        'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    ],
                ];
            @endphp
            @foreach ($summaries as $s)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-lg bg-{{ $s['color'] }}-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-{{ $s['color'] }}-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $s['icon'] }}" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ $counts[$s['key']] }}</p>
                        <p class="text-xs text-gray-500">{{ $s['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Request Cards --}}
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">All Approved Requests</h2>
            <p class="text-xs text-gray-400">Sorted by priority: Revision → On Process → Pending → Completed</p>
        </div>

        @if ($requests->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
                <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="text-gray-400 text-sm">No approved requests yet.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($requests as $req)
                    <div x-data="{ panel: null }"
                        class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

                        {{-- Main row --}}
                        <div class="px-6 py-4 flex flex-col lg:flex-row lg:items-center gap-4">

                            {{-- Left: Request info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <a href="{{ route('requests.show', $req) }}"
                                        class="text-base font-bold text-[#1D3557] hover:underline">
                                        #{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}
                                    </a>
                                    <span class="{{ $req->production_status_badge }} text-xs">
                                        {{ $req->production_status_label }}
                                    </span>
                                    @if ($req->deadline->isPast() && $req->production_status !== 'completed')
                                        <span class="badge-red text-xs">Overdue</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mt-1 truncate">{{ $req->purpose }}</p>
                                <div class="flex flex-wrap items-center gap-x-1.5 gap-y-1 mt-2 text-xs text-gray-500">
                                    <span>{{ $req->user->name }}</span>
                                    <span class="text-gray-300">&mdash;</span>
                                    <span>{{ $req->department?->name ?? '—' }}</span>
                                    <span class="text-gray-300">·</span>
                                    <span>Deadline:
                                        <span
                                            class="{{ $req->deadline->isPast() && $req->production_status !== 'completed' ? 'text-red-500 font-semibold' : '' }}">{{ $req->deadline->format('d M Y') }}</span>
                                    </span>
                                    @if ($req->production_updated_at)
                                        <span class="text-gray-300">·</span>
                                        <span>Updated {{ $req->production_updated_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                                @if ($req->production_status === 'revision' && $req->production_notes)
                                    <div
                                        class="mt-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-1.5">
                                        <span class="font-semibold">Revision note:</span> {{ $req->production_notes }}
                                    </div>
                                @endif

                                {{-- Milestone sub-stepper (on_process only) --}}
                                @if ($req->production_status === 'on_process')
                                    @php
                                        $milestones = \App\Models\MarketingRequest::productionMilestoneLabels();
                                        $current = $req->production_milestone ?? 0;
                                    @endphp
                                    <div class="mt-3">
                                        {{-- Dots + connectors --}}
                                        <div class="flex items-center">
                                            @foreach ($milestones as $step => $label)
                                                @if ($step > 1)
                                                    <div
                                                        class="flex-1 h-0.5 {{ $step - 1 < $current ? 'bg-blue-400' : 'bg-gray-200' }}">
                                                    </div>
                                                @endif
                                                <div
                                                    class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center {{ $step <= $current ? 'bg-blue-600' : 'bg-gray-200' }}">
                                                    @if ($step <= $current)
                                                        <svg class="w-3 h-3 text-white" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="3" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        {{-- Labels --}}
                                        <div class="flex justify-between mt-1.5">
                                            @foreach ($milestones as $step => $label)
                                                <span
                                                    class="text-xs {{ $step <= $current ? 'text-blue-700 font-medium' : 'text-gray-400' }} whitespace-nowrap">{{ $label }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Right: Action buttons (admin/marcom only) --}}
                            <div class="flex-shrink-0 flex flex-wrap items-center gap-2">

                                @if (auth()->user()->isMarcom() && $req->production_status === 'pending')
                                    {{-- Start Production: single click, no extra input --}}
                                    <form method="POST" action="{{ route('production.update', $req) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="action" value="start">
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Start Production
                                        </button>
                                    </form>
                                @endif

                                @if (auth()->user()->isMarcom() && $req->production_status === 'on_process')
                                    {{-- Advance Milestone --}}
                                    @if (($req->production_milestone ?? 0) < 4)
                                        <form method="POST" action="{{ route('production.update', $req) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="action" value="milestone">
                                            @php $nextLabel = \App\Models\MarketingRequest::productionMilestoneLabels()[($req->production_milestone ?? 0) + 1]; @endphp
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 text-sm font-medium rounded-lg hover:bg-indigo-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                </svg>
                                                {{ $nextLabel }}
                                            </button>
                                        </form>
                                    @endif
                                    {{-- Send for Revision: needs notes --}}
                                    <button type="button" @click="panel = panel === 'revision' ? null : 'revision'"
                                        :class="panel === 'revision' ? 'bg-amber-600 text-white' :
                                            'bg-amber-100 text-amber-800 hover:bg-amber-200'"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Send for Revision
                                    </button>
                                    {{-- Mark Completed: only after all 4 milestones done --}}
                                    @if (($req->production_milestone ?? 0) >= 4)
                                        <button type="button"
                                            @click="panel = panel === 'complete' ? null : 'complete'"
                                            :class="panel === 'complete' ? 'bg-green-700 text-white' :
                                                'bg-green-600 text-white hover:bg-green-700'"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Mark Completed
                                        </button>
                                    @endif
                                @endif

                                @if (auth()->user()->isMarcom() && $req->production_status === 'revision')
                                    {{-- Resume Production: single click --}}
                                    <form method="POST" action="{{ route('production.update', $req) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="action" value="resume">
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Resume Production
                                        </button>
                                    </form>
                                @endif

                                @if (auth()->user()->isMarcom() && $req->production_status === 'completed')
                                    @if ($req->final_file)
                                        <a href="{{ Storage::url($req->final_file) }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-50 border border-green-200 text-green-700 text-sm font-medium rounded-lg hover:bg-green-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Download File
                                        </a>
                                    @endif
                                @endif

                                {{-- View details link --}}
                                <a href="{{ route('requests.show', $req) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    View
                                </a>
                            </div>
                        </div>

                        {{-- Expandable: Revision Notes Form --}}
                        <div x-show="panel === 'revision'" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="border-t border-amber-200 bg-amber-50 px-6 py-4">
                            <p class="text-sm font-semibold text-amber-900 mb-3">Revision Notes <span
                                    class="font-normal text-amber-700">(required — will be shown to the
                                    requestor)</span></p>
                            <form method="POST" action="{{ route('production.update', $req) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="revision">
                                <textarea name="production_notes" rows="3" required
                                    placeholder="Describe what needs to be revised or corrected..."
                                    class="w-full px-3 py-2 text-sm border border-amber-300 rounded-lg focus:ring-2 focus:ring-amber-300 outline-none resize-none bg-white">{{ old('production_notes') }}</textarea>
                                @error('production_notes')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <div class="flex gap-3 mt-3">
                                    <button type="button" @click="panel = null"
                                        class="flex-1 px-4 py-2 bg-white border border-gray-300 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="flex-1 px-5 py-2 text-white text-sm font-semibold rounded-lg transition-colors"
                                        style="background-color:#f59e0b"
                                        onmouseover="this.style.backgroundColor='#d97706'"
                                        onmouseout="this.style.backgroundColor='#f59e0b'">
                                        Confirm Revision
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Expandable: Complete + File Upload Form --}}
                        <div x-show="panel === 'complete'" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="border-t border-green-200 bg-green-50 px-6 py-4">
                            <p class="text-sm font-semibold text-green-900 mb-3">Upload Final File <span
                                    class="font-normal text-green-700">(required — requestor will be able to download
                                    this)</span></p>
                            <form method="POST" action="{{ route('production.update', $req) }}"
                                enctype="multipart/form-data">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="complete">
                                <input type="file" name="final_file" required
                                    class="w-full text-sm text-gray-600 border border-green-300 rounded-lg p-2 bg-white focus:ring-2 focus:ring-green-300 outline-none">
                                @error('final_file')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <div class="flex gap-3 mt-3">
                                    <button type="button" @click="panel = null"
                                        class="flex-1 px-4 py-2 bg-white border border-gray-300 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="flex-1 px-5 py-2 text-white text-sm font-semibold rounded-lg transition-colors"
                                        style="background-color:#16a34a"
                                        onmouseover="this.style.backgroundColor='#15803d'"
                                        onmouseout="this.style.backgroundColor='#16a34a'">
                                        Mark Completed &amp; Upload
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
