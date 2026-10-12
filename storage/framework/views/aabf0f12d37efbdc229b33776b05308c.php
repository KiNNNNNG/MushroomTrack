<?php $__env->startSection('content'); ?>
<?php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); ?>
<div class="shop">
  <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="card sp">
      <div class="img" style="<?php echo \Illuminate\Support\Arr::toCssStyles(["background-image: url('".$p->image_url."')" => $p->image_url]) ?>"></div>
      <div class="sb">
        <div class="st"><h3><?php echo e($p->name); ?></h3><span>₱<?php echo e($fmt($p->price)); ?></span></div>
        <?php if($p->description): ?><p class="sd">"<?php echo e($p->description); ?>"</p><?php endif; ?>
      </div>
      <form class="sf" method="POST" action="<?php echo e(route('customer.cart.add')); ?>"><?php echo csrf_field(); ?>
        <input type="hidden" name="product_id" value="<?php echo e($p->id); ?>">
        <div class="sr">
          <span><?php echo e($p->stock_kg > 0 ? strtoupper($fmt($p->stock_kg)).'KG LEFT' : 'OUT OF STOCK'); ?></span>
          <input type="number" name="quantity_kg" value="1" min="0.5" step="0.5" max="<?php echo e($p->stock_kg); ?>" <?php if($p->stock_kg <= 0): echo 'disabled'; endif; ?>>
        </div>
        <div class="sbtn">
          <button class="btn out" <?php if($p->stock_kg <= 0): echo 'disabled'; endif; ?>>ADD TO CART</button>
          <button class="btn" formaction="<?php echo e(route('customer.order.place')); ?>" <?php if($p->stock_kg <= 0): echo 'disabled'; endif; ?>>BUY NOW</button>
        </div>
      </form>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\customer\shop.blade.php ENDPATH**/ ?>