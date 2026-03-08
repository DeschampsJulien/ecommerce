<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
    private string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
        Stripe::setApiKey($this->secretKey);
    }
    // Crée un PaymentIntent en mode sandbox
    
    // public function createPaymentIntent(float $amount, string $currency = 'eur'): PaymentIntent
    // {
    //     return PaymentIntent::create([
    //         'amount' => (int)($amount * 100), // Stripe utilise les centimes
    //         'currency' => $currency,
    //     ]);
    // }
    public function createPaymentIntent(float $amount, string $currency = 'eur', array $metadata = [])
    {
        return PaymentIntent::create([
            'amount' => (int)($amount * 100),
            'currency' => $currency,
            'metadata' => $metadata
        ]);
    }
}