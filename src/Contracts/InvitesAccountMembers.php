<?php

namespace Laravel\Jetstream\Contracts;

interface InvitesAccountMembers
{
    /**
     * Invite a new account member to the given account.
     *
     * @param  mixed  $user
     * @param  mixed  $account
     * @param  string  $email
     * @return void
     */
    public function invite($user, $account, string $email, string $role = null);
}
