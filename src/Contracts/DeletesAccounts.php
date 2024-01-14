<?php

namespace Laravel\Jetstream\Contracts;

interface DeletesAccounts
{
    /**
     * Delete the given account.
     *
     * @param  mixed  $account
     * @return void
     */
    public function delete($account);
}
