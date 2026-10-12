<?php

namespace App\Http\Controllers;

use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    public function showForgot()
    {
        return view('auth.forgot');
    }

    /** Step 1: email a 6-digit OTP (valid 10 minutes). */
    public function sendOtp(Request $request)
    {
        $email = $request->validate(['email' => ['required', 'email']])['email'];

        $user = User::where('email', $email)->where('status', 'active')->first();
        $devOtp = null;

        if ($user) {
            PasswordOtp::where('email', $email)->delete();
            $code = (string) random_int(100000, 999999);
            PasswordOtp::create(['email' => $email, 'code' => Hash::make($code), 'expires_at' => now()->addMinutes(10)]);

            try {
                Mail::raw("Your MushFungAI password reset code is {$code}. It expires in 10 minutes.\n\nIf you did not request this, ignore this email.",
                    fn ($m) => $m->to($email)->subject('MushFungAI password reset code'));
            } catch (\Throwable $e) {
                Log::warning('OTP mail failed: '.$e->getMessage());
            }

            if (app()->environment('local') && config('app.debug')) {
                Log::info("MushFungAI OTP for {$email}: {$code}");
                $devOtp = $code;   // shown on screen ONLY while developing locally
            }
        }

        // Same answer whether or not the email exists (prevents account guessing)
        return back()->withInput(['email' => $email])
            ->with('ok', 'If that email is registered, we sent a 6-digit OTP. It expires in 10 minutes.')
            ->with('dev_otp', $devOtp);
    }

    /** Step 2: check the OTP. */
    public function verifyOtp(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'otp' => ['required', 'digits:6']]);

        $row = PasswordOtp::where('email', $data['email'])->latest()->first();
        $fail = fn ($msg) => back()->withInput(['email' => $data['email']])->withErrors(['otp' => $msg]);

        if (! $row || $row->expires_at->isPast()) return $fail('OTP is invalid or expired. Please request a new one.');
        if ($row->attempts >= 5) return $fail('Too many attempts. Please request a new OTP.');

        if (! Hash::check($data['otp'], $row->code)) {
            $row->increment('attempts');
            return $fail('Incorrect OTP.');
        }

        $row->delete();
        $request->session()->put(['reset_email' => $data['email'], 'reset_until' => now()->addMinutes(15)->timestamp]);

        return redirect()->route('password.reset');
    }

    private function allowed(Request $request): bool
    {
        return $request->session()->has('reset_email') && $request->session()->get('reset_until', 0) > now()->timestamp;
    }

    public function showReset(Request $request)
    {
        if (! $this->allowed($request)) return redirect()->route('password.forgot');

        return view('auth.reset');
    }

    /** Step 3: save the new password and sign the user in. */
    public function reset(Request $request)
    {
        if (! $this->allowed($request)) return redirect()->route('password.forgot');

        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);

        $user = User::where('email', $request->session()->get('reset_email'))->firstOrFail();
        $user->forceFill(['password' => Hash::make($data['password'])])->save();

        $request->session()->forget(['reset_email', 'reset_until']);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('ok', 'Your password has been updated.');
    }
}
