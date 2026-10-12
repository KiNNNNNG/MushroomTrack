<?php $__env->startSection('title', 'Sign up — MushFungAI'); ?>
<?php $__env->startSection('card'); ?>
  <h2 class="brand">MushFung<i>AI</i></h2>
  <p>Create a customer account to browse fresh harvests, place orders and track them in one place.</p>
  <p>Farm staff accounts are created by an administrator.</p>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('right'); ?>
  <h1 class="hello">Welcome</h1>
  <div class="tabs2"><a href="<?php echo e(route('login')); ?>">Sign in</a><a href="<?php echo e(route('register')); ?>" class="on">Sign up</a></div>
  <form method="POST" action="<?php echo e(route('register')); ?>"><?php echo csrf_field(); ?>
    <label>Username</label>
    <input name="username" value="<?php echo e(old('username')); ?>" autocomplete="username" required autofocus>
    <label>Email</label>
    <input type="email" name="email" value="<?php echo e(old('email')); ?>" autocomplete="email" required>
    <label>Password</label>
    <input type="password" name="password" autocomplete="new-password" minlength="8" required>
    <label>Confirm password</label>
    <input type="password" name="password_confirmation" autocomplete="new-password" required>
    <div class="act"><button class="pill">Sign up</button></div>
  </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\auth\register.blade.php ENDPATH**/ ?>