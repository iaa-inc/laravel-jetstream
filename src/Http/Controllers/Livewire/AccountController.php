<?php

namespace Laravel\Jetstream\Http\Controllers\Livewire;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Jetstream;

class AccountController extends Controller
{
    /**
     * Show the account management screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $accountId
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $accountId)
    {
        $account = Jetstream::newAccountModel()->findOrFail($accountId);

        if (Gate::denies('view', $account)) {
            abort(403);
        }

        return view('accounts.show', [
            'user' => $request->user(),
            'account' => $account,
        ]);
    }

    /**
     * Show the account creation screen.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        Gate::authorize('create', Jetstream::newAccountModel());

        return view('accounts.create', [
            'user' => $request->user(),
        ]);
    }
}
