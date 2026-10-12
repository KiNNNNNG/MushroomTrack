<!doctype html>
<html><head><meta charset="utf-8"><title>MushFungAI Reports</title><?php echo $__env->make('exports._style', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></head>
<body>
<?php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); ?>
<h1>MushFungAI — Batch Reports</h1>
<div class="sub">Generated <?php echo e(now()->format('Y-m-d H:i')); ?> · Losses &amp; harvests: <?php echo e($rangeLabel); ?></div>

<h2>Batch health &amp; schedule</h2>
<table>
  <tr><th>Batch</th><th>Name</th><th>Status</th><th class="r">Healthy</th><th class="r">Lost</th><th class="r">Health</th><th class="r">Yield kg</th><th>Est. harvest</th></tr>
  <?php $__empty_1 = true; $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr><td><?php echo e($b->code); ?></td><td><?php echo e($b->name); ?></td><td><?php echo e(ucfirst($b->status)); ?></td><td class="r"><?php echo e($b->bags); ?></td><td class="r"><?php echo e($b->lost_bags); ?></td><td class="r"><?php echo e($b->health); ?>%</td><td class="r"><?php echo e($fmt($b->yield_kg)); ?></td><td><?php echo e(optional($b->harvest_date)->format('Y-m-d') ?? '—'); ?></td></tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="8" class="none">No batches.</td></tr> <?php endif; ?>
  <?php if($batches->isNotEmpty()): ?>
    <tr class="tot"><td colspan="3">TOTAL</td><td class="r"><?php echo e($batches->sum('bags')); ?></td><td class="r"><?php echo e($batches->sum('lost_bags')); ?></td><td></td><td class="r"><?php echo e($fmt($batches->sum('yield_kg'))); ?></td><td></td></tr>
  <?php endif; ?>
</table>

<h2>Bags lost by reason</h2>
<table>
  <tr><th>Reason</th><th class="r">Bags lost</th></tr>
  <?php $__empty_1 = true; $__currentLoopData = $byReason; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason => $qty): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr><td><?php echo e(\App\Models\BagLoss::labelFor($reason)); ?></td><td class="r"><?php echo e($qty); ?></td></tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="2" class="none">No losses recorded for this period.</td></tr> <?php endif; ?>
</table>

<h2>Loss reports</h2>
<table>
  <tr><th>Date</th><th>Batch</th><th class="r">Bags</th><th>Reason</th><th>Reported by</th></tr>
  <?php $__empty_1 = true; $__currentLoopData = $losses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr><td><?php echo e($l->created_at->format('Y-m-d H:i')); ?></td><td><?php echo e($l->batch->code ?? ''); ?></td><td class="r"><?php echo e($l->quantity); ?></td><td><?php echo e($l->reason_label); ?></td><td><?php echo e($l->user->name ?? 'System'); ?></td></tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="5" class="none">None.</td></tr> <?php endif; ?>
</table>

<h2>Harvests</h2>
<table>
  <tr><th>Date</th><th>Batch</th><th>Store product</th><th class="r">Weight kg</th><th class="r">Bags cleared</th><th>Worker</th></tr>
  <?php $__empty_1 = true; $__currentLoopData = $harvests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr><td><?php echo e($h->created_at->format('Y-m-d H:i')); ?></td><td><?php echo e($h->batch->code ?? ''); ?></td><td><?php echo e($h->product->name ?? 'Not added to store'); ?></td><td class="r"><?php echo e($fmt($h->weight_kg)); ?></td><td class="r"><?php echo e($h->bags_cleared); ?></td><td><?php echo e($h->user->name ?? ''); ?></td></tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="6" class="none">None.</td></tr> <?php endif; ?>
  <?php if($harvests->isNotEmpty()): ?>
    <tr class="tot"><td colspan="3">TOTAL</td><td class="r"><?php echo e($fmt($harvests->sum('weight_kg'))); ?></td><td class="r"><?php echo e($harvests->sum('bags_cleared')); ?></td><td></td></tr>
  <?php endif; ?>
</table>
</body></html>
<?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\exports\reports.blade.php ENDPATH**/ ?>