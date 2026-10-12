@extends('layouts.admin')
@section('content')
@php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp
<form method="POST" action="{{ route('customer.history.clear') }}" data-confirm="Remove the selected records from your history?" data-title="Clear selection" data-yes="CLEAR" data-tone="danger">@csrf
  <div class="ah">
    <span class="label">Order Archive</span>
    <button class="clr"><x-icon n="trash" s="18" />CLEAR SELECTION</button>
  </div>
  @forelse ($orders as $o)
    <label class="card oc hc">
      <input type="checkbox" name="ids[]" value="{{ $o->id }}">
      <div class="oct" style="flex:1">
        <div>
          <small>ORD: {{ $o->display_code }}</small>
          <h3>{{ $o->product->name }} ({{ $fmt($o->quantity_kg) }}kg)</h3>
          <div class="od"><x-icon n="clock" s="14" />{{ $o->created_at->format('Y-m-d') }}</div>
        </div>
        <div class="ocr"><b>₱{{ $fmt($o->total) }}</b><div class="oa">
          @if ($o->payment && in_array($o->payment->status, ['refund_due', 'refunded'], true))<span class="tag pay {{ $o->payment->status }}">{{ strtoupper($o->payment->status_label) }}</span>@endif
          <span class="tag {{ $o->status }}">{{ strtoupper($o->status) }}</span></div></div>
      </div>
    </label>
  @empty
    <div class="emptyi">No historical records found.</div>
  @endforelse
</form>
@endsection
