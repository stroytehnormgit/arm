<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    /**
     * Отобразить список пользователей с фильтрацией
     */
    public function index(Request $request)
    {
        $request->user()->can('users.manage') || abort(403);

        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $roles = Role::all()->pluck('name');

        return Inertia::render('Users', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Создать нового пользователя
     */
    public function store(Request $request)
    {
        $request->user()->can('users.manage') || abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,employee',
            'block_rank' => 'nullable|integer|min:1|max:7',
            'department' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'block_rank' => $request->role === 'admin' ? null : $request->block_rank,
            'department' => $request->department,
        ]);

        $user->assignRole($request->role);

        return redirect()->back()->with('success', 'Пользователь успешно создан');
    }

    /**
     * Обновить существующего пользователя
     */
    public function update(Request $request, User $user)
    {
        $request->user()->can('users.manage') || abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,employee',
            'block_rank' => 'nullable|integer|min:1|max:7',
            'department' => 'nullable|string|max:255',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->block_rank = $request->role === 'admin' ? null : $request->block_rank;
        $user->department = $request->department;
        $user->save();

        $user->syncRoles([$request->role]);

        return redirect()->back()->with('success', 'Пользователь успешно обновлен');
    }

    /**
     * Удалить пользователя
     */
    public function destroy(Request $request, User $user)
    {
        $request->user()->can('users.manage') || abort(403);

        if ($user->id === $request->user()->id) {
            return redirect()->back()->withErrors(['error' => 'Нельзя удалить самого себя']);
        }

        $user->delete();

        return redirect()->back()->with('success', 'Пользователь успешно удален');
    }
}
