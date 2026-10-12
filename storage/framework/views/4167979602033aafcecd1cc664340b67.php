<?php $__env->startSection('content'); ?>

<div class="dash-hero" style="margin-bottom: 24px;">
  <h1 style="font-size: 24px; font-weight: 800; letter-spacing: -0.5px; color: var(--brown);">Good day, Admin!</h1>
  <p style="color: var(--muted); font-size: 14px; margin-top: 4px;">Here's your overview today.</p>
</div>

<div class="stats" style="margin-bottom: 30px;">
  <div class="card stat" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: #f6f4f1; display: grid; place-items: center; color: var(--brown);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
      </div>
      <small style="margin: 0; flex: 1;">Total Batches</small>
    </div>
    <strong style="margin: 0; font-size: 24px;"><?php echo e(number_format($totalBatches)); ?></strong>
  </div>
  
  <div class="card stat g" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: #fffaf2; display: grid; place-items: center; color: var(--gold);">
        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'scale','s' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'scale','s' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
      </div>
      <small style="margin: 0; flex: 1;">Active Yield</small>
    </div>
    <strong style="margin: 0; font-size: 24px;"><?php echo e(rtrim(rtrim(number_format($yield, 1), '0'), '.')); ?> kg</strong>
  </div>

  <div class="card stat" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: #fffaf2; display: grid; place-items: center; color: var(--gold);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 10h20"/></svg>
      </div>
      <small style="margin: 0; flex: 1;">Total Sales</small>
    </div>
    <strong style="margin: 0; font-size: 24px;">₱<?php echo e(number_format($revenue)); ?></strong>
  </div>

  <div class="card stat r" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: #fff1f2; display: grid; place-items: center; color: var(--dred);">
        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'store','s' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'store','s' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
      </div>
      <small style="margin: 0; flex: 1;">Pending Orders</small>
    </div>
    <strong style="margin: 0; font-size: 24px;"><?php echo e(number_format($pendingOrders)); ?></strong>
  </div>
</div>

<div class="card trend" style="margin-bottom: 30px; margin-top: 0;">
  <div class="thead">
    <h2>PRODUCTION YIELD</h2>
    <form id="rangeForm">
      <select name="range" onchange="document.getElementById('rangeForm').submit()">
        <option value="week" <?php echo e(request('range') == 'week' ? 'selected' : ''); ?>>This Week</option>
        <option value="month" <?php echo e(request('range') == 'month' ? 'selected' : ''); ?>>This Month</option>
        <option value="year" <?php echo e(request('range') == 'year' ? 'selected' : ''); ?>>This Year</option>
      </select>
    </form>
  </div>
  <div class="chartwrap">
    <div id="salesChart" data-sales="<?php echo e(json_encode($sales)); ?>"></div>
  </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
  <h2 style="font-size: 16px; font-weight: 700; color: var(--brown);">Recent Activity</h2>
  <a href="#" style="font-size: 12px; color: var(--gold); font-weight: 700; text-transform: uppercase;">View All</a>
</div>

<div class="card" style="padding: 0;">
  <?php $__empty_1 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div style="display: flex; align-items: center; gap: 16px; padding: 16px 20px; border-bottom: 1px solid var(--line);">
      <div style="width: 36px; height: 36px; border-radius: 50%; background: #f6f4f1; display: grid; place-items: center; color: var(--brown);">
        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'history','s' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'history','s' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
      </div>
      <div style="flex: 1;">
        <b style="display: block; font-size: 14px; margin-bottom: 2px;"><?php echo e($act->action); ?></b>
        <?php if($act->details): ?><span style="color: var(--muted); font-size: 12px; display: block;"><?php echo e($act->details); ?></span><?php endif; ?>
      </div>
      <small style="color: var(--muted); font-size: 11px; white-space: nowrap;"><?php echo e($act->created_at->diffForHumans(null, true, true)); ?></small>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div style="padding: 30px; text-align: center; color: var(--muted); font-size: 13px;">No recent activity</div>
  <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>