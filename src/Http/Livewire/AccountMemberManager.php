<?php

namespace Laravel\Jetstream\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Actions\UpdateAccountMemberRole;
use Laravel\Jetstream\Contracts\AddsAccountMembers;
use Laravel\Jetstream\Contracts\InvitesAccountMembers;
use Laravel\Jetstream\Contracts\RemovesAccountMembers;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\AccountInvitation;
use Livewire\Component;

class AccountMemberManager extends Component
{
    /**
     * The account instance.
     *
     * @var mixed
     */
    public $account;

    /**
     * Indicates if a user's role is currently being managed.
     *
     * @var bool
     */
    public $currentlyManagingRole = false;

    /**
     * The user that is having their role managed.
     *
     * @var mixed
     */
    public $managingRoleFor;

    /**
     * The current role for the user that is having their role managed.
     *
     * @var string
     */
    public $currentRole;

    /**
     * Indicates if the application is confirming if a user wishes to leave the current account.
     *
     * @var bool
     */
    public $confirmingLeavingAccount = false;

    /**
     * Indicates if the application is confirming if a account member should be removed.
     *
     * @var bool
     */
    public $confirmingAccountMemberRemoval = false;

    /**
     * The ID of the account member being removed.
     *
     * @var int|null
     */
    public $accountMemberIdBeingRemoved = null;

    /**
     * The "add account member" form state.
     *
     * @var array
     */
    public $addAccountMemberForm = [
        'email' => '',
        'role' => null,
    ];

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
     * Add a new account member to a account.
     *
     * @return void
     */
    public function addAccountMember()
    {
        $this->resetErrorBag();

        if (Features::sendsAccountInvitations()) {
            app(InvitesAccountMembers::class)->invite(
                $this->user,
                $this->account,
                $this->addAccountMemberForm['email'],
                $this->addAccountMemberForm['role']
            );
        } else {
            app(AddsAccountMembers::class)->add(
                $this->user,
                $this->account,
                $this->addAccountMemberForm['email'],
                $this->addAccountMemberForm['role']
            );
        }

        $this->addAccountMemberForm = [
            'email' => '',
            'role' => null,
        ];

        $this->account = $this->account->fresh();

        $this->emit('saved');
    }

    /**
     * Cancel a pending account member invitation.
     *
     * @param  int  $invitationId
     * @return void
     */
    public function cancelAccountInvitation($invitationId)
    {
        if (! empty($invitationId)) {
            $model = Jetstream::accountInvitationModel();

            $model::whereKey($invitationId)->delete();
        }

        $this->account = $this->account->fresh();
    }

    /**
     * Allow the given user's role to be managed.
     *
     * @param  int  $userId
     * @return void
     */
    public function manageRole($userId)
    {
        $this->currentlyManagingRole = true;
        $this->managingRoleFor = Jetstream::findUserByIdOrFail($userId);
        $this->currentRole = $this->managingRoleFor->accountRole($this->account)->key;
    }

    /**
     * Save the role for the user being managed.
     *
     * @param  \Laravel\Jetstream\Actions\UpdateAccountMemberRole  $updater
     * @return void
     */
    public function updateRole(UpdateAccountMemberRole $updater)
    {
        $updater->update(
            $this->user,
            $this->account,
            $this->managingRoleFor->id,
            $this->currentRole
        );

        $this->account = $this->account->fresh();

        $this->stopManagingRole();
    }

    /**
     * Stop managing the role of a given user.
     *
     * @return void
     */
    public function stopManagingRole()
    {
        $this->currentlyManagingRole = false;
    }

    /**
     * Remove the currently authenticated user from the account.
     *
     * @param  \Laravel\Jetstream\Contracts\RemovesAccountMembers  $remover
     * @return void
     */
    public function leaveAccount(RemovesAccountMembers $remover)
    {
        $remover->remove(
            $this->user,
            $this->account,
            $this->user
        );

        $this->confirmingLeavingAccount = false;

        $this->account = $this->account->fresh();

        return redirect(config('fortify.home'));
    }

    /**
     * Confirm that the given account member should be removed.
     *
     * @param  int  $userId
     * @return void
     */
    public function confirmAccountMemberRemoval($userId)
    {
        $this->confirmingAccountMemberRemoval = true;

        $this->accountMemberIdBeingRemoved = $userId;
    }

    /**
     * Remove a account member from the account.
     *
     * @param  \Laravel\Jetstream\Contracts\RemovesAccountMembers  $remover
     * @return void
     */
    public function removeAccountMember(RemovesAccountMembers $remover)
    {
        $remover->remove(
            $this->user,
            $this->account,
            $user = Jetstream::findUserByIdOrFail($this->accountMemberIdBeingRemoved)
        );

        $this->confirmingAccountMemberRemoval = false;

        $this->accountMemberIdBeingRemoved = null;

        $this->account = $this->account->fresh();
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
     * Get the available account member roles.
     *
     * @return array
     */
    public function getRolesProperty()
    {
        return array_values(Jetstream::$roles);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('accounts.account-member-manager');
    }
}
