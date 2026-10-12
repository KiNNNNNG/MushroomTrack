<?php $__env->startSection('content'); ?>
<?php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); ?>
<div class="tabs">
  <?php $__currentLoopData = ['products','orders','sales','history']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="<?php echo e(route('admin.store', ['tab' => $t])); ?>" class="<?php echo e($tab === $t ? 'on' : ''); ?>"><?php echo e(strtoupper($t)); ?></a>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php if($tab === 'products'): ?>
  <div class="shead">
    <span class="label">Store Catalog</span>
    <button class="btn" onclick="document.getElementById('p-new').showModal()"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'plus','s' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'plus','s' => '20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>NEW PRODUCT</button>
  </div>
  <div class="prods">
    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="card prod">
        <div class="img" style="<?php echo \Illuminate\Support\Arr::toCssStyles(["background-image: url('".$p->image_url."')" => $p->image_url]) ?>"></div>
        <h3><?php echo e($p->name); ?></h3>
        <div class="meta"><span>₱<?php echo e($fmt($p->price)); ?></span><span><?php echo e($fmt($p->stock_kg)); ?>kg</span></div>
        <div class="acts">
          <button type="button" onclick="document.getElementById('p<?php echo e($p->id); ?>').showModal()">EDIT</button>
          <form method="POST" action="<?php echo e(route('admin.products.destroy', $p)); ?>" data-confirm="Delete this product?" data-title="Delete product" data-yes="DELETE" data-no="CANCEL" data-tone="danger"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button type="submit" style="color:#b91c1c;padding-left:60px;text-align:left">DELETE</button></form>
        </div>
      </div>
      <?php echo $__env->make('admin.partials.product-form', ['p' => $p], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
  <?php echo $__env->make('admin.partials.product-form', ['p' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php elseif($tab === 'orders'): ?>
  <div class="card tbl plain big50">
    <table>
      <tr><th>Customer</th><th>Product</th><th>Total</th><th>Payment</th><th>Receive</th><th>Status</th><th class="r">Actions</th></tr>
      <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php $p = $o->payment; $waiting = $o->isAwaitingPayment(); ?>
        <tr>
          <td class="b"><?php echo e($o->user->name); ?></td>
          <td><?php echo e($o->product->name); ?> (<?php echo e($fmt($o->quantity_kg)); ?>kg)</td>
          <td class="g">₱<?php echo e($fmt($o->total)); ?></td>
          <td><?php if($p): ?><div><?php echo e($p->method_label); ?></div><span class="tag pay <?php echo e($p->status); ?>"><?php echo e(strtoupper($p->status_label)); ?></span><?php else: ?><span class="tag">—</span><?php endif; ?></td>
          <td>
            <div><?php echo e($o->fulfillment === 'delivery' ? 'Delivery' : 'Pickup'); ?></div>
            <?php if($o->fulfillment === 'delivery'): ?><small class="oaddr" style="display:block;max-width:220px;white-space:normal"><?php echo e($o->delivery_address); ?><br><?php echo e($o->contact_phone); ?></small><?php endif; ?>
            <?php if($o->status === 'confirmed'): ?><span class="tag fl" style="margin-top:6px"><?php echo e(strtoupper($o->fulfillment_status_label)); ?></span><?php endif; ?>
          </td>
          <td><span class="tag"><?php echo e(strtoupper($o->status)); ?></span></td>
          <td class="r">
            <div class="acts2">
              <?php if($waiting): ?>
                <span class="tag" title="The customer has not paid through PayMongo yet">WAITING FOR PAYMENT</span>
              <?php else: ?>
                <form method="POST" action="<?php echo e(route('admin.orders.update', $o)); ?>"
                  data-confirm="<?php echo e($o->status === 'pending' ? 'Confirm this order? The customer will be notified.' : 'Mark this order as done? Stock will be reduced and a sale recorded.'); ?>"
                  data-title="<?php echo e($o->status === 'pending' ? 'Confirm order' : 'Complete order'); ?>" data-yes="<?php echo e($o->status === 'pending' ? 'CONFIRM' : 'DONE'); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                  <input type="hidden" name="status" value="<?php echo e($o->status === 'pending' ? 'confirmed' : 'completed'); ?>">
                  <button class="btn sm"><?php echo e($o->status === 'pending' ? 'CONFIRM' : 'DONE'); ?></button></form>
              <?php endif; ?>
              <?php if($o->status === 'confirmed' && $o->fulfillment_status === 'pending'): ?>
                <form method="POST" action="<?php echo e(route('admin.orders.fulfillment', $o)); ?>" data-confirm="Tell the customer their order is <?php echo e($o->fulfillment === 'delivery' ? 'out for delivery' : 'ready for pickup'); ?>?" data-title="<?php echo e($o->fulfillment === 'delivery' ? 'Out for delivery' : 'Ready for pickup'); ?>" data-yes="SEND UPDATE"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                  <input type="hidden" name="fulfillment_status" value="<?php echo e($o->fulfillment === 'delivery' ? 'out_for_delivery' : 'ready_for_pickup'); ?>">
                  <button class="btn sm gold"><?php echo e($o->fulfillment === 'delivery' ? 'OUT FOR DELIVERY' : 'READY FOR PICKUP'); ?></button></form>
              <?php endif; ?>
              <form method="POST" action="<?php echo e(route('admin.orders.update', $o)); ?>" data-confirm="Cancel this order?<?php echo e($p && $p->status === 'paid' ? ' The customer already paid, so a refund will be needed.' : ''); ?>" data-title="Cancel order" data-yes="CANCEL ORDER" data-no="KEEP ORDER" data-tone="danger"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <input type="hidden" name="status" value="cancelled"><button class="lk">CANCEL</button></form>
            </div>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="7" class="empty">No active orders.</td></tr> <?php endif; ?>
    </table>
  </div>

<?php elseif($tab === 'sales'): ?>
  <div class="shead">
    <span class="label">Revenue Ledger</span>
    <form method="GET"><input type="hidden" name="tab" value="sales">
      <select name="range" onchange="this.form.submit()">
        <?php $__currentLoopData = ['week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all' => 'All Time']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($k); ?>" <?php if($range === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select></form>
  </div>
  <form method="GET" action="<?php echo e(route('admin.store.export')); ?>" class="toolbar" style="gap:10px"><input type="hidden" name="range" value="<?php echo e($range); ?>">
    <button class="btn ghost" name="format" value="xlsx">DOWNLOAD EXCEL</button>
    <button class="btn" name="format" value="pdf">DOWNLOAD PDF</button>
  </form>
  <div class="bigcard"><small>TOTAL SALES</small><strong>₱<?php echo e(number_format($totalSales)); ?></strong></div>

<?php else: ?>
  <div class="card tbl plain big50">
    <table>
      <tr><th>Date</th><th>Customer</th><th>Total</th><th>Payment</th><th class="r">Status</th></tr>
      <?php $__empty_1 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php $p = $o->payment; ?>
        <tr>
          <td style="color:#64748b"><?php echo e($o->created_at->format('Y-m-d')); ?></td>
          <td class="b"><?php echo e($o->user->name); ?></td>
          <td class="b">₱<?php echo e($fmt($o->total)); ?></td>
          <td>
            <?php if($p): ?>
              <span class="tag pay <?php echo e($p->status); ?>"><?php echo e(strtoupper($p->method_label)); ?> · <?php echo e(strtoupper($p->status_label)); ?></span>
              <?php if($p->status === 'refund_due'): ?>
                <form method="POST" action="<?php echo e(route('admin.payments.refunded', $p)); ?>" style="display:inline" data-confirm="Only confirm after you sent the refund in PayMongo." data-title="Refund sent?" data-yes="YES, REFUNDED"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                  <button class="lk">MARK REFUNDED</button></form>
              <?php endif; ?>
            <?php else: ?> — <?php endif; ?>
          </td>
          <td class="r"><span class="tag <?php echo e($o->status); ?>"><?php echo e(strtoupper($o->status)); ?></span></td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="5" class="empty">No order history yet.</td></tr> <?php endif; ?>
    </table>
  </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\admin\store.blade.php ENDPATH**/ ?>