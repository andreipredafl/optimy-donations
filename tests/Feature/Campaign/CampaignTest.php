<?php

use App\Models\Campaign;
use App\Models\Category;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create required roles
    Role::create(['name' => 'employee', 'guard_name' => 'web']);
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
});

test('users can view campaigns index page', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $category = Category::factory()->create();
    Campaign::factory(3)->create(['category_id' => $category->id, 'status' => Campaign::STATUS_ACTIVE]);

    $response = $this
        ->actingAs($user)
        ->get('/campaigns');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Campaigns/Index'));
});

test('users can view individual campaign', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $category = Category::factory()->create();
    $campaign = Campaign::factory()->create([
        'category_id' => $category->id,
        'status' => Campaign::STATUS_ACTIVE,
    ]);

    $response = $this
        ->actingAs($user)
        ->get("/campaigns/{$campaign->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Campaigns/Show'));
});

test('authenticated users can create campaigns', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $category = Category::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/campaigns', [
            'title' => 'Help Build a School',
            'description' => 'We need your help to build a new school for children in our community.',
            'goal_amount' => 5000.00,
            'category_id' => $category->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('campaigns', [
        'title' => 'Help Build a School',
        'goal_amount_cents' => 500000,
        'creator_id' => $user->id,
        'category_id' => $category->id,
        'status' => Campaign::STATUS_ACTIVE,
    ]);
});

test('campaign creation requires valid data', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $response = $this
        ->actingAs($user)
        ->post('/campaigns', [
            'title' => '',
            'description' => '',
            'goal_amount' => -100,
            'category_id' => 999,
        ]);

    $response->assertSessionHasErrors(['title', 'description', 'goal_amount', 'category_id']);
});

test('campaign creator can update their campaign', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $category = Category::factory()->create();
    $campaign = Campaign::factory()->create([
        'creator_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Original Title',
        'goal_amount_cents' => 500000,
        'start_date' => now()->addDay(),
        'end_date' => now()->addMonth(),
    ]);

    $response = $this
        ->actingAs($user)
        ->put("/campaigns/{$campaign->id}", [
            'title' => 'Updated Campaign Title',
            'description' => 'Updated description for the campaign.',
            'goal_amount' => 7500.00,
            'category_id' => $category->id,
            'start_date' => $campaign->start_date->format('Y-m-d'),
            'end_date' => $campaign->end_date->format('Y-m-d'),
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $campaign->refresh();
    expect($campaign->title)->toBe('Updated Campaign Title');
    expect($campaign->goal_amount_cents)->toBe(750000);
});

test('campaigns can be soft deleted', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $campaign = Campaign::factory()->create(['creator_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->delete("/campaigns/{$campaign->id}");

    $response->assertRedirect('/campaigns');

    $this->assertSoftDeleted('campaigns', ['id' => $campaign->id]);
});

test('campaign goal amount is properly converted to cents', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $category = Category::factory()->create();

    $this
        ->actingAs($user)
        ->post('/campaigns', [
            'title' => 'Test Campaign',
            'description' => 'Test description',
            'goal_amount' => 123.45,
            'category_id' => $category->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
        ]);

    $this->assertDatabaseHas('campaigns', [
        'title' => 'Test Campaign',
        'goal_amount_cents' => 12345,
    ]);
});

test('guests cannot create campaigns', function () {
    $category = Category::factory()->create();

    $response = $this->post('/campaigns', [
        'title' => 'Test Campaign',
        'description' => 'Test description',
        'goal_amount' => 1000.00,
        'category_id' => $category->id,
    ]);

    $response->assertRedirect('/login');
});
