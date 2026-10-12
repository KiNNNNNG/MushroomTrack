<?php $__env->startSection('content'); ?>
<?php
  $fmt = fn ($n) => rtrim(rtrim(number_format((float)$n, 2), '0'), '.');
  $now = now();
  $harvestDate = $batch->harvest_date;
  $estDispose  = $batch->estimated_dispose_date;
  $expectedYield = $batch->expected_next_yield_kg;
  $lastLog = $logs->first();
  $lastAi  = $lastAiLog;
?>


<div class="mon-hd" style="margin-bottom:18px">
  <div>
    <span class="mon-title">Bag #<?php echo e($bagNum); ?></span>
    <span class="mid" style="margin-left:10px"><?php echo e($batch->code); ?></span>
  </div>
  <a class="btn ghost" href="<?php echo e(route('worker.monitoring')); ?>">← MONITORING</a>
</div>


<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:24px">

  
  <div class="card" style="padding:20px">
    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:6px">Bag Identity</div>
    <div style="font:900 32px 'Courier New',monospace;color:var(--primary,#22c55e)">BAG #<?php echo e($bagNum); ?></div>
    <div style="font-weight:700;font-size:18px;margin:4px 0"><?php echo e($batch->name); ?></div>
    <div style="font-size:13px;color:var(--muted)">Batch: <span style="font-family:'Courier New',monospace;font-weight:700"><?php echo e($batch->code); ?></span></div>
    <div style="margin-top:10px">
      <span class="tag"><?php echo e(strtoupper($batch->status)); ?></span>
      <?php if($batch->disposed_at): ?> <span class="tag" style="background:#fee2e2;color:#b91c1c">DISPOSED</span> <?php endif; ?>
    </div>
    <div style="font-size:12px;color:var(--muted);margin-top:10px">
      Bag <?php echo e($bagNum); ?> of <?php echo e($totalBags); ?> total bags in this batch
    </div>
  </div>

  
  <div class="card" style="padding:20px">
    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:12px">🤖 AI Predictions</div>

    
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--line)">
      <span style="font-size:13px;color:var(--ink)">📅 Possible Harvest</span>
      <span style="font-weight:700;font-size:14px">
        <?php if($lastAi && $lastAi->ai_days_to_harvest !== null): ?>
          <?php $aiHarvest = $now->copy()->addDays($lastAi->ai_days_to_harvest); ?>
          <?php if($lastAi->ai_days_to_harvest === 0): ?>
            <span style="color:#16a34a">🌾 Ready Today!</span>
          <?php else: ?>
            <?php echo e($aiHarvest->format('M j, Y')); ?>

            <small style="color:var(--muted);font-weight:400">(~<?php echo e($lastAi->ai_days_to_harvest); ?>d)</small>
          <?php endif; ?>
        <?php elseif($harvestDate): ?>
          <?php echo e($harvestDate->format('M j, Y')); ?>

          <small style="color:var(--muted);font-weight:400">(planned)</small>
        <?php else: ?>
          <span style="color:var(--muted)">—</span>
        <?php endif; ?>
      </span>
    </div>

    
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--line)">
      <span style="font-size:13px;color:var(--ink)">⚖️ Expected Next Yield</span>
      <span style="font-weight:700;font-size:14px">
        ~<?php echo e($fmt($expectedYield)); ?> kg
        <small style="color:var(--muted);font-weight:400">(est.)</small>
      </span>
    </div>

    
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--line)">
      <span style="font-size:13px;color:var(--ink)">🔁 Harvest Flushes</span>
      <span style="font-weight:700;font-size:14px">
        <?php echo e($batch->flush_count); ?> / <?php echo e($batch->max_flushes ?? 5); ?>

        <?php if($batch->flushes_exhausted): ?>
          <span style="color:#dc2626"> ⚠ Exhausted</span>
        <?php else: ?>
          <small style="color:var(--muted);font-weight:400">(<?php echo e($batch->flushes_remaining); ?> left)</small>
        <?php endif; ?>
      </span>
    </div>

    
    <div style="display:flex;justify-content:space-between;align-items:center">
      <span style="font-size:13px;color:var(--ink)">🗑️ Dispose By</span>
      <span style="font-weight:700;font-size:14px">
        <?php if($batch->disposed_at): ?>
          <span style="color:#dc2626"><?php echo e($batch->disposed_at->format('M j, Y')); ?></span>
        <?php elseif($batch->flushes_exhausted): ?>
          <span style="color:#dc2626">Now (max flushes reached)</span>
        <?php elseif($lastAi && $lastAi->ai_stage === 'past_peak'): ?>
          <span style="color:#d97706">⚠ AI: Past peak — consider disposing</span>
        <?php elseif($estDispose): ?>
          <?php echo e($estDispose->format('M j, Y')); ?>

          <small style="color:var(--muted);font-weight:400">(est.)</small>
        <?php else: ?>
          <span style="color:var(--muted)">—</span>
        <?php endif; ?>
      </span>
    </div>
  </div>

  
  <?php if($lastAi && $lastAi->ai_summary): ?>
  <div class="card ai-card" style="padding:20px;border-left:4px solid
    <?php if($lastAi->ai_status === 'healthy'): ?> #16a34a
    <?php elseif($lastAi->ai_status === 'contaminated'): ?> #dc2626
    <?php elseif($lastAi->ai_status === 'attention'): ?> #d97706
    <?php else: ?> #64748b <?php endif; ?>">
    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:8px">Latest AI Review</div>
    <div class="ai-card-hd" style="margin-bottom:8px">
      <span class="ai-badge" style="background:
        <?php if($lastAi->ai_status === 'healthy'): ?> #dcfce7;color:#15803d
        <?php elseif($lastAi->ai_status === 'contaminated'): ?> #fee2e2;color:#b91c1c
        <?php elseif($lastAi->ai_status === 'attention'): ?> #fef9c3;color:#92400e
        <?php else: ?> #f1f5f9;color:#64748b <?php endif; ?>">
        <?php echo e(strtoupper($lastAi->ai_status ?? 'UNKNOWN')); ?>

      </span>
      <span style="font-size:11px;color:var(--muted);margin-left:8px"><?php echo e($lastAi->ai_confidence ?? ''); ?> confidence</span>
    </div>
    <?php if($lastAi->ai_line): ?>
      <div style="font-size:12px;color:#52617d;margin-bottom:8px"><?php echo e($lastAi->ai_line); ?></div>
    <?php endif; ?>
    <p style="font-size:13px;line-height:1.6;margin:0"><?php echo e($lastAi->ai_summary); ?></p>
    <div style="font-size:11px;color:var(--muted);margin-top:8px">
      Reviewed: <?php echo e($lastAi->ai_analyzed_at->format('M j, Y g:i a')); ?>

    </div>
  </div>
  <?php endif; ?>

  
  <div class="card" style="padding:20px">
    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:12px">Batch Stats</div>
    <div style="display:flex;justify-content:space-between;margin-bottom:8px">
      <span style="font-size:13px">Active Bags</span><span style="font-weight:700"><?php echo e($batch->bags); ?></span>
    </div>
    <div style="display:flex;justify-content:space-between;margin-bottom:8px">
      <span style="font-size:13px">Lost Bags</span><span style="font-weight:700;color:#dc2626"><?php echo e($batch->lost_bags); ?></span>
    </div>
    <div style="display:flex;justify-content:space-between;margin-bottom:8px">
      <span style="font-size:13px">Total Yield</span><span style="font-weight:700"><?php echo e($fmt($batch->yield_kg)); ?> kg</span>
    </div>
    <div style="display:flex;justify-content:space-between;margin-bottom:8px">
      <span style="font-size:13px">Health</span>
      <span style="font-weight:700;color:<?php echo e($batch->health >= 70 ? '#16a34a' : ($batch->health >= 40 ? '#d97706' : '#dc2626')); ?>">
        <?php echo e($batch->health); ?>%
      </span>
    </div>
    <?php if($batch->status === 'fruiting'): ?>
      <div style="margin-top:14px">
        <a class="btn" style="width:100%;justify-content:center;text-align:center"
           href="<?php echo e(route('worker.monitoring', ['open' => $batch->code])); ?>">
          📋 DO DAILY CHECK
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>


