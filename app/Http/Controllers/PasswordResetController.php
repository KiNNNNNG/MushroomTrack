<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $mailer = config('mail.password_reset_mailer');
        $transport = config("mail.mailers.{$mailer}.transport");
        if (!in_array($transport, ['smtp', 'sendmail', 'ses', 'ses-v2', 'postmark', 'resend', 'mailgun'], true)
            || blank(config('mail.from.address')) || str_ends_with((string) config('mail.from.address'), '@example.com')) {
            return back()->withInput(['email' => $email])->withErrors([
                'email' => 'Password reset email delivery is not configured. Please contact the farm administrator.',
            ]);
        }
        $user = User::where('email', $email)->where('status', 'active')->first();
        if ($user) {
            $code = (string) random_int(100000, 999999);
            $otp = PasswordOtp::create(['email' => $email, 'code' => Hash::make($code), 'expires_at' => now()->addMinutes(10)]);
            try {
                Mail::mailer($mailer)->to($email)->send(new PasswordResetCodeMail($code));
            } catch (\Throwable $e) {
                $otp->delete();
                report($e);
                return back()->withInput(['email' => $email])->withErrors([
                    'email' => 'We could not send the reset email. Please try again later or contact the farm administrator.',
                ]);
            }
            // Keep a previously sent code valid until its replacement is actually sent.
            PasswordOtp::where('email', $email)->where('id', '<>', $otp->id)->delete();
        }
        return back()->withInput(['email' => $email])
            ->with('ok', 'If that email is registered, a 6-digit code has been sent. Check your inbox and spam folder. It expires in 10 minutes.');
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
