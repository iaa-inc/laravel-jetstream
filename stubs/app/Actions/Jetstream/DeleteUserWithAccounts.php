<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Contracts\DeletesAccounts;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * The account deleter implementation.
     *
     * @var \Laravel\Jetstream\Contracts\DeletesAccounts
     */
    protected $deletesAccounts;

    /**
     * Create a new action instance.
     *
     * @param  \Laravel\Jetstream\Contracts\DeletesAccounts  $deletesAccounts
     * @return void
     */
    public function __construct(DeletesAccounts $deletesAccounts)
    {
        $this->deletesAccounts = $deletesAccounts;
    }

    /**
     * Delete the given user.
     *
     * @param  mixed  $user
     * @return void
     */
    public function delete($user)
    {
        DB::transaction(function () use ($user) {
            $this->deleteAccounts($user);
            $user->deleteProfilePhoto();
            $user->tokens->each->delete();
            $user->delete();
        });
    }

    /**
     * Delete the accounts and account associations attached to the user.
     *
     * @param  mixed  $user
     * @return void
     */
    protected function deleteAccounts($user)
    {
        $user->accounts()->detach();

        $user->ownedAccounts->each(function ($account) {
            $this->deletesAccounts->delete($account);
        });
    }
}
