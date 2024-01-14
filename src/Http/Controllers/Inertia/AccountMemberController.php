<?php

namespace Laravel\Jetstream\Http\Controllers\Inertia;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Jetstream\Actions\UpdateAccountMemberRole;
use Laravel\Jetstream\Contracts\AddsAccountMembers;
use Laravel\Jetstream\Contracts\InvitesAccountMembers;
use Laravel\Jetstream\Contracts\RemovesAccountMembers;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Jetstream;

class AccountMemberController extends Controller
{
    /**
     * Add a new account member to a account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $accountId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $accountId)
    {
        $account = Jetstream::newAccountModel()->findOrFail($accountId);

        if (Features::sendsAccountInvitations()) {
            app(InvitesAccountMembers::class)->invite(
                $request->user(),
                $account,
                $request->email ?: '',
                $request->role
            );
        } else {
            app(AddsAccountMembers::class)->add(
                $request->user(),
                $account,
                $request->email ?: '',
                $request->role
            );
        }

        return back(303);
    }

    /**
     * Update the given account member's role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $accountId
     * @param  int  $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $accountId, $userId)
    {
        app(UpdateAccountMemberRole::class)->update(
            $request->user(),
            Jetstream::newAccountModel()->findOrFail($accountId),
            $userId,
            $request->role
        );

        return back(303);
    }

    /**
     * Remove the given user from the given account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $accountId
     * @param  int  $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $accountId, $userId)
    {
        $account = Jetstream::newAccountModel()->findOrFail($accountId);

        app(RemovesAccountMembers::class)->remove(
            $request->user(),
            $account,
            $user = Jetstream::findUserByIdOrFail($userId)
        );

        if ($request->user()->id === $user->id) {
            return redirect(config('fortify.home'));
        }

        return back(303);
    }
}
