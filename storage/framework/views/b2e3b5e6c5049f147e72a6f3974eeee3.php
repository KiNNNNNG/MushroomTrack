<dialog id="<?php echo e($u ? 'u'.$u->id : 'u-new'); ?>">
  <form method="POST" action="<?php echo e($u ? route('admin.users.update', $u) : route('admin.users.store')); ?>">
    <?php echo csrf_field(); ?> <?php if($u): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
    <h3><?php echo e($u ? 'Edit User' : 'Add New User'); ?></h3>
    <div class="f"><label>Name</label><input name="name" required value="<?php echo e($u->name ?? ''); ?>"></div>
    <div class="f"><label>Email</label><input type="email" name="email" required value="<?php echo e($u->email ?? ''); ?>"></div>
    <div class="f2">
      <div class="f"><label>Role</label><select name="role"><?php $__currentLoopData = ['admin','worker','customer']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($r); ?>" <?php if(($u->role ?? 'worker') === $r): echo 'selected'; endif; ?>><?php echo e(ucfirst($r)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
      <?php if($u): ?><div class="f"><label>Status</label><select name="status"><?php $__currentLoopData = ['active','inactive']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s); ?>" <?php if($u->status === $s): echo 'selected'; endif; ?>><?php echo e(ucfirst($s)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><?php endif; ?>
    </div>
    <div class="f"><label>Password <?php echo e($u ? '(leave blank to keep)' : ''); ?></label><input type="password" name="password" minlength="8" <?php if(! $u): echo 'required'; endif; ?>></div>
    <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CANCEL</button><button class="btn">SAVE</button></div>
  </form>
  <?php if($u && $u->id !== auth()->id()): ?>
    <form class="del" method="POST" action="<?php echo e(route('admin.users.destroy', $u)); ?>" data-confirm="Delete this user?" data-title="Delete user" data-yes="DELETE" data-no="CANCEL" data-tone="danger"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button>DELETE USER</button></form>
  <?php endif; ?>
</dialog>
<?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\admin\partials\user-form.blade.php ENDPATH**/ ?>