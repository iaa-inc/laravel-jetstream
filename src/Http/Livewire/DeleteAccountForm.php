<?php

namespace Laravel\Jetstream\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Actions\ValidateAccountDeletion;
use Laravel\Jetstream\Contracts\DeletesAccounts;
use Laravel\Jetstream\RedirectsActions;
use Livewire\Component;

class DeleteAccountForm extends Component
{
    use RedirectsActions;

    /**
     * The account instance.
     *
     * @var mixed
     */
    public $account;

    /**
     * Indicates if account deletion is being confirmed.
     *
     * @var bool
     */
    public $confirmingAccountDeletion = false;

    /**
     * Mount the component.
     *
     * @param  mixed  $account
     * @return void
     */
    public function mount($account)
    {
        $this->account = $account;
    }

    /**
     * Delete the account.
     *
     * @param  \Laravel\Jetstream\Actions\ValidateAccountDeletion  $validator
     * @param  \Laravel\Jetstream\Contracts\DeletesAccounts  $deleter
     * @return void
     */
    public function deleteAccount(ValidateAccountDeletion $validator, DeletesAccounts $deleter)
    {
        $validator->validate(Auth::user(), $this->account);

        $deleter->delete($this->account);

        return $this->redirectPath($deleter);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('accounts.delete-account-form');
    }
}
