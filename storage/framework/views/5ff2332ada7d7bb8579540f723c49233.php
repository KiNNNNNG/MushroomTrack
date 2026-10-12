<?php $__env->startSection('content'); ?>
<?php $av = $u->avatar ? asset($u->avatar) : null; ?>
<div class="pgrid">
  
  <div class="card pc pav">
    <div class="av2"><?php if($av): ?><img src="<?php echo e($av); ?>" alt=""><?php else: ?><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'user','s' => '90']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'user','s' => '90']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><?php endif; ?></div>
    <h2><?php echo e($u->name); ?></h2>
    <div class="pinfo">
      <div><span>Role</span><b><?php echo e(ucfirst($u->role)); ?></b></div>
      <div><span>Username</span><b><?php echo e($u->username); ?></b></div>
      <div><span>Member since</span><b><?php echo e($u->created_at->format('M Y')); ?></b></div>
    </div>
    <form method="POST" action="<?php echo e(route('profile.photo')); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?>
      <input type="file" name="photo" id="avIn" accept="image/*" hidden onchange="this.form.submit()">
      <button type="button" class="mb" style="margin:0 auto" onclick="document.getElementById('avIn').click()"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'camera','s' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'camera','s' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>CHANGE PHOTO</button>
    </form>
  </div>

  
  <form class="card pc" method="POST" action="<?php echo e(route('profile.update')); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <div class="pc-h">Personal information</div>
    <div class="pf">
      <div class="rw"><label>Full name</label><input name="name" value="<?php echo e(old('name', $u->name)); ?>" required></div>
      <div class="rw"><label>Username</label><input name="username" value="<?php echo e(old('username', $u->username)); ?>" required></div>
      <div class="rw"><label>Email</label><input type="email" name="email" value="<?php echo e(old('email', $u->email)); ?>" required></div>
      <div class="rw"><label>Phone</label><input name="phone" value="<?php echo e(old('phone', $u->phone)); ?>" placeholder="+63 900 000 0000"></div>
      <div class="rw"><label>Address</label><input name="address" value="<?php echo e(old('address', $u->address)); ?>"></div>
      <div class="fa"><button class="btn">SAVE CHANGES</button></div>
    </div>
  </form>
</div>


<div class="card pc" style="margin-top:40px">
  <div class="pc-h">Password and other settings</div>
  <div class="setl">
    <div><b>Change password</b><span>Use a strong password you don't use anywhere else.</span></div>
    <button class="ebtn" onclick="document.getElementById('dPw').showModal()">EDIT</button>
  </div>
  <div class="setl">
    <div><b>Notifications</b><span>Bell <?php echo e($u->notify_in_app ? 'ON' : 'OFF'); ?> · Email <?php echo e($u->notify_email ? 'ON' : 'OFF'); ?> · SMS <?php echo e($u->notify_sms ? 'ON' : 'OFF'); ?></span></div>
    <button class="ebtn" onclick="document.getElementById('dNt').showModal()">EDIT</button>
  </div>
  <div class="setl">
    <div><b>Account preferences</b><span>Manage your profile photo or delete your account.</span></div>
    <button class="ebtn" onclick="document.getElementById('dPf').showModal()">EDIT</button>
  </div>
</div>

<dialog id="dPw">
  <form method="POST" action="<?php echo e(route('profile.password')); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <h3>Change password</h3>
    <div class="f"><label>Current password</label><input type="password" name="current_password" required></div>
    <div class="f"><label>New password</label><input type="password" name="password" minlength="8" required></div>
    <div class="f"><label>Confirm new password</label><input type="password" name="password_confirmation" required></div>
    <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CANCEL</button><button class="btn">SAVE</button></div>
  </form>
</dialog>

<dialog id="dNt">
  <form method="POST" action="<?php echo e(route('profile.notifications')); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <h3>Notifications</h3>
    <label class="chk2"><input type="checkbox" name="notify_in_app" value="1" <?php if($u->notify_in_app): echo 'checked'; endif; ?>> In-app notifications (bell icon)</label>
    <label class="chk2"><input type="checkbox" name="notify_email" value="1" <?php if($u->notify_email): echo 'checked'; endif; ?>> Email me about order updates and reminders</label>
    <label class="chk2"><input type="checkbox" name="notify_sms" value="1" <?php if($u->notify_sms): echo 'checked'; endif; ?>> Text me (SMS) about order updates and reminders</label>
    <p class="chkh">SMS goes to the phone number in your profile and needs a Philippine mobile number (like 0917 123 4567).</p>
    <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CANCEL</button><button class="btn">SAVE</button></div>
  </form>
</dialog>

<dialog id="dPf">
  <h3>Account preferences</h3>
  <?php if($u->avatar): ?>
    <form method="POST" action="<?php echo e(route('profile.photo.remove')); ?>"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
      <button class="btn ghost" style="margin-bottom:22px">REMOVE PROFILE PHOTO</button></form>
  <?php endif; ?>
  <form method="POST" action="<?php echo e(route('profile.destroy')); ?>" data-confirm="Delete your account permanently? This cannot be undone." data-title="Delete account" data-yes="DELETE" data-tone="danger"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
    <div class="f"><label>Delete account — confirm with your password</label><input type="password" name="password" required></div>
    <button class="dng">DELETE MY ACCOUNT</button>
  </form>
  <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CLOSE</button></div>
</dialog>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\profile.blade.php ENDPATH**/ ?>