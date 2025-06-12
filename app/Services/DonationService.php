<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DonationService
{
    protected PaymentManager $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * Process a donation
     *
     * @throws ValidationException
     */
    public function processDonation(Campaign $campaign, User $user, array $donationData): array
    {
        // Validate campaign can receive donations
        $this->validateCampaignForDonation($campaign);

        // Validate donation amount
        $amountCents = $this->validateAndConvertAmount($donationData['amount']);

        DB::beginTransaction();

        try {
            $donation = $this->createDonation($campaign, $user, $amountCents, $donationData);

            $paymentResult = $this->processPayment($donation, $donationData);

            if (! $paymentResult['success']) {

                $donation->update([
                    'status' => Donation::STATUS_FAILED,
                    'gateway_transaction_id' => $paymentResult['gateway_transaction_id'],
                ]);

                DB::commit();

                return [
                    'success' => false,
                    'donation' => $donation,
                    'error_message' => $paymentResult['error_message'],
                    'error_code' => $paymentResult['error_code'],
                ];
            }

            $this->completeDonation($donation, $campaign, $paymentResult);

            DB::commit();

            return [
                'success' => true,
                'donation' => $donation->fresh(),
                'error_message' => null,
                'error_code' => null,
            ];

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Donation processing failed', [
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Validate campaign can receive donations
     *
     * @throws ValidationException
     */
    protected function validateCampaignForDonation(Campaign $campaign): void
    {
        if ($campaign->status !== Campaign::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'campaign' => 'This campaign is not currently accepting donations.',
            ]);
        }

        if ($campaign->end_date && $campaign->end_date->isPast()) {
            throw ValidationException::withMessages([
                'campaign' => 'This campaign has ended and is no longer accepting donations.',
            ]);
        }
    }

    /**
     * Validate and convert donation amount
     *
     * @param  mixed  $amount
     *
     * @throws ValidationException
     */
    protected function validateAndConvertAmount($amount): int
    {
        $amount = (float) $amount;

        if ($amount < 1) {
            throw ValidationException::withMessages([
                'amount' => 'Donation amount must be at least €1.00.',
            ]);
        }

        if ($amount > 10000) {
            throw ValidationException::withMessages([
                'amount' => 'Donation amount cannot exceed €10,000.00.',
            ]);
        }

        return (int) round($amount * 100);
    }

    /**
     * Create donation record
     */
    protected function createDonation(Campaign $campaign, User $user, int $amountCents, array $donationData): Donation
    {
        return Donation::create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'amount_cents' => $amountCents,
            'payment_method' => 'credit_card',
            'transaction_id' => 'TXN_'.Str::random(10),
            'status' => Donation::STATUS_PENDING,
            'is_anonymous' => $donationData['is_anonymous'] ?? false,
            'message' => null,
        ]);
    }

    /**
     * Process payment using payment manager
     */
    protected function processPayment(Donation $donation, array $paymentData): array
    {
        $cardData = [
            'card_number' => $paymentData['card_number'],
            'card_expiry' => $paymentData['card_expiry'],
            'card_cvc' => $paymentData['card_cvc'],
            'card_holder_name' => $paymentData['card_holder_name'],
        ];

        return $this->paymentManager->processPayment($donation, $cardData);
    }

    /**
     * Complete donation after successful payment
     */
    protected function completeDonation(Donation $donation, Campaign $campaign, array $paymentResult): void
    {
        $donation->update([
            'status' => Donation::STATUS_COMPLETED,
            'gateway_transaction_id' => $paymentResult['gateway_transaction_id'],
            'completed_at' => now(),
        ]);

        $this->updateCampaignTotals($campaign, $donation);
    }

    /**
     * Update campaign donation totals
     */
    protected function updateCampaignTotals(Campaign $campaign, Donation $donation): void
    {
        $campaign->increment('current_amount_cents', $donation->amount_cents);
        $campaign->increment('donations_count');

        $existingDonationsCount = Donation::where('campaign_id', $campaign->id)
            ->where('user_id', $donation->user_id)
            ->where('status', Donation::STATUS_COMPLETED)
            ->where('id', '!=', $donation->id)
            ->count();

        if ($existingDonationsCount === 0) {
            $campaign->increment('donors_count');
        }

        if ($campaign->fresh()->current_amount_cents >= $campaign->goal_amount_cents) {
            $campaign->update([
                'status' => Campaign::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        }
    }
}
