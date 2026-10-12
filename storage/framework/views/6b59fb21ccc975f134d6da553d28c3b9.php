<?php $__env->startSection('content'); ?>
<div class="card wf" style="max-width:500px; margin: 40px auto; text-align:center;">
    <h1 style="margin-bottom:5px;"><?php echo e($batch->code); ?></h1>
    <p style="color:var(--gray); margin-bottom: 20px;">Bag #<?php echo e($bagNum); ?> of <?php echo e($totalBags); ?></p>
    
    <div style="margin-bottom: 25px;">
        <?php if($status === 'good'): ?>
            <span class="tag" style="background:var(--lg); color:var(--green); font-size:16px; padding:6px 12px;">Marked Good</span>
        <?php elseif($status === 'reject'): ?>
            <span class="tag" style="background:var(--lred); color:var(--red); font-size:16px; padding:6px 12px;">Rejected</span>
        <?php else: ?>
            <span class="tag" style="background:var(--bg); color:var(--ink); font-size:16px; padding:6px 12px;">Not Scanned Yet</span>
        <?php endif; ?>
    </div>

    <p style="color:var(--gray); margin-bottom: 30px;">Inspect this bag and determine if it is healthy enough to be moved to the fruiting stage.</p>

    <!-- Form / Buttons -->
    <form action="<?php echo e(route('worker.bag.scan', ['batch' => $batch->code, 'bag' => $bagNum])); ?>" method="POST" style="display:flex; flex-direction:column; gap:15px;">
        <?php echo csrf_field(); ?>
        <button type="submit" name="status" value="good" class="btn" style="background:var(--green); font-size:24px; padding:20px; border-radius:12px;">
            GOOD
        </button>

        <button type="submit" name="status" value="reject" class="btn" style="background:var(--red); font-size:24px; padding:20px; border-radius:12px;">
            REJECT
        </button>
    </form>

    <div style="margin-top: 30px;">
        <a href="<?php echo e(route('worker.register')); ?>" class="btn ghost">Back to Incubating List</a>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\worker\bag-incubating.blade.php ENDPATH**/ ?>