<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Support\Facades\Log;

class PaymentService implements PaymentServiceInterface
{
    /**
     * Create payment intent for Stripe.
     */
    public function createPaymentIntent(Booking $booking): array
    {
        // In a real implementation, you would integrate with Stripe
        // For now, we'll return a mock response
        
        $paymentIntent = [
            'client_secret' => 'pi_mock_' . uniqid() . '_secret_' . uniqid(),
            'payment_intent_id' => 'pi_mock_' . uniqid(),
            'amount' => $booking->total_amount * 100, // Convert to cents
            'currency' => 'vnd',
        ];

        return $paymentIntent;
    }

    /**
     * Confirm payment.
     */
    public function confirmPayment(Booking $booking, string $paymentIntentId, string $paymentMethod): Payment
    {
        // In a real implementation, you would verify with Stripe
        // For now, we'll create a mock payment record
        
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'currency' => 'VND',
            'payment_method' => $paymentMethod,
            'stripe_payment_intent_id' => $paymentIntentId,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // Update booking status
        $booking->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        return $payment;
    }

    /**
     * Handle Stripe webhook.
     */
    public function handleWebhook($request): void
    {
        // In a real implementation, you would verify the webhook signature
        // and process the webhook events
        
        Log::info('Payment webhook received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);
    }
}
