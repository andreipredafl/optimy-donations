<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DonationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');

})->middleware(['auth', 'verified'])->name('dashboard');

Route::group(['middleware' => ['auth']], function () {

    Route::resource('campaigns', CampaignController::class);
    
    // Donation routes
    Route::post('campaigns/{campaign}/donate', [DonationController::class, 'store'])
        ->name('campaigns.donate');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
