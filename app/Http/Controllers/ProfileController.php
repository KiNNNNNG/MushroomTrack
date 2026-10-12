<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Sms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('profile', ['u' => $request->user()]);
    }

    public function update(Request $request)
    {
        $u = $request->user();
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash', 'unique:users,username,'.$u->id],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email,'.$u->id],
            'phone'    => ['nullable', 'string', 'max:30'],
            'address'  => ['nullable', 'string', 'max:255'],
        ]);
        $u->forceFill($data)->save();

        // Texts need a real Philippine mobile number; switch SMS off instead of silently failing every time
        if ($u->notify_sms && ! Sms::normalize($u->phone)) {
            $u->forceFill(['notify_sms' => false])->save();

            return back()->with('ok', 'Profile updated. SMS notifications were switched off because the phone number is not a valid Philippine mobile number.');
        }

        return back()->with('ok', 'Profile updated.');
    }

    private function dropAvatar(User $u): void
    {
        if ($u->avatar && Str::startsWith($u->avatar, 'uploads/avatars/') && is_file(public_path($u->avatar))) {
            @unlink(public_path($u->avatar));
        }
    }

    public function photo(Request $request)
    {
        $request->validate(['photo' => ['required', 'image', 'max:4096']]);
        $u = $request->user();
        $this->dropAvatar($u);

        $file = $request->file('photo');
        $name = Str::random(24).'.'.$file->extension();
        $file->move(public_path('uploads/avatars'), $name);
        $u->forceFill(['avatar' => 'uploads/avatars/'.$name])->save();

        return back()->with('ok', 'Profile photo updated.');
    }

    public function removePhoto(Request $request)
    {
        $u = $request->user();
        $this->dropAvatar($u);
        $u->forceFill(['avatar' => null])->save();

        return back()->with('ok', 'Profile photo removed.');
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $request->user()->forceFill(['password' => Hash::make($data['password'])])->save();

        return back()->with('ok', 'Password changed.');
    }

    public function notifications(Request $request)
    {
        $u = $request->user();

        if ($request->boolean('notify_sms') && ! Sms::normalize($u->phone)) {
            return back()->withErrors('To get text messages, first save a valid Philippine mobile number (like 0917 123 4567) in your profile.');
        }

        $u->forceFill([
            'notify_in_app' => $request->boolean('notify_in_app'),
            'notify_email'  => $request->boolean('notify_email'),
            'notify_sms'    => $request->boolean('notify_sms'),
        ])->save();

        return back()->with('ok', 'Notification settings saved.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $u = $request->user();

        if ($u->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors('You are the only admin, so this account cannot be deleted.');
        }

        $this->dropAvatar($u);
        Auth::logout();
        $u->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
