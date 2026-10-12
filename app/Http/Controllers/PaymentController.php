<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notice;
use App\Models\Payment;
use App\Services\PayMongo;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

/** GCash / Maya payments through PayMongo, plus the admin's "refund done" button. */
class PaymentController extends Controller
{
    /** Customer taps PAY NOW: open a fresh PayMongo checkout for an unpaid online payment. */
    public function start(Request $request, Payment $payment)
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        if ($payment->status !== 'unpaid' || ! $payment->isOnline()) return back()->withErrors('This payment does not need to be paid online.');
        if (! $payment->orders()->where('status', 'pending')->exists()) return back()->withErrors('This order was cancelled, so there is nothing to pay.');

        try {
            return redirect()->away($payment->startCheckout());
        } catch (RuntimeException $e) {
            return back()->withErrors("The payment page could not open ({$e->getMessage()}). Please try again in a moment.");
        }
    }

    /**
     * PayMongo sends the customer back here after paying. We ask PayMongo directly instead of trusting the
     * browser, so it also works on localhost, where PayMongo's webhook cannot reach us.
     */
    public function returned(Request $request, Payment $payment, PayMongo $paymongo)
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        if ($payment->status === 'unpaid' && $payment->paymongo_session_id) {
            try {
                if ($id = $paymongo->paidPaymentId($payment->paymongo_session_id)) $payment->settle($id);
            } catch (Throwable $e) {
                report($e);
            }
        }

        $payment->refresh();

        return redirect()->route('customer.orders')->with('ok', match (true) {
            in_array($payment->status, ['paid', 'refund_due', 'refunded'], true) => 'Payment received. Thank you!',
            default => 'We have not received your payment yet. If you already paid, give it a minute and refresh this page.',
        });
    }

    public function cancelled(Request $request, Payment $payment)
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        return redirect()->route('customer.orders')->withErrors('Payment was not completed. Your order is saved. Tap PAY NOW when you are ready, or cancel it.');
    }

    /** PayMongo -> us. No login and no CSRF token: the signature header proves it really came from PayMongo. */
    public function webhook(Request $request, PayMongo $paymongo)
    {
        $raw = $request->getContent();
        if (! $paymongo->verifySignature($raw, $request->header('Paymongo-Signature'))) {
            return response('Invalid signature.', 400);
        }

        $event = $paymongo->parseWebhook($raw);
        if ($event['type'] !== 'checkout_session.payment.paid') return response('Ignored.', 200);

        $session = $event['resource'];
        $payment = Payment::where('paymongo_session_id', (string) ($session['id'] ?? ''))->first()
            ?? Payment::where('reference', (string) ($session['attributes']['reference_number'] ?? ''))->first();

        if ($payment) $payment->settle($paymongo->paidIdFrom($session));

        return response('OK', 200);
    }

    /** Admin: the money was sent back through the PayMongo dashboard. */
    public function refunded(Payment $payment)
    {
        if ($payment->status !== 'refund_due') return back()->withErrors('Nothing to refund on this payment.');

        $payment->update(['status' => 'refunded']);
        ActivityLog::record('Refund done', "{$payment->reference}: {$payment->method_label}");
        Notice::send($payment->user_id, 'Refund sent', "Your refund for {$payment->reference} has been sent via {$payment->method_label}.", route('customer.history', [], false), true);

        return back()->with('ok', "{$payment->reference} marked as refunded.");
    }
}
