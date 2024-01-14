<?php

namespace App\Actions\Fortify;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]), function (User $user) {
                $this->createAccount($user);
            });
        });
    }

    /**
     * Create a personal account for the user.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function createAccount(User $user)
    {
        $user->ownedAccounts()->save(Account::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Account",
            'personal_account' => true,
        ]));
    }
}
