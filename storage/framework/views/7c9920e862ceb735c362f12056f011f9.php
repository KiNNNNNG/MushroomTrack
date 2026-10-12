<?php
$u = auth()->user();
$role = $u->role;
$navs = [
  'admin' => [
    ['admin.dashboard', 'dashboard', 'Dashboard', 'Dash'],
    ['admin.batches', 'batches', 'Cultivation & Batches', 'Batches'],
    ['admin.store', 'store', 'Inventory & Store', 'Store'],
    ['admin.reports', 'reports', 'Reports & Analytics', 'Reports'],
    ['admin.users', 'users', 'Personnel & Roles', 'Users'],
  ],
  'worker' => [
    ['worker.overview', 'dashboard', 'Operations Overview', 'Home'], ['worker.monitoring', 'clipboard', 'Fruiting Monitoring', 'Fruiting'],
    ['worker.history', 'history', 'Monitoring History', 'History'], ['worker.register', 'plus', 'Batch Registration', 'Register'], ['worker.harvest', 'scale', 'Harvest Entry', 'Harvest'],
  ],
  'customer' => [
    ['customer.shop', 'store', 'Browse Shop', 'Shop'], ['customer.orders', 'list', 'My Orders', 'Orders'], ['customer.history', 'history', 'Order History', 'History'],
  ],
];
$hub = ['admin' => 'Admin Hub', 'worker' => 'Worker Hub', 'customer' => 'Customer Hub'][$role] ?? 'Hub';
$hubIcon = $role === 'worker' ? 'batches' : 'user';
{
    $notes  = \App\Models\Notice::where('user_id', $u->id)->latest()->limit(8)->get();
    $unread = \App\Models\Notice::where('user_id', $u->id)->whereNull('read_at')->count();
}
if ($role === 'customer') {
    $cart = \App\Models\CartItem::with('product')->where('user_id', $u->id)->get();
}
$fmtk = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title>MushFungAI</title>
  <link rel="stylesheet" href="<?php echo e(asset('css/mushroomtrack.css')); ?>?v=<?php echo e(time()); ?>">
