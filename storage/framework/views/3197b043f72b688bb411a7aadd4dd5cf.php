<?php $__env->startSection('content'); ?>
<form method="GET" action="<?php echo e(route('admin.reports.export')); ?>" class="toolbar" style="gap:10px;align-items:center">
  <select name="range" title="Period for losses and harvests">
    <?php $__currentLoopData = \App\Support\DateRange::OPTIONS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($k === 'all'): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
  <button class="btn ghost" name="format" value="xlsx">DOWNLOAD EXCEL</button>
  <button class="btn" name="format" value="pdf">DOWNLOAD PDF</button>
</form>
<div class="rhead">BATCH HEALTH &amp; SCHEDULE</div>
<div class="rlist">
  <?php $__empty_1 = true; $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php $pct = $b->health; ?>
    <div class="card rcard">
      <div class="rtop">
        <div>
          <h3><?php echo e($b->code); ?> — <?php echo e($b->name); ?></h3>
          <div class="rsub">
            <span>Status: <?php echo e(ucfirst($b->status)); ?></span>
            <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Est. Harvest: <?php echo e(optional($b->harvest_date)->format('Y-m-d') ?? '—'); ?></span>
          </div>
        </div>
        <div class="hi"><b><?php echo e($pct); ?>%</b><small>HEALTH INDEX</small></div>
      </div>
      <div class="bar"><span style="<?php echo \Illuminate\Support\Arr::toCssStyles(['flex: '.$pct]) ?>"></span><?php if($b->lost_bags): ?><i style="<?php echo \Illuminate\Support\Arr::toCssStyles(['flex: '.(100 - $pct)]) ?>"></i><?php endif; ?></div>
      <div class="leg">
        <span><em></em>Healthy: <?php echo e($b->bags); ?> Bags</span>
        <span><em></em>Lost: <?php echo e($b->lost_bags); ?> Bags</span>
      </div>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="empty">No batches yet.</div>
  <?php endif; ?>
</div>
<?php if($lossByReason->isNotEmpty()): ?>
  <div class="rhead" style="margin-top:28px">BAGS LOST BY REASON</div>
  <div class="card tbl plain" style="max-width:720px">
    <table>
      <tr><th>Reason</th><th class="r">Bags lost</th></tr>
      <?php $__currentLoopData = $lossByReason; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr><td><?php echo e(\App\Models\BagLoss::labelFor($row->reason)); ?></td><td class="r"><b><?php echo e($row->qty); ?></b></td></tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </table>
  </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\admin\reports.blade.php ENDPATH**/ ?>