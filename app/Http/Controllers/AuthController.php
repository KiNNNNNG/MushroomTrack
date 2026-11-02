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

        $identifier = trim($data['username']);

        // Accept username, email address, or contact number
        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->orWhere(function ($q) use ($identifier) {
                if (!empty($identifier) && strlen($identifier) >= 7) {
                    $q->where('phone', $identifier);
                }
            })
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['username' => 'Invalid username or password.'])->onlyInput('username');
        }

        if ($user->status === 'inactive') {
            return back()->withErrors(['username' => 'This account is inactive.'])->onlyInput('username');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $role = $user->role;
        $dest = match($role) {
            'admin'  => route('admin.dashboard'),
            'worker' => route('worker.overview'),
            default  => route('customer.shop'),
        };

        // If an intended URL exists, only allow it if it strictly belongs to this user's role
        $intended = $request->session()->get('url.intended');
        $request->session()->forget('url.intended');

        if ($intended) {
            $isValidIntended = match($role) {
                'admin'  => str_contains($intended, '/admin'),
                'worker' => str_contains($intended, '/worker'),
                default  => str_contains($intended, '/shop'),
            };

            if ($isValidIntended && !str_ends_with($intended, '/login') && !str_ends_with($intended, '/register')) {
                return redirect()->to($intended);
            }
        }

        return redirect()->to($dest);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash', 'unique:users,username'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'email'    => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $username = trim($data['username']);
        $email = !empty($data['email']) ? $data['email'] : $username . '@mushfungai.local';
        $suffix = 1;
        while (User::where('email', $email)->exists()) {
            $email = $username . ($suffix++) . '@mushfungai.local';
        }

        // Self-registration always creates a customer. Admins can promote users later.
        $user = new User();
        $user->forceFill([
            'name'     => $username,
            'username' => $username,
            'phone'    => $data['phone'] ?? null,
            'email'    => $email,
            'role'     => 'customer',
            'status'   => 'active',
            'password' => Hash::make($data['password']),
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
