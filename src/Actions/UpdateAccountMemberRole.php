<?php

namespace Laravel\Jetstream\Actions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Events\AccountMemberUpdated;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Rules\Role;

class UpdateAccountMemberRole
{
    /**
     * Update the role for the given account member.
     *
     * @param  mixed  $user
     * @param  mixed  $account
     * @param  int  $accountMemberId
     * @param  string  $role
     * @return void
     */
    public function update($user, $account, $accountMemberId, string $role)
    {
        Gate::forUser($user)->authorize('updateAccountMember', $account);

        Validator::make([
            'role' => $role,
        ], [
            'role' => ['required', 'string', new Role],
        ])->validate();

        $account->users()->updateExistingPivot($accountMemberId, [
            'role' => $role,
        ]);

        AccountMemberUpdated::dispatch($account->fresh(), Jetstream::findUserByIdOrFail($accountMemberId));
    }
}
