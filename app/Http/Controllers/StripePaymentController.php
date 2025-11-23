<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Transfer;

class StripePaymentController extends Controller
{

    public function __construct()
    {
        Stripe::setApiKey(apiKey: env(key: 'STRIPE_SECRET'));
    }

    public function testStripeConnection()
    {
        Stripe::setApiKey(apiKey: env(key: 'STRIPE_SECRET'));

        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => 2000, // السعر بـ سنت (يعني 20 دولار)
                    'product_data' => [
                        'name' => 'خدمة اشتراك مثلاً',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('checkout.success'),
            'cancel_url' => route('checkout.cancel'),
        ]);
        return $checkout_session;
    }
    // 1. إيداع مبلغ إلى المحفظة عبر بطاقة (charge)
    public function deposit(Request $request)
{
    try {

        $request->validate([
            'amount' => 'required|min:1',
           
        ]);

        $user = Auth::user();

        Charge::create([
            'amount' => $request->amount * 100,
            'currency' => 'usd',
            'source' => $user->token,
            'description' => 'Deposit to wallet',
        ]);

        $user->wallet += $request->amount;
        if ($user->is_suspended && $user->wallet > 0) {
            $user->is_suspended = false;
        }
        $user->save();

        return response()->json([
            'message' => 'Deposit successful',
            'balance' => $user->wallet,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
}
    public function cancel()
    {
        return 'تم إلغاء الدفع ❌';
    }
    // 2. سحب مبلغ من المحفظة إلى حساب Stripe متصل (transfer)
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'destination' => 'required|string', // معرف حساب Stripe متصل
        ]);

        $user = Auth::user();

        if ($user->wallet < $request->amount) {
            return response()->json([
                'message' => 'Insufficient balance'
            ], 400);
        }

        Transfer::create([
            'amount' => $request->amount * 100,
            'currency' => 'usd',
            'destination' => $request->destination,
        ]);

        $user->wallet -= $request->amount;
        $user->save();

        return response()->json([
            'message' => 'Withdrawal successful',
            'balance' => $user->wallet,
        ]);
    }
}
