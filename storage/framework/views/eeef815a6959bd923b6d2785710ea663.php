<?php $__env->startSection('content'); ?>
<style>
  .lbl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
  .lbl{background:#fff;border:1px dashed #94a3b8;border-radius:12px;padding:14px;text-align:center;break-inside:avoid;page-break-inside:avoid}
  .lbl svg{width:160px;height:160px;display:block;margin:0 auto 8px}
  .lbl b{display:block;font:700 15px 'Courier New',monospace;letter-spacing:1px}
  .lbl .bag-num{display:block;font:900 22px 'Courier New',monospace;color:var(--primary,#22c55e);margin:2px 0 4px}
  .lbl span{display:block;font-size:12px;color:#475569;margin-top:2px}
  .lbl small{display:block;font-size:10px;color:#94a3b8;margin-top:6px;letter-spacing:.5px}
  @media print{
    header, aside, .flash, .no-print{display:none !important}
    .app{display:block !important}
    main{padding:0 !important;margin:0 !important;max-width:none !important}
    .lbl-grid{grid-template-columns:repeat(4,1fr);gap:8px}
    .lbl{border:1px dashed #000}
  }
</style>
<div class="toolbar no-print" style="gap:10px;align-items:center">
  <?php if(request('batch')): ?><a class="btn ghost" href="<?php echo e(route('worker.batches.labels')); ?>">SHOW ALL ACTIVE BATCHES</a><?php endif; ?>
  <a class="btn ghost" href="javascript:history.back()">BACK</a>
  <button class="btn" onclick="window.print()">PRINT LABELS</button>
</div>
<p class="no-print" style="color:#64748b;font-size:13px;margin-bottom:18px">
  Each QR code is unique to an individual bag. When a worker scans it, the monitoring tab opens for that bag. Labels use <b><?php echo e(config('app.url')); ?></b> — make sure <code>APP_URL</code> is your real public address before printing.
</p>
<div class="lbl-grid">
  <?php $__empty_1 = true; $__currentLoopData = $labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="lbl">
      <?php echo $l['svg']; ?>

      <b><?php echo e($l['batch']->code); ?></b>
      <span class="bag-num">BAG #<?php echo e($l['bagNum']); ?></span>
      <span><?php echo e($l['batch']->name); ?></span>
      <small>SCAN TO MONITOR · <?php echo e($l['bagNum']); ?> of <?php echo e($l['total']); ?></small>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="empty">No batches to label.</div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\worker\labels.blade.php ENDPATH**/ ?>