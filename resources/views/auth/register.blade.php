@extends('layouts.auth')
@section('title', 'Create account — MushFungAI')

@section('right')
  <div class="au-tag">GET STARTED</div>
  <h1 class="au-heading">Create account</h1>
  <p class="au-subtext">Browse fresh harvests, place orders, and track them in one place.</p>

  <div class="segmented-nav">
    <a href="{{ route('login') }}">Sign in</a>
    <a href="{{ route('register') }}" class="on">Sign up</a>
  </div>

  <form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group">
      <label class="form-label">Username</label>
      <div class="input-icon-wrap">
        <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
        <input type="text" name="username" value="{{ old('username') }}" placeholder="Choose a username" autocomplete="username" required autofocus>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Contact number</label>
      <div class="input-icon-wrap">
        <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
        </svg>
        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="09XX XXX XXXX" autocomplete="tel">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Password</label>
      <div class="input-icon-wrap">
        <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        <input type="password" id="regPw" name="password" class="has-eye" placeholder="Create a password" autocomplete="new-password" required>
        <button type="button" class="eye-toggle-btn" onclick="togglePassword('regPw', this)" aria-label="Toggle password visibility">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="7" r="3"/>
          </svg>
        </button>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Confirm password</label>
      <div class="input-icon-wrap">
        <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        <input type="password" id="regPwConf" name="password_confirmation" class="has-eye" placeholder="Confirm your password" autocomplete="new-password" required>
        <button type="button" class="eye-toggle-btn" onclick="togglePassword('regPwConf', this)" aria-label="Toggle password visibility">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="7" r="3"/>
          </svg>
        </button>
      </div>
    </div>

    <div class="notice-box">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a87834" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
      </svg>
      <div>This creates a customer account. Farm staff accounts are created by an administrator.</div>
    </div>

    <button type="submit" class="btn-primary-auth">
      Create account
    </button>
  </form>

  <div class="auth-switch-footer">
    Already have an account? <a href="{{ route('login') }}">Sign in</a>
  </div>
@endsection
