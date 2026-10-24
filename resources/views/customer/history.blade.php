@extends('layouts.admin')
@section('content')
@php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp

<form method="POST" action="{{ route('customer.history.clear') }}"
  data-confirm="Remove the selected records from your history?"
  data-title="Clear Selection"
  data-yes="CLEAR"
  data-tone="danger"
  id="historyForm">
  @csrf

  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
    <div>
      <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin: 0 0 4px;">Order History</h1>
      <p style="font-size: 14px; color: #7c726a; margin: 0;">Completed and cancelled orders.</p>
    </div>
    @if($orders->count())
      <button type="submit"
        style="background: #ba2d2d; color: #ffffff; border: none; border-radius: 8px; padding: 10px 20px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; letter-spacing: 0.3px; transition: background 0.15s ease;"
        onmouseover="this.style.background='#a12323'" onmouseout="this.style.background='#ba2d2d'">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
        DELETE
      </button>
    @endif
  </div>

  @if($orders->count())
  <div style="overflow-x: auto; border-radius: 16px; border: 1px solid #ece6dd; background: #ffffff; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #faf8f5; border-bottom: 1px solid #ece6dd;">
          <th style="padding: 16px 20px; text-align: center; width: 44px;">
            <input type="checkbox" id="histSelectAll" onchange="toggleAllHistory(this)"
              style="width: 17px; height: 17px; accent-color: #43352b; cursor: pointer; border-radius: 4px;">
          </th>
          <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; white-space: nowrap;">DATE ORDERED</th>
          <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">NAME</th>
          <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">ADDRESS</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">QTY</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">ORDER TYPE</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; white-space: nowrap;">DATE CLOSED</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">STATUS</th>
        </tr>
      </thead>
      <tbody>
        @foreach($orders as $o)
          <tr style="border-bottom: 1px solid #f2ede5; transition: background 0.15s;" onmouseover="this.style.background='#fcfbfa'" onmouseout="this.style.background=''">
            <td style="padding: 16px 20px; text-align: center;">
              <input type="checkbox" name="ids[]" value="{{ $o->id }}"
                style="width: 17px; height: 17px; accent-color: #43352b; cursor: pointer; border-radius: 4px;">
            </td>
            <td style="padding: 16px 20px; color: #4a4037; font-size: 14px; white-space: nowrap;">{{ $o->created_at->format('Y-m-d') }}</td>
            <td style="padding: 16px 20px; font-weight: 600; color: #241c16; font-size: 14px;">{{ $o->customer_name ?: ($o->user->name ?? '—') }}</td>
            <td style="padding: 16px 20px; color: #5a5047; font-size: 14px; max-width: 220px;">{{ $o->delivery_address ?: ($o->user->address ?: '—') }}</td>
            <td style="padding: 16px 20px; text-align: center; font-weight: 700; color: #241c16; font-size: 14px;">{{ $fmt($o->quantity_kg) }} kg</td>
            <td style="padding: 16px 20px; text-align: center; color: #4a4037; font-size: 14px;">
              {{ $o->fulfillment === 'delivery' ? 'Deliver' : 'Pick up' }}
            </td>
            <td style="padding: 16px 20px; text-align: center; color: #4a4037; font-size: 14px; white-space: nowrap;">{{ $o->updated_at->format('Y-m-d') }}</td>
            <td style="padding: 16px 20px; text-align: center;">
              @if($o->status === 'completed')
                <span style="background: #daf2e6; color: #227753; font-size: 11px; font-weight: 800; padding: 5px 14px; border-radius: 12px; letter-spacing: 0.4px;">COMPLETED</span>
              @elseif($o->status === 'cancelled')
                <span style="background: #fae2e2; color: #c73838; font-size: 11px; font-weight: 800; padding: 5px 14px; border-radius: 12px; letter-spacing: 0.4px;">CANCELLED</span>
              @else
                <span style="background: #f3f0ea; color: #6b6055; font-size: 11px; font-weight: 800; padding: 5px 14px; border-radius: 12px; letter-spacing: 0.4px;">{{ strtoupper($o->status) }}</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
    <div style="padding: 60px 0; text-align: center; color: #8a7f74; font-size: 14px; background: #ffffff; border-radius: 16px; border: 1px solid #ece6dd;">
      No historical records found.
    </div>
  @endif
</form>

<script>
function toggleAllHistory(master) {
  document.querySelectorAll('#historyForm input[name="ids[]"]').forEach(cb => cb.checked = master.checked);
}
</script>
@endsection
