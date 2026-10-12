<?php $__env->startSection('title', 'Sign in — MushFungAI'); ?>
<?php $__env->startSection('card'); ?>
  <h2 class="brand">MushFung<i>AI</i></h2>
  <p>Smart tracking for mushroom farms — follow every batch from substrate to harvest.</p>
  <p>Record daily checks and yields, manage customer orders, and keep your whole team in sync.</p>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('right'); ?>
  <h1 class="hello">Welcome</h1>
  <div class="tabs2"><a href="<?php echo e(route('login')); ?>" class="on">Sign in</a><a href="<?php echo e(route('register')); ?>">Sign up</a></div>
  <form method="POST" action="<?php echo e(route('login')); ?>"><?php echo csrf_field(); ?>
    <label>Username</label>
    <input name="username" value="<?php echo e(old('username')); ?>" placeholder="Username or email" autocomplete="username" required autofocus>
    <label>Password</label>
    <input type="password" name="password" autocomplete="current-password" required>
    <div class="forgot"><a href="<?php echo e(route('password.forgot')); ?>">Forgot?</a></div>
    <div class="act c"><button class="pill">Enter</button></div>
  </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\auth\login.blade.php ENDPATH**/ ?>