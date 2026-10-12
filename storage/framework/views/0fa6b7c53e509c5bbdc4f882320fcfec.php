<?php $__env->startSection('content'); ?>
<?php
  $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');
  $sub  = (float) $subtotal;
  $mode = old('fulfillment', 'pickup');
?>
<form method="POST" action="<?php echo e(route('customer.checkout.store')); ?>" class="co" id="coForm"><?php echo csrf_field(); ?>

  <div class="card cob">
    <div class="pc-h">1. Your items</div>
    <div class="cob-b">
      <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="cor co-item"
             data-update-url="<?php echo e(route('customer.cart.update', $i)); ?>"
             data-price="<?php echo e($i->product->price); ?>"
             data-stock="<?php echo e($i->product->stock_kg); ?>">
          <span class="co-name"><?php echo e($i->product->name); ?></span>
          <div class="co-qty-wrap">
            <button type="button" class="qty-btn co-minus">−</button>
            <input type="number" class="qty-in co-qty"
                   value="<?php echo e($i->quantity_kg); ?>"
                   min="0.5" step="0.5" max="<?php echo e($i->product->stock_kg); ?>">
            <span class="co-unit">kg</span>
            <button type="button" class="qty-btn co-plus">+</button>
          </div>
          <b class="co-item-price">₱<?php echo e($fmt($i->quantity_kg * $i->product->price)); ?></b>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      <a href="<?php echo e(route('customer.shop')); ?>" class="cokl">+ ADD MORE PRODUCTS</a>
    </div>
  </div>

  <div class="card cob">
    <div class="pc-h">2. Pickup or delivery</div>
    <div class="cob-b">
      <label class="copt"><input type="radio" name="fulfillment" value="pickup" <?php if($mode === 'pickup'): echo 'checked'; endif; ?>>
        <span><b>Pick up at the farm</b><small>Free. We will tell you when it is ready.</small></span></label>
      <label class="copt"><input type="radio" name="fulfillment" value="delivery" <?php if($mode === 'delivery'): echo 'checked'; endif; ?>>
        <span><b>Deliver to my address</b><small><?php echo e('₱' . $fmt($fee)); ?> delivery fee<?php echo e($freeOver > 0 ? ' (free for orders of ₱' . $fmt($freeOver) . ' or more)' : ''); ?>.</small></span></label>

      <div id="coDelivery" class="cod" <?php if($mode !== 'delivery'): ?> hidden <?php endif; ?>>
        <div class="f"><label>Delivery address</label><input name="delivery_address" value="<?php echo e(old('delivery_address', $u->address)); ?>" placeholder="House no., street, barangay, city"></div>
        <div class="f"><label>Contact number</label><input name="contact_phone" value="<?php echo e(old('contact_phone', $u->phone)); ?>" placeholder="0917 123 4567"></div>
        <small class="cohint">Taken from your profile. You can change it for this order only.</small>
      </div>
    </div>
  </div>

  
  <input type="hidden" name="payment_method" value="cash">
  <div class="card cob">
    <div class="pc-h">3. Payment</div>
    <div class="cob-b">
      <div class="copt" style="cursor:default">
        <span>
          <b>💵 Cash on Pickup / Delivery</b>
          <small>Pay in cash when you pick up your order at the farm or when it is delivered to you. No online payment needed.</small>
        </span>
      </div>
    </div>
  </div>

  <div class="card cob cot">
    <div class="cor"><span>Subtotal</span><b id="coSubtotal">₱<?php echo e($fmt($subtotal)); ?></b></div>
    <div class="cor"><span>Delivery fee</span><b id="coFee">₱0</b></div>
    <div class="cor tot"><span>Total</span><b id="coTotal">₱<?php echo e($fmt($subtotal)); ?></b></div>
    <div class="fa"><a class="btn ghost" href="<?php echo e(route('customer.shop')); ?>">BACK TO SHOP</a><button class="btn" id="coGo">PLACE ORDER</button></div>
  </div>

</form>

<script>
(() => {
  let subtotal = <?php echo json_encode($sub, 15, 512) ?>;
  const fee = <?php echo json_encode($fee, 15, 512) ?>, freeOver = <?php echo json_encode($freeOver, 15, 512) ?>;
  const form = document.getElementById('coForm');
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
  const peso = n => '₱' + Number(n).toLocaleString('en-PH', { maximumFractionDigits: 2 });

  const sync = () => {
    const delivery = form.fulfillment.value === 'delivery';
    document.getElementById('coDelivery').hidden = !delivery;
    const charge = delivery && !(freeOver > 0 && subtotal >= freeOver) ? fee : 0;
    document.getElementById('coFee').textContent = charge ? peso(charge) : (delivery ? 'FREE' : '₱0');
    document.getElementById('coTotal').textContent = peso(subtotal + charge);
    document.getElementById('coSubtotal').textContent = peso(subtotal);
  };

  // Quantity controls on checkout items
  document.querySelectorAll('.co-item').forEach(row => {
    const input = row.querySelector('.co-qty');
    const priceEl = row.querySelector('.co-item-price');
    const price = parseFloat(row.dataset.price);

    const updateRow = () => {
      const qty = parseFloat(input.value) || 0;
      const rowTotal = qty * price;
      if (priceEl) priceEl.textContent = peso(rowTotal);
      // Recalculate total subtotal from all rows
      subtotal = 0;
      document.querySelectorAll('.co-item').forEach(r => {
        const q = parseFloat(r.querySelector('.co-qty')?.value || 0);
        const p = parseFloat(r.dataset.price || 0);
        subtotal += q * p;
      });
      subtotal = Math.round(subtotal * 100) / 100;
      sync();
    };

    const saveQty = (qty) => {
      const body = new URLSearchParams({ _method: 'PUT', _token: csrf(), quantity_kg: qty });
      fetch(row.dataset.updateUrl, { method: 'POST', body });
    };

    row.querySelector('.co-minus').onclick = () => {
      const step = parseFloat(input.step) || 0.5;
      const min = parseFloat(input.min) || 0.5;
      input.value = Math.max(min, Math.round((parseFloat(input.value) - step) * 100) / 100);
      updateRow();
      clearTimeout(row._t);
      row._t = setTimeout(() => saveQty(input.value), 600);
    };

    row.querySelector('.co-plus').onclick = () => {
      const step = parseFloat(input.step) || 0.5;
      const max = parseFloat(input.max) || 9999;
      input.value = Math.min(max, Math.round((parseFloat(input.value) + step) * 100) / 100);
      updateRow();
      clearTimeout(row._t);
      row._t = setTimeout(() => saveQty(input.value), 600);
    };

    input.addEventListener('change', () => {
      updateRow();
      clearTimeout(row._t);
      row._t = setTimeout(() => saveQty(input.value), 600);
    });
  });

  form.addEventListener('change', sync);
  sync();
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views\customer\checkout.blade.php ENDPATH**/ ?>