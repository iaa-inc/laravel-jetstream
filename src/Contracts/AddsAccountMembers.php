<?php

namespace Laravel\Jetstream\Contracts;

interface AddsAccountMembers
{
    /**
     * Add a new account member to the given account.
     *
     * @param  mixed  $user
     * @param  mixed  $account
     * @param  string  $email
     * @return void
     */
    public function add($user, $account, string $email, string $role = null);
}
