<?php

use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\CurrentAccountController;
use Laravel\Jetstream\Http\Controllers\Inertia\ApiTokenController;
use Laravel\Jetstream\Http\Controllers\Inertia\CurrentUserController;
use Laravel\Jetstream\Http\Controllers\Inertia\OtherBrowserSessionsController;
use Laravel\Jetstream\Http\Controllers\Inertia\PrivacyPolicyController;
use Laravel\Jetstream\Http\Controllers\Inertia\ProfilePhotoController;
use Laravel\Jetstream\Http\Controllers\Inertia\AccountController;
use Laravel\Jetstream\Http\Controllers\Inertia\AccountMemberController;
use Laravel\Jetstream\Http\Controllers\Inertia\TermsOfServiceController;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;
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

        Route::delete('/user/other-browser-sessions', [OtherBrowserSessionsController::class, 'destroy'])
                    ->name('other-browser-sessions.destroy');

        Route::delete('/user/profile-photo', [ProfilePhotoController::class, 'destroy'])
                    ->name('current-user-photo.destroy');

        if (Jetstream::hasAccountDeletionFeatures()) {
            Route::delete('/user', [CurrentUserController::class, 'destroy'])
                        ->name('current-user.destroy');
        }

        // API...
        if (Jetstream::hasApiFeatures()) {
            Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
            Route::post('/user/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
            Route::put('/user/api-tokens/{token}', [ApiTokenController::class, 'update'])->name('api-tokens.update');
            Route::delete('/user/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
        }

        // Accounts...
        if (Jetstream::hasAccountFeatures()) {
            Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
            Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
            Route::get('/accounts/{account}', [AccountController::class, 'show'])->name('accounts.show');
            Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
            Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');
            Route::put('/current-account', [CurrentAccountController::class, 'update'])->name('current-account.update');
            Route::post('/accounts/{account}/members', [AccountMemberController::class, 'store'])->name('account-members.store');
            Route::put('/accounts/{account}/members/{user}', [AccountMemberController::class, 'update'])->name('account-members.update');
            Route::delete('/accounts/{account}/members/{user}', [AccountMemberController::class, 'destroy'])->name('account-members.destroy');

            Route::get('/account-invitations/{invitation}', [AccountInvitationController::class, 'accept'])
                        ->middleware(['signed'])
                        ->name('account-invitations.accept');

            Route::delete('/account-invitations/{invitation}', [AccountInvitationController::class, 'destroy'])
                        ->name('account-invitations.destroy');
        }
    });
});
