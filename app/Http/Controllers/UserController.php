<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'role'     => ['required', 'in:admin,worker,customer'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $base = \Illuminate\Support\Str::slug(\Illuminate\Support\Str::before($data['email'], '@'), '_') ?: 'user';
        $username = $base; $i = 1;
        while (User::where('username', $username)->exists()) { $username = $base.$i++; }

        $user = new User();
        $user->forceFill([
            'name' => $data['name'], 'username' => $username, 'email' => $data['email'], 'role' => $data['role'],
            'status' => 'active', 'password' => Hash::make($data['password']),
        ])->save();
        ActivityLog::record('User created', "{$user->name} ({$user->role})");

        return back()->with('ok', 'User added.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email,'.$user->id],
            'role'     => ['required', 'in:admin,worker,customer'],
            'status'   => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if ($user->id === $request->user()->id && ($data['role'] !== 'admin' || $data['status'] !== 'active')) {
            return back()->withErrors('You cannot demote or deactivate your own account.');
        }

        $user->forceFill(collect($data)->except('password')->all());
        if (! empty($data['password'])) {
            $user->forceFill(['password' => Hash::make($data['password'])]);
        }
        $user->save();
        ActivityLog::record('User updated', $user->name);

        return back()->with('ok', 'User updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors('You cannot delete your own account.');
        }
        $user->delete();
        ActivityLog::record('User deleted', $user->name);

        return back()->with('ok', 'User deleted.');
    }
}
