@extends('layouts.admin')
@section('content')
@php $av = $u->avatar ? asset($u->avatar) : null; @endphp
<div class="pgrid">
  {{-- left: avatar + summary --}}
  <div class="card pc pav">
    <div class="av2">@if ($av)<img src="{{ $av }}" alt="">@else<x-icon n="user" s="90" />@endif</div>
    <h2>{{ $u->name }}</h2>
    <div class="pinfo">
      <div><span>Role</span><b>{{ ucfirst($u->role) }}</b></div>
      <div><span>Username</span><b>{{ $u->username }}</b></div>
      <div><span>Member since</span><b>{{ $u->created_at->format('M Y') }}</b></div>
    </div>
    <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data">@csrf
      <input type="file" name="photo" id="avIn" accept="image/*" hidden onchange="this.form.submit()">
      <button type="button" class="mb" style="margin:0 auto" onclick="document.getElementById('avIn').click()"><x-icon n="camera" s="16" />CHANGE PHOTO</button>
    </form>
  </div>

  {{-- right: personal details --}}
  <form class="card pc" method="POST" action="{{ route('profile.update') }}">@csrf @method('PUT')
    <div class="pc-h">Personal information</div>
    <div class="pf">
      <div class="rw"><label>Full name</label><input name="name" value="{{ old('name', $u->name) }}" required></div>
      <div class="rw"><label>Username</label><input name="username" value="{{ old('username', $u->username) }}" required></div>
      <div class="rw"><label>Email</label><input type="email" name="email" value="{{ old('email', $u->email) }}" required></div>
      <div class="rw"><label>Phone</label><input name="phone" value="{{ old('phone', $u->phone) }}" placeholder="+63 900 000 0000"></div>
      <div class="rw"><label>Address</label><input name="address" value="{{ old('address', $u->address) }}"></div>
      <div class="fa"><button class="btn">SAVE CHANGES</button></div>
    </div>
  </form>
</div>

{{-- bottom: password and other settings --}}
<div class="card pc" style="margin-top:40px">
  <div class="pc-h">Password and other settings</div>
  <div class="setl">
    <div><b>Change password</b><span>Use a strong password you don't use anywhere else.</span></div>
    <button class="ebtn" onclick="document.getElementById('dPw').showModal()">EDIT</button>
  </div>
  <div class="setl">
    <div><b>Notifications</b><span>Bell {{ $u->notify_in_app ? 'ON' : 'OFF' }} · Email {{ $u->notify_email ? 'ON' : 'OFF' }} · SMS {{ $u->notify_sms ? 'ON' : 'OFF' }}</span></div>
    <button class="ebtn" onclick="document.getElementById('dNt').showModal()">EDIT</button>
  </div>
  <div class="setl">
    <div><b>Account preferences</b><span>Manage your profile photo or delete your account.</span></div>
    <button class="ebtn" onclick="document.getElementById('dPf').showModal()">EDIT</button>
  </div>
</div>

<dialog id="dPw">
  <form method="POST" action="{{ route('profile.password') }}">@csrf @method('PUT')
    <h3>Change password</h3>
    <div class="f"><label>Current password</label><input type="password" name="current_password" required></div>
    <div class="f"><label>New password</label><input type="password" name="password" minlength="8" required></div>
    <div class="f"><label>Confirm new password</label><input type="password" name="password_confirmation" required></div>
    <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CANCEL</button><button class="btn">SAVE</button></div>
  </form>
</dialog>

<dialog id="dNt">
  <form method="POST" action="{{ route('profile.notifications') }}">@csrf @method('PUT')
    <h3>Notifications</h3>
    <label class="chk2"><input type="checkbox" name="notify_in_app" value="1" @checked($u->notify_in_app)> In-app notifications (bell icon)</label>
    <label class="chk2"><input type="checkbox" name="notify_email" value="1" @checked($u->notify_email)> Email me about order updates and reminders</label>
    <label class="chk2"><input type="checkbox" name="notify_sms" value="1" @checked($u->notify_sms)> Text me (SMS) about order updates and reminders</label>
    <p class="chkh">SMS goes to the phone number in your profile and needs a Philippine mobile number (like 0917 123 4567).</p>
    <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CANCEL</button><button class="btn">SAVE</button></div>
  </form>
</dialog>

<dialog id="dPf">
  <h3>Account preferences</h3>
  @if ($u->avatar)
    <form method="POST" action="{{ route('profile.photo.remove') }}">@csrf @method('DELETE')
      <button class="btn ghost" style="margin-bottom:22px">REMOVE PROFILE PHOTO</button></form>
  @endif
  <form method="POST" action="{{ route('profile.destroy') }}" data-confirm="Delete your account permanently? This cannot be undone." data-title="Delete account" data-yes="DELETE" data-tone="danger">@csrf @method('DELETE')
    <div class="f"><label>Delete account — confirm with your password</label><input type="password" name="password" required></div>
    <button class="dng">DELETE MY ACCOUNT</button>
  </form>
  <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CLOSE</button></div>
</dialog>
@endsection
