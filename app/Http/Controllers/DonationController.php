<?php

namespace App\Http\Controllers;

use App\Http\Requests\Donation\StoreDonationRequest;
use App\Models\Campaign;
use App\Services\DonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DonationController extends Controller
{
    protected DonationService $donationService;

    public function __construct(DonationService $donationService)
    {
        $this->donationService = $donationService;
    }

    public function store(StoreDonationRequest $request, Campaign $campaign): RedirectResponse
    {
        try {
            $result = $this->donationService->processDonation(
                $campaign,
                Auth::user(),
                $request->validated()
            );

            if (! $result['success']) {
                return back()->withErrors([
                    'payment' => $result['error_message'],
                ])->withInput();
            }

            return redirect()
                ->route('campaigns.show', $campaign)
                ->with('success', 'Thank you for your donation! Your contribution has been processed successfully.')
                ->with('donation_id', $result['donation']->id);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->withErrors([
                'general' => 'An unexpected error occurred while processing your donation: '.$e->getMessage(),
            ])->withInput();
        }
    }
}
