<?php

namespace App\Contracts;

use App\Models\Donation;

interface PaymentServiceInterface
{
    /**
     * Process a payment for a donation
     */
    public function processPayment(Donation $donation, array $paymentData): array;

    /**
     * Verify a payment transaction
     */
    public function verifyPayment(string $transactionId): array;

    /**
     * Refund a payment
     */
    public function refundPayment(Donation $donation, ?int $amountCents = null): array;

    /**
     * Get the payment processor name
     */
    public function getName(): string;

    /**
     * Check if the payment processor is available
     */
    public function isAvailable(): bool;
}
