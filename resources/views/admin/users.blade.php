@extends('layouts.admin')
@section('content')

<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h1 style="font-size: 24px; font-weight: 800; color: #241c16; margin: 0 0 4px;">Users</h1>
    <p style="font-size: 13px; color: #6b5a4e; margin: 0;">Manage farm accounts</p>
  </div>
  <button onclick="document.getElementById('u-new').showModal()" style="background: #205e46; color: #fff; border: none; border-radius: 8px; padding: 10px 18px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
    + ADD NEW WORKER
  </button>
</div>

<div class="table-responsive" style="background: #ffffff; border: 1px solid #e8e2d8; border-radius: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; box-sizing: border-box; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
  <table style="width: 100%; min-width: 520px; border-collapse: collapse; font-size: 14px;">
    <thead>
      <tr style="background: #faf8f5; border-bottom: 1px solid #e8e2d8;">
        <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">NAME</th>
        <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">EMAIL</th>
        <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">ROLE</th>
        <th style="padding: 14px 20px; width: 50px; text-align: center;"></th>
      </tr>
    </thead>
    <tbody>
      @forelse ($users as $u)
        <tr style="border-bottom: 1px solid #f2ede5;">
          <td style="padding: 14px 20px; font-weight: 600; color: #241c16;">{{ $u->name }}</td>
          <td style="padding: 14px 20px; color: #4a4037;">{{ $u->email ?: ($u->username ? $u->username.'@mushfungai.com' : '—') }}</td>
          <td style="padding: 14px 20px;">
            @php
              $roleColors = [
                'admin'    => ['bg' => '#fef9e7', 'color' => '#b45309'],
                'worker'   => ['bg' => '#e6f7f0', 'color' => '#0e6b47'],
                'customer' => ['bg' => '#eff6ff', 'color' => '#1d4ed8'],
              ];
              $rc = $roleColors[$u->role] ?? ['bg' => '#f3f4f6', 'color' => '#374151'];
            @endphp
            <span style="background: {{ $rc['bg'] }}; color: {{ $rc['color'] }}; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
              {{ strtoupper($u->role) }}
            </span>
          </td>
          <td style="padding: 14px 20px; text-align: center;">
            @if ($u->id !== auth()->id())
              <button onclick="document.getElementById('u{{ $u->id }}').showModal()" style="background: none; border: none; cursor: pointer; color: #6b5a4e; font-size: 18px; padding: 4px 8px; border-radius: 4px; line-height: 1;" title="Edit user">
                &#8942;
              </button>
            @endif
          </td>
        </tr>
        @include('admin.partials.user-form', ['u' => $u])
      @empty
        <tr><td colspan="4" style="padding: 40px 20px; text-align: center; color: #6b5a4e;">No users found.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@include('admin.partials.user-form', ['u' => null])

@endsection
