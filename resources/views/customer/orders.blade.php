@extends('layouts.admin')
@section('content')
@php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp

<div style="margin-bottom: 24px;">
  <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin: 0 0 4px;">My Orders</h1>
  <p style="font-size: 14px; color: #7c726a; margin: 0;">Track your active orders. You can cancel only while an order is pending.</p>
</div>

@if($orders->count())
<div style="overflow-x: auto; border-radius: 16px; border: 1px solid #ece6dd; background: #ffffff; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
  <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
    <thead>
      <tr style="background: #faf8f5; border-bottom: 1px solid #ece6dd;">
        <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; white-space: nowrap;">DATE</th>
        <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">NAME</th>
        <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">ADDRESS</th>
        <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">QTY</th>
        <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">ORDER TYPE</th>
        <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">STATUS</th>
        <th style="padding: 16px 20px; text-align: center; width: 120px;"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($orders as $o)
        <tr style="border-bottom: 1px solid #f2ede5; transition: background 0.15s;" onmouseover="this.style.background='#fcfbfa'" onmouseout="this.style.background=''">
          <td style="padding: 16px 20px; color: #4a4037; font-size: 14px; white-space: nowrap;">{{ $o->created_at->format('Y-m-d') }}</td>
          <td style="padding: 16px 20px; font-weight: 600; color: #241c16; font-size: 14px;">{{ $o->customer_name ?: ($o->user->name ?? '—') }}</td>
          <td style="padding: 16px 20px; color: #5a5047; font-size: 14px; max-width: 220px;">
            {{ $o->delivery_address ?: ($o->fulfillment === 'delivery' ? ($o->user->address ?: '—') : '—') }}
          </td>
          <td style="padding: 16px 20px; text-align: center; font-weight: 700; color: #241c16; font-size: 14px;">{{ $fmt($o->quantity_kg) }} kg</td>
          <td style="padding: 16px 20px; text-align: center; color: #4a4037; font-size: 14px;">
            {{ $o->fulfillment === 'delivery' ? 'Deliver' : 'Pick up' }}
          </td>
          <td style="padding: 16px 20px; text-align: center;">
            @if($o->status === 'pending')
              <span style="background: #fdf3d6; color: #b37400; font-size: 11px; font-weight: 800; padding: 5px 14px; border-radius: 12px; letter-spacing: 0.4px;">PENDING</span>
            @elseif($o->status === 'confirmed')
              <span style="background: #e1effe; color: #1e60c8; font-size: 11px; font-weight: 800; padding: 5px 14px; border-radius: 12px; letter-spacing: 0.4px;">CONFIRMED</span>
            @elseif(in_array($o->fulfillment_status, ['out_for_delivery', 'ready_for_pickup', 'delivered']))
              <span style="background: #dcf3eb; color: #217a5e; font-size: 11px; font-weight: 800; padding: 5px 14px; border-radius: 12px; letter-spacing: 0.4px;">{{ strtoupper(str_replace('_', ' ', $o->fulfillment_status)) }}</span>
            @else
              <span style="background: #f3f0ea; color: #6b6055; font-size: 11px; font-weight: 800; padding: 5px 14px; border-radius: 12px; letter-spacing: 0.4px;">{{ strtoupper($o->status) }}</span>
            @endif
          </td>
          <td style="padding: 16px 20px; text-align: center;">
            @if($o->status === 'pending')
              <form method="POST" action="{{ route('customer.orders.cancel', $o) }}"
                data-confirm="Cancel this order?"
                data-title="Cancel Order"
                data-yes="YES, CANCEL"
                data-no="KEEP ORDER"
                data-tone="danger"
                style="display: inline-block; margin: 0;">
                @csrf @method('PUT')
                <button type="submit"
                  style="background: #ffffff; color: #d33b3b; border: 1px solid #f0a8a8; border-radius: 16px; padding: 5px 16px; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; letter-spacing: 0.3px; transition: all 0.15s ease;"
                  onmouseover="this.style.background='#fdf2f2'" onmouseout="this.style.background='#ffffff'">
                  &times; CANCEL
                </button>
              </form>
            @endif
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@else
  <div style="padding: 60px 0; text-align: center; color: #8a7f74; font-size: 14px; background: #ffffff; border-radius: 16px; border: 1px solid #ece6dd;">
    No active orders.
  </div>
@endif
@endsection
