<?php $__env->startSection('content'); ?>
<div class="toolbar" style="gap:10px">
  
</div>
<div class="card tbl">
  <table>
    <tr><th>Batch ID</th><th style="width:40%">Name</th><th>Bags</th><th>Status</th><th>Harvest Date</th></tr>
    <?php $__empty_1 = true; $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr class="click" onclick="document.getElementById('b<?php echo e($b->id); ?>').showModal()">
        <td class="mono"><?php echo e($b->code); ?></td>
        <td><?php echo e($b->name); ?></td>
        <td><?php echo e($b->bags); ?></td>
        <td><span class="tag"><?php echo e(strtoupper($b->status)); ?></span></td>
        <td class="date"><?php echo e(optional($b->harvest_date)->format('Y-m-d') ?? '—'); ?></td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="5" class="empty">No batches yet.</td></tr>
    <?php endif; ?>
  </table>
</div>
<?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo $__env->make('admin.partials.batch-form', ['b' => $b], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\admin\batches.blade.php ENDPATH**/ ?>