<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function view(User $user, Account $account)
    {
        return $user->belongsToAccount($account);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function update(User $user, Account $account)
    {
        return $user->ownsAccount($account);
    }

    /**
     * Determine whether the user can add account members.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function addAccountMember(User $user, Account $account)
    {
        return $user->ownsAccount($account);
    }

    /**
     * Determine whether the user can update account member permissions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function updateAccountMember(User $user, Account $account)
    {
        return $user->ownsAccount($account);
    }

    /**
     * Determine whether the user can remove account members.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function removeAccountMember(User $user, Account $account)
    {
        return $user->ownsAccount($account);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function delete(User $user, Account $account)
    {
        return $user->ownsAccount($account);
    }
}
