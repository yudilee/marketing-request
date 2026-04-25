<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct()
    {
        // Only admins can access user management
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->isAdmin()) {
                abort(403, 'Access denied.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = User::with('department')->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        $users       = $query->paginate(20)->withQueryString();
        $roles       = Role::cases();
        $departments = Department::orderBy('name')->get();

        return view('users.index', compact('users', 'roles', 'departments'));
    }

    public function create()
    {
        $roles       = Role::cases();
        $departments = Department::orderBy('name')->get();
        return view('users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:255|unique:users,username',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => ['required', Password::min(8)],
            'role'          => 'required|in:' . implode(',', array_column(Role::cases(), 'value')),
            'department_id' => 'nullable|exists:departments,id',
        ]);

        User::create([
            'name'          => $validated['name'],
            'username'      => $validated['username'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'role'          => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User "' . $validated['name'] . '" created successfully.');
    }

    public function edit(User $user)
    {
        $roles       = Role::cases();
        $departments = Department::orderBy('name')->get();
        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:255|unique:users,username,' . $user->id,
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'      => ['nullable', Password::min(8)],
            'role'          => 'required|in:' . implode(',', array_column(Role::cases(), 'value')),
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $data = [
            'name'          => $validated['name'],
            'username'      => $validated['username'],
            'email'         => $validated['email'],
            'role'          => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'User "' . $user->name . '" updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User "' . $name . '" deleted successfully.');
    }
}
