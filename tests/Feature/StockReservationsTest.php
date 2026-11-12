<?php

use App\Models\CartItem;
use App\Models\Harvest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Validation\ValidationException;

it('reserves stock across customers and releases it after cancellation', function () {
    $product = Product::create(['name' => 'White Oyster', 'price' => 200, 'stock_kg' => 10]);
    $first = User::factory()->create(['role' => 'customer']);
    $second = User::factory()->create(['role' => 'customer']);
    $order = Order::place($first, $product, 7);
    expect($product->available_stock_kg)->toBe(3.0);
    expect(fn () => Order::place($second, $product, 4))->toThrow(ValidationException::class);
    $this->actingAs($first)->put(route('customer.orders.cancel', $order))->assertSessionHasNoErrors();
    expect($product->available_stock_kg)->toBe(10.0);
    Order::place($second, $product, 4);
    expect($product->available_stock_kg)->toBe(6.0);
});

it('keeps cart and payment unchanged when reserved stock runs out', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $product = Product::create(['name' => 'White Oyster', 'price' => 200, 'stock_kg' => 10]);
    Order::place(User::factory()->create(), $product, 7);
    $item = CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity_kg' => 6]);
    $this->actingAs($user)->post(route('customer.cart.place'), ['cart_ids' => [$item->id]])->assertSessionHasErrors();
    expect(Order::count())->toBe(1)->and(CartItem::count())->toBe(1);
    $this->assertDatabaseCount('payments', 0);
});

it('does not place the whole cart when nothing is selected', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $product = Product::create(['name' => 'White Oyster', 'price' => 200, 'stock_kg' => 10]);
    CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity_kg' => 2]);
    $this->actingAs($user)->post(route('customer.cart.place'), ['cart_ids' => []])->assertSessionHasErrors('cart_ids');
    expect(Order::count())->toBe(0)->and(CartItem::count())->toBe(1);
});

it('deducts physical stock on completion without keeping its reservation', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $admin = User::factory()->create(['role' => 'admin']);
    $product = Product::create(['name' => 'White Oyster', 'price' => 200, 'stock_kg' => 10]);
    $order = Order::place($customer, $product, 4);
    $order->update(['status' => 'confirmed']);
    $this->actingAs($admin)->put(route('admin.orders.update', $order), ['status' => 'completed'])->assertSessionHasNoErrors();
    expect((float) $product->fresh()->stock_kg)->toBe(6.0)->and($product->fresh()->available_stock_kg)->toBe(6.0);
    $this->put(route('admin.orders.update', $order), ['status' => 'completed'])->assertSessionHasErrors();
    expect(Sale::count())->toBe(1);
});

it('charts harvested kilograms rather than sale amounts', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $batch = App\Models\Batch::create(['code' => 'CHART', 'name' => 'White Oyster', 'bags' => 10, 'status' => 'fruiting']);
    Harvest::create(['batch_id' => $batch->id, 'user_id' => $admin->id, 'weight_kg' => 2.5, 'bags_cleared' => 0]);
    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
        ->assertViewHas('sales', fn ($points) => $points->last()['amount'] === 2.5)
        ->assertSee('SALES LESS EXPENSES');
});
