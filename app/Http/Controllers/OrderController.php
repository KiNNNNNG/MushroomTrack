<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notice;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Admin: move an order through pending -> confirmed -> completed (or cancelled), and track pickup / delivery. */
class OrderController extends Controller
{
    public function update(Request $request, Order $order)
    {
        $status = $request->validate(['status' => ['required', 'in:confirmed,completed,cancelled']])['status'];

        $allowed = ['pending' => ['confirmed', 'cancelled'], 'confirmed' => ['completed', 'cancelled']];
        if (! in_array($status, $allowed[$order->status] ?? [], true)) {
            return back()->withErrors('That status change is not allowed.');
        }

        $order->loadMissing('payment', 'product');
        if ($status !== 'cancelled' && $order->isAwaitingPayment()) {
            return back()->withErrors("{$order->display_code} is still waiting for the customer's {$order->payment->method_label} payment.");
        }

        $refunding = false;
        if ($status === 'completed') {
            $error = DB::transaction(function () use ($order) {
                $product = Product::lockForUpdate()->find($order->product_id);
                if ($product->stock_kg < $order->quantity_kg) {
                    return "Not enough stock ({$product->stock_kg}kg left).";
                }
                $product->decrement('stock_kg', $order->quantity_kg);
                Sale::create([
                    'order_id' => $order->id, 'product_id' => $product->id,
                    'quantity_kg' => $order->quantity_kg, 'amount' => $order->total, 'sold_at' => now(),
                ]);
                $order->update([
                    'status' => 'completed',
                    'fulfillment_status' => $order->fulfillment === 'delivery' ? 'delivered' : 'picked_up',
                ]);
                return null;
            });
            if ($error) return back()->withErrors($error);

            $this->settleCash($order);
        } else {
            $order->update(['status' => $status]);
            if ($status === 'cancelled') $refunding = (bool) $order->payment?->orderCancelled($order);
        }

        ActivityLog::record('Order '.$status, $order->display_code);

        $qty = rtrim(rtrim(number_format($order->quantity_kg, 2), '0'), '.');
        $next = match (true) {
            $status === 'confirmed' && $order->fulfillment === 'delivery' => ' We will tell you when it is out for delivery.',
            $status === 'confirmed' => ' We will tell you when it is ready for pickup.',
            default => '',
        };
        if (! $refunding) {
            // (a paid order that is cancelled already got its own "refund" message from the payment)
            Notice::send(
                $order->user_id,
                'Order '.$status,
                "{$order->display_code} — {$qty}kg {$order->product->name} is now {$status}.{$next}",
                route($status === 'confirmed' ? 'customer.orders' : 'customer.history', [], false),
                true
            );
        }

        if ($status === 'completed') {
            $left = $order->product->fresh()->stock_kg;
            if ($left <= 10) {
                Notice::toAdmins('Low stock', "{$order->product->name} has only {$left}kg left.", route('admin.store', [], false));
            }
        }

        return back()->with('ok', "{$order->display_code} marked {$status}.");
    }

    /** Admin: "ready for pickup" / "out for delivery". */
    public function fulfillment(Request $request, Order $order)
    {
        $to = $request->validate(['fulfillment_status' => ['required', 'in:ready_for_pickup,out_for_delivery']])['fulfillment_status'];

        if ($order->status !== 'confirmed') return back()->withErrors('Confirm the order first.');
        if ($to === 'ready_for_pickup' && $order->fulfillment !== 'pickup') return back()->withErrors('This order is for delivery.');
        if ($to === 'out_for_delivery' && $order->fulfillment !== 'delivery') return back()->withErrors('This order is for pickup.');

        $order->update(['fulfillment_status' => $to]);
        ActivityLog::record($to === 'ready_for_pickup' ? 'Order ready for pickup' : 'Order out for delivery', $order->display_code);

        Notice::send(
            $order->user_id,
            $to === 'ready_for_pickup' ? 'Ready for pickup' : 'Out for delivery',
            $to === 'ready_for_pickup'
                ? "{$order->display_code} is packed and ready for pickup at the farm."
                : "{$order->display_code} is on its way to {$order->delivery_address}.",
            route('customer.orders', [], false),
            true
        );

        return back()->with('ok', "{$order->display_code}: ".strtolower($order->fulfillment_status_label).'.');
    }

    /** Cash is collected when the goods change hands, so the payment closes when its last order is completed. */
    private function settleCash(Order $order): void
    {
        $p = $order->payment;
        if (! $p || $p->method !== 'cash' || $p->status !== 'unpaid') return;
        if ($p->orders()->whereIn('status', ['pending', 'confirmed'])->exists()) return;

        $p->settle();
    }
}
