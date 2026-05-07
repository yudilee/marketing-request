<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Prevent FOUC: hide body until Alpine has initialised all x-show directives -->
    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            opacity: 0;
        }
    </style>

    <title>{{ config('app.name', 'Marcom Request') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Compiled Assets (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Per-page head content (e.g. extra scripts/styles) -->
    @stack('head')

    <style>
        /* x-cloak is already defined inline above for FOUC prevention */

        .badge-yellow {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800;
        }

        .badge-blue {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800;
        }

        .badge-green {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800;
        }

        .badge-red {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800;
        }

        .badge-gray {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50">

    <!-- Sidebar + Main Layout -->
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-[#1D3557]">
                <!-- Logo -->
                <div
                    class="flex flex-col items-center justify-center bg-[#162840] border-b border-[#0f1d2e] pt-4 pb-2 px-4">
                    <img src="/hartonogroup.png" alt="Hartono Group Logo" class="h-10 w-auto mb-1 mt-1">
                    {{-- <span class="text-white font-bold text-lg tracking-wider leading-tight mt-1">HARTONO <span
                            class="text-blue-300 font-light">GROUP</span></span> --}}
                    <div class="w-3/4 border-b border-blue-900 mt-2 mb-1 opacity-30"></div>
                </div>

                <!-- Nav -->
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

                    <p class="px-2 py-1 text-xs font-semibold text-blue-300 uppercase tracking-wider mt-2">Menu</p>

                    <a href="{{ route('dashboard') }}"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-[#162840] hover:text-white' }} transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('requests.create') }}"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('requests.create') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-[#162840] hover:text-white' }} transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Request
                    </a>

                    <a href="{{ route('requests.index') }}"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('requests.index') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-[#162840] hover:text-white' }} transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        My Requests
                    </a>

                    <a href="{{ route('production.completed') }}"
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('production.completed') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-[#162840] hover:text-white' }} transition-colors">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Completed Requests
                    </a>

                    @if (auth()->user()->canApprove())
                        <p class="px-2 py-1 text-xs font-semibold text-blue-300 uppercase tracking-wider mt-4">
                            Approvals
                        </p>
                        <a href="{{ route('approvals.index') }}"
                            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('approvals.*') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-[#162840] hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            Pending Approvals
                            @php $pendingCount = \App\Models\MarketingRequest::whereIn('status',['submitted','under_review'])->count(); @endphp
                            @if ($pendingCount > 0)
                                <span
                                    class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                            @endif
                        </a>

                        <a href="{{ route('approvals.all') }}"
                            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('approvals.all') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-[#162840] hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            All Requests
                        </a>
                    @endif

                    @if (auth()->user()->isMarcom() || auth()->user()->isAdmin())
                        <p class="px-2 py-1 text-xs font-semibold text-blue-300 uppercase tracking-wider mt-4">
                            Production</p>
                        <a href="{{ route('production.index') }}"
                            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('production.*') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-[#162840] hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Production Queue
                            @php
                                $activeCount = \App\Models\MarketingRequest::where('status', 'approved')
                                    ->whereIn('production_status', ['pending', 'on_process', 'revision'])
                                    ->count();
                                $overdueCount = \App\Models\MarketingRequest::where('status', 'approved')
                                    ->whereIn('production_status', ['pending', 'on_process', 'revision'])
                                    ->whereDate('deadline', '<', today())
                                    ->count();
                            @endphp
                            @if ($overdueCount > 0)
                                <span
                                    class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-md whitespace-nowrap">{{ $overdueCount }}
                                    overdue</span>
                            @elseif ($activeCount > 0)
                                <span
                                    class="ml-auto bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $activeCount }}</span>
                            @endif
                        </a>

                        <p class="px-2 py-1 text-xs font-semibold text-blue-300 uppercase tracking-wider mt-4">Calendar
                        </p>
                        <a href="{{ route('calendar.index') }}"
                            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('calendar.index') || request()->routeIs('calendar.pending') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-[#162840] hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Calendar
                        </a>
                    @endif

                    {{-- Calendar approvals: Marcom Manager, GM, Director only --}}
                    @if (auth()->user()->isMarcomManager() ||
                            in_array(auth()->user()->role, [\App\Enums\Role::Gm, \App\Enums\Role::Director]))
                        @php
                            $pendingCalStatus = auth()->user()->isMarcomManager()
                                ? 'pending_manager'
                                : 'pending_gm_director';
                            $pendingCalCount = \App\Models\CalendarEvent::where('status', $pendingCalStatus)->count();
                        @endphp
                        <a href="{{ route('calendar.approvals') }}"
                            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('calendar.approvals') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-[#162840] hover:text-white' }} transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            Calendar Approvals
                            @if ($pendingCalCount > 0)
                                <span
                                    class="ml-auto bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingCalCount }}</span>
                            @endif
                        </a>
                    @endif
                </nav>

                <!-- User Info -->
                <div class="flex-shrink-0 px-3 py-4 border-t border-[#162840]">

                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-9 h-9 bg-blue-500 rounded-full flex items-center justify-center">
                            <span
                                class="text-white text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3 min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-blue-300 capitalize">{{ auth()->user()->role->value }}</p>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('profile.edit') }}"
                            class="block px-3 py-1.5 text-xs text-blue-200 hover:text-white hover:bg-[#162840] rounded transition-colors">Profile
                            Settings</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-3 py-1.5 text-xs text-blue-200 hover:text-white hover:bg-[#162840] rounded transition-colors">Sign
                                Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 min-w-0">

            <!-- Top Navigation Bar -->
            <div
                class="flex-shrink-0 flex items-center justify-between h-14 px-4 md:px-6 bg-white border-b border-gray-200 z-20">
                <!-- Left: mobile hamburger -->
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="md:hidden p-1.5 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span
                        class="md:hidden font-bold text-gray-700 text-sm tracking-wide">{{ config('app.name') }}</span>
                </div>

                <!-- Right: Notification Bell + User -->
                <div class="flex items-center gap-1">
                    {{-- Notification Bell --}}
                    @php $unreadNotifs = auth()->user()->unreadNotifications; @endphp
                    <div x-data="{
                        open: false,
                        top: 0,
                        right: 0,
                        toggle() {
                            const r = this.$refs.bell.getBoundingClientRect();
                            this.top = r.bottom + 8;
                            this.right = window.innerWidth - r.right;
                            this.open = !this.open;
                        }
                    }">
                        <button x-ref="bell" @click="toggle()"
                            class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if ($unreadNotifs->isNotEmpty())
                                <span
                                    class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                            @endif
                        </button>

                        {{-- Notification Dropdown — fixed to escape overflow:hidden ancestors --}}
                        <div x-show="open" x-cloak @click.outside="open = false"
                            :style="`position:fixed; top:${top}px; right:${right}px;`"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="w-[420px] bg-white rounded-2xl shadow-2xl border border-gray-200 z-[9999] overflow-hidden">

                            {{-- Header --}}
                            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <span class="text-base font-semibold text-gray-800">Notifications</span>
                                    @if ($unreadNotifs->isNotEmpty())
                                        <span
                                            class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $unreadNotifs->count() }}</span>
                                    @endif
                                </div>
                                @if ($unreadNotifs->isNotEmpty())
                                    <form method="POST" action="{{ route('notifications.readAll') }}">
                                        @csrf
                                        <button type="submit"
                                            class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors">Mark
                                            all read</button>
                                    </form>
                                @endif
                            </div>

                            @php $recentNotifs = auth()->user()->notifications()->latest()->take(15)->get(); @endphp

                            @if ($recentNotifs->isEmpty())
                                <div class="flex flex-col items-center justify-center py-14 px-6 text-center">
                                    <div
                                        class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-700">You're all caught up!</p>
                                    <p class="text-sm text-gray-400 mt-1">No notifications yet.</p>
                                </div>
                            @else
                                <div class="divide-y divide-gray-100 max-h-[460px] overflow-y-auto">
                                    @foreach ($recentNotifs as $notif)
                                        @php $d = $notif->data; @endphp
                                        <a href="{{ route('notifications.read', $notif->id) }}"
                                            class="flex items-start gap-4 px-5 py-4 hover:bg-gray-50 transition-colors {{ $notif->read_at ? 'bg-white' : 'bg-blue-50' }}">
                                            <div
                                                class="flex-shrink-0 w-11 h-11 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-sm">
                                                <span
                                                    class="text-white text-sm font-bold">{{ strtoupper(substr($d['mentioned_by_name'], 0, 1)) }}</span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-gray-800 leading-snug">
                                                    <span class="font-semibold">{{ $d['mentioned_by_name'] }}</span>
                                                    mentioned you in
                                                    <span
                                                        class="font-medium text-blue-700">{{ Str::limit($d['request_purpose'], 45) }}</span>
                                                </p>
                                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">
                                                    {{ $d['body_preview'] }}</p>
                                                <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $notif->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                            @if (!$notif->read_at)
                                                <span
                                                    class="flex-shrink-0 w-2.5 h-2.5 bg-blue-500 rounded-full mt-2 ring-2 ring-blue-100"></span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- User avatar --}}
                    <div class="ml-2">
                        <div class="w-8 h-8 bg-[#1D3557] rounded-full flex items-center justify-center">
                            <span
                                class="text-white text-xs font-semibold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Header -->
            @isset($header)
                <div class="bg-white border-b border-gray-200 px-6 py-4">
                    {{ $header }}
                </div>
            @endisset

            <!-- Flash Messages -->
            <div class="px-6 pt-4">
                @if (auth()->user()->isMarcom() || auth()->user()->isAdmin())
                    @php
                        $overdueAlertCount = \App\Models\MarketingRequest::where('status', 'approved')
                            ->whereIn('production_status', ['pending', 'on_process', 'revision'])
                            ->whereDate('deadline', '<', today())
                            ->count();
                    @endphp
                    @if ($overdueAlertCount > 0)
                        <div x-data="{ show: true }" x-show="show" x-cloak x-transition
                            class="flex items-center justify-between p-4 mb-4 bg-orange-50 border border-orange-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-orange-500 mr-2 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span class="text-sm text-orange-700">
                                    <strong>{{ $overdueAlertCount }} overdue
                                        project{{ $overdueAlertCount > 1 ? 's' : '' }}</strong> — deadline has passed
                                    but production is not yet completed.
                                    <a href="{{ route('production.index') }}"
                                        class="underline font-semibold ml-1">View production queue →</a>
                                </span>
                            </div>
                            <button @click="show = false"
                                class="text-orange-400 hover:text-orange-600 ml-3 flex-shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    @endif
                @endif
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-cloak x-transition
                        class="flex items-center justify-between p-4 mb-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm text-green-700">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-green-400 hover:text-green-600"><svg
                                class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg></button>
                    </div>
                @endif
                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-cloak x-transition
                        class="flex items-center justify-between p-4 mb-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm text-red-700">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-red-400 hover:text-red-600"><svg class="w-4 h-4"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg></button>
                    </div>
                @endif
            </div>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto px-6 pb-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Live update banner: polls every 30s, shows notification if counts change --}}
    <div id="update-banner" style="display:none"
        class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 bg-[#1D3557] text-white text-sm font-medium px-5 py-3 rounded-full shadow-lg">
        <svg class="w-4 h-4 text-blue-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span id="update-banner-text">New updates available</span>
        <button onclick="window.location.reload()"
            class="ml-1 bg-white text-[#1D3557] text-xs font-bold px-3 py-1 rounded-full hover:bg-blue-50 transition-colors">
            Refresh
        </button>
        <button onclick="document.getElementById('update-banner').style.display='none'"
            class="text-blue-300 hover:text-white ml-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <script>
        (function() {
            const POLL_INTERVAL = 30000; // 30 seconds
            const pollUrl = '{{ route('poll.counts') }}';

            // Which counts are relevant to the current page
            const currentRoute = '{{ request()->route()?->getName() }}';
            const watchApprovals = ['approvals.index', 'approvals.all'].includes(currentRoute);
            const watchProduction = ['production.index', 'production.completed'].includes(currentRoute);
            const watchMyRequests = ['requests.index'].includes(currentRoute);

            // Only poll on list pages where it's useful
            if (!watchApprovals && !watchProduction && !watchMyRequests) return;

            let baseline = null;

            function getWatchedValue(data) {
                if (watchApprovals) return data.pending_approvals;
                if (watchProduction) return data.production_active;
                if (watchMyRequests) return data.my_requests;
                return null;
            }

            function showBanner(text) {
                document.getElementById('update-banner-text').textContent = text;
                document.getElementById('update-banner').style.display = 'flex';
            }

            async function poll() {
                try {
                    const res = await fetch(pollUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) return;
                    const data = await res.json();

                    if (baseline === null) {
                        baseline = getWatchedValue(data);
                        return;
                    }

                    const current = getWatchedValue(data);
                    if (current !== baseline) {
                        const diff = current - baseline;
                        let msg = 'Page data has changed — refresh to see updates';
                        if (watchApprovals && diff > 0) msg =
                            `${diff} new request${diff > 1 ? 's' : ''} waiting for approval`;
                        else if (watchApprovals && diff < 0) msg = 'Approval queue updated';
                        else if (watchProduction && diff > 0) msg =
                            `${diff} new item${diff > 1 ? 's' : ''} in production queue`;
                        else if (watchProduction && diff < 0) msg = 'Production queue updated';
                        else if (watchMyRequests && diff > 0) msg = 'You have new requests';
                        showBanner(msg);
                        baseline = current; // reset so it doesn't keep re-triggering
                    }
                } catch (_) {
                    // silently ignore network errors
                }
            }

            // Start polling after 30s
            setTimeout(function tick() {
                poll();
                setTimeout(tick, POLL_INTERVAL);
            }, POLL_INTERVAL);
        })();
    </script>
    @stack('scripts')
</body>

</html>
