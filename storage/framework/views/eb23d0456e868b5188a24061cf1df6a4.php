<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $__env->yieldContent('title', 'MushFungAI'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo e(asset('css/auth.css')); ?>">
</head>
<body class="au">
  <section class="au-l">
    <div class="au-card <?php echo $__env->yieldContent('variant', 'wide'); ?>">
      <?php echo $__env->yieldContent('card'); ?>
    </div>
  </section>
  <section class="au-r">
    <div class="au-box">
      <?php if(session('ok')): ?><div class="al ok"><?php echo e(session('ok')); ?></div><?php endif; ?>
      <?php if(session('dev_otp')): ?><div class="al dev">Dev mode — your OTP is <b><?php echo e(session('dev_otp')); ?></b> (also saved in storage/logs/laravel.log)</div><?php endif; ?>
      <?php if($errors->any()): ?><div class="al err"><?php echo e($errors->first()); ?></div><?php endif; ?>
      <?php echo $__env->yieldContent('right'); ?>
    </div>
  </section>
  <script>
    (function () {
      var EYE = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
      var OFF = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.9 17.9A10.4 10.4 0 0 1 12 19c-6 0-10-7-10-7a18.7 18.7 0 0 1 5-5.7M9.9 5.2A9.7 9.7 0 0 1 12 5c6 0 10 7 10 7a18.5 18.5 0 0 1-2.2 3.2M14.1 14.1a3 3 0 1 1-4.2-4.2M2 2l20 20"/></svg>';
      document.querySelectorAll('input[type=password]').forEach(function (input) {
        var wrap = document.createElement('div'); wrap.className = 'pw';
        input.parentNode.insertBefore(wrap, input); wrap.appendChild(input);
        var b = document.createElement('button'); b.type = 'button'; b.className = 'eye';
        b.innerHTML = EYE; b.setAttribute('aria-label', 'Show password');
        b.onclick = function () {
          var show = input.type === 'password';
          input.type = show ? 'text' : 'password';
          b.innerHTML = show ? OFF : EYE;
          b.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        };
        wrap.appendChild(b);
      });
    })();
  </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views/layouts/auth.blade.php ENDPATH**/ ?>