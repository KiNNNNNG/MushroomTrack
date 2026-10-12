<?php $__env->startSection('content'); ?>
<div class="stats">
  <div class="card stat"><small>Active Bags</small><strong><?php echo e(number_format($activeBags)); ?></strong></div>
  <div class="card stat r"><small>Bags Lost</small><strong><?php echo e(number_format($bagsLost)); ?></strong></div>
  <div class="card stat"><small>Revenue</small><strong>₱<?php echo e(number_format($revenue)); ?></strong></div>
  <div class="card stat g"><small>Total Yield</small><strong><?php echo e(rtrim(rtrim(number_format($yield, 1), '0'), '.')); ?>kg</strong></div>
</div>
<div class="card trend">
  <div class="thead">
    <h2>SALES TREND</h2>
    <form method="GET">
      <select name="range" onchange="this.form.submit()">
        <?php $__currentLoopData = ['week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all' => 'All Time']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($k); ?>" <?php if($range === $k): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>
    </form>
  </div>
  <div class="chartwrap"><div id="salesChart" data-sales='<?php echo json_encode($sales, 15, 512) ?>'></div></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\admin\dashboard.blade.php ENDPATH**/ ?>