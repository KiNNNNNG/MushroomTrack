@extends('layouts.auth')
@section('title', 'Sign in — MushFungAI')

@section('right')
  <div class="au-tag">WELCOME BACK</div>
  <h1 class="au-heading">Sign in</h1>
  <p class="au-subtext">Enter your account details to continue.</p>

  <div class="segmented-nav">
    <a href="{{ route('login') }}" class="on">Sign in</a>
    <a href="{{ route('register') }}">Sign up</a>
  </div>

  <form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="form-group">
      <label class="form-label">Username</label>
      <div class="input-icon-wrap">
        <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
        <input type="text" name="username" value="{{ old('username') }}" placeholder="Your username" autocomplete="username" required autofocus>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Password</label>
      <div class="input-icon-wrap">
        <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        <input type="password" id="loginPw" name="password" class="has-eye" placeholder="Your password" autocomplete="current-password" required>
        <button type="button" class="eye-toggle-btn" onclick="togglePassword('loginPw', this)" aria-label="Toggle password visibility">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="7" r="3"/>
          </svg>
        </button>
      </div>
    </div>

    <div class="form-helper-row">
      <label>
        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
        <span>Remember me</span>
      </label>
      <a href="{{ route('password.forgot') }}" class="forgot-link">Forgot password?</a>
    </div>

    <button type="submit" class="btn-primary-auth">
      <span>Sign in</span>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
      </svg>
    </button>
  </form>
@endsection
