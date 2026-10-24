@extends('layouts.admin')
@section('content')
@php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp

<div class="dash-hero" style="margin-bottom: 24px;">
  <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin-bottom: 4px;">Fresh Mushroom Marketplace</h1>
  <p style="color: #7c726a; font-size: 14px; margin: 0;">Locally cultivated, fresh organic oyster mushrooms.</p>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
  <h2 style="font-size: 11px; font-weight: 800; color: #8c7d70; text-transform: uppercase; letter-spacing: 1px; margin: 0;">AVAILABLE HARVEST</h2>
  <span style="font-size: 12px; color: #8c7d70; font-weight: 600;">{{ count($products) }} item listed</span>
</div>

<div class="shop" id="shopGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
  @forelse ($products as $p)
    <div class="card sp prod-card" style="display: flex; flex-direction: column; overflow: hidden; padding: 0; background: #ffffff; border: 1px solid #ece6dd; border-radius: 16px; box-shadow: 0 4px 18px rgba(40,30,20, 0.04);">
      <div style="height: 200px; background: #faf8f5 url('{{ asset($p->image_url) }}') center/cover no-repeat; border-bottom: 1px solid #f2ede5;"></div>
      <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
        <h3 style="font-size: 17px; font-weight: 700; color: #221c17; line-height: 1.3; margin: 0 0 12px 0;">{{ $p->name }}</h3>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <div style="display: flex; align-items: baseline; gap: 4px;">
            <span style="font-size: 22px; color: #b28135; font-weight: 800;">&#8369;{{ $fmt($p->price) }}</span>
            <span style="font-size: 13px; color: #8d8276; font-weight: 500;">/ kg</span>
          </div>
          @if($p->stock_kg > 5)
            <span style="background: #dcf3eb; color: #2d7862; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 12px; letter-spacing: 0.4px;">IN STOCK</span>
          @elseif($p->stock_kg > 0)
            <span style="background: #fdf3d6; color: #b37400; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 12px; letter-spacing: 0.4px;">{{ $p->stock_kg }} KG LEFT</span>
          @else
            <span style="background: #fae2e2; color: #c73838; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 12px; letter-spacing: 0.4px;">OUT OF STOCK</span>
          @endif
        </div>

        <div style="font-size: 13px; color: #756a60; margin-bottom: 20px;">
          Stock: <strong style="color: #221c17;">{{ $p->stock_kg }} kg</strong>
        </div>

        <div style="margin-top: auto; display: flex; gap: 10px;">
          <button type="button"
            onclick="openBuyModal({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }}, {{ $p->stock_kg }}, true)"
            style="flex: 1.1; height: 42px; border-radius: 8px; border: none; background: #b3833b; color: #ffffff; font-weight: 800; font-size: 12px; cursor: pointer; letter-spacing: 0.5px; display: flex; align-items: center; justify-content: center; gap: 6px;"
            @disabled($p->stock_kg <= 0)>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            ADD TO CART
          </button>
          <button type="button"
            onclick="openBuyModal({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }}, {{ $p->stock_kg }}, false)"
            style="flex: 0.9; height: 42px; border-radius: 8px; border: none; background: #43352b; color: #ffffff; font-weight: 800; font-size: 12px; cursor: pointer; letter-spacing: 0.5px;"
            @disabled($p->stock_kg <= 0)>
            BUY
          </button>
        </div>
      </div>
    </div>
  @empty
    <div style="grid-column: 1 / -1; padding: 60px 0; text-align: center; color: #8c7d70;">
      No fresh products listed at the moment.
    </div>
  @endforelse
</div>

