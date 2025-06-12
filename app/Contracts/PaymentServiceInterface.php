<?php

namespace App\Contracts;

use App\Models\Donation;

interface PaymentServiceInterface
{
    /**
     * Process a payment for a donation
     *
     * @param Donation $donation
     * @param array $paymentData
     * @return array
     */
    public function processPayment(Donation $donation, array $paymentData): array;

    /**
     * Verify a payment transaction
     *
     * @param string $transactionId
     * @return array
     */
    public function verifyPayment(string $transactionId): array;

    /**
     * Refund a payment
     *
     * @param Donation $donation
     * @param int|null $amountCents
     * @return array
     */
    public function refundPayment(Donation $donation, ?int $amountCents = null): array;

    /**
     * Get the payment processor name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the payment processor is available
     *
     * @return bool
     */
    public function isAvailable(): bool;
} 