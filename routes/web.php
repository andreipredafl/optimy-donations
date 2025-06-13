<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\UserController;
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
    Route::get('donations', [DonationController::class, 'index'])->name('donations.index');
    Route::post('campaigns/{campaign}/donate', [DonationController::class, 'store'])
        ->name('campaigns.donate');
    
    // Users routes
    Route::get('users', [UserController::class, 'index'])->name('users.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
