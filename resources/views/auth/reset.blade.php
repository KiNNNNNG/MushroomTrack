@extends('layouts.auth')
@section('title', 'New password — MushFungAI')
@section('variant', 'tall')
@section('card')
  <div class="au-logo"><img src="{{ asset('images/mushroom.png') }}" alt=""></div>
  <h2 class="brand">MushFung<i>AI</i></h2>
  <hr>
  <p class="sub">Set a new password</p>
  <p>Choose at least 8 characters. You'll be signed in right after.</p>
@endsection
@section('right')
  <a class="back" href="{{ route('password.forgot') }}" aria-label="Back">←</a>
  <h1 class="hello" style="font-size:clamp(34px,4vw,52px);margin-bottom:34px">Set your new password</h1>
  <form method="POST" action="{{ route('password.update') }}">@csrf
    <label>New password</label>
    <input type="password" name="password" autocomplete="new-password" minlength="8" required autofocus>
    <label>Confirm password</label>
    <input type="password" name="password_confirmation" autocomplete="new-password" required>
    <div class="act" style="margin-top:34px"><button class="pill">Sign in</button></div>
  </form>
@endsection
