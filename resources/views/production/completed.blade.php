<x-app-layout>
    @section('title', 'Completed Requests')

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Completed Requests</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    @if (auth()->user()->canViewAllRequests())
                        All requests that have been fully completed
                    @else
                        Your requests that have been fully completed
                    @endif
                </p>
            </div>
            <div class="text-sm text-gray-500">
                {{ $requests->total() }} {{ Str::plural('request', $requests->total()) }}
            </div>
        </div>
    </x-slot>

    <div class="py-4">


        @if ($requests->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-16 text-center">
                <svg class="w-14 h-14 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-500 font-medium">No completed requests yet</p>
                <p class="text-gray-400 text-sm mt-1">Requests will appear here once production is marked complete.</p>
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th class="text-left px-6 py-3 font-semibold">#</th>
                            <th class="text-left px-4 py-3 font-semibold">Purpose</th>
                            @if (auth()->user()->canViewAllRequests())
                                <th class="text-left px-4 py-3 font-semibold">Requested By</th>
                                <th class="text-left px-4 py-3 font-semibold">Department</th>
                            @endif
                            <th class="text-left px-4 py-3 font-semibold">Type</th>
                            <th class="text-left px-4 py-3 font-semibold">Deadline</th>
                            <th class="text-left px-4 py-3 font-semibold">Completed</th>
                            <th class="text-right px-6 py-3 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($requests as $req)
                            <tr class="hover:bg-gray-50 transition-colors">
                                {{-- ID --}}
                                <td class="px-6 py-4 font-mono font-bold text-[#1D3557] whitespace-nowrap">
                                    #{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}
                                </td>

                                {{-- Purpose --}}
                                <td class="px-4 py-4 max-w-xs">
                                    <p class="text-gray-800 font-medium truncate">{{ $req->purpose }}</p>
                                    @if ($req->detail_concept)
                                        <p class="text-gray-400 text-xs truncate mt-0.5">{{ $req->detail_concept }}</p>
                                    @endif
                                </td>

                                @if (auth()->user()->canViewAllRequests())
                                    {{-- Requested By --}}
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <p class="text-gray-700 font-medium">{{ $req->user->name }}</p>
                                    </td>

                                    {{-- Department --}}
                                    <td class="px-4 py-4 whitespace-nowrap text-gray-600">
                                        {{ $req->department?->name ?? '—' }}
                                    </td>
                                @endif

                                {{-- Type --}}
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if ($req->is_local_campaign)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            Local
                                        </span>
                                    @elseif ($req->is_group_campaign)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">
                                            Group
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                                            General
                                        </span>
                                    @endif
                                </td>

                                {{-- Deadline --}}
                                <td class="px-4 py-4 whitespace-nowrap text-gray-600">
                                    {{ $req->deadline->format('d M Y') }}
                                </td>

                                {{-- Completed At --}}
                                <td class="px-4 py-4 whitespace-nowrap text-gray-600">
                                    @if ($req->production_updated_at)
                                        <span title="{{ $req->production_updated_at->format('d M Y H:i') }}">
                                            {{ $req->production_updated_at->format('d M Y') }}
                                        </span>
                                        <p class="text-xs text-gray-400">
                                            {{ $req->production_updated_at->diffForHumans() }}</p>
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        {{-- Download final file if available --}}
                                        @if ($req->final_file)
                                            <a href="{{ Storage::url($req->final_file) }}" target="_blank"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 border border-green-200 text-green-700 text-xs font-medium rounded-lg hover:bg-green-100 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                                Download
                                            </a>
                                        @endif

                                        {{-- View detail --}}
                                        <a href="{{ route('requests.show', $req) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-600 text-xs font-medium rounded-lg hover:bg-gray-200 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($requests->hasPages())
                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
            @endif
        @endif

    </div>
</x-app-layout>
