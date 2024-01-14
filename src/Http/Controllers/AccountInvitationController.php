<?php

namespace Laravel\Jetstream\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Contracts\AddsAccountMembers;
use Laravel\Jetstream\AccountInvitation;

class AccountInvitationController extends Controller
{
    /**
     * Accept a account invitation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Jetstream\AccountInvitation  $invitation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function accept(Request $request, AccountInvitation $invitation)
    {
        app(AddsAccountMembers::class)->add(
            $invitation->account->owner,
            $invitation->account,
            $invitation->email,
            $invitation->role
        );

        $invitation->delete();

        return redirect(config('fortify.home'))->banner(
            __('Great! You have accepted the invitation to join the :account account.', ['account' => $invitation->account->name]),
        );
    }

    /**
     * Cancel the given account invitation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Jetstream\AccountInvitation  $invitation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, AccountInvitation $invitation)
    {
        if (! Gate::forUser($request->user())->check('removeAccountMember', $invitation->account)) {
            throw new AuthorizationException;
        }

        $invitation->delete();

        return back(303);
    }
}
