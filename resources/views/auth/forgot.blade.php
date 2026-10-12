@extends('layouts.auth')
@section('title', 'Forgot password — MushFungAI')
@section('variant', 'tall')
@section('card')
  <div class="au-logo"><img src="{{ asset('images/mushroom.png') }}" alt=""></div>
  <h2 class="brand">MushFung<i>AI</i></h2>
  <hr>
  <p class="sub">Reset your password</p>
  <p>We'll email you a one-time code to confirm it's really you.</p>
@endsection
@section('right')
  <a class="back" href="{{ route('login') }}" aria-label="Back to sign in">←</a>
  <h1 class="hello" style="font-size:clamp(38px,4.4vw,60px);margin-bottom:22px">Forgot password?</h1>
  <p class="lead" style="margin-top:0">Enter your email, then the OTP we send you.</p>
  <form method="POST" action="{{ route('password.verify') }}">@csrf
    <label>Email</label>
    <div class="row">
      <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
      <button type="submit" class="btn-sm" formaction="{{ route('password.send') }}" formnovalidate>Send OTP</button>
    </div>
    <label>OTP</label>
    <input name="otp" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code" placeholder="6-digit code" required>
    <div class="act" style="margin-top:34px"><button class="pill">Submit</button></div>
  </form>
@endsection
