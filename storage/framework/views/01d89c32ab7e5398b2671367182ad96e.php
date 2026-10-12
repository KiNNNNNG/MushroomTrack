<?php $__env->startSection('content'); ?>

<?php if(session('new_batch_id')): ?>
<dialog id="newBatchQrDlg" class="cd" style="text-align:center; padding: 30px;">
  <button type="button" class="cdx" onclick="this.closest('dialog').close()" aria-label="Close">✕</button>
  <h3 style="margin-bottom: 20px;">Batch Registered Successfully!</h3>
  <p style="margin-bottom: 25px;">Please print the QR labels for this new batch.</p>
  <a href="<?php echo e(route('worker.batches.labels', ['batch' => session('new_batch_id')])); ?>" target="_blank" class="btn" style="width:100%; justify-content:center;">
    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'print','s' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'print','s' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> DOWNLOAD QR LABELS
  </a>
</dialog>
<script>
  window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('newBatchQrDlg').showModal();
  });
</script>
<?php endif; ?>

<div class="card wf">
  <h2>New Substrate Registry</h2>
  <form class="wb" method="POST" action="<?php echo e(route('worker.register.store')); ?>" data-confirm="Register a new batch to incubating?" data-title="Register batch" data-yes="REGISTER"><?php echo csrf_field(); ?>
    <label>Starting bags</label>
    <div style="display:flex;gap:10px;">
      <input type="number" name="bags" value="<?php echo e(old('bags', 100)); ?>" min="1" required style="max-width:150px">
      <button class="btn">REGISTER TO INCUBATING</button>
    </div>
  </form>
</div>

<div class="mon-hd" style="margin-top: 30px;">
  <span class="mon-title sec">Current Incubating List</span>
  <button type="button" class="btn" id="qrScanBtn">
    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'camera','s' => '17']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'camera','s' => '17']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> SCAN BAG QR
  </button>
</div>

<dialog id="qrDlg" class="qr-dlg">
  <button type="button" class="cdx" onclick="this.closest('dialog').close()" aria-label="Close">✕</button>
  <h3 style="margin-bottom:14px">Point at an incubating bag QR</h3>
  <div class="qr-viewport">
    <video id="qrVideo" autoplay playsinline muted></video>
    <div class="qr-frame"></div>
  </div>
  <p id="qrStatus" class="qr-status">Initialising camera…</p>
  <button type="button" class="btn ghost" style="width:100%;margin-top:18px" onclick="stopQr();this.closest('dialog').close()">CANCEL</button>
</dialog>


<div class="mgrid">
  <?php $__empty_1 = true; $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
      $scanned = $b->scanned_bags ?? [];
      $good = 0;
      $reject = 0;
      foreach ($scanned as $status) {
          if ($status === 'good') $good++;
          elseif ($status === 'reject') $reject++;
      }
      $total = $b->bags + $b->lost_bags;
      $unscanned = max(0, $total - count($scanned));
    ?>
    <div class="card mc" style="width:432px">
      <div class="mcb">
        <div class="mt1"><span class="mid">ID: <?php echo e($b->code); ?></span><span class="tag">INCUBATING</span></div>
        <h3 style="margin:14px 0 8px"><?php echo e($b->name); ?></h3>
        
        <div class="mrow"><span>Total Bags</span><span><?php echo e($total); ?> units</span></div>
        <div class="mrow"><span>Scanned Good</span><span style="color:var(--green); font-weight:bold;"><?php echo e($good); ?></span></div>
        <div class="mrow"><span>Scanned Reject</span><span style="color:var(--red); font-weight:bold;"><?php echo e($reject); ?></span></div>
        <div class="mrow" style="margin-bottom:18px"><span>Unscanned</span><span style="color:var(--gray)"><?php echo e($unscanned); ?></span></div>
        
        <form method="POST" action="<?php echo e(route('worker.register.finalize', $b)); ?>" data-confirm="Move this batch to fruiting? Unscanned bags will be considered lost." data-title="Move to fruiting" data-yes="MOVE">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <button class="btn gold sm2" style="width:100%; justify-content:center;">MOVE TO FRUITING</button>
        </form>
      </div>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="empty">No incubating batches.</div>
  <?php endif; ?>
</div>

<script>
/* ── QR scanner ─────────────────────────────────────────────────── */
let qrStream = null, qrAnim = null;

const qrScanBtn = document.getElementById('qrScanBtn');
if(qrScanBtn) {
    qrScanBtn.onclick = () => {
      document.getElementById('qrDlg').showModal();
      startQr();
    };
}

function startQr() {
  const video = document.getElementById('qrVideo');
  const status = document.getElementById('qrStatus');
  status.textContent = 'Initialising camera…';

  navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
    .then(stream => {
      qrStream = stream;
      video.srcObject = stream;
      status.textContent = 'Point at an incubating bag QR code…';
      scanLoop(video);
    })
    .catch(() => { status.textContent = 'Camera access denied. Allow camera and try again.'; });
}

function scanLoop(video) {
  if (typeof BarcodeDetector === 'undefined') {
    document.getElementById('qrStatus').textContent = 'QR scanning not supported in this browser. Use Chrome on Android.';
    return;
  }
  const detector = new BarcodeDetector({ formats: ['qr_code'] });
  const tick = () => {
    if (!qrStream) return;
    detector.detect(video).then(codes => {
      if (codes.length) {
        const raw = codes[0].rawValue;
        stopQr();
        document.getElementById('qrDlg').close();
        handleQrResult(raw);
        return;
      }
      qrAnim = requestAnimationFrame(tick);
    }).catch(() => { qrAnim = requestAnimationFrame(tick); });
  };
  video.addEventListener('loadedmetadata', () => { qrAnim = requestAnimationFrame(tick); }, { once: true });
}

function stopQr() {
  if (qrAnim) cancelAnimationFrame(qrAnim);
  if (qrStream) qrStream.getTracks().forEach(t => t.stop());
  qrStream = null;
}

function handleQrResult(raw) {
  // If it's a URL, redirect to it. The URL should be something like /worker/bag/BATCH-001/1
  if (raw.includes('/worker/bag/')) {
    window.location.href = raw;
  } else {
    // Attempt fallback
    alert("Scanned QR code doesn't look like a valid bag URL: " + raw);
  }
}

document.getElementById('qrDlg').addEventListener('close', stopQr);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views/worker/register.blade.php ENDPATH**/ ?>