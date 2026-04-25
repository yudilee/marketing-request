<x-app-layout>
    @section('title', 'Add User')

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Add User</h1>
                <p class="text-sm text-gray-500 mt-0.5">Create a new system user</p>
            </div>
        </div>
    </x-slot>

    <div class="py-4 max-w-2xl">
        <form method="POST" action="{{ route('users.store') }}"
            class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <x-input-label for="name" value="Full Name" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                        value="{{ old('name') }}" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="username" value="Username" />
                    <x-text-input id="username" name="username" type="text" class="mt-1 block w-full"
                        value="{{ old('username') }}" required />
                    <x-input-error :messages="$errors->get('username')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="email" value="Email Address" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                    value="{{ old('email') }}" required />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <x-input-label for="role" value="Role" />
                    <select id="role" name="role"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                        required>
                        <option value="">Select a role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" {{ old('role') === $role->value ? 'selected' : '' }}>
                                {{ $role->label() }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="department_id" value="Department" />
                    <select id="department_id" name="department_id"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">— None —</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('department_id')" class="mt-1" />
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <x-primary-button>Create User</x-primary-button>
                <a href="{{ route('users.index') }}"
                    class="text-sm text-gray-500 hover:text-gray-700 underline">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
