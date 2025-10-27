<?php

namespace App\Services\Contracts;

use App\Models\Booking;
use App\Models\Payment;

interface PaymentServiceInterface
{
    /**
     * Create payment intent for Stripe.
     */
    public function createPaymentIntent(Booking $booking): array;

    /**
     * Confirm payment.
     */
    public function confirmPayment(Booking $booking, string $paymentIntentId, string $paymentMethod): Payment;

    /**
     * Handle Stripe webhook.
     */
    public function handleWebhook($request): void;
}