{{-- Buy / Add-to-Cart Modal matching media_1792505912963_5b3fc18f.png --}}
<dialog id="buyModal" style="width: min(450px, 95vw); border-radius: 20px; border: none; padding: 0; box-shadow: 0 16px 48px rgba(0,0,0, 0.22); background: #ffffff;">
  <div style="padding: 28px 30px;">
    <h3 id="buyModalTitle" style="font-size: 20px; font-weight: 800; color: #241c16; margin: 0 0 4px;">Buy Now</h3>
    <p id="buyModalSubtitle" style="font-size: 13px; color: #7c726a; margin: 0 0 22px;">Fresh Oyster Mushrooms · ₱180 / kg</p>

    <form id="buyForm" method="POST">
      @csrf
      <input type="hidden" name="product_id" id="buyProductId">

      {{-- FULL NAME (Blank by default per user request) --}}
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">FULL NAME</label>
        <input type="text" name="customer_name" id="buyName" value="" placeholder="Enter your full name"
          style="width: 100%; height: 44px; border: 1px solid #e5ded4; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #241c16; background: #fcfbfa; outline: none; box-sizing: border-box;">
      </div>

      {{-- ORDER TYPE (Placed BEFORE Address per user request) --}}
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">ORDER TYPE</label>
        <div style="display: flex; gap: 10px;">
          <button type="button" id="btnPickup" onclick="setOrderType('pickup')"
            style="flex: 1; height: 44px; border-radius: 8px; border: 1px solid #e5ded4; background: #43352b; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer;">
            PICK UP
          </button>
          <button type="button" id="btnDeliver" onclick="setOrderType('deliver')"
            style="flex: 1; height: 44px; border-radius: 8px; border: 1px solid #e5ded4; background: #fcfbfa; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;">
            DELIVER
          </button>
        </div>
        <input type="hidden" name="order_type" id="buyOrderType" value="pickup">
      </div>

      {{-- ADDRESS (Appears ONLY when DELIVER is clicked per user request) --}}
      <div id="buyAddressRow" style="margin-bottom: 16px; display: none;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">ADDRESS</label>
        <input type="text" name="delivery_address" id="buyAddress" value="" placeholder="Brgy. Ilaya, Calapan City"
          style="width: 100%; height: 44px; border: 1px solid #e5ded4; border-radius: 8px; padding: 0 14px; font-size: 14px; color: #241c16; background: #fcfbfa; outline: none; box-sizing: border-box;">
      </div>

      {{-- KILOS TO BUY with live total --}}
      <div style="margin-bottom: 24px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">KILOS TO BUY</label>
        <div style="display: flex; align-items: center; justify-content: space-between; height: 44px; border: 1px solid #e5ded4; border-radius: 8px; padding: 0 14px; background: #fcfbfa; box-sizing: border-box;">
          <div style="display: flex; align-items: center; gap: 6px;">
            <input type="number" name="quantity_kg" id="buyQty" value="1" min="0.5" step="0.5"
              oninput="updateBuyTotal()"
              style="width: 60px; height: 36px; border: none; background: transparent; font-size: 14px; font-weight: 700; color: #241c16; outline: none;">
            <span style="font-size: 13px; color: #756a60; font-weight: 600;">kg</span>
          </div>
          <span style="font-size: 13px; font-weight: 700; color: #6b5f54;" id="buyTotal">Total: &#8369;0</span>
        </div>
      </div>

      {{-- Action Buttons --}}
      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="document.getElementById('buyModal').close()"
          style="padding: 10px 22px; height: 44px; border-radius: 8px; border: 1px solid #dcd5c9; background: #ffffff; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;">
          CANCEL
        </button>
        <button type="submit" id="buySubmit"
          style="padding: 10px 24px; height: 44px; border-radius: 8px; border: none; background: #43352b; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer; letter-spacing: 0.3px;">
          ORDER NOW
        </button>
      </div>
    </form>
  </div>
</dialog>

<script>
var _buyPrice = 0;
var _buyMax = 0;
var _buyIsCart = false;

function openBuyModal(productId, productName, price, maxKg, isCart) {
  _buyIsCart = !!isCart;
  _buyPrice = price;
  _buyMax = maxKg;

  document.getElementById('buyProductId').value = productId;
  document.getElementById('buyModalTitle').textContent = isCart ? 'Add to Cart' : 'Buy Now';
  document.getElementById('buyModalSubtitle').textContent = productName + ' · ₱' + price.toFixed(2) + ' / kg';
  document.getElementById('buyQty').max = maxKg;
  document.getElementById('buyQty').value = 1;
  document.getElementById('buyName').value = '';
  document.getElementById('buyAddress').value = '';

  // Form action
  document.getElementById('buyForm').action = isCart
    ? '{{ route("customer.cart.add") }}'
    : '{{ route("customer.order.place") }}';

  document.getElementById('buySubmit').textContent = isCart ? 'ADD TO CART' : 'ORDER NOW';

  // Default to Pick Up, which keeps address hidden
  setOrderType('pickup');
  updateBuyTotal();
  document.getElementById('buyModal').showModal();
}

function updateBuyTotal() {
  var qty = parseFloat(document.getElementById('buyQty').value) || 0;
  var total = (qty * _buyPrice).toFixed(2);
  document.getElementById('buyTotal').textContent = 'Total: ₱' + rtrimZeros(total);
}

function rtrimZeros(val) {
  var s = parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
  return s;
}

function setOrderType(type) {
  document.getElementById('buyOrderType').value = type;

  var activeStyle   = 'flex: 1; height: 44px; border-radius: 8px; border: none; background: #43352b; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer;';
  var inactiveStyle = 'flex: 1; height: 44px; border-radius: 8px; border: 1px solid #e5ded4; background: #fcfbfa; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;';

  var btnPickup  = document.getElementById('btnPickup');
  var btnDeliver = document.getElementById('btnDeliver');
  var addressRow = document.getElementById('buyAddressRow');

  if (type === 'pickup') {
    btnPickup.style.cssText = activeStyle;
    btnDeliver.style.cssText = inactiveStyle;
    addressRow.style.display = 'none';
  } else {
    btnDeliver.style.cssText = activeStyle;
    btnPickup.style.cssText = inactiveStyle;
    addressRow.style.display = 'block';
  }
}
</script>
@endsection
