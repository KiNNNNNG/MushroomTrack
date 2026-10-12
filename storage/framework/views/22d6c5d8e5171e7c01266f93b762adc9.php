<dialog id="<?php echo e($p ? 'p'.$p->id : 'p-new'); ?>">
  <form method="POST" action="<?php echo e($p ? route('admin.products.update', $p) : route('admin.products.store')); ?>">
    <?php echo csrf_field(); ?> <?php if($p): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
    <h3><?php echo e($p ? 'Edit Product' : 'New Product'); ?></h3>
    <div class="f"><label>Name</label><input name="name" required value="<?php echo e($p->name ?? ''); ?>"></div>
    <div class="f"><label>Description</label><input name="description" value="<?php echo e($p->description ?? ''); ?>"></div>
    <div class="f2">
      <div class="f"><label>Price (₱/kg)</label><input type="number" step="0.01" min="0" name="price" required value="<?php echo e($p->price ?? ''); ?>"></div>
      <div class="f"><label>Stock (kg)</label><input type="number" step="0.01" min="0" name="stock_kg" required value="<?php echo e($p->stock_kg ?? ''); ?>"></div>
    </div>
    <div class="f"><label>Image URL</label><input type="url" name="image_url" value="<?php echo e($p->image_url ?? ''); ?>"></div>
    <div class="fa"><button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CANCEL</button><button class="btn">SAVE</button></div>
  </form>
</dialog>
<?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views/admin/partials/product-form.blade.php ENDPATH**/ ?>