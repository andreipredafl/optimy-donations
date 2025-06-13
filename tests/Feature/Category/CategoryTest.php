<?php

use App\Models\Campaign;
use App\Models\Category;

test('category can be created with valid data', function () {
    $category = Category::create([
        'name' => 'Environmental Projects',
        'description' => 'Projects focused on environmental conservation and sustainability',
        'is_active' => true,
    ]);

    expect($category->name)->toBe('Environmental Projects');
    expect($category->slug)->toBe('environmental-projects');
    expect($category->is_active)->toBeTrue();

    $this->assertDatabaseHas('categories', [
        'name' => 'Environmental Projects',
        'slug' => 'environmental-projects',
        'is_active' => true,
    ]);
});

test('category slug is automatically generated from name', function () {
    $category = Category::create([
        'name' => 'Health & Wellness Projects',
        'is_active' => true,
    ]);

    expect($category->slug)->toBe('health-wellness-projects');
});

test('category can have multiple campaigns', function () {
    $category = Category::factory()->create();
    $campaigns = Campaign::factory(3)->create(['category_id' => $category->id]);

    expect($category->campaigns)->toHaveCount(3);
    expect($category->campaigns->first())->toBeInstanceOf(Campaign::class);
});

test('active scope filters only active categories', function () {
    // Create active and inactive categories
    Category::factory()->create(['is_active' => true, 'name' => 'Active Category']);
    Category::factory()->create(['is_active' => false, 'name' => 'Inactive Category']);

    $activeCategories = Category::active()->get();

    expect($activeCategories)->toHaveCount(1);
    expect($activeCategories->first()->name)->toBe('Active Category');
});

test('category name is required', function () {
    expect(fn () => Category::create([
        'description' => 'Test description',
        'is_active' => true,
    ]))->toThrow(Exception::class);
});

test('category is_active defaults to proper boolean', function () {
    $category = Category::factory()->create(['is_active' => 1]);

    expect($category->is_active)->toBeTrue();
    expect($category->is_active)->toBeBool();
});

test('slug is updated when name changes', function () {
    $category = Category::create([
        'name' => 'Original Name',
        'is_active' => true,
    ]);

    expect($category->slug)->toBe('original-name');

    $category->update(['name' => 'Updated Category Name']);

    expect($category->fresh()->slug)->toBe('updated-category-name');
});

test('category deletion affects campaigns with foreign key constraint', function () {
    $category = Category::factory()->create();
    $campaign = Campaign::factory()->create(['category_id' => $category->id]);

    // This test demonstrates that categories with campaigns cannot be deleted
    // due to foreign key constraints (which is good for data integrity)
    expect(function () use ($category) {
        $category->delete();
    })->toThrow(Exception::class);

    // Both category and campaign should still exist
    expect($category->fresh())->not->toBeNull();
    expect($campaign->fresh())->not->toBeNull();
});
