@extends('layouts.admin')
@section('content')
@php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp
@forelse ($orders as $o)
  @php $p = $o->payment; @endphp
  <div class="card oc">
    <div class="oct">
      <div>
        <small>ORD: {{ $o->display_code }}</small>
        <h3>{{ $o->product->name }} ({{ $fmt($o->quantity_kg) }}kg)</h3>
        <div class="od"><x-icon n="clock" s="14" />{{ $o->created_at->format('Y-m-d') }}</div>
        <div class="oinfo">
          <span class="tag fl">{{ $o->fulfillment === 'delivery' ? 'DELIVERY' : 'PICKUP' }} · {{ strtoupper($o->status === 'pending' ? ($o->isAwaitingPayment() ? 'awaiting payment' : 'waiting for confirmation') : $o->fulfillment_status_label) }}</span>
          @if ($p)<span class="tag pay {{ $p->status }}">{{ strtoupper($p->method_label) }} · {{ strtoupper($p->status_label) }}</span>@endif
        </div>
        @if ($o->fulfillment === 'delivery' && $o->delivery_address)<div class="oaddr">Deliver to: {{ $o->delivery_address }}</div>@endif
        @if ($p && (float) $p->delivery_fee > 0)<div class="oaddr">Payment {{ $p->reference }} · total ₱{{ $fmt($p->amount) }} including ₱{{ $fmt($p->delivery_fee) }} delivery</div>@endif
      </div>
      <div class="ocr">
        <b>₱{{ $fmt($o->total) }}</b>
        <div class="oa">
          <span class="tag">{{ strtoupper($o->status) }}</span>
          @if ($o->isAwaitingPayment() && $o->status === 'pending')
            <form method="POST" action="{{ route('customer.pay.start', $p) }}">@csrf<button class="btn sm gold">PAY NOW</button></form>
          @endif
          @if ($o->status === 'pending')
            <form method="POST" action="{{ route('customer.orders.cancel', $o) }}" data-confirm="Cancel this order?" data-title="Cancel order" data-yes="YES, CANCEL" data-no="KEEP ORDER" data-tone="danger">@csrf @method('PUT')
              <button class="cx"><x-icon n="xcircle" s="18" />CANCEL</button></form>
          @endif
        </div>
      </div>
    </div>
  </div>
@empty
  <div class="emptyi">No active orders.</div>
@endforelse
@endsection
