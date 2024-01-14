<?php

namespace Laravel\Jetstream\Contracts;

interface CreatesAccounts
{
    /**
     * Validate and create a new account for the given user.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return mixed
     */
    public function create($user, array $input);
}