</head>
<body>
<div class="app">
  <aside>
    <div class="logo">
      <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'batches','s' => '26']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'batches','s' => '26']); ?>
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
      <span>MushFung<b>AI</b></span>
    </div>
    
    <div class="hub" style="margin-top:40px;"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => $hubIcon,'s' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hubIcon),'s' => '16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><?php echo e(strtoupper($hub)); ?></div>
    <nav>
      <?php $__currentLoopData = $navs[$role]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$route, $icon, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route($route)); ?>" class="<?php echo e(request()->routeIs($route) ? 'on' : ''); ?>"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => $icon,'s' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'s' => '20']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><?php echo e($label); ?></a>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>

    <div class="sidebar-bottom">
      <div class="sb-user">
        <div class="sb-av"><?php echo e(substr($u->name, 0, 1)); ?></div>
        <div class="sb-info">
          <b><?php echo e($u->name); ?></b>
          <span><?php echo e(ucfirst($role)); ?> Account</span>
        </div>
        <a href="<?php echo e(route('profile')); ?>" class="sb-prof" title="My Profile"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></a>
      </div>
      <form method="POST" action="<?php echo e(route('logout')); ?>" data-confirm="You will be signed out of MushFungAI." data-title="Log out?" data-yes="LOG OUT">
        <?php echo csrf_field(); ?>
        <button type="submit" class="sb-logout">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          LOG OUT
        </button>
      </form>
    </div>
  </aside>

  <main class="<?php echo e($role === 'admin' ? '' : 'wide'); ?>">
    <header class="top-bar">
      <div class="mobile-logo">
        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'batches','s' => '26']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'batches','s' => '26']); ?>
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
        <span>MushFung<b>AI</b></span>
      </div>
      <div class="hright">
        <div class="ddwrap">
            <div class="bell" data-dd>
              <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'bell','s' => '22']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'bell','s' => '22']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><?php if($unread): ?><i><?php echo e($unread > 9 ? '9+' : $unread); ?></i><?php endif; ?>
            </div>
            <div class="dd nt">
              <div class="nth"><b>NOTIFICATIONS</b>
                <?php if($unread): ?><form method="POST" action="<?php echo e(route('notifications.readall')); ?>"><?php echo csrf_field(); ?><button>MARK ALL READ</button></form><?php endif; ?>
              </div>
              <?php $__empty_1 = true; $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('notifications.open', $n)); ?>" class="ni <?php echo e($n->read_at ? '' : 'new'); ?>">
                  <b><?php echo e($n->title); ?></b><span><?php echo e($n->body); ?></span><small><?php echo e($n->created_at->diffForHumans()); ?></small>
                </a>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <span class="nn">No notifications</span>
              <?php endif; ?>
            </div>
        </div>
        
        <?php if($role === 'customer'): ?>
          <div class="bell" style="cursor:pointer" onclick="document.getElementById('cartDlg').showModal()">
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => 'cart','s' => '22']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => 'cart','s' => '22']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?><?php if($cart->count()): ?><i class="gold"><?php echo e($cart->count()); ?></i><?php endif; ?>
          </div>
        <?php endif; ?>

        <div class="ddwrap mobile-only">
          <button class="menu" id="menuBtn" data-dd aria-label="Menu"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
          <div class="dd" id="menuDd">
            <span><?php echo e($u->name); ?> · <?php echo e($role); ?></span>
            <a href="<?php echo e(route('profile')); ?>" class="ddl">MY PROFILE</a>
            <form method="POST" action="<?php echo e(route('logout')); ?>" data-confirm="You will be signed out of MushFungAI." data-title="Log out?" data-yes="LOG OUT"><?php echo csrf_field(); ?><button type="submit">LOG OUT</button></form>
          </div>
        </div>
      </div>
    </header>

    <div class="main-content">
      <?php if(session('ok')): ?><div class="flash ok"><?php echo e(session('ok')); ?></div><?php endif; ?>
      <?php if($errors->any()): ?><div class="flash err"><?php echo e($errors->first()); ?></div><?php endif; ?>
      <?php echo $__env->yieldContent('content'); ?>
    </div>
  </main>
  
  <!-- Mobile Bottom Navigation -->
  <nav class="bottom-nav">
    <?php $__currentLoopData = $navs[$role]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$route, $icon, $label, $mobileLabel]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <a href="<?php echo e(route($route)); ?>" class="<?php echo e(request()->routeIs($route) ? 'on' : ''); ?>">
        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['n' => $icon,'s' => '24']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['n' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'s' => '24']); ?>
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
        <span><?php echo e($mobileLabel); ?></span>
      </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </nav>
</div>

<?php if($role === 'customer'): ?>
<dialog id="cartDlg">
  <h3>Your Cart</h3>
  <?php $__empty_1 = true; $__currentLoopData = $cart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="ci" data-id="<?php echo e($c->id); ?>"
         data-remove-url="<?php echo e(route('customer.cart.remove', $c)); ?>"
         data-update-url="<?php echo e(route('customer.cart.update', $c)); ?>"
         data-stock="<?php echo e($c->product->stock_kg); ?>"
         data-price="<?php echo e($c->product->price); ?>">
      <div class="ci-info">
        <span class="ci-name"><?php echo e($c->product->name); ?></span>
        <b class="ci-price" style="color:var(--gold)">₱<?php echo e($fmtk($c->quantity_kg * $c->product->price)); ?></b>
      </div>
      <div class="ci-controls">
        <button type="button" class="qty-btn" data-dir="-1">−</button>
        <input type="number" class="qty-in" value="<?php echo e($c->quantity_kg); ?>" min="0.5" step="0.5" max="<?php echo e($c->product->stock_kg); ?>">
        <button type="button" class="qty-btn" data-dir="1">+</button>
        <button type="button" class="lk ci-rm">REMOVE</button>
      </div>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <p class="emptyi" style="padding:30px 0">Your cart is empty.</p>
  <?php endif; ?>
  <div class="fa">
    <button type="button" class="btn ghost" onclick="this.closest('dialog').close()">CLOSE</button>
    <?php if($cart->count()): ?>
      <a class="btn" id="cartCheckoutBtn" href="<?php echo e(route('customer.checkout')); ?>">CHECKOUT (₱<?php echo e($fmtk($cart->sum(fn ($c) => $c->quantity_kg * $c->product->price))); ?>)</a>
    <?php endif; ?>
  </div>
</dialog>
<?php endif; ?>

<script src="<?php echo e(asset('js/mushroomtrack.js')); ?>"></script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\MushroomTrack\resources\views/layouts/admin.blade.php ENDPATH**/ ?>