@extends('layouts.admin')
@section('content')
@php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp

<div class="dash-hero" style="margin-bottom: 24px;">
  <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: var(--brown);">Fresh Mushroom Marketplace</h1>
  <p style="color: var(--muted); font-size: 14px; margin-top: 4px;">Locally cultivated, fresh organic oyster and specialty mushrooms.</p>
</div>

<div style="position: relative; margin-bottom: 24px;">
  <svg style="position: absolute; left: 14px; top: 12px; color: var(--muted);" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
  <input type="text" id="shopSearch" placeholder="Search fresh mushroom varieties..." onkeyup="filterShop(this.value)" style="width: 100%; height: 42px; border-radius: 10px; border: 1px solid var(--line); padding: 0 16px 0 42px; background: #fff; font-size: 14px; outline: none;">
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
  <h2 style="font-size: 16px; font-weight: 800; color: var(--brown); text-transform: uppercase; letter-spacing: 0.5px;">Available Harvests</h2>
  <span style="font-size: 12px; color: var(--muted); font-weight: 600;">{{ count($products) }} items listed</span>
</div>

<div class="shop" id="shopGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
  @forelse ($products as $p)
    <div class="card sp prod-card" data-name="{{ strtolower($p->name) }}" style="display: flex; flex-direction: column; overflow: hidden; padding: 0; border: 1px solid var(--line);">
      <div class="img" style="height: 180px; background: #e9e4de url('{{ $p->image_url ?? 'https://images.unsplash.com/photo-1590059345229-3733215be132?auto=format&fit=crop&q=80&w=400' }}') center/cover; border-bottom: 1px solid var(--line);"></div>
      <div style="padding: 16px; flex: 1; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 8px;">
          <h3 style="font-size: 16px; font-weight: 700; color: var(--ink); line-height: 1.3; margin: 0;">{{ $p->name }}</h3>
          @if($p->stock_kg > 5)
            <span style="background: #ecfdf5; color: var(--green); font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 12px; white-space: nowrap;">IN STOCK</span>
          @elseif($p->stock_kg > 0)
            <span style="background: #fffbeb; color: var(--amber); font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 12px; white-space: nowrap;">{{ $p->stock_kg }} KG LEFT</span>
          @else
            <span style="background: #fef2f2; color: var(--dred); font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 12px; white-space: nowrap;">OUT OF STOCK</span>
          @endif
        </div>

        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 16px;">
          <span style="font-size: 18px; color: var(--brown); font-weight: 800;">?{{ $fmt($p->price) }} <small style="font-size: 12px; color: var(--muted); font-weight: 600;">/ kg</small></span>
          <span style="font-size: 12px; color: var(--muted);">Stock: <strong>{{ $p->stock_kg }} kg</strong></span>
        </div>
        
        <form method="POST" action="{{ route('customer.cart.add') }}" style="margin-top: auto; display: flex; gap: 8px;">
          @csrf
          <input type="hidden" name="product_id" value="{{ $p->id }}">
          <div style="display: flex; align-items: center; border: 1px solid var(--line); border-radius: 8px; padding: 0 4px; background: #faf9f7;">
            <input type="number" name="quantity_kg" value="1" min="0.5" step="0.5" max="{{ max(0.5, $p->stock_kg) }}" style="width: 50px; height: 38px; border: none; background: transparent; text-align: center; font-weight: 700; font-size: 14px;" @disabled($p->stock_kg <= 0)>
            <span style="font-size: 11px; color: var(--muted); font-weight: 700; padding-right: 6px;">kg</span>
          </div>
          <button class="btn" style="flex: 1; justify-content: center; height: 40px; font-size: 12px;" @disabled($p->stock_kg <= 0)>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            ADD TO CART
          </button>
        </form>
      </div>
    </div>
  @empty
    <div style="grid-column: 1 / -1; padding: 60px 0; text-align: center; color: var(--muted);">
      No fresh products listed at the moment.
    </div>
  @endforelse
</div>

<script>
function filterShop(query) {
  const q = query.toLowerCase().trim();
  document.querySelectorAll('.prod-card').forEach(el => {
    const name = el.getAttribute('data-name') || '';
    el.style.display = name.includes(q) ? 'flex' : 'none';
  });
}
</script>
@endsection