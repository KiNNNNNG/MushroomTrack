<?php

use App\Models\CartItem;
use App\Models\Notice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function pdUser(string $role = 'customer', array $extra = []): User
{
    return User::factory()->create(['role' => $role, 'status' => 'active'] + $extra);
}

function pdProduct(float $price = 200, float $stock = 10): Product
{
    return Product::create(['name' => 'White Oyster', 'price' => $price, 'stock_kg' => $stock]);
}

function pdCart(User $u, Product $p, float $kg = 2): void
{
    CartItem::create(['user_id' => $u->id, 'product_id' => $p->id, 'quantity_kg' => $kg]);
}

/** A saved payment with one order, the way checkout leaves it. */
function pdOrder(User $u, Product $p, string $method = 'cash', string $fulfillment = 'pickup', array $payment = []): Order
{
    $pay = Payment::create($payment + [
        'reference' => Payment::newReference(), 'user_id' => $u->id, 'method' => $method,
        'delivery_fee' => 0, 'amount' => 2 * $p->price, 'status' => 'unpaid',
    ]);

    return Order::place($u, $p, 2, [
        'payment_id' => $pay->id, 'fulfillment' => $fulfillment, 'fulfillment_status' => 'pending',
        'delivery_address' => $fulfillment === 'delivery' ? 'Brgy. Uno, San Pablo' : null,
    ]);
}

beforeEach(function () {
    config([
        'mushroomtrack.delivery.fee' => 50, 'mushroomtrack.delivery.free_over' => 0,
        'mushroomtrack.paymongo.secret_key' => null, 'mushroomtrack.paymongo.webhook_secret' => 'whsk_test',
    ]);
});

/* ---------------- Checkout ---------------- */

it('checks out a cash pickup order with no delivery fee', function () {
    $u = pdUser(); $p = pdProduct(); pdCart($u, $p, 2);

    $this->actingAs($u)->post(route('customer.checkout.store'), ['fulfillment' => 'pickup', 'payment_method' => 'cash'])
        ->assertRedirect(route('customer.orders'));

    $pay = Payment::first();
    expect((float) $pay->amount)->toBe(400.0)->and((float) $pay->delivery_fee)->toBe(0.0)
        ->and($pay->status)->toBe('unpaid')->and($pay->method)->toBe('cash');

    $o = Order::first();
    expect($o->payment_id)->toBe($pay->id)->and($o->fulfillment)->toBe('pickup')->and($o->status)->toBe('pending');
    expect(CartItem::count())->toBe(0);
});

it('needs an address and a phone for delivery, then adds the delivery fee and remembers them on the profile', function () {
    $u = pdUser(extra: ['address' => null, 'phone' => null]); $p = pdProduct(); pdCart($u, $p, 2);

    $this->actingAs($u)->post(route('customer.checkout.store'), ['fulfillment' => 'delivery', 'payment_method' => 'cash'])
        ->assertSessionHasErrors(['delivery_address', 'contact_phone']);
    expect(Order::count())->toBe(0)->and(CartItem::count())->toBe(1);

    $this->actingAs($u)->post(route('customer.checkout.store'), [
        'fulfillment' => 'delivery', 'payment_method' => 'cash', 'delivery_address' => 'Brgy. Uno, San Pablo', 'contact_phone' => '0917 123 4567',
    ])->assertRedirect(route('customer.orders'));

    expect((float) Payment::first()->amount)->toBe(450.0)->and((float) Payment::first()->delivery_fee)->toBe(50.0)
        ->and(Order::first()->delivery_address)->toBe('Brgy. Uno, San Pablo')
        ->and($u->fresh()->address)->toBe('Brgy. Uno, San Pablo')->and($u->fresh()->phone)->toBe('0917 123 4567');
});

it('waives the delivery fee above the free-delivery amount', function () {
    config(['mushroomtrack.delivery.free_over' => 400]);
    $u = pdUser(); $p = pdProduct(); pdCart($u, $p, 2);

    $this->actingAs($u)->post(route('customer.checkout.store'), [
        'fulfillment' => 'delivery', 'payment_method' => 'cash', 'delivery_address' => 'Somewhere', 'contact_phone' => '09171234567',
    ]);

    expect((float) Payment::first()->delivery_fee)->toBe(0.0)->and((float) Payment::first()->amount)->toBe(400.0);
});

it('refuses GCash and Maya while PayMongo is not set up', function () {
    $u = pdUser(); $p = pdProduct(); pdCart($u, $p);

    $this->actingAs($u)->post(route('customer.checkout.store'), ['fulfillment' => 'pickup', 'payment_method' => 'gcash'])
        ->assertSessionHasErrors();

    expect(Order::count())->toBe(0)->and(Payment::count())->toBe(0)->and(CartItem::count())->toBe(1);
});

