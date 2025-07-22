<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use App\Http\Modle\User;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Transfer;

class StripePaymentController extends Controller
{

    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function testStripeConnection()
    {
        $stripe = new \Stripe\StripeClient("sk_test_51RQ2xkE7tBgJSK9kh3ZoJVuA3bRrAEotzzYLP0bYGwNJv4PmoWeFoBaJM8wjv8g5qo7BJ2yBRx39ihuBofcpzfPY007bT00AeB");

        try {
            $checkoutSession = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' =>  [
                    [
                        'price' => 'price_1MotwRLkdIwHu7ixYcPLm5uZ',
                        'quantity' => 2,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => route('deposit'),
                'cancel_url' => route('deposit'),
            ]);
            return $checkoutSession;
            // يعرض رصيد حساب Stripe في وضع test
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // 1. إيداع مبلغ إلى المحفظة عبر بطاقة (charge)
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'token' => 'required|string',
        ]);

        $user = Auth::user();

        Charge::create([
            'amount' => $request->amount * 100, // بالسنتات
            'currency' => 'usd',
            'source' => $request->token,
            'description' => 'Deposit to wallet',
        ]);

        $user->wallet += $request->amount;
        $user->save();

        return response()->json([
            'message' => 'Deposit successful',
            'balance' => $user->wallet,
        ]);
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
