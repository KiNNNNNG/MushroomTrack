<?php $__env->startSection('title', 'New password — MushFungAI'); ?>
<?php $__env->startSection('variant', 'tall'); ?>
<?php $__env->startSection('card'); ?>
  <div class="au-logo"><img src="<?php echo e(asset('images/mushroom.png')); ?>" alt=""></div>
  <h2 class="brand">MushFung<i>AI</i></h2>
  <hr>
  <p class="sub">Set a new password</p>
  <p>Choose at least 8 characters. You'll be signed in right after.</p>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('right'); ?>
  <a class="back" href="<?php echo e(route('password.forgot')); ?>" aria-label="Back">←</a>
  <h1 class="hello" style="font-size:clamp(34px,4vw,52px);margin-bottom:34px">Set your new password</h1>
  <form method="POST" action="<?php echo e(route('password.update')); ?>"><?php echo csrf_field(); ?>
    <label>New password</label>
    <input type="password" name="password" autocomplete="new-password" minlength="8" required autofocus>
    <label>Confirm password</label>
    <input type="password" name="password_confirmation" autocomplete="new-password" required>
    <div class="act" style="margin-top:34px"><button class="pill">Sign in</button></div>
  </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\auth\reset.blade.php ENDPATH**/ ?>