it('sends a GCash customer to the PayMongo page with the right amounts', function () {
    config(['mushroomtrack.paymongo.secret_key' => 'sk_test_abc']);
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'cs_123', 'attributes' => ['checkout_url' => 'https://checkout.paymongo.com/cs_123']]], 200)]);
    $u = pdUser(); $p = pdProduct(); pdCart($u, $p, 2);

    $this->actingAs($u)->post(route('customer.checkout.store'), [
        'fulfillment' => 'delivery', 'payment_method' => 'gcash', 'delivery_address' => 'Brgy. Uno', 'contact_phone' => '09171234567',
    ])->assertRedirect('https://checkout.paymongo.com/cs_123');

    expect(Payment::first()->paymongo_session_id)->toBe('cs_123')->and(Order::first()->status)->toBe('pending');

    Http::assertSent(function ($request) {
        $a = $request['data']['attributes'];
        $amounts = array_column($a['line_items'], 'amount');

        return $a['payment_method_types'] === ['gcash'] && $amounts === [40000, 5000] && str_contains($a['reference_number'], 'PAY-');
    });
});

it('keeps the order and lets the customer retry when PayMongo is down', function () {
    config(['mushroomtrack.paymongo.secret_key' => 'sk_test_abc']);
    Http::fake(['api.paymongo.com/*' => Http::response(['errors' => [['detail' => 'boom']]], 500)]);
    $u = pdUser(); $p = pdProduct(); pdCart($u, $p);

    $this->actingAs($u)->post(route('customer.checkout.store'), ['fulfillment' => 'pickup', 'payment_method' => 'maya'])
        ->assertRedirect(route('customer.orders'))->assertSessionHasErrors();

    expect(Order::count())->toBe(1)->and(Payment::first()->status)->toBe('unpaid');
});

/* ---------------- Coming back from PayMongo / webhook ---------------- */

it('marks the payment paid and confirms the order when the customer returns from PayMongo', function () {
    config(['mushroomtrack.paymongo.secret_key' => 'sk_test_abc']);
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'cs_1', 'attributes' => ['payments' => [['id' => 'pay_9', 'attributes' => ['status' => 'paid']]]]]], 200)]);
    $u = pdUser(); $p = pdProduct();
    $o = pdOrder($u, $p, 'gcash', payment: ['paymongo_session_id' => 'cs_1']);

    $this->actingAs($u)->get(route('customer.pay.return', $o->payment))->assertRedirect(route('customer.orders'));

    expect($o->payment->fresh()->status)->toBe('paid')->and($o->payment->fresh()->paymongo_payment_id)->toBe('pay_9')
        ->and($o->fresh()->status)->toBe('confirmed');

    // coming back a second time must not notify twice
    $before = Notice::where('user_id', $u->id)->count();
    $this->actingAs($u)->get(route('customer.pay.return', $o->payment));
    expect(Notice::where('user_id', $u->id)->count())->toBe($before);
});

it('leaves the order pending when PayMongo says it is not paid yet', function () {
    config(['mushroomtrack.paymongo.secret_key' => 'sk_test_abc']);
    Http::fake(['api.paymongo.com/*' => Http::response(['data' => ['id' => 'cs_1', 'attributes' => ['payments' => []]]], 200)]);
    $u = pdUser(); $o = pdOrder($u, pdProduct(), 'gcash', payment: ['paymongo_session_id' => 'cs_1']);

    $this->actingAs($u)->get(route('customer.pay.return', $o->payment))->assertRedirect(route('customer.orders'));

    expect($o->payment->fresh()->status)->toBe('unpaid')->and($o->fresh()->status)->toBe('pending');
});

it('does not let one customer open another customer\'s payment', function () {
    $o = pdOrder(pdUser(), pdProduct(), 'gcash');

    $this->actingAs(pdUser())->get(route('customer.pay.return', $o->payment))->assertForbidden();
    $this->actingAs(pdUser())->post(route('customer.pay.start', $o->payment))->assertForbidden();
});

