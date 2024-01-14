<?php

namespace Laravel\Jetstream\Http\Controllers\Inertia;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Laravel\Jetstream\Actions\ValidateAccountDeletion;
use Laravel\Jetstream\Contracts\CreatesAccounts;
use Laravel\Jetstream\Contracts\DeletesAccounts;
use Laravel\Jetstream\Contracts\UpdatesAccountNames;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\RedirectsActions;

class AccountController extends Controller
{
    use RedirectsActions;

    /**
     * Show the account management screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $accountId
     * @return \Inertia\Response
     */
    public function show(Request $request, $accountId)
    {
        $account = Jetstream::newAccountModel()->findOrFail($accountId);

        Gate::authorize('view', $account);

        return Jetstream::inertia()->render($request, 'Accounts/Show', [
            'account' => $account->load('owner', 'users', 'accountInvitations'),
            'availableRoles' => array_values(Jetstream::$roles),
            'availablePermissions' => Jetstream::$permissions,
            'defaultPermissions' => Jetstream::$defaultPermissions,
            'permissions' => [
                'canAddAccountMembers' => Gate::check('addAccountMember', $account),
                'canDeleteAccount' => Gate::check('delete', $account),
                'canRemoveAccountMembers' => Gate::check('removeAccountMember', $account),
                'canUpdateAccount' => Gate::check('update', $account),
            ],
        ]);
    }

    /**
     * Show the account creation screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Jetstream::newAccountModel());

        return Inertia::render('Accounts/Create');
    }

    /**
     * Create a new account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $creator = app(CreatesAccounts::class);

        $creator->create($request->user(), $request->all());

        return $this->redirectPath($creator);
    }

    /**
     * Update the given account's name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $accountId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $accountId)
    {
        $account = Jetstream::newAccountModel()->findOrFail($accountId);

        app(UpdatesAccountNames::class)->update($request->user(), $account, $request->all());

        return back(303);
    }

    /**
     * Delete the given account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $accountId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $accountId)
    {
        $account = Jetstream::newAccountModel()->findOrFail($accountId);

        app(ValidateAccountDeletion::class)->validate($request->user(), $account);

        $deleter = app(DeletesAccounts::class);

        $deleter->delete($account);

        return $this->redirectPath($deleter);
    }
}
