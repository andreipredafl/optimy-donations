<?php

namespace App\Http\Controllers;

use App\Http\Requests\Campaign\StoreCampaignRequest;
use App\Models\Campaign;
use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;
use Storage;

class CampaignController extends Controller
{
    public function index(): Response
    {
        $campaigns = Campaign::where('status', Campaign::STATUS_ACTIVE)
            ->with(['category', 'creator'])
            ->orderBy('start_date', 'desc')
            ->paginate(6);

        return Inertia::render('Campaigns/Index', [
            'campaigns' => $campaigns,
        ]);
    }

    public function show(Campaign $campaign): Response
    {
        $campaign->load(['category', 'creator', 'donations']);

        return Inertia::render('Campaigns/Show', [
            'campaign' => $campaign,
        ]);
    }

    public function create(): Response
    {
        $categories = Category::all();

        return Inertia::render('Campaigns/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreCampaignRequest $request)
    {
        $validatedData = $request->getValidatedData();

        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('campaigns', 'public');
            $validatedData['featured_image_url'] = Storage::url($imagePath);
        }

        $campaign = Campaign::create($validatedData);

        return Inertia::location(route('campaigns.show', $campaign->id));

    }

    public function edit(Campaign $campaign): Response
    {
        $categories = Category::all();

        return Inertia::render('Campaigns/Edit', [
            'campaign' => $campaign,
            'categories' => $categories,
        ]);
    }

    public function update(StoreCampaignRequest $request, Campaign $campaign)
    {
        $validatedData = $request->getValidatedData();

        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('campaigns', 'public');
            $validatedData['featured_image_url'] = Storage::url($imagePath);
        }

        $campaign->update($validatedData);

        return Inertia::location(route('campaigns.show', $campaign->id));
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('campaigns.index');
    }

    public function donate(Campaign $campaign): Response
    {
        return Inertia::render('Campaigns/Donate', [
            'campaign' => $campaign,
        ]);
    }
}
