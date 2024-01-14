<?php

use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\CurrentAccountController;
use Laravel\Jetstream\Http\Controllers\Livewire\ApiTokenController;
use Laravel\Jetstream\Http\Controllers\Livewire\PrivacyPolicyController;
use Laravel\Jetstream\Http\Controllers\Livewire\AccountController;
use Laravel\Jetstream\Http\Controllers\Livewire\TermsOfServiceController;
use Laravel\Jetstream\Http\Controllers\Livewire\UserProfileController;
use Laravel\Jetstream\Http\Controllers\AccountInvitationController;
use Laravel\Jetstream\Jetstream;

Route::group(['middleware' => config('jetstream.middleware', ['web'])], function () {
    if (Jetstream::hasTermsAndPrivacyPolicyFeature()) {
        Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');
        Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
    }

    Route::group(['middleware' => ['auth', 'verified']], function () {
        // User & Profile...
        Route::get('/user/profile', [UserProfileController::class, 'show'])
                    ->name('profile.show');

        // API...
        if (Jetstream::hasApiFeatures()) {
            Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
        }

        // Accounts...
        if (Jetstream::hasAccountFeatures()) {
            Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
            Route::get('/accounts/{account}', [AccountController::class, 'show'])->name('accounts.show');
            Route::put('/current-account', [CurrentAccountController::class, 'update'])->name('current-account.update');

            Route::get('/account-invitations/{invitation}', [AccountInvitationController::class, 'accept'])
                        ->middleware(['signed'])
                        ->name('account-invitations.accept');
        }
    });
});
