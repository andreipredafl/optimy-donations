<?php

use App\Models\Campaign;
use App\Models\Category;
use App\Models\Donation;
use App\Models\User;
use App\Services\Payment\MockPaymentService;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create required roles
    Role::create(['name' => 'employee', 'guard_name' => 'web']);
    Role::create(['name' => 'admin', 'guard_name' => 'web']);
});

test('mock payment service processes successful payment', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $donation = Donation::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
        'amount_cents' => 5000,
        'status' => Donation::STATUS_PENDING,
    ]);

    $mockPaymentService = new MockPaymentService;

    $result = $mockPaymentService->processPayment($donation, [
        'card_number' => '4532 1234 5678 9010',
        'card_expiry' => '12/25',
        'card_cvc' => '123',
        'card_holder_name' => 'John Doe',
    ]);

    expect($result['success'])->toBeTrue();
    expect($result['transaction_id'])->toMatch('/^TXN_/');
    expect($result['gateway_transaction_id'])->toMatch('/^MOCK_/');
    expect($result['error_message'])->toBeNull();
    expect($result['processor_fee_cents'])->toBe(145);
    expect($result['net_amount_cents'])->toBe(4855);
});

test('mock payment service handles card declined', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $donation = Donation::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
        'amount_cents' => 2500,
        'status' => Donation::STATUS_PENDING,
    ]);

    $mockPaymentService = new MockPaymentService;

    $result = $mockPaymentService->processPayment($donation, [
        'card_number' => '4532 1234 5678 9011',
        'card_expiry' => '12/25',
        'card_cvc' => '123',
        'card_holder_name' => 'John Doe',
    ]);

    expect($result['success'])->toBeFalse();
    expect($result['transaction_id'])->toBeNull();
    expect($result['gateway_transaction_id'])->toBeNull();
    expect($result['error_message'])->toBe('Payment declined by issuing bank');
    expect($result['error_code'])->toBe('CARD_DECLINED');
});

test('mock payment service handles insufficient funds', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $donation = Donation::factory()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
        'amount_cents' => 10000,
        'status' => Donation::STATUS_PENDING,
    ]);

    $mockPaymentService = new MockPaymentService;

    $result = $mockPaymentService->processPayment($donation, [
        'card_number' => '4532 1234 5678 9012',
        'card_expiry' => '12/25',
        'card_cvc' => '123',
        'card_holder_name' => 'John Doe',
    ]);

    expect($result['success'])->toBeFalse();
    expect($result['transaction_id'])->toBeNull();
    expect($result['gateway_transaction_id'])->toBeNull();
    expect($result['error_message'])->toBe('Insufficient funds');
    expect($result['error_code'])->toBe('INSUFFICIENT_FUNDS');
});

test('users can make successful donation through campaign', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $category = Category::factory()->create();
    $campaign = Campaign::factory()->create([
        'category_id' => $category->id,
        'status' => Campaign::STATUS_ACTIVE,
        'goal_amount_cents' => 100000,
        'current_amount_cents' => 0,
        'donations_count' => 0,
        'donors_count' => 0,
    ]);

    $response = $this
        ->actingAs($user)
        ->post("/campaigns/{$campaign->id}/donate", [
            'amount' => 50.00,
            'is_anonymous' => false,
            'card_number' => '4532 1234 5678 9010', // Successful card
            'card_expiry' => '12/25',
            'card_cvc' => '123',
            'card_holder_name' => 'John Doe',
        ]);

    $response->assertRedirect("/campaigns/{$campaign->id}");
    $response->assertSessionHas('success');

    // Check donation was created and completed
    $this->assertDatabaseHas('donations', [
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
        'amount_cents' => 5000,
        'status' => Donation::STATUS_COMPLETED,
        'is_anonymous' => false,
    ]);

    // Check campaign totals were updated
    $campaign->refresh();
    expect($campaign->current_amount_cents)->toBe(5000);
    expect($campaign->donations_count)->toBe(1);
    expect($campaign->donors_count)->toBe(1);
});

test('failed payment creates failed donation record', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $category = Category::factory()->create();
    $campaign = Campaign::factory()->create([
        'category_id' => $category->id,
        'status' => Campaign::STATUS_ACTIVE,
        'current_amount_cents' => 0,
        'donations_count' => 0,
        'donors_count' => 0,
    ]);

    $response = $this
        ->actingAs($user)
        ->post("/campaigns/{$campaign->id}/donate", [
            'amount' => 25.00,
            'is_anonymous' => false,
            'card_number' => '4532 1234 5678 9011', // Declined card
            'card_expiry' => '12/25',
            'card_cvc' => '123',
            'card_holder_name' => 'John Doe',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['payment']);

    // Check donation was created but failed
    $this->assertDatabaseHas('donations', [
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
        'amount_cents' => 2500,
        'status' => Donation::STATUS_FAILED,
    ]);

    // Check campaign totals were NOT updated
    $campaign->refresh();
    expect($campaign->current_amount_cents)->toBe(0);
    expect($campaign->donations_count)->toBe(0);
    expect($campaign->donors_count)->toBe(0);
});

test('donation validation requires valid data', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $campaign = Campaign::factory()->create(['status' => Campaign::STATUS_ACTIVE]);

    $response = $this
        ->actingAs($user)
        ->post("/campaigns/{$campaign->id}/donate", [
            'amount' => -10, // Invalid amount
            'card_number' => '123', // Invalid card number
            'card_expiry' => 'invalid', // Invalid expiry
            'card_cvc' => '1', // Invalid CVC
            'card_holder_name' => '', // Missing name
        ]);

    $response->assertSessionHasErrors([
        'amount',
        'card_number',
        'card_expiry',
        'card_cvc',
        'card_holder_name',
    ]);
});

test('cannot donate to inactive campaign', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $campaign = Campaign::factory()->create(['status' => Campaign::STATUS_COMPLETED]);

    $response = $this
        ->actingAs($user)
        ->post("/campaigns/{$campaign->id}/donate", [
            'amount' => 50.00,
            'is_anonymous' => false,
            'card_number' => '4532 1234 5678 9010',
            'card_expiry' => '12/25',
            'card_cvc' => '123',
            'card_holder_name' => 'John Doe',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors();
});

test('mock payment service can process refunds', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $donation = Donation::factory()->completed()->create([
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
        'amount_cents' => 7500,
    ]);

    $mockPaymentService = new MockPaymentService;

    $result = $mockPaymentService->refundPayment($donation);

    expect($result['success'])->toBeTrue();
    expect($result['refund_id'])->toMatch('/^REF_/');
    expect($result['refund_amount_cents'])->toBe(7500);
    expect($result['refunded_at'])->not->toBeNull();
});

test('anonymous donations hide donor information', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $category = Category::factory()->create();
    $campaign = Campaign::factory()->create([
        'category_id' => $category->id,
        'status' => Campaign::STATUS_ACTIVE,
    ]);

    $this
        ->actingAs($user)
        ->post("/campaigns/{$campaign->id}/donate", [
            'amount' => 100.00,
            'is_anonymous' => true, // Anonymous donation
            'card_number' => '4532 1234 5678 9010',
            'card_expiry' => '12/25',
            'card_cvc' => '123',
            'card_holder_name' => 'John Doe',
        ]);

    $this->assertDatabaseHas('donations', [
        'campaign_id' => $campaign->id,
        'user_id' => $user->id,
        'is_anonymous' => true,
        'status' => Donation::STATUS_COMPLETED,
    ]);
});
