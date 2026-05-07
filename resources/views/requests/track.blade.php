<x-app-layout>
    @section('title', 'Track Request #' . str_pad($marketingRequest->id, 4, '0', STR_PAD_LEFT))

    @php
        $req = $marketingRequest;
        $prodStages = [
            ['key' => 'pending', 'label' => 'Pending', 'desc' => 'Request received, waiting to start production.'],
            ['key' => 'on_process', 'label' => 'On Process', 'desc' => 'Admin Marketing is working on this request.'],
            ['key' => 'revision', 'label' => 'Revision', 'desc' => 'Revision requested — see notes below.'],
            ['key' => 'completed', 'label' => 'Completed', 'desc' => 'Production complete. Final file is ready.'],
        ];
        $prodOrder = array_flip(array_column($prodStages, 'key'));
        $currentIdx = $prodOrder[$req->production_status] ?? 0;
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('requests.show', $req) }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-semibold text-gray-800">
                        Production Tracking — Request #{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">Submitted {{ $req->created_at->diffForHumans() }} by
                        {{ $req->user->name }}</p>
                </div>
            </div>
            <span
                class="{{ $req->production_status_badge }} text-sm px-3 py-1.5">{{ $req->production_status_label }}</span>
        </div>
    </x-slot>

    <div class="py-4">


        {{-- === APPROVAL PROGRESS BAR === --}}
        @php
            $approvalStages = [
                ['key' => 'submitted', 'label' => 'Submitted', 'desc' => 'Request submitted and awaiting review.'],
                ['key' => 'under_review', 'label' => 'Under Review', 'desc' => 'Manager is reviewing the request.'],
                [
                    'key' => 'decision',
                    'label' => $req->status === 'rejected' ? 'Rejected' : 'Approved',
                    'desc' => $req->status === 'rejected' ? 'Request was rejected.' : 'Request fully approved.',
                ],
            ];
            $approvalStatusOrder = ['submitted' => 0, 'under_review' => 1, 'approved' => 2, 'rejected' => 2];
            $approvalCurrentIdx = $approvalStatusOrder[$req->status] ?? 0;
        @endphp
        {{-- <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-5">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-[#1D3557] uppercase tracking-wide">Approval Progress</h2>
            </div>
            <div class="p-6">
                <div class="flex items-start">
                    @foreach ($approvalStages as $i => $stage)
                        @php
                            $isDone = $i < $approvalCurrentIdx;
                            $isActive = $i === $approvalCurrentIdx;
                            $isRejected = $isActive && $req->status === 'rejected';
                        @endphp
                        <div class="flex items-start {{ $i < count($approvalStages) - 1 ? 'flex-1' : '' }}">
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 transition-all
                                    {{ $isDone
                                        ? 'border-green-500 bg-green-500 text-white'
                                        : ($isRejected
                                            ? 'border-red-500 bg-red-500 text-white ring-4 ring-red-500/10'
                                            : ($isActive
                                                ? 'bg-[#1D3557] border-[#1D3557] text-white ring-4 ring-[#1D3557]/10'
                                                : 'bg-white border-gray-200 text-gray-400')) }}">
                                    @if ($isDone)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif ($isRejected)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    @elseif ($isActive && $req->status === 'approved')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <span class="text-sm">{{ $i + 1 }}</span>
                                    @endif
                                </div>
                                <span
                                    class="text-xs mt-2 text-center font-medium whitespace-nowrap
                                    {{ $isRejected ? 'text-red-600' : ($isActive ? 'text-[#1D3557]' : ($isDone ? 'text-green-600' : 'text-gray-400')) }}">
                                    {{ $stage['label'] }}
                                </span>
                            </div>
                            @if ($i < count($approvalStages) - 1)
                                <div
                                    class="flex-1 h-0.5 mx-3 mt-5 {{ $i < $approvalCurrentIdx ? 'bg-green-400' : 'bg-gray-200' }}">
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if ($req->manager_comment)
                    <p class="text-xs text-gray-500 mt-4 bg-gray-50 rounded-lg px-4 py-2 border border-gray-100">
                        <span class="font-medium text-gray-700">Manager note:</span> {{ $req->manager_comment }}
                    </p>
                @endif
            </div>
        </div> --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-5">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-[#1D3557] uppercase tracking-wide">Production Progress</h2>
            </div>
            <div class="p-6">
                {{-- Horizontal stepper --}}
                <div class="flex items-start mb-6">
                    @foreach ($prodStages as $i => $stage)
                        @php
                            $isDone = $i < $currentIdx;
                            $isActive = $i === $currentIdx;
                        @endphp
                        <div class="flex items-start {{ $i < count($prodStages) - 1 ? 'flex-1' : '' }}">
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 transition-all
                                    {{ $isDone
                                        ? 'border-green-500 text-white'
                                        : ($isActive
                                            ? 'bg-[#1D3557] border-[#1D3557] text-white ring-4 ring-[#1D3557]/10'
                                            : 'bg-white border-gray-200 text-gray-400') }}"
                                    @if ($isDone) style="background-color:#22c55e" @endif>
                                    @if ($isDone)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    @elseif ($isActive && $stage['key'] === 'revision')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @elseif ($isActive && $stage['key'] === 'completed')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <span class="text-sm">{{ $i + 1 }}</span>
                                    @endif
                                </div>
                                <span
                                    class="text-xs mt-2 text-center font-medium whitespace-nowrap
                                    {{ $isActive ? 'text-[#1D3557]' : ($isDone ? 'text-green-600' : 'text-gray-400') }}">
                                    {{ $stage['label'] }}
                                </span>
                                @if ($isActive)
                                    <span
                                        class="text-xs mt-0.5 text-center text-gray-500 max-w-[80px] text-center leading-tight hidden sm:block">
                                        {{ $stage['desc'] }}
                                    </span>
                                @endif
                            </div>
                            @if ($i < count($prodStages) - 1)
                                <div class="flex-1 h-0.5 mx-3 mt-5 transition-all {{ $i < $currentIdx ? '' : 'bg-gray-200' }}"
                                    @if ($i < $currentIdx) style="background-color:#4ade80" @endif>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Current status description (mobile) --}}
                <div class="sm:hidden text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-3 mb-4">
                    {{ $prodStages[$currentIdx]['desc'] }}
                </div>

                {{-- Last updated --}}
                @if ($req->production_updated_at)
                    <p class="text-xs text-gray-400 text-center">
                        Last updated {{ $req->production_updated_at->diffForHumans() }}
                        ({{ $req->production_updated_at->format('d M Y, H:i') }})
                    </p>
                @endif

                {{-- === MILESTONE SUB-STEPS (only when On Process) === --}}
                @if ($req->production_status === 'on_process')
                    @php
                        $milestones = \App\Models\MarketingRequest::productionMilestoneLabels();
                        $milestoneIcons = [
                            1 => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                            2 => 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z',
                            3 => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                            4 => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
                        ];
                        $currentMilestone = $req->production_milestone ?? 0;
                        $milestoneTs = $req->milestone_timestamps ?? [];
                    @endphp
                    <div class="mt-6 border-t border-gray-100 pt-5">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4 text-center">
                            Production Milestones</p>
                        <div class="flex items-start justify-center gap-0">
                            @foreach ($milestones as $step => $label)
                                @php
                                    $mDone = $step < $currentMilestone;
                                    $mActive = $step === $currentMilestone;
                                    $mTs = isset($milestoneTs[$step])
                                        ? \Carbon\Carbon::parse($milestoneTs[$step])
                                        : null;
                                @endphp
                                <div class="flex items-start {{ $step < count($milestones) ? 'flex-1' : '' }}">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-9 h-9 rounded-full flex items-center justify-center border-2 transition-all
                                            {{ $mDone
                                                ? 'bg-blue-600 border-blue-600 text-white'
                                                : ($mActive
                                                    ? 'bg-[#1D3557] border-[#1D3557] text-white ring-4 ring-[#1D3557]/10'
                                                    : 'bg-white border-gray-200 text-gray-300') }}">
                                            @if ($mDone)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.8" d="{{ $milestoneIcons[$step] }}" />
                                                </svg>
                                            @endif
                                        </div>
                                        <span
                                            class="text-xs mt-1.5 text-center font-medium leading-tight max-w-[64px]
                                            {{ $mActive ? 'text-[#1D3557]' : ($mDone ? 'text-blue-600' : 'text-gray-300') }}">
                                            {{ $label }}
                                        </span>
                                        @if ($mTs)
                                            <span
                                                class="text-[10px] text-gray-400 text-center mt-0.5 leading-tight max-w-[64px]">
                                                {{ $mTs->format('d M, H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                    @if ($step < count($milestones))
                                        <div
                                            class="flex-1 h-0.5 mx-2 mt-4 transition-all {{ $mDone ? 'bg-blue-400' : 'bg-gray-200' }}">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if ($currentMilestone === 0)
                            <p class="text-xs text-gray-400 text-center mt-3">Production is starting…</p>
                        @else
                            <p class="text-xs text-blue-600 font-medium text-center mt-3">Currently:
                                {{ $milestones[$currentMilestone] }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- === REVISION NOTES === --}}
        @if ($req->production_status === 'revision' && $req->production_notes)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-5 flex gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-900">Revision Requested by Admin Marketing</p>
                    <p class="text-sm text-amber-800 mt-1">{{ $req->production_notes }}</p>
                    <p class="text-xs text-amber-600 mt-2">Updated {{ $req->production_updated_at?->diffForHumans() }}
                    </p>
                </div>
            </div>
        @endif

        {{-- === FINAL FILE DOWNLOAD === --}}
        @if ($req->production_status === 'completed')
            <div
                class="bg-green-50 border border-green-200 rounded-xl p-5 mb-5 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-green-900">Production Completed!</p>
                        <p class="text-sm text-green-700 mt-0.5">Your final file is ready to download.</p>
                    </div>
                </div>
                @if ($req->final_file)
                    <a href="{{ Storage::url($req->final_file) }}" target="_blank"
                        class="flex-shrink-0 inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Final File
                    </a>
                @endif
            </div>
        @endif

        {{-- === BASIC INFO === --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-5">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-[#1D3557] uppercase tracking-wide">Request Info</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">PIC Name</p>
                        <p class="text-sm font-medium text-gray-800">{{ $req->pic_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Department</p>
                        <p class="text-sm font-medium text-gray-800">{{ $req->department?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Date Request</p>
                        <p class="text-sm font-medium text-gray-800">{{ $req->date_request->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Deadline</p>
                        <p class="text-sm font-medium text-gray-800">{{ $req->deadline->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Submitted By</p>
                        <p class="text-sm font-medium text-gray-800">{{ $req->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Approval Status</p>
                        <span class="{{ $req->status_badge }} text-xs px-2.5 py-1">{{ $req->status_label }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- === PROJECT DETAIL === --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-5">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-[#1D3557] uppercase tracking-wide">Project Detail</h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1.5">Purpose</p>
                    <p class="text-sm text-gray-800">{{ $req->purpose }}</p>
                </div>
                @if ($req->detail_concept)
                    <div class="border-t border-gray-50 pt-4">
                        <p class="text-xs text-gray-500 mb-1.5">Detail Concept</p>
                        <p class="text-sm text-gray-800 whitespace-pre-line">{{ $req->detail_concept }}</p>
                    </div>
                @endif
                @if ($req->output_media && count($req->output_media) > 0)
                    <div class="border-t border-gray-50 pt-4">
                        <p class="text-xs text-gray-500 mb-1.5">Output Media</p>
                        @php
                            $mediaLabels = [
                                'poster_a3' => 'Poster A3',
                                'poster_a4' => 'Poster A4',
                                'flyer_a5' => 'Flyer A5',
                                'booklet' => 'Booklet',
                                'voucher' => 'Voucher',
                                'x_banner' => 'X-Banner',
                                'backdrop' => 'Backdrop',
                                'banner' => 'Banner',
                                'sticker' => 'Sticker',
                            ];
                        @endphp
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($req->output_media as $media)
                                <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">
                                    {{ $mediaLabels[$media] ?? $media }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- === UPLOADED FILES === --}}
        @if ($req->reference_visual || $req->final_file)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-5">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-[#1D3557] uppercase tracking-wide">Uploaded Files</h2>
                </div>
                <div class="p-6 space-y-3">
                    @if ($req->reference_visual)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Reference Visual</p>
                                    <p class="text-xs text-gray-500">Submitted with request</p>
                                </div>
                            </div>
                            <a href="{{ Storage::url($req->reference_visual) }}" target="_blank"
                                class="text-sm text-blue-600 hover:text-blue-700 font-medium">View</a>
                        </div>
                    @endif
                    @if ($req->final_file)
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Final Deliverable</p>
                                    <p class="text-xs text-gray-500">Uploaded by Admin Marketing</p>
                                </div>
                            </div>
                            <a href="{{ Storage::url($req->final_file) }}" target="_blank"
                                class="text-sm text-green-600 hover:text-green-700 font-medium">Download</a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- === FULL ACTIVITY TIMELINE === --}}
        @php
            // Build a unified timeline array from all sources
            $timelineEvents = [];

            // 1. Submitted
            $timelineEvents[] = [
                'label' => 'Request Submitted',
                'sub' => 'by ' . $req->user->name,
                'at' => $req->created_at,
                'color' => 'gray',
                'icon' =>
                    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            ];

            // 2. Each approval action (sorted by acted_at)
            $approvalColorMap = ['approved' => 'green', 'rejected' => 'red'];
            $approvalIconMap = [
                'approved' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'rejected' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            ];
            foreach ($req->approvals->sortBy('acted_at') as $apv) {
                $roleName = is_object($apv->approver?->role)
                    ? ucfirst($apv->approver->role->value)
                    : ucfirst($apv->approver?->role ?? 'Approver');
                $timelineEvents[] = [
                    'label' => ($apv->status === 'approved' ? 'Approved' : 'Rejected') . ' by ' . $roleName,
                    'sub' => $apv->approver?->name . ($apv->comment ? ' — "' . $apv->comment . '"' : ''),
                    'at' => $apv->acted_at,
                    'color' => $approvalColorMap[$apv->status] ?? 'gray',
                    'icon' => $approvalIconMap[$apv->status] ?? 'M9 12l2 2 4-4',
                ];
            }

            // 3. Production timeline events
            $prodEventMeta = [
                'started' => [
                    'label' => 'Production Started',
                    'color' => 'blue',
                    'icon' =>
                        'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                'revision_sent' => [
                    'label' => 'Sent for Revision',
                    'color' => 'amber',
                    'icon' =>
                        'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                ],
                'resumed' => [
                    'label' => 'Production Resumed',
                    'color' => 'blue',
                    'icon' =>
                        'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                ],
                'completed' => [
                    'label' => 'Marked as Completed',
                    'color' => 'green',
                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
            ];
            $milestoneLabels = \App\Models\MarketingRequest::productionMilestoneLabels();
            foreach ($req->production_timeline ?? [] as $evt) {
                $meta = $prodEventMeta[$evt['event']] ?? ['label' => $evt['event'], 'color' => 'gray', 'icon' => ''];
                $sub = 'by ' . ($evt['by'] ?? 'Marcom');
                if ($evt['event'] === 'revision_sent' && !empty($evt['note'])) {
                    $sub .= ' — "' . $evt['note'] . '"';
                }
                $timelineEvents[] = [
                    'label' => $meta['label'],
                    'sub' => $sub,
                    'at' => \Carbon\Carbon::parse($evt['at']),
                    'color' => $meta['color'],
                    'icon' => $meta['icon'],
                ];
                // Inline milestone timestamps right after 'started' or 'resumed' that belong to this cycle
                if (in_array($evt['event'], ['started', 'resumed']) && !empty($req->milestone_timestamps)) {
                    foreach ($req->milestone_timestamps as $step => $ts) {
                        $timelineEvents[] = [
                            'label' => 'Milestone: ' . ($milestoneLabels[$step] ?? "Step $step"),
                            'sub' => 'by Marcom',
                            'at' => \Carbon\Carbon::parse($ts),
                            'color' => 'indigo',
                            'icon' => 'M13 7l5 5m0 0l-5 5m5-5H6',
                        ];
                    }
                }
            }

            // Sort all events by time
            usort($timelineEvents, fn($a, $b) => $a['at'] <=> $b['at']);

            $colorClasses = [
                'gray' => ['bg' => 'bg-gray-100', 'icon' => 'text-gray-500', 'ring' => 'ring-gray-200'],
                'green' => ['bg' => 'bg-green-100', 'icon' => 'text-green-600', 'ring' => 'ring-green-200'],
                'red' => ['bg' => 'bg-red-100', 'icon' => 'text-red-500', 'ring' => 'ring-red-200'],
                'blue' => ['bg' => 'bg-blue-100', 'icon' => 'text-blue-600', 'ring' => 'ring-blue-200'],
                'amber' => ['bg' => 'bg-amber-100', 'icon' => 'text-amber-600', 'ring' => 'ring-amber-200'],
                'indigo' => ['bg' => 'bg-indigo-100', 'icon' => 'text-indigo-600', 'ring' => 'ring-indigo-200'],
            ];
        @endphp

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-5">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-[#1D3557] uppercase tracking-wide">Activity Timeline</h2>
            </div>
            <div class="px-6 py-5">
                <ol class="relative">
                    @foreach ($timelineEvents as $i => $ev)
                        @php $cc = $colorClasses[$ev['color']] ?? $colorClasses['gray']; @endphp
                        <li class="flex gap-4 {{ !$loop->last ? 'pb-6' : '' }}">
                            {{-- Line + dot --}}
                            <div class="flex flex-col items-center flex-shrink-0">
                                <div
                                    class="w-8 h-8 rounded-full {{ $cc['bg'] }} ring-2 {{ $cc['ring'] }} flex items-center justify-center">
                                    <svg class="w-4 h-4 {{ $cc['icon'] }}" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="{{ $ev['icon'] }}" />
                                    </svg>
                                </div>
                                @if (!$loop->last)
                                    <div class="w-px flex-1 bg-gray-200 mt-1"></div>
                                @endif
                            </div>
                            {{-- Content --}}
                            <div class="pt-1 pb-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800">{{ $ev['label'] }}</p>
                                @if ($ev['sub'])
                                    <p class="text-xs text-gray-500 mt-0.5 break-words">{{ $ev['sub'] }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $ev['at']->format('d M Y, H:i') }}
                                    <span class="ml-1 text-gray-300">({{ $ev['at']->diffForHumans() }})</span>
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        {{-- Footer link --}}
        <div class="text-center">
            <a href="{{ route('requests.show', $req) }}" class="text-sm text-gray-500 hover:text-gray-700 underline">
                View full request details &rarr;
            </a>
        </div>

    </div>
</x-app-layout>
