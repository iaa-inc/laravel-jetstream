<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\CreatesAccounts;
use Laravel\Jetstream\Events\AddingAccount;
use Laravel\Jetstream\Jetstream;

class CreateAccount implements CreatesAccounts
{
    /**
     * Validate and create a new account for the given user.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return mixed
     */
    public function create($user, array $input)
    {
        Gate::forUser($user)->authorize('create', Jetstream::newAccountModel());

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('createAccount');

        AddingAccount::dispatch($user);

        $user->switchAccount($account = $user->ownedAccounts()->create([
            'name' => $input['name'],
            'personal_account' => false,
        ]));

        return $account;
    }
}
