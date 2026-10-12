<!doctype html>
<html><head><meta charset="utf-8"><title>MushFungAI Sales</title><?php echo $__env->make('exports._style', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></head>
<body>
<?php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); ?>
<h1>MushFungAI — Sales Report</h1>
<div class="sub">Generated <?php echo e(now()->format('Y-m-d H:i')); ?> · Period: <?php echo e($rangeLabel); ?></div>
<div class="box"><b>Total sales: ₱<?php echo e(number_format($total, 2)); ?></b> &nbsp;·&nbsp; <?php echo e($fmt($totalKg)); ?> kg sold &nbsp;·&nbsp; <?php echo e($sales->count()); ?> sale(s)</div>

<h2>By product</h2>
<table>
  <tr><th>Product</th><th class="r">Kg</th><th class="r">Amount</th></tr>
  <?php $__empty_1 = true; $__currentLoopData = $byProduct; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr><td><?php echo e($name); ?></td><td class="r"><?php echo e($fmt($v['kg'])); ?></td><td class="r">₱<?php echo e(number_format($v['amount'], 2)); ?></td></tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="3" class="none">No sales in this period.</td></tr> <?php endif; ?>
</table>

<h2>Revenue ledger</h2>
<table>
  <tr><th>Date</th><th>Order</th><th>Customer</th><th>Product</th><th class="r">Kg</th><th class="r">Amount</th></tr>
  <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr><td><?php echo e($s->sold_at->format('Y-m-d H:i')); ?></td><td><?php echo e($s->order?->display_code ?? '—'); ?></td><td><?php echo e($s->order?->user?->name ?? '—'); ?></td><td><?php echo e($s->product->name ?? 'Deleted product'); ?></td><td class="r"><?php echo e($fmt($s->quantity_kg)); ?></td><td class="r">₱<?php echo e(number_format($s->amount, 2)); ?></td></tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="6" class="none">No sales in this period.</td></tr> <?php endif; ?>
  <?php if($sales->isNotEmpty()): ?>
    <tr class="tot"><td colspan="4">TOTAL</td><td class="r"><?php echo e($fmt($totalKg)); ?></td><td class="r">₱<?php echo e(number_format($total, 2)); ?></td></tr>
  <?php endif; ?>
</table>
</body></html>
<?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\exports\sales.blade.php ENDPATH**/ ?>