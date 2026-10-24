<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkerController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// First thing visitors see: redirect to dashboard if logged in, otherwise show animated splash screen
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('home');
    }
    return view('loading');
})->name('splash');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');

    Route::get('/forgot', [PasswordResetController::class, 'showForgot'])->name('password.forgot');
    Route::post('/forgot/send', [PasswordResetController::class, 'sendOtp'])->name('password.send')->middleware('throttle:5,1');
    Route::post('/forgot/verify', [PasswordResetController::class, 'verifyOtp'])->name('password.verify')->middleware('throttle:10,1');
    Route::get('/reset', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset', [PasswordResetController::class, 'reset'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// PayMongo calls this by itself (no login, no CSRF token): the signature header is the proof. Needs a public URL (ngrok on localhost).
Route::post('/webhooks/paymongo', [PaymentController::class, 'webhook'])->name('webhooks.paymongo')->middleware('throttle:120,1');

// Send each role to its own hub
Route::get('/home', fn () => redirect()->route(match (auth()->user()->role) {
    'admin'  => 'admin.dashboard',
    'worker' => 'worker.overview',
    default  => 'customer.shop',
}))->middleware('auth')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'photo'])->name('profile.photo');
    Route::delete('/profile/photo', [ProfileController::class, 'removePhoto'])->name('profile.photo.remove');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::put('/profile/notifications', [ProfileController::class, 'notifications'])->name('profile.notifications');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications/{notice}/open', [NoticeController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NoticeController::class, 'readAll'])->name('notifications.readall');
    Route::delete('/notifications/selected', [NoticeController::class, 'destroySelected'])->name('notifications.destroySelected');
    Route::post('/notifications/selected/delete', [NoticeController::class, 'destroySelected'])->name('notifications.destroySelectedPost');
    Route::delete('/notifications/{notice}', [NoticeController::class, 'destroy'])->name('notifications.destroy');
});

/* ---------------- ADMIN ---------------- */
Route::prefix('admin')->name('admin.')->middleware(['auth', EnsureRole::class.':admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/batches', [AdminController::class, 'batches'])->name('batches');
    Route::get('/completed', [AdminController::class, 'completedBatches'])->name('completed');
    Route::get('/store', [AdminController::class, 'store'])->name('store');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/users', [AdminController::class, 'users'])->name('users');

    Route::get('/reports/export', [ExportController::class, 'reports'])->name('reports.export');
    Route::get('/store/export', [ExportController::class, 'sales'])->name('store.export');

    Route::post('/batches', [BatchController::class, 'store'])->name('batches.store');
    Route::put('/batches/{batch}', [BatchController::class, 'update'])->name('batches.update');
    Route::delete('/batches/{batch}', [BatchController::class, 'destroy'])->name('batches.destroy');

    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::put('/orders/{order}/fulfillment', [OrderController::class, 'fulfillment'])->name('orders.fulfillment');
    Route::put('/payments/{payment}/refunded', [PaymentController::class, 'refunded'])->name('payments.refunded');

    Route::post('/expenses', [AdminController::class, 'storeExpense'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [AdminController::class, 'destroyExpense'])->name('expenses.destroy');
    Route::delete('/history/bulk', [AdminController::class, 'deleteHistory'])->name('history.destroy');

    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

/* ---------------- WORKER ---------------- */
Route::prefix('worker')->name('worker.')->middleware(['auth', EnsureRole::class.':worker'])->group(function () {
    Route::get('/', [WorkerController::class, 'overview'])->name('overview');
    Route::get('/monitoring', [WorkerController::class, 'monitoring'])->name('monitoring');
    Route::get('/ai-detection', [WorkerController::class, 'aiDetection'])->name('ai-detection');
    Route::post('/monitoring/analyze-photo', [WorkerController::class, 'analyzePhoto'])->name('monitoring.analyze');
    Route::post('/monitoring/analyze-multiple', [WorkerController::class, 'analyzeMultiplePhotos'])->name('monitoring.analyzeMultiple');
    Route::post('/monitoring/{batch}', [WorkerController::class, 'storeCheck'])->name('monitoring.store');
    Route::get('/history', [WorkerController::class, 'history'])->name('history');
    Route::get('/register', [WorkerController::class, 'register'])->name('register');
    Route::post('/register', [WorkerController::class, 'storeBatch'])->name('register.store');
    Route::put('/register/{batch}/finalize', [WorkerController::class, 'finalizeFruiting'])->name('register.finalize');
    Route::get('/harvest', [WorkerController::class, 'harvest'])->name('harvest');
    Route::post('/harvest', [WorkerController::class, 'storeHarvest'])->name('harvest.store');
    Route::get('/completed', [WorkerController::class, 'completedBatches'])->name('completed');
});

/* ---------------- CUSTOMER ---------------- */
Route::prefix('shop')->name('customer.')->middleware(['auth', EnsureRole::class.':customer'])->group(function () {
    Route::get('/', [CustomerController::class, 'shop'])->name('shop');
    Route::get('/orders', [CustomerController::class, 'orders'])->name('orders');
    Route::get('/history', [CustomerController::class, 'history'])->name('history');
    Route::post('/cart', [CustomerController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/place', [CustomerController::class, 'placeCartOrders'])->name('cart.place');
    Route::post('/cart/delete-selected', [CustomerController::class, 'deleteSelectedCart'])->name('cart.deleteSelected');
    Route::put('/cart/{item}', [CustomerController::class, 'updateCartItem'])->name('cart.update');
    Route::delete('/cart/{item}', [CustomerController::class, 'removeFromCart'])->name('cart.remove');
    Route::get('/checkout', [CustomerController::class, 'checkoutPage'])->name('checkout');
    Route::post('/checkout', [CustomerController::class, 'checkout'])->name('checkout.store');
    Route::post('/pay/{payment}', [PaymentController::class, 'start'])->name('pay.start');
    Route::get('/pay/{payment}/return', [PaymentController::class, 'returned'])->name('pay.return');
    Route::get('/pay/{payment}/cancelled', [PaymentController::class, 'cancelled'])->name('pay.cancelled');
    Route::post('/order', [CustomerController::class, 'placeOrder'])->name('order.place');
    Route::put('/orders/{order}/cancel', [CustomerController::class, 'cancel'])->name('orders.cancel');
    Route::post('/history/clear', [CustomerController::class, 'clearHistory'])->name('history.clear');
});

