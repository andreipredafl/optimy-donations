<?php

namespace App\Http\Controllers;

use App\Http\Requests\Campaign\StoreCampaignRequest;
use App\Models\Campaign;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Storage;

class CampaignController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Campaign::where('status', Campaign::STATUS_ACTIVE)
            ->with(['category', 'creator']);

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        $campaigns = $query->orderBy('start_date', 'desc')
            ->paginate(6)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return Inertia::render('Campaigns/Index', [
            'campaigns' => $campaigns,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
            'auth' => [
                'user' => auth()->user(),
            ],
        ]);
    }

    public function show(Campaign $campaign): Response
    {
        $campaign->load([
            'category',
            'creator',
            'donations' => function ($query) {
                $query->where('status', 'completed')
                    ->orderBy('created_at', 'desc');
            },
            'donations.user',
        ]);

        return Inertia::render('Campaigns/Show', [
            'campaign' => $campaign,
            'payment_driver' => config('payment.default_driver'),
            'auth' => [
                'user' => auth()->user(),
            ],
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
