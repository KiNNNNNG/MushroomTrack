@extends('layouts.auth')
@section('title', 'Sign in — MushFungAI')
@section('card')
  <h2 class="brand">MushFung<i>AI</i></h2>
  <p>Smart tracking for mushroom farms — follow every batch from substrate to harvest.</p>
  <p>Record daily checks and yields, manage customer orders, and keep your whole team in sync.</p>
@endsection
@section('right')
  <h1 class="hello">Welcome</h1>
  <div class="tabs2"><a href="{{ route('login') }}" class="on">Sign in</a><a href="{{ route('register') }}">Sign up</a></div>
  <form method="POST" action="{{ route('login') }}">@csrf
    <label>Username</label>
    <input name="username" value="{{ old('username') }}" placeholder="Username or email" autocomplete="username" required autofocus>
    <label>Password</label>
    <input type="password" name="password" autocomplete="current-password" required>
    <div class="forgot"><a href="{{ route('password.forgot') }}">Forgot?</a></div>
    <div class="act c"><button class="pill">Enter</button></div>
  </form>
@endsection
