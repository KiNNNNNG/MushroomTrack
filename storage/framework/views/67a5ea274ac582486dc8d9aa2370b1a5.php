<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>MushFungAI</title>
  <noscript><meta http-equiv="refresh" content="3;url=<?php echo e(route('home')); ?>"></noscript>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%;overflow:hidden;font-family: Arial, Helvetica, sans-serif;}
    body {
      background-color: #ffffff;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #333;
    }
    .content {
      text-align: center;
      z-index: 10;
      margin-top: -10vh; /* slightly above center */
    }
    .logo {
      font-family: 'Fredoka', sans-serif;
      font-weight: 600;
      font-size: clamp(48px, 8vw, 90px);
      letter-spacing: -1px;
    }
    .logo-mush { color: #433324; }
    .logo-ai { color: #d09435; }
    
    .subtitle {
      font-size: clamp(14px, 1.8vw, 20px);
      color: #837a72;
      margin-top: 10px;
      line-height: 1.5;
      font-weight: 400;
    }

    .loading-container {
      margin-top: 50px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .progress-bar {
      width: 300px;
      height: 2px;
      background-color: #e5e0d8;
      position: relative;
      border-radius: 2px;
      overflow: hidden;
    }
    .progress-fill {
      position: absolute;
      left: 0;
      top: 0;
      height: 100%;
      background-color: #433324;
      width: 0%;
      transition: width 0.15s ease-out;
    }

    .loading-text {
      margin-top: 16px;
      font-size: 13px;
      color: #837a72;
      font-weight: 400;
    }

    .mushrooms {
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: flex-end;
      overflow: hidden;
      z-index: 1;
    }
    .mushrooms svg {
      width: 100%;
      min-width: 1200px;
      height: auto;
    }
  </style>
</head>
<body>

  <div class="content">
    <div class="logo"><span class="logo-mush">MushFung</span><span class="logo-ai">AI</span></div>
    <div class="subtitle">Smart tracking for mushroom<br>farms, from substrate to harvest.</div>
    
    <div class="loading-container">
      <div class="progress-bar">
        <div class="progress-fill" id="fill"></div>
      </div>
      <div class="loading-text" id="txt">Loading your farm data...</div>
    </div>
  </div>

  <div class="mushrooms">
    <svg viewBox="0 0 1200 250" preserveAspectRatio="xMidYMax slice" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="capGrad" x1="0%" y1="0%" x2="0%" y2="100%">
          <stop offset="0%" stop-color="#fcfbfa" />
          <stop offset="100%" stop-color="#e8e0cc" />
        </linearGradient>
        <linearGradient id="stemGrad" x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" stop-color="#ded4be" />
          <stop offset="50%" stop-color="#fdfbf7" />
          <stop offset="100%" stop-color="#d4c9b3" />
        </linearGradient>
        <linearGradient id="gillGrad" x1="0%" y1="0%" x2="0%" y2="100%">
          <stop offset="0%" stop-color="#bdae97" />
          <stop offset="100%" stop-color="#d2c5b0" />
        </linearGradient>
        <g id="mushroom">
          <!-- gills -->
          <ellipse cx="0" cy="0" rx="80" ry="22" fill="url(#gillGrad)" />
          <!-- gills lines -->
          <g stroke="#b5a691" stroke-width="0.8" fill="none" opacity="0.4">
            <path d="M-75,7.6 L0,0" />
            <path d="M-65,12.7 L0,0" />
            <path d="M-50,17 L0,0" />
            <path d="M-30,20.4 L0,0" />
            <path d="M-15,21.6 L0,0" />
            <path d="M0,22 L0,0" />
            <path d="M15,21.6 L0,0" />
            <path d="M30,20.4 L0,0" />
            <path d="M50,17 L0,0" />
            <path d="M65,12.7 L0,0" />
            <path d="M75,7.6 L0,0" />
          </g>
          <!-- stem -->
          <path d="M-14,140 C-14,20 -20,0 0,0 C20,0 14,20 14,140 Z" fill="url(#stemGrad)" />
          <!-- cap -->
          <path d="M-80,0 C-80,-75 80,-75 80,0 Z" fill="url(#capGrad)" />
        </g>
      </defs>

      <!-- Arrange mushrooms with translate and scale -->
      <use href="#mushroom" transform="translate(50, 260) scale(0.9)" />
      <use href="#mushroom" transform="translate(200, 240) scale(1.3)" />
      <use href="#mushroom" transform="translate(350, 260) scale(0.8)" />
      <use href="#mushroom" transform="translate(500, 250) scale(1.1)" />
      <use href="#mushroom" transform="translate(700, 220) scale(1.5)" />
      <use href="#mushroom" transform="translate(850, 265) scale(0.8)" />
      <use href="#mushroom" transform="translate(980, 240) scale(1.2)" />
      <use href="#mushroom" transform="translate(1150, 250) scale(1)" />
      <use href="#mushroom" transform="translate(-80, 250) scale(1)" />
    </svg>
  </div>

  <script>
    (function () {
      var fill = document.getElementById('fill'), p = 0;
      var go = "<?php echo e(route('home')); ?>";
      var t = setInterval(function () {
        p = Math.min(100, p + Math.random() * 15 + 5);
        fill.style.width = p + '%';
        if (p >= 100) { clearInterval(t); setTimeout(function () { location.replace(go); }, 400); }
      }, 150);
    })();
  </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\loading.blade.php ENDPATH**/ ?>