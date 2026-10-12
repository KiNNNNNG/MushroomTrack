@extends('layouts.admin')
@section('content')
@php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp
<div class="tabs">
  @foreach (['products','orders','sales','history'] as $t)
    <a href="{{ route('admin.store', ['tab' => $t]) }}" class="{{ $tab === $t ? 'on' : '' }}">{{ strtoupper($t) }}</a>
  @endforeach
</div>

@if ($tab === 'products')
  <div class="shead">
    <span class="label">Store Catalog</span>
    <button class="btn" onclick="document.getElementById('p-new').showModal()"><x-icon n="plus" s="20" />NEW PRODUCT</button>
  </div>
  <div class="prods">
    @foreach ($products as $p)
      <div class="card prod">
        <div class="img" @style(["background-image: url('".$p->image_url."')" => $p->image_url])></div>
        <h3>{{ $p->name }}</h3>
        <div class="meta"><span>₱{{ $fmt($p->price) }}</span><span>{{ $fmt($p->stock_kg) }}kg</span></div>
        <div class="acts">
          <button type="button" onclick="document.getElementById('p{{ $p->id }}').showModal()">EDIT</button>
          <form method="POST" action="{{ route('admin.products.destroy', $p) }}" data-confirm="Delete this product?" data-title="Delete product" data-yes="DELETE" data-no="CANCEL" data-tone="danger">@csrf @method('DELETE')<button type="submit" style="color:#b91c1c;padding-left:60px;text-align:left">DELETE</button></form>
        </div>
      </div>
      @include('admin.partials.product-form', ['p' => $p])
    @endforeach
  </div>
  @include('admin.partials.product-form', ['p' => null])

@elseif ($tab === 'orders')
  <div class="card tbl plain big50">
    <table>
      <tr><th>Customer</th><th>Product</th><th>Total</th><th>Payment</th><th>Receive</th><th>Status</th><th class="r">Actions</th></tr>
      @forelse ($orders as $o)
        @php $p = $o->payment; $waiting = $o->isAwaitingPayment(); @endphp
        <tr>
          <td class="b">{{ $o->user->name }}</td>
          <td>{{ $o->product->name }} ({{ $fmt($o->quantity_kg) }}kg)</td>
          <td class="g">₱{{ $fmt($o->total) }}</td>
          <td>@if ($p)<div>{{ $p->method_label }}</div><span class="tag pay {{ $p->status }}">{{ strtoupper($p->status_label) }}</span>@else<span class="tag">—</span>@endif</td>
          <td>
            <div>{{ $o->fulfillment === 'delivery' ? 'Delivery' : 'Pickup' }}</div>
            @if ($o->fulfillment === 'delivery')<small class="oaddr" style="display:block;max-width:220px;white-space:normal">{{ $o->delivery_address }}<br>{{ $o->contact_phone }}</small>@endif
            @if ($o->status === 'confirmed')<span class="tag fl" style="margin-top:6px">{{ strtoupper($o->fulfillment_status_label) }}</span>@endif
          </td>
          <td><span class="tag">{{ strtoupper($o->status) }}</span></td>
          <td class="r">
            <div class="acts2">
              @if ($waiting)
                <span class="tag" title="The customer has not paid through PayMongo yet">WAITING FOR PAYMENT</span>
              @else
                <form method="POST" action="{{ route('admin.orders.update', $o) }}"
                  data-confirm="{{ $o->status === 'pending' ? 'Confirm this order? The customer will be notified.' : 'Mark this order as done? Stock will be reduced and a sale recorded.' }}"
                  data-title="{{ $o->status === 'pending' ? 'Confirm order' : 'Complete order' }}" data-yes="{{ $o->status === 'pending' ? 'CONFIRM' : 'DONE' }}">@csrf @method('PUT')
                  <input type="hidden" name="status" value="{{ $o->status === 'pending' ? 'confirmed' : 'completed' }}">
                  <button class="btn sm">{{ $o->status === 'pending' ? 'CONFIRM' : 'DONE' }}</button></form>
              @endif
              @if ($o->status === 'confirmed' && $o->fulfillment_status === 'pending')
                <form method="POST" action="{{ route('admin.orders.fulfillment', $o) }}" data-confirm="Tell the customer their order is {{ $o->fulfillment === 'delivery' ? 'out for delivery' : 'ready for pickup' }}?" data-title="{{ $o->fulfillment === 'delivery' ? 'Out for delivery' : 'Ready for pickup' }}" data-yes="SEND UPDATE">@csrf @method('PUT')
                  <input type="hidden" name="fulfillment_status" value="{{ $o->fulfillment === 'delivery' ? 'out_for_delivery' : 'ready_for_pickup' }}">
                  <button class="btn sm gold">{{ $o->fulfillment === 'delivery' ? 'OUT FOR DELIVERY' : 'READY FOR PICKUP' }}</button></form>
              @endif
              <form method="POST" action="{{ route('admin.orders.update', $o) }}" data-confirm="Cancel this order?{{ $p && $p->status === 'paid' ? ' The customer already paid, so a refund will be needed.' : '' }}" data-title="Cancel order" data-yes="CANCEL ORDER" data-no="KEEP ORDER" data-tone="danger">@csrf @method('PUT')
                <input type="hidden" name="status" value="cancelled"><button class="lk">CANCEL</button></form>
            </div>
          </td>
        </tr>
      @empty <tr><td colspan="7" class="empty">No active orders.</td></tr> @endforelse
    </table>
  </div>

