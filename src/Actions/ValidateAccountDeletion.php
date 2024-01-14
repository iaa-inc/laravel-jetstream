<?php

namespace Laravel\Jetstream\Actions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ValidateAccountDeletion
{
    /**
     * Validate that the account can be deleted by the given user.
     *
     * @param  mixed  $user
     * @param  mixed  $account
     * @return void
     */
    public function validate($user, $account)
    {
        Gate::forUser($user)->authorize('delete', $account);

        if ($account->personal_account) {
            throw ValidationException::withMessages([
                'account' => __('You may not delete your personal account.'),
            ])->errorBag('deleteAccount');
        }
    }
}
