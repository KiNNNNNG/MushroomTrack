<?php $__env->startSection('content'); ?>
<div class="fbar">
  <span class="label">Monitoring Archive</span>
  <form method="GET" class="fsel"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'filter','s' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'filter','s' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
    <select name="batch" onchange="this.form.submit()">
      <option value="">Show All Batches</option>
      <?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($b->id); ?>" <?php if(request('batch') == $b->id): echo 'selected'; endif; ?>><?php echo e($b->code); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select></form>
</div>
<div class="card tbl plain">
  <table>
    <tr><th>Photo</th><th>Batch</th><th>Date &amp; Time</th><th>Result</th><th>Worker Notes</th><th class="r">Action</th></tr>
    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr>
        <td><div class="timg" style="<?php echo \Illuminate\Support\Arr::toCssStyles(["background-image: url('".$l->photo_url."')" => $l->photo_url]) ?>"></div></td>
        <td class="mono"><?php echo e($l->batch->code); ?></td>
        <td style="color:#475569"><?php echo e($l->created_at->format('Y-m-d')); ?></td>
        <td>
          <span class="hl <?php echo e($l->result === 'healthy' ? '' : ($l->result === 'contaminated' ? 'bad' : 'warn')); ?>"><?php echo e($l->result === 'attention' ? 'Attention' : $l->result_label); ?></span>
          <?php if($l->ai_line): ?><div class="ai">AI: <?php echo e($l->ai_line); ?></div><?php elseif($l->ai_error): ?><div class="ai off">AI review unavailable</div><?php endif; ?>
        </td>
        <td><div class="note">"<?php echo e($l->notes ?: 'No notes'); ?>"</div><?php if($l->loss): ?><span class="hl warn" style="margin-top:6px"><?php echo e($l->loss->quantity); ?> lost · <?php echo e($l->loss->reason_label); ?></span><?php endif; ?></td>
        <td class="r">
          <button class="eye" data-view data-photo="<?php echo e($l->photo_url); ?>" data-batch="<?php echo e($l->batch->code); ?>" data-date="<?php echo e($l->created_at->format('Y-m-d H:i')); ?>"
            data-notes="<?php echo e($l->notes); ?>" data-watered="<?php echo e($l->watered ? 'Yes' : 'No'); ?>" data-result="<?php echo e($l->result); ?>" data-lost="<?php echo e($l->loss ? $l->loss->quantity.' ('.$l->loss->reason_label.')' : 'None'); ?>" data-worker="<?php echo e($l->user->name); ?>"
            data-ai="<?php echo e(trim(($l->ai_line ?? '').($l->ai_summary ? ($l->ai_line ? ' — ' : '').$l->ai_summary : ''))); ?>" data-ai-conf="<?php echo e($l->ai_confidence); ?>">
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'eye','s' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'eye','s' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>VIEW</button>
        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="6" class="empty">No monitoring records yet.</td></tr> <?php endif; ?>
  </table>
</div>
<dialog id="viewDlg">
  <h3 id="vTitle"></h3>
  <img id="vImg" alt="" style="width:100%;border-radius:8px;margin-bottom:14px">
  <p id="vMeta" class="label" style="font-size:11px"></p>
  <p id="vNotes" class="note" style="white-space:normal;max-width:none;margin-top:12px"></p>
  <p id="vAi" class="aibox" hidden></p>
  <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CLOSE</button></div>
</dialog>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\worker\history.blade.php ENDPATH**/ ?>