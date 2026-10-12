<dialog id="<?php echo e($b ? 'b'.$b->id : 'b-new'); ?>">
  <form method="POST" action="<?php echo e($b ? route('admin.batches.update', $b) : route('admin.batches.store')); ?>">
    <?php echo csrf_field(); ?> <?php if($b): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
    <h3><?php echo e($b ? 'Edit '.$b->code : 'New Batch'); ?></h3>
    <div class="f2">
      <div class="f"><label>Batch ID</label><input name="code" value="<?php echo e($b->code ?? ''); ?>" placeholder="Auto"></div>
      <div class="f"><label>Status</label>
        <select name="status"><?php $__currentLoopData = ['incubating','fruiting','harvested']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s); ?>" <?php if(($b->status ?? 'incubating') === $s): echo 'selected'; endif; ?>><?php echo e(ucfirst($s)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
    </div>
    <div class="f"><label>Name</label><input name="name" required value="<?php echo e($b->name ?? ''); ?>"></div>
    <div class="f2">
      <div class="f"><label>Healthy bags</label><input type="number" min="0" name="bags" required value="<?php echo e($b->bags ?? 0); ?>"></div>
      <div class="f"><label>Lost bags <small style="font-weight:400">(workers report these in the daily check; raising it here is logged as an admin adjustment)</small></label><input type="number" min="0" name="lost_bags" required value="<?php echo e($b->lost_bags ?? 0); ?>"></div>
      <div class="f"><label>Yield (kg)</label><input type="number" step="0.01" min="0" name="yield_kg" value="<?php echo e($b->yield_kg ?? 0); ?>"></div>
      <div class="f"><label>Harvest date</label><input type="date" name="harvest_date" value="<?php echo e(optional($b->harvest_date ?? null)->format('Y-m-d')); ?>"></div>
    </div>
    <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CANCEL</button><button class="btn">SAVE</button></div>
  </form>
  <?php if($b): ?>
    <form class="del" method="POST" action="<?php echo e(route('admin.batches.destroy', $b)); ?>" data-confirm="Delete this batch? Its monitoring logs and harvest records will be deleted too." data-title="Delete batch" data-yes="DELETE" data-no="CANCEL" data-tone="danger"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button>DELETE BATCH</button></form>
  <?php endif; ?>
</dialog>
<?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views/admin/partials/batch-form.blade.php ENDPATH**/ ?>