@extends('layouts.admin')
@section('content')
<div class="uhead">
  <h2>PERSONNEL MANAGEMENT</h2>
  <button class="btn" onclick="document.getElementById('u-new').showModal()">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="8" r="4"/><path d="M2 21v-1a6 6 0 0 1 12 0v1M19 8v6M16 11h6"/></svg>ADD NEW USER
  </button>
</div>
<div class="users">
  @foreach ($users as $u)
    <div class="card user" onclick="document.getElementById('u{{ $u->id }}').showModal()">
      <div class="l">
        <div class="av">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</div>
        <div><b>{{ $u->name }}</b><small>{{ $u->role }}</small></div>
      </div>
      <span class="pill {{ $u->status === 'active' ? '' : 'off' }}">{{ $u->status }}</span>
    </div>
    @include('admin.partials.user-form', ['u' => $u])
  @endforeach
</div>
@include('admin.partials.user-form', ['u' => null])
@endsection
