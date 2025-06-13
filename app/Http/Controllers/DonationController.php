<?php

namespace App\Http\Controllers;

use App\Http\Requests\Donation\StoreDonationRequest;
use App\Models\Campaign;
use App\Models\Donation;
use App\Services\DonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends Controller
{
    protected DonationService $donationService;

    public function __construct(DonationService $donationService)
    {
        $this->donationService = $donationService;
    }

    public function index(): Response
    {
        if (auth()->user()->cannot('donations.view')) {
            abort(403, 'Unauthorized action.');
        }

        $user = auth()->user();

        if ($user->can('donations.view_all')) {

            $donations = Donation::with(['user', 'campaign', 'campaign.creator'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return Inertia::render('Donations/Index', [
                'donations' => $donations,
                'view_type' => 'admin',
                'auth' => [
                    'user' => $user,
                ],
            ]);
        } else {

            $sentDonations = Donation::with(['campaign', 'campaign.creator'])
                ->where('user_id', $user->id)
                ->completed()
                ->orderBy('created_at', 'desc')
                ->get();

            $receivedDonations = Donation::with(['user', 'campaign'])
                ->whereHas('campaign', function ($query) use ($user) {
                    $query->where('creator_id', $user->id);
                })
                ->completed()
                ->orderBy('created_at', 'desc')
                ->get();

            return Inertia::render('Donations/Index', [
                'sentDonations' => $sentDonations,
                'receivedDonations' => $receivedDonations,
                'view_type' => 'user',
                'auth' => [
                    'user' => $user,
                ],
            ]);
        }
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
