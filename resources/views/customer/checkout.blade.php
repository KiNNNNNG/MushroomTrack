@extends('layouts.admin')
@section('content')
@php
  $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');
  $sub  = (float) $subtotal;
  $mode = old('fulfillment', 'pickup');
@endphp
<form method="POST" action="{{ route('customer.checkout.store') }}" class="co" id="coForm">@csrf

  <div class="card cob">
    <div class="pc-h">1. Your items</div>
    <div class="cob-b">
      @foreach ($items as $i)
        <div class="cor co-item"
             data-update-url="{{ route('customer.cart.update', $i) }}"
             data-price="{{ $i->product->price }}"
             data-stock="{{ $i->product->stock_kg }}">
          <span class="co-name">{{ $i->product->name }}</span>
          <div class="co-qty-wrap">
            <button type="button" class="qty-btn co-minus">−</button>
            <input type="number" class="qty-in co-qty"
                   value="{{ $i->quantity_kg }}"
                   min="0.5" step="0.5" max="{{ $i->product->stock_kg }}">
            <span class="co-unit">kg</span>
            <button type="button" class="qty-btn co-plus">+</button>
          </div>
          <b class="co-item-price">₱{{ $fmt($i->quantity_kg * $i->product->price) }}</b>
        </div>
      @endforeach
      <a href="{{ route('customer.shop') }}" class="cokl">+ ADD MORE PRODUCTS</a>
    </div>
  </div>

  <div class="card cob">
    <div class="pc-h">2. Pickup or delivery</div>
    <div class="cob-b">
      <label class="copt"><input type="radio" name="fulfillment" value="pickup" @checked($mode === 'pickup')>
        <span><b>Pick up at the farm</b><small>Free. We will tell you when it is ready.</small></span></label>
      <label class="copt"><input type="radio" name="fulfillment" value="delivery" @checked($mode === 'delivery')>
        <span><b>Deliver to my address</b><small>{{ '₱' . $fmt($fee) }} delivery fee{{ $freeOver > 0 ? ' (free for orders of ₱' . $fmt($freeOver) . ' or more)' : '' }}.</small></span></label>

      <div id="coDelivery" class="cod" @if ($mode !== 'delivery') hidden @endif>
        <div class="f"><label>Delivery address</label><input name="delivery_address" value="{{ old('delivery_address', $u->address) }}" placeholder="House no., street, barangay, city"></div>
        <div class="f"><label>Contact number</label><input name="contact_phone" value="{{ old('contact_phone', $u->phone) }}" placeholder="0917 123 4567"></div>
        <small class="cohint">Taken from your profile. You can change it for this order only.</small>
      </div>
    </div>
  </div>

  {{-- Cash-only notice (hidden field keeps the server-side validation happy) --}}
  <input type="hidden" name="payment_method" value="cash">
  <div class="card cob">
    <div class="pc-h">3. Payment</div>
    <div class="cob-b">
      <div class="copt" style="cursor:default">
        <span>
          <b>💵 Cash on Pickup / Delivery</b>
          <small>Pay in cash when you pick up your order at the farm or when it is delivered to you. No online payment needed.</small>
        </span>
      </div>
    </div>
  </div>

  <div class="card cob cot">
    <div class="cor"><span>Subtotal</span><b id="coSubtotal">₱{{ $fmt($subtotal) }}</b></div>
    <div class="cor"><span>Delivery fee</span><b id="coFee">₱0</b></div>
    <div class="cor tot"><span>Total</span><b id="coTotal">₱{{ $fmt($subtotal) }}</b></div>
    <div class="fa"><a class="btn ghost" href="{{ route('customer.shop') }}">BACK TO SHOP</a><button class="btn" id="coGo">PLACE ORDER</button></div>
  </div>

</form>

<script>
(() => {
  let subtotal = @json($sub);
  const fee = @json($fee), freeOver = @json($freeOver);
  const form = document.getElementById('coForm');
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
  const peso = n => '₱' + Number(n).toLocaleString('en-PH', { maximumFractionDigits: 2 });

  const sync = () => {
    const delivery = form.fulfillment.value === 'delivery';
    document.getElementById('coDelivery').hidden = !delivery;
    const charge = delivery && !(freeOver > 0 && subtotal >= freeOver) ? fee : 0;
    document.getElementById('coFee').textContent = charge ? peso(charge) : (delivery ? 'FREE' : '₱0');
    document.getElementById('coTotal').textContent = peso(subtotal + charge);
    document.getElementById('coSubtotal').textContent = peso(subtotal);
  };

  // Quantity controls on checkout items
  document.querySelectorAll('.co-item').forEach(row => {
    const input = row.querySelector('.co-qty');
    const priceEl = row.querySelector('.co-item-price');
    const price = parseFloat(row.dataset.price);

    const updateRow = () => {
      const qty = parseFloat(input.value) || 0;
      const rowTotal = qty * price;
      if (priceEl) priceEl.textContent = peso(rowTotal);
      // Recalculate total subtotal from all rows
      subtotal = 0;
      document.querySelectorAll('.co-item').forEach(r => {
        const q = parseFloat(r.querySelector('.co-qty')?.value || 0);
        const p = parseFloat(r.dataset.price || 0);
        subtotal += q * p;
      });
      subtotal = Math.round(subtotal * 100) / 100;
      sync();
    };

    const saveQty = (qty) => {
      const body = new URLSearchParams({ _method: 'PUT', _token: csrf(), quantity_kg: qty });
      fetch(row.dataset.updateUrl, { method: 'POST', body });
    };

    row.querySelector('.co-minus').onclick = () => {
      const step = parseFloat(input.step) || 0.5;
      const min = parseFloat(input.min) || 0.5;
      input.value = Math.max(min, Math.round((parseFloat(input.value) - step) * 100) / 100);
      updateRow();
      clearTimeout(row._t);
      row._t = setTimeout(() => saveQty(input.value), 600);
    };

    row.querySelector('.co-plus').onclick = () => {
      const step = parseFloat(input.step) || 0.5;
      const max = parseFloat(input.max) || 9999;
      input.value = Math.min(max, Math.round((parseFloat(input.value) + step) * 100) / 100);
      updateRow();
      clearTimeout(row._t);
      row._t = setTimeout(() => saveQty(input.value), 600);
    };

    input.addEventListener('change', () => {
      updateRow();
      clearTimeout(row._t);
      row._t = setTimeout(() => saveQty(input.value), 600);
    });
  });

  form.addEventListener('change', sync);
  sync();
})();
</script>
@endsection
