<?php

namespace Laravel\Jetstream\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Contracts\UpdatesAccountNames;
use Livewire\Component;

class UpdateAccountNameForm extends Component
{
    /**
     * The account instance.
     *
     * @var mixed
     */
    public $account;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [];

    /**
     * Mount the component.
     *
     * @param  mixed  $account
     * @return void
     */
    public function mount($account)
    {
        $this->account = $account;

        $this->state = $account->withoutRelations()->toArray();
    }

    /**
     * Update the account's name.
     *
     * @param  \Laravel\Jetstream\Contracts\UpdatesAccountNames  $updater
     * @return void
     */
    public function updateAccountName(UpdatesAccountNames $updater)
    {
        $this->resetErrorBag();

        $updater->update($this->user, $this->account, $this->state);

        $this->emit('saved');

        $this->emit('refresh-navigation-menu');
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('accounts.update-account-name-form');
    }
}