it('accepts a correctly signed PayMongo webhook and rejects a forged one', function () {
    $u = pdUser(); $o = pdOrder($u, pdProduct(), 'gcash', payment: ['reference' => 'PAY-111111', 'paymongo_session_id' => 'cs_77']);

    $body = json_encode(['data' => ['id' => 'evt_1', 'type' => 'event', 'attributes' => ['type' => 'checkout_session.payment.paid', 'data' => [
        'id' => 'cs_77', 'type' => 'checkout_session',
        'attributes' => ['reference_number' => 'PAY-111111', 'payments' => [['id' => 'pay_5', 'attributes' => ['status' => 'paid']]]],
    ]]]]);
    $post = fn (string $header) => $this->call('POST', route('webhooks.paymongo'), [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_PAYMONGO_SIGNATURE' => $header], $body);

    $post('t=1,te=deadbeef,li=')->assertStatus(400);
    expect($o->payment->fresh()->status)->toBe('unpaid');

    $t = (string) time();
    $post("t={$t},te=".hash_hmac('sha256', $t.'.'.$body, 'whsk_test').',li=')->assertOk();
    expect($o->payment->fresh()->status)->toBe('paid')->and($o->fresh()->status)->toBe('confirmed');
});

it('flags a refund when a payment arrives after the order was cancelled', function () {
    $admin = pdUser('admin'); $u = pdUser();
    $o = pdOrder($u, pdProduct(), 'gcash');
    $o->update(['status' => 'cancelled']);

    $o->payment->settle('pay_late');

    expect($o->payment->fresh()->status)->toBe('refund_due')
        ->and(Notice::where('user_id', $admin->id)->where('title', 'Refund needed')->exists())->toBeTrue();
});

/* ---------------- Admin side ---------------- */

it('will not confirm an order that is still waiting for its online payment', function () {
    $o = pdOrder(pdUser(), pdProduct(), 'gcash');

    $this->actingAs(pdUser('admin'))->put(route('admin.orders.update', $o), ['status' => 'confirmed'])->assertSessionHasErrors();

    expect($o->fresh()->status)->toBe('pending');
});

it('runs a cash pickup order from confirmed to ready to picked up, and closes the payment', function () {
    $admin = pdUser('admin'); $u = pdUser(); $p = pdProduct();
    $o = pdOrder($u, $p, 'cash');

    $this->actingAs($admin)->put(route('admin.orders.update', $o), ['status' => 'confirmed'])->assertSessionHasNoErrors();
    $this->actingAs($admin)->put(route('admin.orders.fulfillment', $o), ['fulfillment_status' => 'ready_for_pickup'])->assertSessionHasNoErrors();
    expect($o->fresh()->fulfillment_status)->toBe('ready_for_pickup')
        ->and(Notice::where('user_id', $u->id)->where('title', 'Ready for pickup')->exists())->toBeTrue();

    $this->actingAs($admin)->put(route('admin.orders.update', $o), ['status' => 'completed'])->assertSessionHasNoErrors();

    expect($o->fresh()->fulfillment_status)->toBe('picked_up')->and($o->payment->fresh()->status)->toBe('paid')
        ->and((float) $p->fresh()->stock_kg)->toBe(8.0)->and(Sale::count())->toBe(1);
});

it('does not let a pickup order go out for delivery', function () {
    $o = pdOrder(pdUser(), pdProduct(), 'cash');
    $o->update(['status' => 'confirmed']);

    $this->actingAs(pdUser('admin'))->put(route('admin.orders.fulfillment', $o), ['fulfillment_status' => 'out_for_delivery'])->assertSessionHasErrors();

    expect($o->fresh()->fulfillment_status)->toBe('pending');
});

it('flags a refund when a paid order is cancelled, and the admin can close it', function () {
    $admin = pdUser('admin'); $u = pdUser();
    $o = pdOrder($u, pdProduct(), 'gcash', payment: ['status' => 'paid', 'paid_at' => now()]);
    $o->update(['status' => 'confirmed']);

    $this->actingAs($admin)->put(route('admin.orders.update', $o), ['status' => 'cancelled'])->assertSessionHasNoErrors();
    expect($o->payment->fresh()->status)->toBe('refund_due')
        ->and(Notice::where('user_id', $admin->id)->where('title', 'Refund needed')->exists())->toBeTrue();

    $this->actingAs($admin)->put(route('admin.payments.refunded', $o->payment))->assertSessionHasNoErrors();
    expect($o->payment->fresh()->status)->toBe('refunded');
});

it('shows payment and delivery details on the order pages', function () {
    $u = pdUser(); $o = pdOrder($u, pdProduct(), 'gcash', 'delivery', ['delivery_fee' => 50, 'amount' => 450]);

    $this->actingAs($u)->get(route('customer.orders'))->assertOk()->assertSee('GCASH')->assertSee('Brgy. Uno, San Pablo')->assertSee('PAY NOW');
    $this->actingAs(pdUser('admin'))->get(route('admin.store', ['tab' => 'orders']))->assertOk()->assertSee('Brgy. Uno, San Pablo')->assertSee('WAITING FOR PAYMENT');
    $this->actingAs($u)->get(route('customer.checkout'))->assertRedirect(route('customer.shop'));
});
