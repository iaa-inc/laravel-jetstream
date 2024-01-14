<?php

namespace Laravel\Jetstream\Contracts;

interface RemovesAccountMembers
{
    /**
     * Remove the account member from the given account.
     *
     * @param  mixed  $user
     * @param  mixed  $account
     * @param  mixed  $accountMember
     * @return void
     */
    public function remove($user, $account, $accountMember);
}
