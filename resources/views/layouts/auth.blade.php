<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'MushFungAI')</title>
  <script>
    (function() {
      try {
        var t = localStorage.getItem('mushfung_theme');
        if (t === 'dark') document.documentElement.classList.add('dark-theme');
      } catch(e) {}
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <meta name="theme-color" content="#241c16">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
  <style>
    html, body.au {
      height: 100% !important;
      min-height: 100vh !important;
      min-height: 100dvh !important;
      margin: 0 !important;
      padding: 0 !important;
      background: #1c1511 !important;
    }
    body.au {
      display: flex !important;
      flex-direction: row !important;
      width: 100% !important;
      height: 100% !important;
      min-height: 100vh !important;
      min-height: 100dvh !important;
      overflow: hidden !important;
      background: linear-gradient(to right, #1c1511 45%, #f6f4ee 45%) !important;
    }
    html.dark-theme body.au {
      background: linear-gradient(to right, #1c1511 45%, #140f0c 45%) !important;
    }
    .au-l {
      width: 45% !important;
      flex: 0 0 45% !important;
      height: 100% !important;
      min-height: 100vh !important;
      min-height: 100dvh !important;
      background: #1c1511 !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      box-sizing: border-box !important;
    }
    .au-r {
      width: 55% !important;
      flex: 1 1 55% !important;
      height: 100% !important;
      min-height: 100vh !important;
      min-height: 100dvh !important;
      background: #f6f4ee !important;
      overflow-y: auto !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      box-sizing: border-box !important;
    }
    html.dark-theme .au-r {
      background: #140f0c !important;
    }
    @media (max-width: 900px) {
      body.au {
        display: block !important;
        height: auto !important;
        min-height: 100vh !important;
        overflow-y: auto !important;
        background: #f6f4ee !important;
      }
      html.dark-theme body.au {
        background: #140f0c !important;
      }
      .au-l {
        display: none !important;
      }
      .au-r {
        width: 100% !important;
        height: auto !important;
        min-height: 100vh !important;
        display: flex !important;
      }
    }
  </style>
</head>
<body class="au">
  <section class="au-l">
    @hasSection('card')
      <div class="brand-hero">
        @yield('card')
      </div>
    @else
      <div class="brand-hero">
        <div class="brand-icon-box" style="background:transparent;box-shadow:none;width:auto;height:auto;margin-bottom:16px;">
          <img src="{{ asset('images/logo-emblem.png') }}" alt="MushFungAI Logo" style="width: 88px; height: 88px; object-fit: contain; filter: drop-shadow(0 6px 18px rgba(0,0,0,0.35));">
        </div>
        <div class="brand-title">MushFung<span>AI</span></div>
        <div class="brand-tagline">Smart Monitoring for Healthier Mushrooms</div>
        <button type="button" class="pwa-install-btn" onclick="installMushFungApp()" style="display: none; margin-top: 18px; background: rgba(212, 154, 61, 0.15); border: 1px solid rgba(212, 154, 61, 0.4); color: #d49a3d; border-radius: 8px; padding: 8px 16px; font-size: 11px; font-weight: 800; cursor: pointer; align-items: center; gap: 6px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          INSTALL APP
        </button>
      </div>
    @endif
  </section>

  <section class="au-r">
    <div class="au-box">
      @if (session('ok'))<div class="al ok">{{ session('ok') }}</div>@endif
      @if (session('dev_otp'))<div class="al dev">Dev mode — your OTP is <b>{{ session('dev_otp') }}</b> (also saved in storage/logs/laravel.log)</div>@endif
      @if (isset($errors) && $errors->any())<div class="al err">{{ $errors->first() }}</div>@endif
      @yield('right')
    </div>
  </section>

  <script src="{{ asset('js/mushroomtrack.js') }}?v={{ time() }}"></script>
  <script>
    function togglePassword(inputId, btn) {
      var inp = document.getElementById(inputId);
      if (!inp) return;
      var isPw = inp.type === 'password';
      inp.type = isPw ? 'text' : 'password';
      btn.innerHTML = isPw 
        ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.9 17.9A10.4 10.4 0 0 1 12 19c-6 0-10-7-10-7a18.7 18.7 0 0 1 5-5.7M9.9 5.2A9.7 9.7 0 0 1 12 5c6 0 10 7 10 7a18.5 18.5 0 0 1-2.2 3.2M14.1 14.1a3 3 0 1 1-4.2-4.2M2 2l20 20"/></svg>'
        : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="7" r="3"/></svg>';
    }
  </script>
</body>
</html>