@elseif ($tab === 'sales')
  <div class="shead">
    <span class="label">Revenue Ledger</span>
    <form method="GET"><input type="hidden" name="tab" value="sales">
      <select name="range" onchange="this.form.submit()">
        @foreach (['week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all' => 'All Time'] as $k => $l)
          <option value="{{ $k }}" @selected($range === $k)>{{ $l }}</option>
        @endforeach
      </select></form>
  </div>
  <form method="GET" action="{{ route('admin.store.export') }}" class="toolbar" style="gap:10px"><input type="hidden" name="range" value="{{ $range }}">
    <button class="btn ghost" name="format" value="xlsx">DOWNLOAD EXCEL</button>
    <button class="btn" name="format" value="pdf">DOWNLOAD PDF</button>
  </form>
  <div class="bigcard"><small>TOTAL SALES</small><strong>₱{{ number_format($totalSales) }}</strong></div>

@else
  <div class="card tbl plain big50">
    <table>
      <tr><th>Date</th><th>Customer</th><th>Total</th><th>Payment</th><th class="r">Status</th></tr>
      @forelse ($history as $o)
        @php $p = $o->payment; @endphp
        <tr>
          <td style="color:#64748b">{{ $o->created_at->format('Y-m-d') }}</td>
          <td class="b">{{ $o->user->name }}</td>
          <td class="b">₱{{ $fmt($o->total) }}</td>
          <td>
            @if ($p)
              <span class="tag pay {{ $p->status }}">{{ strtoupper($p->method_label) }} · {{ strtoupper($p->status_label) }}</span>
              @if ($p->status === 'refund_due')
                <form method="POST" action="{{ route('admin.payments.refunded', $p) }}" style="display:inline" data-confirm="Only confirm after you sent the refund in PayMongo." data-title="Refund sent?" data-yes="YES, REFUNDED">@csrf @method('PUT')
                  <button class="lk">MARK REFUNDED</button></form>
              @endif
            @else — @endif
          </td>
          <td class="r"><span class="tag {{ $o->status }}">{{ strtoupper($o->status) }}</span></td>
        </tr>
      @empty <tr><td colspan="5" class="empty">No order history yet.</td></tr> @endforelse
    </table>
  </div>
@endif
@endsection
