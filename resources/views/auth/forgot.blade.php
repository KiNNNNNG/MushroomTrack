@extends('layouts.auth')
@section('title', 'Forgot password — MushFungAI')

@section('right')
  <a class="back-link" href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;color:var(--text-muted);text-decoration:none;margin-bottom:20px;">
    ← Back to sign in
  </a>
  <div class="au-tag">ACCOUNT RECOVERY</div>
  <h1 class="au-heading">Forgot password?</h1>
  <p class="au-subtext">Enter your email to receive a 6-digit verification code.</p>

  <form method="POST" action="{{ route('password.verify') }}">
    @csrf
    <div class="form-group">
      <label class="form-label">Email address</label>
      <div style="display:flex;gap:8px;">
        <div class="input-icon-wrap" style="flex:1;">
          <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
          <input type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" autocomplete="email" required>
        </div>
        <button type="submit" formaction="{{ route('password.send') }}" formnovalidate class="btn-primary-auth" style="width:auto;padding:0 18px;white-space:nowrap;font-size:13px;">
          Send OTP
        </button>
      </div>
    </div>

    <div class="form-group" style="margin-top:20px;">
      <label class="form-label">Verification code (OTP)</label>
      <div class="input-icon-wrap">
        <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        <input name="otp" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code" placeholder="Enter 6-digit code" required>
      </div>
    </div>

    <button type="submit" class="btn-primary-auth" style="margin-top:24px;">
      Verify and continue
    </button>
  </form>
@endsection
