<?php $__env->startSection('content'); ?>
<?php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); ?>
<?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <?php $p = $o->payment; ?>
  <div class="card oc">
    <div class="oct">
      <div>
        <small>ORD: <?php echo e($o->display_code); ?></small>
        <h3><?php echo e($o->product->name); ?> (<?php echo e($fmt($o->quantity_kg)); ?>kg)</h3>
        <div class="od"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'clock','s' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'clock','s' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><?php echo e($o->created_at->format('Y-m-d')); ?></div>
        <div class="oinfo">
          <span class="tag fl"><?php echo e($o->fulfillment === 'delivery' ? 'DELIVERY' : 'PICKUP'); ?> · <?php echo e(strtoupper($o->status === 'pending' ? ($o->isAwaitingPayment() ? 'awaiting payment' : 'waiting for confirmation') : $o->fulfillment_status_label)); ?></span>
          <?php if($p): ?><span class="tag pay <?php echo e($p->status); ?>"><?php echo e(strtoupper($p->method_label)); ?> · <?php echo e(strtoupper($p->status_label)); ?></span><?php endif; ?>
        </div>
        <?php if($o->fulfillment === 'delivery' && $o->delivery_address): ?><div class="oaddr">Deliver to: <?php echo e($o->delivery_address); ?></div><?php endif; ?>
        <?php if($p && (float) $p->delivery_fee > 0): ?><div class="oaddr">Payment <?php echo e($p->reference); ?> · total ₱<?php echo e($fmt($p->amount)); ?> including ₱<?php echo e($fmt($p->delivery_fee)); ?> delivery</div><?php endif; ?>
      </div>
      <div class="ocr">
        <b>₱<?php echo e($fmt($o->total)); ?></b>
        <div class="oa">
          <span class="tag"><?php echo e(strtoupper($o->status)); ?></span>
          <?php if($o->isAwaitingPayment() && $o->status === 'pending'): ?>
            <form method="POST" action="<?php echo e(route('customer.pay.start', $p)); ?>"><?php echo csrf_field(); ?><button class="btn sm gold">PAY NOW</button></form>
          <?php endif; ?>
          <?php if($o->status === 'pending'): ?>
            <form method="POST" action="<?php echo e(route('customer.orders.cancel', $o)); ?>" data-confirm="Cancel this order?" data-title="Cancel order" data-yes="YES, CANCEL" data-no="KEEP ORDER" data-tone="danger"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
              <button class="cx"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'xcircle','s' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'xcircle','s' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>CANCEL</button></form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <div class="emptyi">No active orders.</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views/customer/orders.blade.php ENDPATH**/ ?>