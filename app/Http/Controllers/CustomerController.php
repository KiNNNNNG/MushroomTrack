<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CartItem;
use App\Models\Notice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function shop()
    {
        return view('customer.shop', ['products' => Product::orderBy('id')->get()]);
    }

    public function orders(Request $request)
    {
        return view('customer.orders', [
            'orders' => Order::with('product', 'payment')->where('user_id', $request->user()->id)
                ->whereIn('status', ['pending', 'confirmed'])->latest()->get(),
        ]);
    }

    public function history(Request $request)
    {
        return view('customer.history', [
            'orders' => Order::with('product', 'payment')->where('user_id', $request->user()->id)
                ->whereIn('status', ['completed', 'cancelled'])->where('customer_hidden', false)->latest()->get(),
        ]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'product_id'  => ['required', 'exists:products,id'],
            'quantity_kg' => ['required', 'numeric', 'min:0.1'],
        ]);
        $product = Product::findOrFail($data['product_id']);
        abort_if($data['quantity_kg'] > $product->stock_kg, 422, "Only {$product->stock_kg}kg available.");

        return [$product, (float) $data['quantity_kg']];
    }

    private function putInCart(Request $request): void
    {
        [$product, $qty] = $this->validated($request);
        $item = CartItem::firstOrNew(['user_id' => $request->user()->id, 'product_id' => $product->id]);
        $item->quantity_kg = min(($item->quantity_kg ?? 0) + $qty, $product->stock_kg);
        $item->save();
    }

    public function addToCart(Request $request)
    {
        $this->putInCart($request);

        return back()->with('ok', 'Added to cart.');
    }

    /** "Buy now": puts the item in the cart and goes straight to the checkout page. */
    public function placeOrder(Request $request)
    {
        $this->putInCart($request);

        return redirect()->route('customer.checkout');
    }

    public function removeFromCart(Request $request, CartItem $item)
    {
        abort_unless($item->user_id === $request->user()->id, 403);
        $item->delete();

        return back()->with('ok', 'Removed from cart.');
    }

    public function updateCartItem(Request $request, CartItem $item)
    {
        abort_unless($item->user_id === $request->user()->id, 403);
        $qty = (float) $request->input('quantity_kg', 0);
        abort_if($qty <= 0, 422, 'Quantity must be greater than zero.');
        $item->quantity_kg = min($qty, $item->product->stock_kg);
        $item->save();

        return back()->with('ok', 'Cart updated.');
    }

    /** Step 1: choose pickup / delivery and how to pay. */
    public function checkoutPage(Request $request)
    {
        $items = CartItem::with('product')->where('user_id', $request->user()->id)->get();
        if ($items->isEmpty()) return redirect()->route('customer.shop')->withErrors('Your cart is empty.');

        return view('customer.checkout', [
            'u'        => $request->user(),
            'items'    => $items,
            'subtotal' => round($items->sum(fn ($i) => $i->quantity_kg * $i->product->price), 2),
            'fee'      => (float) config('mushroomtrack.delivery.fee'),
            'freeOver' => (float) config('mushroomtrack.delivery.free_over'),
        ]);
    }

    /** Step 2: create the orders (always cash — online payments have been removed). */
    public function checkout(Request $request)
    {
        $u = $request->user();
        $delivery = $request->input('fulfillment') === 'delivery';

        $data = $request->validate([
            'fulfillment'      => ['required', 'in:pickup,delivery'],
            'payment_method'   => ['required', Rule::in(array_keys(config('mushroomtrack.payment_methods')))],
            'delivery_address' => [Rule::requiredIf($delivery), 'nullable', 'string', 'max:255'],
            'contact_phone'    => [Rule::requiredIf($delivery), 'nullable', 'string', 'max:30', 'regex:/\d{7,}/'],
        ], [
            'delivery_address.required' => 'Enter the address where we should deliver.',
            'contact_phone.required'    => 'Enter a mobile number the rider can call.',
            'contact_phone.regex'       => 'Enter a valid contact number.',
        ]);

        $items = CartItem::with('product')->where('user_id', $u->id)->get();
        if ($items->isEmpty()) return redirect()->route('customer.shop')->withErrors('Your cart is empty.');
        foreach ($items as $i) {
            if ($i->quantity_kg > $i->product->stock_kg) {
                return back()->withErrors("Not enough stock for {$i->product->name}.")->withInput();
            }
        }

        $subtotal = round($items->sum(fn ($i) => $i->quantity_kg * $i->product->price), 2);
        $fee      = Payment::deliveryFeeFor($data['fulfillment'], $subtotal);
        $amount   = round($subtotal + $fee, 2);

        $address = $delivery ? trim($data['delivery_address']) : null;
        $phone   = $delivery ? trim($data['contact_phone']) : ($data['contact_phone'] ?? null);

        $payment = DB::transaction(function () use ($items, $u, $data, $fee, $amount, $address, $phone) {
            $payment = Payment::create([
                'reference' => Payment::newReference(), 'user_id' => $u->id, 'method' => 'cash',
                'delivery_fee' => $fee, 'amount' => $amount, 'status' => 'unpaid',
            ]);
            foreach ($items as $i) {
                Order::place($u, $i->product, (float) $i->quantity_kg, [
                    'payment_id' => $payment->id, 'fulfillment' => $data['fulfillment'], 'fulfillment_status' => 'pending',
                    'delivery_address' => $address, 'contact_phone' => $phone,
                ]);
            }
            CartItem::where('user_id', $u->id)->delete();

            return $payment;
        });

        // First time they give an address / number, remember it on their profile
        $fill = [];
        if ($address && blank($u->address)) $fill['address'] = $address;
        if ($phone && blank($u->phone)) $fill['phone'] = $phone;
        if ($fill) $u->forceFill($fill)->save();

        $how = $delivery ? 'delivery' : 'pickup';
        ActivityLog::record('Checkout', "{$payment->reference}: {$items->count()} item(s), {$how}, cash");
        Notice::toAdmins('New order', "{$u->name} placed {$items->count()} order(s) for {$how} (cash).", route('admin.store', ['tab' => 'orders'], false));

        return redirect()->route('customer.orders')->with('ok', 'Order placed. You will pay in cash when you '.($delivery ? 'receive it.' : 'pick it up.'));
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        if ($order->status !== 'pending') return back()->withErrors('Only pending orders can be cancelled.');

        $order->update(['status' => 'cancelled']);
        ActivityLog::record('Order cancelled', $order->display_code);
        Notice::toAdmins('Order cancelled', "{$request->user()->name} cancelled {$order->display_code}.", route('admin.store', ['tab' => 'history'], false));

        return back()->with('ok', 'Order cancelled.');
    }

    public function clearHistory(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        Order::where('user_id', $request->user()->id)->whereIn('id', $ids)
            ->whereIn('status', ['completed', 'cancelled'])->update(['customer_hidden' => true]);

        return back()->with('ok', count($ids) ? 'Selected records cleared.' : 'Nothing selected.');
    }
}
