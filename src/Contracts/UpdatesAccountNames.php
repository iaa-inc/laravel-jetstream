<?php

namespace Laravel\Jetstream\Contracts;

interface UpdatesAccountNames
{
    /**
     * Validate and update the given account's name.
     *
     * @param  mixed  $user
     * @param  mixed  $account
     * @param  array  $input
     * @return void
     */
    public function update($user, $account, array $input);
}
