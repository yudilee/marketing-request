<x-app-layout>
    @section('title', 'Manage Users')

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Manage Users</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $users->total() }} user(s) in the system</p>
            </div>
            <a href="{{ route('users.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add User
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        @if (session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="mb-4 flex gap-3 flex-wrap items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Name, email or username..."
                    class="rounded-lg border-gray-200 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500 w-60" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                <select name="role"
                    class="rounded-lg border-gray-200 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" {{ request('role') === $role->value ? 'selected' : '' }}>
                            {{ $role->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                Filter
            </button>
            @if (request('search') || request('role'))
                <a href="{{ route('users.index') }}"
                    class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 underline">Clear</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            @if ($users->isEmpty())
                <div class="text-center py-16 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m6 5.87a4 4 0 10-8 0m8 0H9m3-10a4 4 0 10-8 0 4 4 0 008 0z" />
                    </svg>
                    <p class="font-medium">No users found</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-left">Username</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Role</th>
                            <th class="px-5 py-3 text-left">Department</th>
                            <th class="px-5 py-3 text-left">Created</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $user->username }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $user->email }}</td>
                                <td class="px-5 py-3">
                                    @php
                                        $roleColors = [
                                            'admin' => 'bg-red-50 text-red-700',
                                            'marcom' => 'bg-purple-50 text-purple-700',
                                            'manager' => 'bg-blue-50 text-blue-700',
                                            'gm' => 'bg-indigo-50 text-indigo-700',
                                            'director' => 'bg-yellow-50 text-yellow-700',
                                            'staff' => 'bg-gray-100 text-gray-600',
                                        ];
                                        $color = $roleColors[$user->role->value] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="text-xs px-2 py-0.5 rounded font-medium {{ $color }}">
                                        {{ $user->role->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $user->department?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('users.edit', $user) }}"
                                            class="text-xs px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition font-medium">
                                            Edit
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                                onsubmit="return confirm('Delete user {{ addslashes($user->name) }}? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-xs px-3 py-1.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition font-medium">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($users->hasPages())
                    <div class="px-5 py-3 border-t border-gray-100">
                        {{ $users->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
