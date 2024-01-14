<?php

namespace Laravel\Jetstream\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Jetstream\Jetstream;

class CurrentAccountController extends Controller
{
    /**
     * Update the authenticated user's current account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $account = Jetstream::newAccountModel()->findOrFail($request->account_id);

        if (! $request->user()->switchAccount($account)) {
            abort(403);
        }

        return redirect(config('fortify.home'), 303);
    }
}
