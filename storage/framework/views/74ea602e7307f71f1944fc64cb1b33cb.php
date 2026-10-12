<?php $__env->startSection('content'); ?>
<div class="card wf">
  <h2>Harvest Yield Scale</h2>
  <form class="wb" method="POST" action="<?php echo e(route('worker.harvest.store')); ?>" data-confirm="Record this harvest? The weight is added to the batch yield, the cleared bags are removed, and the weight is added to the selected store product." data-title="Record harvest" data-yes="RECORD"><?php echo csrf_field(); ?>
    <label>Select batch</label>
    <select name="batch_id" required>
      <?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($b->id); ?>" <?php if(old('batch_id') == $b->id): echo 'selected'; endif; ?>><?php echo e($b->code); ?> — <?php echo e($b->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <label>Add to store product</label>
    <select name="product_id">
      <option value="">Don't add to store (spoiled / kept)</option>
      <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>" <?php if(old('product_id') == $p->id): echo 'selected'; endif; ?>><?php echo e($p->name); ?> — <?php echo e(rtrim(rtrim(number_format($p->stock_kg, 2), '0'), '.')); ?>kg in stock</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <div class="two">
      <div><label>Weight (kilos)</label><input type="number" step="0.1" min="0.1" name="weight_kg" placeholder="0.0" required value="<?php echo e(old('weight_kg')); ?>"></div>
      <div><label>Bags cleared</label><input type="number" min="0" name="bags_cleared" placeholder="0" required value="<?php echo e(old('bags_cleared')); ?>"></div>
    </div>
    <button class="btn gold">RECORD HARVEST</button>
  </form>
</div>

<div class="sec">Recently Scaled Yields</div>
<?php $__currentLoopData = $recent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="card lr" style="max-width:720px;margin-bottom:12px">
    <div><b><?php echo e($h->batch->code); ?></b><small><?php echo e($h->created_at->format('Y-m-d H:i')); ?> · <?php echo e($h->user->name); ?> · <?php echo e($h->product ? '→ '.$h->product->name : 'not added to store'); ?></small></div>
    <span class="hl"><?php echo e(rtrim(rtrim(number_format($h->weight_kg, 2), '0'), '.')); ?>kg · <?php echo e($h->bags_cleared); ?> bags</span>
  </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\worker\harvest.blade.php ENDPATH**/ ?>