<?php

namespace App\Actions\Jetstream;

use Laravel\Jetstream\Contracts\DeletesAccounts;

class DeleteAccount implements DeletesAccounts
{
    /**
     * Delete the given account.
     *
     * @param  mixed  $account
     * @return void
     */
    public function delete($account)
    {
        $account->purge();
    }
}
