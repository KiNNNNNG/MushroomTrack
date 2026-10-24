@php
$u = auth()->user();
$role = $u->role;
$navs = [
  'admin' => [
    ['admin.dashboard', 'dashboard', 'Dashboard', 'Dash'],
    ['admin.batches', 'batches', 'Cultivation & Batches', 'Batches'],
    ['admin.completed', 'batches', 'Completed Batches', 'Done'],
    ['admin.store', 'store', 'Store', 'Store'],
    ['admin.reports', 'reports', 'Reports & Analytics', 'Reports'],
    ['admin.users', 'users', 'Users', 'Users'],
  ],
  'worker' => [
    ['worker.overview', 'dashboard', 'Operations Overview', 'Home'], ['worker.monitoring', 'clipboard', 'Fruiting Monitoring', 'Fruiting'],
    ['worker.history', 'history', 'Monitoring History', 'History'], ['worker.register', 'plus', 'Batch Registration', 'Register'],
    ['worker.harvest', 'scale', 'Harvest Entry', 'Harvest'], ['worker.completed', 'batches', 'Completed Batches', 'Completed'],
  ],
  'customer' => [
    ['customer.shop', 'store', 'Product', 'Product'], ['customer.orders', 'list', 'My Orders', 'Orders'], ['customer.history', 'history', 'Order History', 'History'],
  ],
];
$hub = ['admin' => 'Admin Hub', 'worker' => 'Worker Hub', 'customer' => 'Customer Hub'][$role] ?? 'Hub';
$hubIcon = $role === 'worker' ? 'batches' : 'user';
{
    $notesQuery = \App\Models\Notice::where('user_id', $u->id);
    if ($role === 'worker') {
        $notesQuery->where(function ($q) {
            $q->where('title', 'LIKE', '%harvest%')
              ->orWhere('body', 'LIKE', '%harvest%');
        });
    }
    $notes  = $notesQuery->latest()->limit(20)->get();
    $unread = $notes->whereNull('read_at')->count();
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
  <script>
    (function() {
      try {
        var t = localStorage.getItem('mushfung_theme');
        if (t === 'dark') document.documentElement.classList.add('dark-theme');
        var s = localStorage.getItem('mushfung_fontsize') || 'default';
        document.documentElement.classList.add('font-' + s);
        if (s === 'small') {
          document.documentElement.style.zoom = '88%';
        } else if (s === 'large') {
          document.documentElement.style.zoom = '114%';
        } else {
          document.documentElement.style.zoom = '100%';
        }
      } catch(e) {}
    })();
  </script>
  <meta name="theme-color" content="#241c16">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <link rel="stylesheet" href="{{ asset('css/mushroomtrack.css') }}?v={{ time() }}">
</head>
<body class="role-{{ $role }}">
<div class="app">
  <aside>
    <div class="logo">
      <img src="{{ asset('images/logo-emblem.png') }}" alt="MushFungAI" style="width: 34px; height: 34px; object-fit: contain; flex-shrink: 0; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
      <span>MushFung<b>AI</b></span>
    </div>
    
    <div class="hub" style="margin-top:40px;"><x-icon :n="$hubIcon" s="16" />{{ strtoupper($hub) }}</div>
    <nav>
      @foreach ($navs[$role] as [$route, $icon, $label])
        <a href="{{ route($route) }}" class="{{ request()->routeIs($route) ? 'on' : '' }}"><x-icon :n="$icon" s="20" />{{ $label }}</a>
      @endforeach
    </nav>

    <div class="sidebar-bottom">
      <button type="button" class="pwa-install-btn" onclick="installMushFungApp()" style="display: none; width: 100%; height: 38px; background: rgba(168, 120, 52, 0.15); border: 1px solid rgba(168, 120, 52, 0.35); border-radius: 8px; color: #a87834; font-size: 11px; font-weight: 800; letter-spacing: 0.5px; align-items: center; justify-content: center; gap: 7px; margin-bottom: 12px; cursor: pointer; transition: all 0.15s ease;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        <span>INSTALL APP</span>
      </button>
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

  <main class="wide">
    <header class="top-bar">
      <div class="mobile-logo">
        <img src="{{ asset('images/logo-emblem.png') }}" alt="MushFungAI" style="width: 30px; height: 30px; object-fit: contain; flex-shrink: 0;">
        <span>MushFung<b>AI</b></span>
      </div>
      <div class="hright">
        <div class="ddwrap">
          <div class="header-icon-btn" data-dd style="background:#ffffff;border:1px solid #e5ded5;border-radius:12px;width:42px;height:42px;display:flex;align-items:center;justify-content:center;position:relative;cursor:pointer;color:#332921;box-shadow:0 1px 4px rgba(0,0,0,0.03);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="#eab308" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
              <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
            </svg>
            @if ($unread)<span style="position:absolute;top:-5px;right:-5px;background:#ba2d2d;color:#ffffff;font-size:10px;font-weight:800;width:17px;height:17px;border-radius:50%;display:flex;align-items:center;justify-content:center;line-height:1;">{{ $unread > 9 ? '9+' : $unread }}</span>@endif
          </div>
          <div class="dd nt" onclick="event.stopPropagation()">
            <!-- Header bar -->
            <div class="nt-head">
              <div class="nt-head-left">
                <span class="nt-title">NOTIFICATIONS</span>
                @if ($unread)
                  <span class="nt-badge">{{ $unread }} NEW</span>
                @endif
              </div>
              @if ($unread)
                <form method="POST" action="{{ route('notifications.readall') }}" style="margin:0;padding:0;width:auto;">
                  @csrf
                  <button type="submit" class="nt-mark-read-btn">MARK ALL READ</button>
                </form>
              @endif
            </div>

            @if ($notes->count())
              <form id="bulkDeleteNoticesForm" method="POST" action="{{ route('notifications.destroySelected') }}" style="margin:0;padding:0;width:100%;">
                @csrf
                @method('DELETE')
                <!-- Toolbar -->
                <div class="nt-toolbar">
                  <label class="nt-select-all-label">
                    <input type="checkbox" id="selectAllNoticesToggle" onchange="toggleAllNotices(this)" class="nt-check">
                    <span class="nt-select-all-text">Select all</span>
                  </label>
                  <button type="submit" id="deleteSelectedNoticesBtn" disabled class="nt-bulk-del-btn" onclick="return confirm('Delete selected notifications?')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    <span class="nt-del-label">DELETE</span>
                  </button>
                </div>

                <!-- Scrollable list of items -->
                <div class="nt-items">
                  @foreach ($notes as $n)
                    <div class="nt-row {{ $n->read_at ? '' : 'is-unread' }}">
                      <!-- Checkbox -->
                      <div class="nt-cb-wrap">
                        <input type="checkbox" name="selected_ids[]" value="{{ $n->id }}" class="notice-item-checkbox nt-check" onchange="onNoticeCheckboxChange()">
                      </div>

                      <!-- Content (Clickable) -->
                      <a href="{{ route('notifications.open', $n) }}" class="nt-link" title="Click to view">
                        <div class="nt-row-header">
                          @if (!$n->read_at)
                            <span class="nt-unread-dot"></span>
                          @endif
                          <span class="nt-item-title">{{ $n->title }}</span>
                        </div>
                        <span class="nt-item-body">{{ $n->body }}</span>
                        <span class="nt-item-time">{{ $n->created_at->diffForHumans() }}</span>
                      </a>

                      <!-- Single Delete Button -->
                      <button type="button" class="nt-single-del-btn" onclick="deleteSingleNotice({{ $n->id }})" title="Delete notification">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                      </button>
                    </div>
                  @endforeach
                </div>
              </form>
            @else
              <div class="nt-empty">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d5cbbe" stroke-width="1.5" stroke-linecap="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" /><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" /></svg>
                <span>No notifications</span>
              </div>
            @endif
          </div>
        </div>
        
        @if ($role === 'customer')
          <div class="header-icon-btn" style="background:#ffffff;border:1px solid #e5ded5;border-radius:12px;width:42px;height:42px;display:flex;align-items:center;justify-content:center;position:relative;cursor:pointer;color:#332921;box-shadow:0 1px 4px rgba(0,0,0,0.03);" onclick="document.getElementById('cartDlg').showModal()">
            <x-icon n="cart" s="20" />
            @if ($cart->count())<span style="position:absolute;top:-5px;right:-5px;background:#332921;color:#ffffff;font-size:10px;font-weight:800;width:17px;height:17px;border-radius:50%;display:flex;align-items:center;justify-content:center;line-height:1;">{{ $cart->count() }}</span>@endif
          </div>
        @endif

        {{-- Mobile Download App Button in Top Bar --}}
        <button type="button" class="pwa-install-btn mobile-header-install" onclick="installMushFungApp()" title="Download MushFungAI App"
          style="display: none; align-items: center; justify-content: center; gap: 5px; background: rgba(178, 129, 53, 0.12); border: 1px solid rgba(178, 129, 53, 0.35); color: #a87834; border-radius: 12px; height: 42px; padding: 0 10px; font-size: 11px; font-weight: 800; letter-spacing: 0.3px; cursor: pointer;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          <span style="font-size: 10px;">APP</span>
        </button>

        <div class="ddwrap mobile-only">
          <button class="menu" id="menuBtn" data-dd aria-label="Menu"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
          <div class="dd" id="menuDd">
            <span>{{ $u->name }} · {{ $role }}</span>
            <button type="button" class="pwa-install-btn ddl" onclick="installMushFungApp()" style="display: flex; align-items: center; gap: 8px; width: 100%; text-align: left; background: none; border: none; padding: 10px 14px; color: #a87834; font-weight: 800; font-size: 11px; cursor: pointer; border-bottom: 1px solid #f1ece4;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              INSTALL APP
            </button>
            <a href="{{ route('profile') }}" class="ddl">MY PROFILE</a>
            <form method="POST" action="{{ route('logout') }}" data-confirm="You will be signed out of MushFungAI." data-title="Log out?" data-yes="LOG OUT">@csrf<button type="submit">LOG OUT</button></form>
          </div>
        </div>
      </div>
    </header>

    <div class="main-content">
      @if (session('ok'))<div class="flash ok">{{ session('ok') }}</div>@endif
      @if (isset($errors) && $errors->any())<div class="flash err">{{ $errors->first() }}</div>@endif
      @yield('content')
      <div class="mobile-bottom-spacer"></div>
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
{{-- Cart Modal matching media_1792505912945_c38015ce.png --}}
<dialog id="cartDlg" style="width: min(540px, 95vw); border-radius: 20px; border: none; padding: 0; box-shadow: 0 16px 48px rgba(0,0,0, 0.22); background: #ffffff;">
  <form id="cartPlaceForm" method="POST" action="{{ route('customer.cart.place') }}">
    @csrf
    <div style="padding: 26px 28px 16px;">
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <h3 style="font-size: 20px; font-weight: 800; color: #241c16; margin: 0;">Your Cart</h3>
        @if($cart->count())
          <button type="button" id="cartDeleteBtn" onclick="cartDeleteSelected()"
            style="background: #ffffff; color: #c73838; border: 1px solid #f0a8a8; border-radius: 8px; padding: 6px 14px; font-size: 11px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; letter-spacing: 0.3px;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            DELETE SELECTED
          </button>
        @endif
      </div>
    </div>

    <div style="max-height: 340px; overflow-y: auto; padding: 0 28px;">
      @forelse ($cart as $c)
        <div class="ci" data-id="{{ $c->id }}"
             data-remove-url="{{ route('customer.cart.remove', $c) }}"
             data-update-url="{{ route('customer.cart.update', $c) }}"
             data-stock="{{ $c->product->stock_kg }}"
             data-price="{{ $c->product->price }}"
             style="display: flex; align-items: center; gap: 14px; padding: 14px 0; border-bottom: 1px solid #f0ebe2;">
          
          <input type="checkbox" class="cart-chk" name="cart_ids[]" value="{{ $c->id }}" checked
            onchange="updateCartCheckboxTotal()"
            style="width: 18px; height: 18px; accent-color: #43352b; cursor: pointer; border-radius: 4px; flex-shrink: 0;">

          <div style="flex: 1; min-width: 0;">
            <div style="font-weight: 700; font-size: 14px; color: #241c16; margin-bottom: 3px;">{{ $c->product->name }}</div>
            <div style="font-size: 12px; color: #7c726a;">
              {{ $c->customer_name ?: $u->name }} &middot;
              @if($c->fulfillment === 'delivery')
                {{ $c->delivery_address ?: 'Deliver' }} &middot; Deliver
              @else
                Pick up
              @endif
            </div>
          </div>

          <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
            <div class="ci-controls" style="display: flex; align-items: center; border: 1px solid #e2ded6; border-radius: 6px; background: #ffffff;">
              <button type="button" class="qty-btn" data-dir="-1" style="width: 26px; height: 26px; border: none; background: transparent; font-size: 15px; font-weight: 700; color: #43352b; cursor: pointer; display: flex; align-items: center; justify-content: center;">&minus;</button>
              <input type="number" class="qty-in" value="{{ $c->quantity_kg }}" min="0.5" step="0.5" max="{{ $c->product->stock_kg }}" style="width: 44px; height: 26px; border: none; text-align: center; font-weight: 700; font-size: 13px; color: #241c16; outline: none; -moz-appearance: textfield;">
              <span style="font-size: 11px; font-weight: 700; color: #7c726a; padding-right: 4px;">kg</span>
              <button type="button" class="qty-btn" data-dir="1" style="width: 26px; height: 26px; border: none; background: transparent; font-size: 15px; font-weight: 700; color: #43352b; cursor: pointer; display: flex; align-items: center; justify-content: center;">+</button>
            </div>
            <b class="ci-price" style="color: #b28135; font-size: 14px; font-weight: 800;">&#8369;{{ $fmtk($c->quantity_kg * $c->product->price) }}</b>
          </div>
        </div>
      @empty
        <p class="emptyi" style="padding: 40px 0; text-align: center; color: #8a7f74; font-size: 14px;">Your cart is empty.</p>
      @endforelse
    </div>

    @if($cart->count())
    <div style="padding: 16px 28px 0; border-top: 1px solid #ece6dd; margin-top: 8px;">
      <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 20px;">
        <span style="font-size: 11px; color: #8a7f74; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px;">TOTAL</span>
        <b id="cartTotal" style="font-size: 24px; color: #241c16; font-weight: 800;">&#8369;{{ $fmtk($cart->sum(fn ($c) => $c->quantity_kg * $c->product->price)) }}</b>
      </div>
    </div>
    @endif

    <div style="padding: 0 28px 26px; display: flex; gap: 10px; justify-content: flex-end;">
      <button type="button" onclick="document.getElementById('cartDlg').close()"
        style="padding: 10px 22px; height: 44px; border-radius: 8px; border: 1px solid #dcd5c9; background: #ffffff; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;">
        CLOSE
      </button>
      @if($cart->count())
        <button type="submit"
          style="padding: 10px 24px; height: 44px; border-radius: 8px; border: none; background: #43352b; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer; letter-spacing: 0.3px;">
          PLACE ORDER
        </button>
      @endif
    </div>
  </form>
</dialog>
@endif

<form id="singleDeleteNoticeForm" method="POST" action="" style="display:none;">
  @csrf
  @method('DELETE')
</form>
<script>
function toggleAllNotices(master) {
  const cbs = document.querySelectorAll('.notice-item-checkbox');
  cbs.forEach(cb => cb.checked = master.checked);
  updateDeleteNoticeBtn();
}

function onNoticeCheckboxChange() {
  const cbs = document.querySelectorAll('.notice-item-checkbox');
  const allChecked = cbs.length > 0 && Array.from(cbs).every(cb => cb.checked);
  const selectAll = document.getElementById('selectAllNoticesToggle');
  if (selectAll) selectAll.checked = allChecked;
  updateDeleteNoticeBtn();
}

function updateDeleteNoticeBtn() {
  const checked = document.querySelectorAll('.notice-item-checkbox:checked');
  const btn = document.getElementById('deleteSelectedNoticesBtn');
  if (!btn) return;
  const label = btn.querySelector('.nt-del-label') || btn.querySelector('span');
  if (checked.length > 0) {
    btn.disabled = false;
    if (label) label.textContent = 'DELETE (' + checked.length + ')';
  } else {
    btn.disabled = true;
    if (label) label.textContent = 'DELETE';
  }
}

function deleteSingleNotice(id) {
  if (confirm('Delete this notification?')) {
    const f = document.getElementById('singleDeleteNoticeForm');
    f.action = '{{ url("/notifications") }}/' + id;
    f.submit();
  }
}
</script>

<script src="{{ asset('js/mushroomtrack.js') }}?v={{ time() }}"></script>
</body>
</html>
