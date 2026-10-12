<?php $__env->startSection('content'); ?>
<?php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); ?>

<div style="position: relative; margin-bottom: 24px;">
  <svg style="position: absolute; left: 12px; top: 12px; color: var(--muted);" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
  <input type="text" placeholder="Search products..." style="width: 100%; height: 42px; border-radius: 20px; border: 1px solid var(--line); padding: 0 16px 0 38px; background: #fff; font-size: 14px;">
</div>

<h2 style="font-size: 18px; font-weight: 800; color: var(--brown); margin-bottom: 16px; letter-spacing: -0.3px;">Fresh Mushrooms</h2>

<div class="shop" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
  <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="card sp" style="display: flex; flex-direction: column; overflow: hidden; padding: 0;">
      <div class="img" style="height: 140px; background: #e9e4de url('<?php echo e($p->image_url ?? 'https://images.unsplash.com/photo-1590059345229-3733215be132?auto=format&fit=crop&q=80&w=300'); ?>') center/cover; border-bottom: 1px solid var(--line);"></div>
      <div style="padding: 12px; flex: 1; display: flex; flex-direction: column;">
        <h3 style="font-size: 14px; font-weight: 700; color: var(--ink); margin-bottom: 4px; line-height: 1.3;"><?php echo e($p->name); ?></h3>
        <span style="font-size: 13px; color: var(--brown); font-weight: 700; margin-bottom: 12px;">₱<?php echo e($fmt($p->price)); ?> / kg</span>
        
        <form method="POST" action="<?php echo e(route('customer.cart.add')); ?>" style="margin-top: auto;"><?php echo csrf_field(); ?>
          <input type="hidden" name="product_id" value="<?php echo e($p->id); ?>">
          <input type="hidden" name="quantity_kg" value="1">
          <button class="btn" style="width: 100%; justify-content: center; height: 36px; font-size: 12px; border-radius: 6px; background: var(--green);" <?php if($p->stock_kg <= 0): echo 'disabled'; endif; ?>>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            ADD
          </button>
        </form>
      </div>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views/customer/shop.blade.php ENDPATH**/ ?>