<div class="sec" style="margin-bottom:14px">Recent Batch Monitoring Logs</div>
<div class="card tbl">
  <table>
    <tr>
      <th>Date</th>
      <th>Worker</th>
      <th>Watered</th>
      <th>Result</th>
      <th>AI Review</th>
      <th>Lost Bags</th>
      <th>Photo</th>
    </tr>
    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr>
        <td class="date"><?php echo e($log->created_at->format('M j, Y g:i a')); ?></td>
        <td><?php echo e($log->user?->name ?? '—'); ?></td>
        <td style="text-align:center"><?php echo e($log->watered ? '✅' : '❌'); ?></td>
        <td><span class="tag" style="background:<?php echo e($log->result === 'healthy' ? '#dcfce7' : '#fef9c3'); ?>;color:<?php echo e($log->result === 'healthy' ? '#15803d' : '#92400e'); ?>"><?php echo e($log->result_label); ?></span></td>
        <td style="font-size:12px;color:var(--muted)">
          <?php if($log->ai_line): ?>
            <span style="color:<?php echo e($log->ai_status === 'healthy' ? '#16a34a' : ($log->ai_status === 'contaminated' ? '#dc2626' : '#d97706')); ?>"><?php echo e($log->ai_line); ?></span>
          <?php elseif($log->ai_error): ?>
            <span style="color:#dc2626">AI error</span>
          <?php else: ?>
            <span style="color:var(--muted)">—</span>
          <?php endif; ?>
        </td>
        <td style="text-align:center">
          <?php if($log->loss): ?>
            <span style="color:#dc2626;font-weight:700">-<?php echo e($log->loss->quantity); ?></span>
          <?php else: ?>
            —
          <?php endif; ?>
        </td>
        <td>
          <?php if($log->photo_url): ?>
            <a href="<?php echo e($log->photo_url); ?>" target="_blank">
              <img src="<?php echo e($log->photo_url); ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid var(--line)">
            </a>
          <?php else: ?>
            —
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="7" class="empty">No monitoring logs for this batch yet.</td></tr>
    <?php endif; ?>
  </table>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\worker\bag-monitoring.blade.php ENDPATH**/ ?>