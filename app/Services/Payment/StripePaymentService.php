<?php

namespace App\Services\Payment;

use App\Contracts\PaymentServiceInterface;
use App\Models\Donation;
use Illuminate\Support\Facades\Log;

/**
 * Stripe Payment Service - Ready for future implementation
 *
 * This class demonstrates how would be to swap payment processors
 * when the business decides which payment provider to use.
 */
class StripePaymentService implements PaymentServiceInterface
{
    protected $stripe;

    public function __construct()
    {
        // Future: Initialize Stripe client
        // $this->stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
    }

    /**
     * Process a payment for a donation
     */
    public function processPayment(Donation $donation, array $paymentData): array
    {
        try {
            // Future Stripe implementation:
            /*
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $donation->amount_cents,
                'currency' => 'eur',
                'payment_method_data' => [
                    'type' => 'card',
                    'card' => [
                        'number' => $paymentData['card_number'],
                        'exp_month' => substr($paymentData['card_expiry'], 0, 2),
                        'exp_year' => '20' . substr($paymentData['card_expiry'], 3, 2),
                        'cvc' => $paymentData['card_cvc'],
                    ],
                ],
                'confirmation_method' => 'manual',
                'confirm' => true,
                'metadata' => [
                    'donation_id' => $donation->id,
                    'campaign_id' => $donation->campaign_id,
                ],
            ]);

            return [
                'success' => true,
                'transaction_id' => $paymentIntent->id,
                'gateway_transaction_id' => $paymentIntent->id,
                'error_message' => null,
                'error_code' => null,
            ];
            */

            // For now, return a placeholder
            throw new \Exception('Stripe integration not yet implemented');
        } catch (\Exception $e) {
            Log::error('Stripe payment failed', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'transaction_id' => null,
                'gateway_transaction_id' => null,
                'error_message' => $e->getMessage(),
                'error_code' => 'STRIPE_ERROR',
            ];
        }
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(string $transactionId): array
    {
        throw new \Exception('Stripe integration not yet implemented');
    }

    /**
     * Refund a payment
     */
    public function refundPayment(Donation $donation, ?int $amountCents = null): array
    {
        throw new \Exception('Stripe integration not yet implemented');
    }

    /**
     * Get the payment processor name
     */
    public function getName(): string
    {
        return 'Stripe';
    }

    /**
     * Check if the payment processor is available
     */
    public function isAvailable(): bool
    {
        return config('payment.drivers.stripe.enabled', false);
    }
}
