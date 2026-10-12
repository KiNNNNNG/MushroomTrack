<?php $__env->startSection('content'); ?>
<div class="uhead">
  <h2>PERSONNEL MANAGEMENT</h2>
  <button class="btn" onclick="document.getElementById('u-new').showModal()">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="8" r="4"/><path d="M2 21v-1a6 6 0 0 1 12 0v1M19 8v6M16 11h6"/></svg>ADD NEW USER
  </button>
</div>
<div class="users">
  <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="card user" onclick="document.getElementById('u<?php echo e($u->id); ?>').showModal()">
      <div class="l">
        <div class="av"><?php echo e(strtoupper(mb_substr($u->name, 0, 1))); ?></div>
        <div><b><?php echo e($u->name); ?></b><small><?php echo e($u->role); ?></small></div>
      </div>
      <span class="pill <?php echo e($u->status === 'active' ? '' : 'off'); ?>"><?php echo e($u->status); ?></span>
    </div>
    <?php echo $__env->make('admin.partials.user-form', ['u' => $u], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php echo $__env->make('admin.partials.user-form', ['u' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views/admin/users.blade.php ENDPATH**/ ?>