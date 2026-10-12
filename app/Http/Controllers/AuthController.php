<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // The "Username" box also accepts an email address
        $field = filter_var($data['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$field => $data['username'], 'password' => $data['password']], $request->boolean('remember'))) {
            return back()->withErrors(['username' => 'Invalid username or password.'])->onlyInput('username');
        }

        if (Auth::user()->status === 'inactive') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['username' => 'This account is inactive.'])->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Self-registration always creates a customer. Admins can promote users later.
        $user = new User();
        $user->forceFill([
            'name' => $data['username'], 'username' => $data['username'], 'email' => $data['email'],
            'role' => 'customer', 'status' => 'active', 'password' => Hash::make($data['password']),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('ok', 'Welcome to MushFungAI!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
