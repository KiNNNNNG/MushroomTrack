@extends('layouts.auth')
@section('title', 'New password — MushFungAI')

@section('right')
  <a class="back-link" href="{{ route('password.forgot') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;color:var(--text-muted);text-decoration:none;margin-bottom:20px;">
    ← Back
  </a>
  <div class="au-tag">SECURE ACCESS</div>
  <h1 class="au-heading">Set new password</h1>
  <p class="au-subtext">Choose a strong password with at least 8 characters.</p>

  <form method="POST" action="{{ route('password.update') }}">
    @csrf
    <div class="form-group">
      <label class="form-label">New password</label>
      <div class="input-icon-wrap">
        <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        <input type="password" id="resetPw" name="password" class="has-eye" autocomplete="new-password" minlength="8" placeholder="At least 8 characters" required autofocus>
        <button type="button" class="eye-toggle-btn" onclick="togglePassword('resetPw', this)" aria-label="Toggle password visibility">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="7" r="3"/>
          </svg>
        </button>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Confirm new password</label>
      <div class="input-icon-wrap">
        <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        <input type="password" id="resetPwConf" name="password_confirmation" class="has-eye" autocomplete="new-password" placeholder="Re-enter new password" required>
        <button type="button" class="eye-toggle-btn" onclick="togglePassword('resetPwConf', this)" aria-label="Toggle password visibility">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="7" r="3"/>
          </svg>
        </button>
      </div>
    </div>

    <button type="submit" class="btn-primary-auth" style="margin-top:24px;">
      Save and sign in
    </button>
  </form>
@endsection
