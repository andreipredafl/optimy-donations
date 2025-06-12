<?php

namespace App\Services\Payment;

use App\Contracts\PaymentServiceInterface;
use App\Models\Donation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MockPaymentService implements PaymentServiceInterface
{
    /**
     * Process a payment for a donation (Mock implementation)
     */
    public function processPayment(Donation $donation, array $paymentData): array
    {
        Log::info('Mock payment processing started', [
            'donation_id' => $donation->id,
            'amount_cents' => $donation->amount_cents,
            'card_last_four' => substr($paymentData['card_number'] ?? '', -4),
        ]);

        // Simulate payment processing delay
        usleep(500000); // 0.5 seconds

        // Mock payment validation
        $cardNumber = str_replace(' ', '', $paymentData['card_number'] ?? '');

        // Simulate different scenarios based on card number,
        if (substr($cardNumber, -1) === '1') {
            // Cards ending in 1 will fail
            return [
                'success' => false,
                'transaction_id' => null,
                'gateway_transaction_id' => null,
                'error_message' => 'Payment declined by issuing bank',
                'error_code' => 'CARD_DECLINED',
            ];
        }

        if (substr($cardNumber, -1) === '2') {
            // Cards ending in 2 will have insufficient funds
            return [
                'success' => false,
                'transaction_id' => null,
                'gateway_transaction_id' => null,
                'error_message' => 'Insufficient funds',
                'error_code' => 'INSUFFICIENT_FUNDS',
            ];
        }

        // Generate mock transaction IDs
        $transactionId = 'TXN_'.Str::random(10);
        $gatewayTransactionId = 'MOCK_'.Str::random(15);

        Log::info('Mock payment processed successfully', [
            'donation_id' => $donation->id,
            'transaction_id' => $transactionId,
            'gateway_transaction_id' => $gatewayTransactionId,
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'gateway_transaction_id' => $gatewayTransactionId,
            'error_message' => null,
            'error_code' => null,
            'processor_fee_cents' => (int) ($donation->amount_cents * 0.029), // 2.9% fee
            'net_amount_cents' => $donation->amount_cents - (int) ($donation->amount_cents * 0.029),
        ];
    }

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(string $transactionId): array
    {
        // Mock verification - always return success for mock
        return [
            'success' => true,
            'status' => 'completed',
            'verified_at' => now(),
        ];
    }

    /**
     * Refund a payment
     */
    public function refundPayment(Donation $donation, ?int $amountCents = null): array
    {
        $refundAmount = $amountCents ?? $donation->amount_cents;
        $refundId = 'REF_'.Str::random(10);

        Log::info('Mock refund processed', [
            'donation_id' => $donation->id,
            'refund_amount_cents' => $refundAmount,
            'refund_id' => $refundId,
        ]);

        return [
            'success' => true,
            'refund_id' => $refundId,
            'refund_amount_cents' => $refundAmount,
            'refunded_at' => now(),
        ];
    }

    /**
     * Get the payment processor name
     */
    public function getName(): string
    {
        return 'Mock Payment Service';
    }

    /**
     * Check if the payment processor is available
     */
    public function isAvailable(): bool
    {
        return true;
    }
}
