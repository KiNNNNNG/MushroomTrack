@extends('layouts.auth')
@section('title', 'Sign up — MushFungAI')
@section('card')
  <h2 class="brand">MushFung<i>AI</i></h2>
  <p>Create a customer account to browse fresh harvests, place orders and track them in one place.</p>
  <p>Farm staff accounts are created by an administrator.</p>
@endsection
@section('right')
  <h1 class="hello">Welcome</h1>
  <div class="tabs2"><a href="{{ route('login') }}">Sign in</a><a href="{{ route('register') }}" class="on">Sign up</a></div>
  <form method="POST" action="{{ route('register') }}">@csrf
    <label>Username</label>
    <input name="username" value="{{ old('username') }}" autocomplete="username" required autofocus>
    <label>Email</label>
    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
    <label>Password</label>
    <input type="password" name="password" autocomplete="new-password" minlength="8" required>
    <label>Confirm password</label>
    <input type="password" name="password_confirmation" autocomplete="new-password" required>
    <div class="act"><button class="pill">Sign up</button></div>
  </form>
@endsection
