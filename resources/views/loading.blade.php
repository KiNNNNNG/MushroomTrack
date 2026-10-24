<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>MushFungAI - Smart Monitoring for Healthier Mushrooms</title>
  <meta name="theme-color" content="#f9f6ee">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  <noscript><meta http-equiv="refresh" content="2;url={{ route('home') }}"></noscript>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    html, body {
      width: 100%;
      height: 100%;
      height: 100dvh;
      overflow: hidden;
      background-color: #f9f6ee;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .splash-screen {
      position: fixed;
      inset: 0;
      width: 100vw;
      height: 100vh;
      height: 100dvh;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f9f6ee;
    }

    /* Fixed 4-corner decorations anchored to screen boundaries */
    .corner {
      position: absolute;
      pointer-events: none;
      user-select: none;
      z-index: 1;
      display: block;
    }

    .corner-tl {
      top: 0;
      left: 0;
      width: clamp(140px, 22vw, 300px);
      height: auto;
    }

    .corner-tr {
      top: 0;
      right: 0;
      width: clamp(140px, 22vw, 290px);
      height: auto;
    }

    .corner-bl {
      bottom: 0;
      left: 0;
      width: clamp(170px, 27vw, 380px);
      height: auto;
    }

    .corner-br {
      bottom: 0;
      right: 0;
      width: clamp(180px, 28vw, 400px);
      height: auto;
    }

    /* Center Content Container */
    .splash-center {
      position: relative;
      z-index: 2;
      width: 100%;
      max-width: 480px;
      padding: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: fadeIn 0.4s ease-out;
    }

    .center-art-wrap {
      position: relative;
      width: 100%;
      max-width: 420px;
      aspect-ratio: 464 / 412;
      display: flex;
      align-items: center;
      justify-content: center;
      user-select: none;
    }

    .center-art {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
      pointer-events: none;
    }

    /* Active smooth rotating 12-blade radial spinner */
    .spinner-active {
      position: absolute;
      left: 50%;
      top: 83.5%;
      transform: translate(-50%, -50%);
      width: 8.5%;
      aspect-ratio: 1 / 1;
      min-width: 28px;
      min-height: 28px;
      max-width: 44px;
      max-height: 44px;
      pointer-events: none;
      animation: mfSpin 0.9s steps(12, end) infinite;
    }

    .spinner-svg {
      width: 100%;
      height: 100%;
      display: block;
    }

    @keyframes mfSpin {
      0%   { transform: translate(-50%, -50%) rotate(0deg); }
      100% { transform: translate(-50%, -50%) rotate(360deg); }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.96); }
      to   { opacity: 1; transform: scale(1); }
    }

    /* Mobile portrait: let corners nicely frame the screen */
    @media (max-width: 600px) and (orientation: portrait) {
      .corner-tl { width: 38vw; max-width: 180px; }
      .corner-tr { width: 38vw; max-width: 180px; }
      .corner-bl { width: 48vw; max-width: 220px; }
      .corner-br { width: 50vw; max-width: 240px; }
      .splash-center { max-width: 92vw; padding: 12px; }
      .center-art-wrap { max-width: 350px; }
    }

    /* Wide screens */
    @media (min-width: 1400px) {
      .corner-tl { width: 19vw; max-width: 360px; }
      .corner-tr { width: 19vw; max-width: 350px; }
      .corner-bl { width: 24vw; max-width: 440px; }
      .corner-br { width: 25vw; max-width: 460px; }
      .center-art-wrap { max-width: 480px; }
    }
  </style>
</head>
<body>
  <div class="splash-screen">
    <!-- Viewport-anchored corner decorations -->
    <img src="{{ asset('images/loading-tl.png') }}" class="corner corner-tl" alt="">
    <img src="{{ asset('images/loading-tr.png') }}" class="corner corner-tr" alt="">
    <img src="{{ asset('images/loading-bl.png') }}" class="corner corner-bl" alt="">
    <img src="{{ asset('images/loading-br.png') }}" class="corner corner-br" alt="">

    <!-- Center Content -->
    <div class="splash-center">
      <div class="center-art-wrap">
        <img src="{{ asset('images/loading-center-trans.png') }}" class="center-art" alt="MushFungAI - Smart Monitoring for Healthier Mushrooms">
        <div class="spinner-active" aria-label="Loading...">
          <svg viewBox="0 0 36 36" class="spinner-svg">
            <g stroke-linecap="round" stroke-width="2.7">
              <line x1="18" y1="3.5" x2="18" y2="8.5" stroke="#4a2c17" opacity="1.0" />
              <line x1="18" y1="3.5" x2="18" y2="8.5" stroke="#4a2c17" opacity="0.92" transform="rotate(30 18 18)" />
              <line x1="18" y1="3.5" x2="18" y2="8.5" stroke="#4a2c17" opacity="0.84" transform="rotate(60 18 18)" />
              <line x1="18" y1="3.5" x2="18" y2="8.5" stroke="#5d3c26" opacity="0.75" transform="rotate(90 18 18)" />
              <line x1="18" y1="3.5" x2="18" y2="8.5" stroke="#734e35" opacity="0.66" transform="rotate(120 18 18)" />
              <line x1="18" y1="3.5" x2="18" y2="8.5" stroke="#8d654a" opacity="0.57" transform="rotate(150 18 18)" />
              <line x1="18" y1="3.5" x2="18" y2="8.5" stroke="#a37c62" opacity="0.48" transform="rotate(180 18 18)" />
              <line x1="18" y1="3.5" x2="18" y2="8.5" stroke="#ba947b" opacity="0.40" transform="rotate(210 18 18)" />
              <line x1="18" y1="3.5" x2="18" y2="8.5" stroke="#cfad96" opacity="0.31" transform="rotate(240 18 18)" />
              <line x1="18" y1="3.5" x2="18" y2="8.5" stroke="#dfc6b3" opacity="0.22" transform="rotate(270 18 18)" />
              <line x1="18" y1="3.5" x2="18" y2="8.5" stroke="#edded4" opacity="0.14" transform="rotate(300 18 18)" />
              <line x1="18" y1="3.5" x2="18" y2="8.5" stroke="#f6ede6" opacity="0.08" transform="rotate(330 18 18)" />
            </g>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function () {
      var target = "{{ route('home') }}";
      setTimeout(function () {
        window.location.replace(target);
      }, 1400);
    })();
  </script>
</body>
</html>
