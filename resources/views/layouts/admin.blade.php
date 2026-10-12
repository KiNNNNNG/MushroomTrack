@php
$u = auth()->user();
$role = $u->role;
$navs = [
  'admin' => [
    ['admin.dashboard', 'dashboard', 'Dashboard', 'Dash'], ['admin.batches', 'batches', 'Batches', 'Batches'], ['admin.store', 'store', 'Store', 'Store'],
    ['admin.reports', 'reports', 'Reports', 'Reports'], ['admin.users', 'users', 'Users', 'Users'],
  ],
  'worker' => [
    ['worker.overview', 'dashboard', 'Operations Overview', 'Home'], ['worker.monitoring', 'clipboard', 'Fruiting Monitoring', 'Fruiting'],
    ['worker.history', 'history', 'Monitoring History', 'History'], ['worker.register', 'plus', 'Batch Registration', 'Register'], ['worker.harvest', 'scale', 'Harvest Entry', 'Harvest'],
  ],
  'customer' => [
    ['customer.shop', 'store', 'Browse Shop', 'Shop'], ['customer.orders', 'list', 'My Orders', 'Orders'], ['customer.history', 'history', 'Order History', 'History'],
  ],
];
$hub = ['admin' => 'Admin Hub', 'worker' => 'Worker Hub', 'customer' => 'Customer Hub'][$role] ?? 'Hub';
$hubIcon = $role === 'worker' ? 'batches' : 'user';
{
    $notes  = \App\Models\Notice::where('user_id', $u->id)->latest()->limit(8)->get();
    $unread = \App\Models\Notice::where('user_id', $u->id)->whereNull('read_at')->count();
}
if ($role === 'customer') {
    $cart = \App\Models\CartItem::with('product')->where('user_id', $u->id)->get();
}
$fmtk = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>MushFungAI</title>
  <link rel="stylesheet" href="{{ asset('css/mushroomtrack.css') }}?v={{ time() }}">
</head>
<body>
<div class="app">
  <aside>
    <div class="logo">
      <x-icon n="batches" s="26" />
      <span>MushFung<b>AI</b></span>
    </div>
    
    <div class="hub" style="margin-top:40px;"><x-icon :n="$hubIcon" s="16" />{{ strtoupper($hub) }}</div>
    <nav>
      @foreach ($navs[$role] as [$route, $icon, $label])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($route) ? 'on' : '' }}"><x-icon :n="$icon" s="20" />{{ $label }}</a>
      @endforeach
    </nav>

    <div class="sidebar-bottom">
      <div class="sb-user">
        <div class="sb-av">{{ substr($u->name, 0, 1) }}</div>
        <div class="sb-info">
          <b>{{ $u->name }}</b>
          <span>{{ ucfirst($role) }} Account</span>
        </div>
        <a href="{{ route('profile') }}" class="sb-prof" title="My Profile"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></a>
      </div>
      <form method="POST" action="{{ route('logout') }}" data-confirm="You will be signed out of MushFungAI." data-title="Log out?" data-yes="LOG OUT">
        @csrf
        <button type="submit" class="sb-logout">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          LOG OUT
        </button>
      </form>
    </div>
  </aside>

  <main class="{{ $role === 'admin' ? '' : 'wide' }}">
    <header class="top-bar">
      <div class="mobile-logo">
        <x-icon n="batches" s="26" />
        <span>MushFung<b>AI</b></span>
      </div>
      <div class="hright">
        <div class="ddwrap">
            <div class="bell" data-dd>
              <x-icon n="bell" s="22" />@if ($unread)<i>{{ $unread > 9 ? '9+' : $unread }}</i>@endif
            </div>
            <div class="dd nt">
              <div class="nth"><b>NOTIFICATIONS</b>
                @if ($unread)<form method="POST" action="{{ route('notifications.readall') }}">@csrf<button>MARK ALL READ</button></form>@endif
              </div>
              @forelse ($notes as $n)
                <a href="{{ route('notifications.open', $n) }}" class="ni {{ $n->read_at ? '' : 'new' }}">
                  <b>{{ $n->title }}</b><span>{{ $n->body }}</span><small>{{ $n->created_at->diffForHumans() }}</small>
                </a>
              @empty
                <span class="nn">No notifications</span>
              @endforelse
            </div>
        </div>
        
        @if ($role === 'customer')
          <div class="bell" style="cursor:pointer" onclick="document.getElementById('cartDlg').showModal()">
            <x-icon n="cart" s="22" />@if ($cart->count())<i class="gold">{{ $cart->count() }}</i>@endif
          </div>
        @endif

        <div class="ddwrap mobile-only">
          <button class="menu" id="menuBtn" data-dd aria-label="Menu"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
          <div class="dd" id="menuDd">
            <span>{{ $u->name }} · {{ $role }}</span>
            <a href="{{ route('profile') }}" class="ddl">MY PROFILE</a>
            <form method="POST" action="{{ route('logout') }}" data-confirm="You will be signed out of MushFungAI." data-title="Log out?" data-yes="LOG OUT">@csrf<button type="submit">LOG OUT</button></form>
          </div>
        </div>
      </div>
    </header>

    <div class="main-content">
      @if (session('ok'))<div class="flash ok">{{ session('ok') }}</div>@endif
      @if ($errors->any())<div class="flash err">{{ $errors->first() }}</div>@endif
      @yield('content')
    </div>
  </main>
  
  <!-- Mobile Bottom Navigation -->
  <nav class="bottom-nav">
    @foreach ($navs[$role] as [$route, $icon, $label, $mobileLabel])
      <a href="{{ route($route) }}" class="{{ request()->routeIs($route) ? 'on' : '' }}">
        <x-icon :n="$icon" s="24" />
        <span>{{ $mobileLabel }}</span>
      </a>
    @endforeach
  </nav>
</div>

@if ($role === 'customer')
<dialog id="cartDlg">
  <h3>Your Cart</h3>
  @forelse ($cart as $c)
    <div class="ci" data-id="{{ $c->id }}"
         data-remove-url="{{ route('customer.cart.remove', $c) }}"
         data-update-url="{{ route('customer.cart.update', $c) }}"
         data-stock="{{ $c->product->stock_kg }}"
         data-price="{{ $c->product->price }}">
      <div class="ci-info">
        <span class="ci-name">{{ $c->product->name }}</span>
        <b class="ci-price" style="color:var(--gold)">₱{{ $fmtk($c->quantity_kg * $c->product->price) }}</b>
      </div>
      <div class="ci-controls">
        <button type="button" class="qty-btn" data-dir="-1">−</button>
        <input type="number" class="qty-in" value="{{ $c->quantity_kg }}" min="0.5" step="0.5" max="{{ $c->product->stock_kg }}">
        <button type="button" class="qty-btn" data-dir="1">+</button>
        <button type="button" class="lk ci-rm">REMOVE</button>
      </div>
    </div>
  @empty
    <p class="emptyi" style="padding:30px 0">Your cart is empty.</p>
  @endforelse
  <div class="fa">
    <button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CLOSE</button>
    @if ($cart->count())
      <a class="btn" id="cartCheckoutBtn" href="{{ route('customer.checkout') }}">CHECKOUT (₱{{ $fmtk($cart->sum(fn ($c) => $c->quantity_kg * $c->product->price)) }})</a>
    @endif
  </div>
</dialog>
@endif

<script src="{{ asset('js/mushroomtrack.js') }}"></script>
</body>
</html>
