<x-app-layout>
    @section('title', 'Review Request #' . str_pad($request->id, 4, '0', STR_PAD_LEFT))

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('approvals.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Review Request
                    #{{ str_pad($request->id, 4, '0', STR_PAD_LEFT) }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">Submitted by {{ $request->user->name }} ·
                    {{ $request->created_at->diffForHumans() }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Basic Info -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-[#1D3557] uppercase tracking-wide">Request Information
                        </h2>
                        <span class="{{ $request->status_badge }}">{{ $request->status_label }}</span>
                    </div>
                    <div class="p-6 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">PIC Name</p>
                            <p class="text-sm font-medium text-gray-800">{{ $request->pic_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Department</p>
                            <p class="text-sm font-medium text-gray-800">{{ $request->department?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Date Request</p>
                            <p class="text-sm text-gray-800">{{ $request->date_request->format('d F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Deadline</p>
                            <p
                                class="text-sm font-medium {{ $request->deadline->isPast() ? 'text-red-600' : 'text-gray-800' }}">
                                {{ $request->deadline->format('d F Y') }}
                                @if ($request->deadline->isPast())
                                    <span class="text-xs">(Overdue)</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Project For -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-[#1D3557] uppercase tracking-wide">Project For</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-wrap gap-2">
                            @if ($request->is_local_campaign)
                                <span
                                    class="px-3 py-1.5 text-sm bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-md">Local
                                    Campaign</span>
                            @endif
                            @if ($request->is_group_campaign)
                                <span
                                    class="px-3 py-1.5 text-sm bg-purple-50 text-purple-700 border border-purple-100 rounded-md">Group
                                    Campaign</span>
                            @endif
                            @if ($request->for_sales)
                                <span
                                    class="px-3 py-1.5 text-sm bg-blue-50 text-blue-700 border border-blue-100 rounded-md">Sales
                                    — {{ ucwords(str_replace('_', ' ', $request->sales_vehicle_type ?? '')) }}</span>
                            @endif
                            @if ($request->for_aftersales)
                                <span
                                    class="px-3 py-1.5 text-sm bg-teal-50 text-teal-700 border border-teal-100 rounded-md">Aftersales
                                    —
                                    {{ ucwords(str_replace('_', ' ', $request->aftersales_vehicle_type ?? '')) }}</span>
                            @endif
                            @if ($request->for_others)
                                <span
                                    class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 border border-gray-200 rounded-md">Others:
                                    {{ $request->others_description }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Project Description -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-[#1D3557] uppercase tracking-wide">Project Description
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Purpose</p>
                            <p class="text-sm text-gray-800">{{ $request->purpose }}</p>
                        </div>
                        @if ($request->detail_concept)
                            <div class="border-t border-gray-50 pt-4">
                                <p class="text-xs text-gray-500 mb-1">Detail Concept</p>
                                <p class="text-sm text-gray-800 whitespace-pre-line">{{ $request->detail_concept }}</p>
                            </div>
                        @endif
                        @if ($request->further_information)
                            <div class="border-t border-gray-50 pt-4">
                                <p class="text-xs text-gray-500 mb-1">Further Information</p>
                                <p class="text-sm text-gray-800 whitespace-pre-line">
                                    {{ $request->further_information }}</p>
                            </div>
                        @endif
                        @if ($request->reference_visual)
                            <div class="border-t border-gray-50 pt-4">
                                <p class="text-xs text-gray-500 mb-2">Reference Visual</p>
                                @php
                                    $refExt = strtolower(pathinfo($request->reference_visual, PATHINFO_EXTENSION));
                                    $refFilename =
                                        'REQ-' . str_pad($request->id, 4, '0', STR_PAD_LEFT) . '-reference.' . $refExt;
                                @endphp
                                <a href="{{ Storage::url($request->reference_visual) }}" target="_blank"
                                    download="{{ $refFilename }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download Reference Visual
                                    <span class="text-xs text-gray-400">({{ strtoupper($refExt) }})</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Output Media -->
                @if ($request->output_media && count($request->output_media) > 0)
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h2 class="text-sm font-semibold text-[#1D3557] uppercase tracking-wide">Output Media
                                Required</h2>
                        </div>
                        <div class="p-6">
                            @php $mediaLabels = ['poster_a3'=>'Poster A3','poster_a4'=>'Poster A4','flyer_a5'=>'Flyer A5','booklet'=>'Booklet','voucher'=>'Voucher','x_banner'=>'X-Banner','backdrop'=>'Backdrop','banner'=>'Banner','sticker'=>'Sticker']; @endphp
                            <div class="flex flex-wrap gap-2">
                                @foreach ($request->output_media as $media)
                                    <span
                                        class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-md">{{ $mediaLabels[$media] ?? $media }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar: Approval Action -->
            <div class="space-y-4">
                @php
                    $alreadyActed = $request->approvals->where('approver_id', auth()->id())->isNotEmpty();
                    $myAction = $request->approvals->where('approver_id', auth()->id())->first();
                    $approvedRoles = $request->approvals
                        ->where('status', 'approved')
                        ->map(fn($a) => $a->approver?->role?->value)
                        ->filter()
                        ->values()
                        ->toArray();
                @endphp

                {{-- Approval Progress Tracker (only while pending) --}}
                @if (in_array($request->status, ['submitted', 'under_review']) &&
                        ($request->is_local_campaign || $request->is_group_campaign))
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Approval Progress
                        </p>
                        @if ($request->is_local_campaign)
                            @php
                                $lokStep1 = in_array('manager', $approvedRoles);
                                $lokStep2 = in_array('gm', $approvedRoles);
                            @endphp
                            <div class="space-y-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 {{ $lokStep1 ? 'bg-green-100' : 'bg-gray-100' }}">
                                        @if ($lokStep1)
                                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                        @endif
                                    </div>
                                    <span
                                        class="text-xs {{ $lokStep1 ? 'text-green-700 font-medium' : 'text-gray-500' }}">Sales
                                        / Aftersales Manager Approval</span>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 {{ $lokStep2 ? 'bg-green-100' : 'bg-gray-100' }}">
                                        @if ($lokStep2)
                                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                        @endif
                                    </div>
                                    <span
                                        class="text-xs {{ $lokStep2 ? 'text-green-700 font-medium' : 'text-gray-500' }}">GM
                                        / Branch Manager Sign-off</span>
                                </div>
                            </div>
                        @elseif ($request->is_group_campaign)
                            @php
                                $grpStep1 = in_array('manager', $approvedRoles);
                                $grpStep2 = in_array('director', $approvedRoles);
                            @endphp
                            {{-- Request By (requester) is auto-fulfilled on submit --}}
                            <div class="flex items-center gap-2.5 mb-2.5">
                                <div
                                    class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 bg-green-100">
                                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-xs text-green-700 font-medium">Request By — Submitted</span>
                            </div>
                            <div class="space-y-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 {{ $grpStep1 ? 'bg-green-100' : 'bg-gray-100' }}">
                                        @if ($grpStep1)
                                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                        @endif
                                    </div>
                                    <span
                                        class="text-xs {{ $grpStep1 ? 'text-green-700 font-medium' : 'text-gray-500' }}">Approve
                                        By — Aftersales Manager</span>
                                </div>
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 {{ $grpStep2 ? 'bg-green-100' : 'bg-gray-100' }}">
                                        @if ($grpStep2)
                                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                        @endif
                                    </div>
                                    <span
                                        class="text-xs {{ $grpStep2 ? 'text-green-700 font-medium' : 'text-gray-500' }}">Acknowledged
                                        By — Director</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if (in_array($request->status, ['submitted', 'under_review']))
                    @php
                        // Determine whose turn it is at the current stage
                        $hasManagerApproval = in_array('manager', $approvedRoles);
                        $isMyTurn = false;
                        $waitingFor = null;
                        $authRole = auth()->user()->role;
                        $authDept = strtolower(auth()->user()->department?->name ?? '');

                        if ($request->is_local_campaign) {
                            if (!$hasManagerApproval) {
                                $isMyTurn =
                                    $authRole === \App\Enums\Role::Manager &&
                                    (str_contains($authDept, 'sales') || str_contains($authDept, 'aftersales'));
                                $waitingFor = 'Sales / Aftersales Manager';
                            } else {
                                $isMyTurn = $authRole === \App\Enums\Role::Gm;
                                $waitingFor = 'GM / Branch Manager';
                            }
                        } elseif ($request->is_group_campaign) {
                            if (!$hasManagerApproval) {
                                $isMyTurn =
                                    $authRole === \App\Enums\Role::Manager && str_contains($authDept, 'aftersales');
                                $waitingFor = 'Aftersales Manager';
                            } else {
                                $isMyTurn = $authRole === \App\Enums\Role::Director;
                                $waitingFor = 'Director';
                            }
                        } else {
                            // No campaign type — any approver role can finalize
                            $isMyTurn = auth()->user()->canApprove();
                        }
                    @endphp

                    @if ($request->user_id === auth()->id())
                        {{-- Creator cannot approve their own request --}}
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                            <div
                                class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700">You submitted this request</p>
                            <p class="text-xs text-gray-400 mt-1">Waiting for approvers to review</p>
                        </div>
                    @elseif ($alreadyActed)
                        {{-- Current user already acted --}}
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                            @if ($myAction?->status === 'approved')
                                <div
                                    class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-green-700">You approved this request</p>
                                <p class="text-xs text-gray-400 mt-1">Waiting for remaining sign-offs</p>
                            @else
                                <div
                                    class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-red-700">You rejected this request</p>
                            @endif
                            @if ($myAction?->comment)
                                <p class="text-xs text-gray-500 mt-2 italic">"{{ $myAction->comment }}"</p>
                            @endif
                        </div>
                    @else
                        @if (!$isMyTurn && auth()->user()->canApprove())
                            {{-- Correct approver role but not their turn yet --}}
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                                <div
                                    class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-amber-700">Not your turn yet</p>
                                <p class="text-xs text-gray-400 mt-1">Waiting for {{ $waitingFor }} to act first</p>
                            </div>
                        @elseif ($isMyTurn)
                            {{-- Decision form — this user's turn --}}
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm" x-data="{ action: '' }">
                                <div class="px-5 py-4 border-b border-gray-100">
                                    <h2 class="text-sm font-semibold text-[#1D3557] uppercase tracking-wide">Your
                                        Decision
                                    </h2>
                                </div>
                                <div class="p-5">
                                    <div class="grid grid-cols-2 gap-2 mb-4">
                                        <button @click="action = 'approved'"
                                            :class="action === 'approved' ? 'bg-green-600 text-white border-green-600' :
                                                'bg-white text-gray-700 border-gray-200 hover:border-green-300'"
                                            class="border-2 rounded-lg py-2.5 text-sm font-medium transition-all flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Approve
                                        </button>
                                        <button @click="action = 'rejected'"
                                            :class="action === 'rejected' ? 'bg-red-600 text-white border-red-600' :
                                                'bg-white text-gray-700 border-gray-200 hover:border-red-300'"
                                            class="border-2 rounded-lg py-2.5 text-sm font-medium transition-all flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Reject
                                        </button>
                                    </div>

                                    <form method="POST" action="{{ route('approvals.decide', $request) }}"
                                        x-show="action !== ''" x-cloak x-transition>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" :value="action">

                                        <div class="mb-3">
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">
                                                Comment <span x-show="action === 'rejected'" x-cloak
                                                    class="text-red-500">*</span>
                                                <span x-show="action === 'approved'" x-cloak
                                                    class="text-gray-400">(optional)</span>
                                            </label>
                                            <textarea name="comment" rows="3" placeholder="Add a comment for the requester..."
                                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none resize-none transition"></textarea>
                                            @error('comment')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <button type="submit"
                                            :class="action === 'approved' ? 'bg-green-600 hover:bg-green-700' :
                                                'bg-red-600 hover:bg-red-700'"
                                            class="w-full py-2.5 text-white text-sm font-semibold rounded-lg transition-colors"
                                            x-text="action === 'approved' ? 'Confirm Approval' : 'Confirm Rejection'">
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endif
                @else
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                        @if ($request->status === 'approved')
                            <div
                                class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-green-700">Fully Approved</p>
                        @else
                            <div
                                class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-red-700">Rejected</p>
                        @endif
                        @if ($request->manager_comment)
                            <p class="text-xs text-gray-500 mt-2 italic">"{{ $request->manager_comment }}"</p>
                        @endif
                        @if ($request->reviewer)
                            <p class="text-xs text-gray-400 mt-2">by {{ $request->reviewer->name }},
                                {{ $request->reviewed_at?->format('d M Y') }}</p>
                        @endif
                    </div>
                @endif

                <!-- Submitter Info -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Submitted By</p>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span
                                class="text-blue-600 text-sm font-semibold">{{ substr($request->user->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $request->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $request->user->department?->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments / Notes Thread -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm mt-5">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-[#1D3557] uppercase tracking-wide">
                    Comments &amp; Notes
                    <span
                        class="ml-1 text-xs font-normal text-gray-400 normal-case">({{ $request->comments->count() }})</span>
                </h2>
            </div>
            <div class="p-6">
                @if ($request->comments->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-4">No comments yet.</p>
                @else
                    @php
                        $renderMentions = fn($text) => preg_replace(
                            '/@(\w+)/',
                            '<span class="inline-flex items-center text-blue-600 font-semibold bg-blue-50 px-1 rounded">@$1</span>',
                            e($text),
                        );
                    @endphp
                    <div class="space-y-4 mb-6">
                        @foreach ($request->comments as $comment)
                            <div class="flex gap-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
                                    <span
                                        class="text-white text-xs font-semibold">{{ substr($comment->user->name, 0, 1) }}</span>
                                </div>
                                <div class="flex-1 bg-gray-50 rounded-lg px-4 py-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-sm font-medium text-gray-800">{{ $comment->user->name }}</span>
                                            <span
                                                class="text-xs px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded capitalize">{{ $comment->user->role->value }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-700 whitespace-pre-line">{!! nl2br($renderMentions($comment->body)) !!}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Add comment form --}}
                <form method="POST" action="{{ route('comments.store', $request) }}" class="flex gap-3">
                    @csrf
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
                        <span class="text-white text-xs font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1">
                        <textarea id="comment-body" name="body" rows="2" placeholder="Add a comment… use @name to mention someone"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none resize-none transition"></textarea>
                        @error('body')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <div class="flex justify-end mt-2">
                            <button type="submit"
                                class="px-4 py-1.5 text-sm font-medium text-white bg-[#1D3557] rounded-lg hover:bg-[#162840] transition-colors">
                                Post Comment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
    <script>
        (function() {
            'use strict';
            const textarea = document.getElementById('comment-body');
            if (!textarea) return;

            let allUsers = [];
            let mentionStart = -1;
            let activeIndex = -1;

            fetch('{{ route('users.suggestions') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(d => {
                    allUsers = d;
                })
                .catch(() => {});

            const dropdown = document.createElement('div');
            dropdown.className = 'fixed z-50 bg-white border border-gray-200 rounded-lg shadow-xl overflow-y-auto';
            dropdown.style.cssText = 'display:none;max-height:220px;min-width:230px;';
            document.body.appendChild(dropdown);

            function escHtml(s) {
                return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function getItems() {
                return Array.from(dropdown.querySelectorAll('.m-item'));
            }

            function setActive(i) {
                getItems().forEach((el, idx) => el.classList.toggle('bg-blue-50', idx === i));
                activeIndex = i;
            }

            function showDropdown(query) {
                const filtered = allUsers.filter(u =>
                    u.name.toLowerCase().includes(query.toLowerCase()) ||
                    u.username.toLowerCase().includes(query.toLowerCase())
                ).slice(0, 8);

                if (!filtered.length) {
                    dropdown.style.display = 'none';
                    return;
                }

                dropdown.innerHTML = filtered.map(u =>
                    `<div class="m-item flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-blue-50 text-sm" data-username="${escHtml(u.username)}">
                <span class="w-7 h-7 rounded-full bg-blue-500 text-white flex-shrink-0 flex items-center justify-center text-xs font-semibold">${escHtml(u.name.charAt(0))}</span>
                <div><span class="font-medium text-gray-800">${escHtml(u.name)}</span><span class="text-gray-400 text-xs ml-1">@${escHtml(u.username)}</span></div>
            </div>`
                ).join('');

                const rect = textarea.getBoundingClientRect();
                dropdown.style.top = (rect.bottom + 4) + 'px';
                dropdown.style.left = rect.left + 'px';
                dropdown.style.width = rect.width + 'px';
                dropdown.style.display = 'block';
                activeIndex = -1;

                getItems().forEach(item => item.addEventListener('mousedown', e => {
                    e.preventDefault();
                    insertMention(item.dataset.username);
                }));
            }

            function hideDropdown() {
                dropdown.style.display = 'none';
                mentionStart = -1;
                activeIndex = -1;
            }

            function insertMention(username) {
                const val = textarea.value;
                const cur = textarea.selectionStart;
                textarea.value = val.substring(0, mentionStart) + '@' + username + ' ' + val.substring(cur);
                const pos = mentionStart + username.length + 2;
                textarea.selectionStart = textarea.selectionEnd = pos;
                textarea.focus();
                hideDropdown();
            }

            textarea.addEventListener('input', function() {
                const pos = this.selectionStart;
                const before = this.value.substring(0, pos);
                const match = before.match(/@(\w*)$/);
                if (match) {
                    mentionStart = pos - match[0].length;
                    showDropdown(match[1]);
                } else {
                    hideDropdown();
                }
            });

            textarea.addEventListener('keydown', function(e) {
                if (dropdown.style.display === 'none') return;
                const items = getItems();
                if (e.key === 'Escape') {
                    hideDropdown();
                    return;
                }
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setActive(Math.min(activeIndex + 1, items.length - 1));
                    return;
                }
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setActive(Math.max(activeIndex - 1, 0));
                    return;
                }
                if ((e.key === 'Enter' || e.key === 'Tab') && activeIndex >= 0) {
                    e.preventDefault();
                    insertMention(items[activeIndex].dataset.username);
                }
            });

            document.addEventListener('click', e => {
                if (!textarea.contains(e.target) && !dropdown.contains(e.target)) hideDropdown();
            });
        })();
    </script>
@endpush
